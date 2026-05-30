import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import { PurchaseOrderStatusBadge } from '@/Components/Inventory/PurchaseOrderStatusBadge';
import { usePermission } from '@/Hooks/usePermission';
import type { PageProps } from '@/types';
import type { PurchaseOrder } from '@/types/inventory';

interface Props extends PageProps {
    order: PurchaseOrder;
    transitions: string[];
}

const transitionLabels: Record<string, string> = {
    submitted:  'Submit',
    approved:   'Approve',
    received:   'Mark Received',
    cancelled:  'Cancel',
};

export default function PurchaseOrderShow({ order, transitions }: Props) {
    const { can } = usePermission();

    function handleTransition(status: string) {
        if (status === 'cancelled' && !confirm('Cancel this purchase order?')) return;
        if (status === 'received' && !confirm('Mark as received? This will update stock levels.')) return;
        router.patch(`/inventory/purchase-orders/${order.id}/transition`, { status });
    }

    return (
        <AppLayout>
            <Head title={`PO-${String(order.id).padStart(4, '0')}`} />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Link href="/inventory/purchase-orders" className="text-sm text-slate-500 hover:text-slate-700">
                            ← Purchase Orders
                        </Link>
                        <h1 className="text-2xl font-semibold text-slate-900">PO-{String(order.id).padStart(4, '0')}</h1>
                        <PurchaseOrderStatusBadge status={order.status} />
                    </div>
                    {can('inventory.update') && (transitions.length > 0 || order.status === 'approved') && (
                        <div className="flex gap-2">
                            {order.status === 'approved' ? (
                                <>
                                    <Link href={`/inventory/purchase-orders/${order.id}/receive`}>
                                        <Button variant="primary" size="sm">Receive Items</Button>
                                    </Link>
                                    {transitions.filter((t) => t !== 'received').map((t) => (
                                        <Button key={t} variant={t === 'cancelled' ? 'danger' : 'secondary'} size="sm" onClick={() => handleTransition(t)}>
                                            {transitionLabels[t] ?? t}
                                        </Button>
                                    ))}
                                </>
                            ) : (
                                transitions.map((t) => (
                                    <Button
                                        key={t}
                                        variant={t === 'cancelled' ? 'danger' : t === 'received' ? 'primary' : 'secondary'}
                                        size="sm"
                                        onClick={() => handleTransition(t)}
                                    >
                                        {transitionLabels[t] ?? t}
                                    </Button>
                                ))
                            )}
                        </div>
                    )}
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div className="lg:col-span-2 rounded-lg border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                        <h2 className="text-base font-semibold text-slate-900 border-b border-slate-100 pb-2">Line Items</h2>
                        {order.items && order.items.length > 0 ? (
                            <>
                                <div className="overflow-x-auto">
                                    <table className="min-w-full text-sm">
                                        <thead>
                                            <tr className="border-b border-slate-200">
                                                <th className="pb-2 text-left font-medium text-slate-500">Product</th>
                                                <th className="pb-2 text-right font-medium text-slate-500">Ordered</th>
                                                <th className="pb-2 text-right font-medium text-slate-500">Received</th>
                                                <th className="pb-2 text-right font-medium text-slate-500">Unit Cost</th>
                                                <th className="pb-2 text-right font-medium text-slate-500">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-100">
                                            {order.items.map((item) => (
                                                <tr key={item.id}>
                                                    <td className="py-2">
                                                        <div className="font-medium text-slate-900">{item.product_name}</div>
                                                        <div className="text-xs text-slate-400 font-mono">{item.product_sku}</div>
                                                    </td>
                                                    <td className="py-2 text-right text-slate-700">{Number(item.quantity).toLocaleString()}</td>
                                                    <td className="py-2 text-right text-slate-700">{Number(item.received_quantity).toLocaleString()}</td>
                                                    <td className="py-2 text-right text-slate-700">${Number(item.unit_cost).toFixed(2)}</td>
                                                    <td className="py-2 text-right font-medium text-slate-900">
                                                        ${(item.line_total ?? Number(item.quantity) * Number(item.unit_cost)).toFixed(2)}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                        <tfoot>
                                            <tr className="border-t-2 border-slate-200">
                                                <td colSpan={4} className="pt-3 text-right font-semibold text-slate-700">Total</td>
                                                <td className="pt-3 text-right font-bold text-slate-900">${Number(order.total ?? 0).toFixed(2)}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </>
                        ) : (
                            <p className="text-sm text-slate-400">No line items.</p>
                        )}
                    </div>

                    <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                        <h2 className="text-base font-semibold text-slate-900 border-b border-slate-100 pb-2">Details</h2>
                        <dl className="space-y-3 text-sm">
                            <div>
                                <dt className="text-slate-500">Supplier</dt>
                                <dd className="font-medium text-slate-900">{order.supplier?.name ?? '—'}</dd>
                            </div>
                            <div>
                                <dt className="text-slate-500">Warehouse</dt>
                                <dd className="font-medium text-slate-900">{order.warehouse?.name ?? '—'}</dd>
                            </div>
                            <div>
                                <dt className="text-slate-500">Status</dt>
                                <dd><PurchaseOrderStatusBadge status={order.status} /></dd>
                            </div>
                            <div>
                                <dt className="text-slate-500">Expected Date</dt>
                                <dd className="font-medium text-slate-900">{order.expected_date ? new Date(order.expected_date).toLocaleDateString() : '—'}</dd>
                            </div>
                            {order.notes && (
                                <div>
                                    <dt className="text-slate-500">Notes</dt>
                                    <dd className="text-slate-900">{order.notes}</dd>
                                </div>
                            )}
                            <div>
                                <dt className="text-slate-500">Created By</dt>
                                <dd className="font-medium text-slate-900">{order.created_by ?? '—'}</dd>
                            </div>
                            <div>
                                <dt className="text-slate-500">Created</dt>
                                <dd className="font-medium text-slate-900">{order.created_at ? new Date(order.created_at).toLocaleDateString() : '—'}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
