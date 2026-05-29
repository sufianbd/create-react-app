import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Table } from '@/Components/Common/Table';
import { Button } from '@/Components/Common/Button';
import { PurchaseOrderStatusBadge } from '@/Components/Inventory/PurchaseOrderStatusBadge';
import { Pagination } from '@/Components/Inventory/Pagination';
import { usePermission } from '@/Hooks/usePermission';
import type { PageProps } from '@/types';
import type { PurchaseOrder, Paginator } from '@/types/inventory';

interface Props extends PageProps {
    orders: Paginator<PurchaseOrder>;
    filters: { status?: string };
}

export default function PurchaseOrdersIndex({ orders, filters }: Props) {
    const { can } = usePermission();

    function handleFilter(status: string) {
        router.get('/inventory/purchase-orders', { status: status || undefined }, { preserveState: true, replace: true });
    }

    return (
        <AppLayout>
            <Head title="Purchase Orders" />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">Purchase Orders</h1>
                        <p className="text-sm text-slate-500 mt-1">{orders.total} orders</p>
                    </div>
                    {can('inventory.create') && (
                        <Link href="/inventory/purchase-orders/create">
                            <Button>New Order</Button>
                        </Link>
                    )}
                </div>

                <div className="rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-4 py-3 flex gap-2">
                        {['', 'draft', 'submitted', 'approved', 'received', 'cancelled'].map((s) => (
                            <button
                                key={s}
                                onClick={() => handleFilter(s)}
                                className={`rounded-md px-3 py-1.5 text-xs font-medium transition-colors ${(filters.status ?? '') === s ? 'bg-indigo-100 text-indigo-700' : 'text-slate-600 hover:bg-slate-100'}`}
                            >
                                {s === '' ? 'All' : s.charAt(0).toUpperCase() + s.slice(1)}
                            </button>
                        ))}
                    </div>
                    <Table
                        columns={[
                            { key: 'id', header: '#', render: (o) => (
                                <Link href={`/inventory/purchase-orders/${o.id}`} className="font-mono text-indigo-600 hover:text-indigo-800">
                                    PO-{String(o.id).padStart(4, '0')}
                                </Link>
                            )},
                            { key: 'supplier', header: 'Supplier', render: (o) => o.supplier?.name ?? '—' },
                            { key: 'warehouse', header: 'Warehouse', render: (o) => o.warehouse?.name ?? '—' },
                            { key: 'status', header: 'Status', render: (o) => <PurchaseOrderStatusBadge status={o.status} /> },
                            { key: 'total', header: 'Total', render: (o) => o.total != null ? `$${Number(o.total).toFixed(2)}` : '—' },
                            { key: 'expected_date', header: 'Expected', render: (o) => o.expected_date ? new Date(o.expected_date).toLocaleDateString() : '—' },
                            { key: 'created_at', header: 'Created', render: (o) => o.created_at ? new Date(o.created_at).toLocaleDateString() : '—' },
                            { key: 'actions', header: '', render: (o) => (
                                <Link href={`/inventory/purchase-orders/${o.id}`} className="text-sm text-indigo-600 hover:text-indigo-800">
                                    View
                                </Link>
                            )},
                        ]}
                        data={orders.data}
                        emptyMessage="No purchase orders found."
                    />
                    <Pagination paginator={orders} />
                </div>
            </div>
        </AppLayout>
    );
}
