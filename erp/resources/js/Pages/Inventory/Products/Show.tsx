import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import { StockLevelBadge } from '@/Components/Inventory/StockLevelBadge';
import { usePermission } from '@/Hooks/usePermission';
import type { PageProps } from '@/types';
import type { Product } from '@/types/inventory';

interface Props extends PageProps {
    product: Product;
}

export default function ProductShow({ product }: Props) {
    const { can } = usePermission();

    function handleDelete() {
        if (!confirm(`Delete "${product.name}"? This cannot be undone.`)) return;
        router.delete(`/inventory/products/${product.id}`);
    }

    return (
        <AppLayout>
            <Head title={product.name} />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Link href="/inventory/products" className="text-sm text-slate-500 hover:text-slate-700">
                            ← Products
                        </Link>
                        <h1 className="text-2xl font-semibold text-slate-900">{product.name}</h1>
                        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${product.is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'}`}>
                            {product.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </div>
                    <div className="flex gap-2">
                        {can('inventory.update') && (
                            <Link href={`/inventory/products/${product.id}/edit`}>
                                <Button variant="secondary" size="sm">Edit</Button>
                            </Link>
                        )}
                        {can('inventory.delete') && (
                            <Button variant="danger" size="sm" onClick={handleDelete}>Delete</Button>
                        )}
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div className="lg:col-span-2 rounded-lg border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                        <h2 className="text-base font-semibold text-slate-900 border-b border-slate-100 pb-2">Details</h2>
                        <dl className="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            <div>
                                <dt className="text-slate-500">SKU</dt>
                                <dd className="font-mono font-medium text-slate-900">{product.sku}</dd>
                            </div>
                            <div>
                                <dt className="text-slate-500">Category</dt>
                                <dd className="font-medium text-slate-900">{product.category?.name ?? '—'}</dd>
                            </div>
                            <div>
                                <dt className="text-slate-500">Unit of Measure</dt>
                                <dd className="font-medium text-slate-900">{product.uom ? `${product.uom.name} (${product.uom.abbreviation})` : '—'}</dd>
                            </div>
                            <div>
                                <dt className="text-slate-500">Reorder Point</dt>
                                <dd className="font-medium text-slate-900">{product.reorder_point}</dd>
                            </div>
                            <div>
                                <dt className="text-slate-500">Cost Price</dt>
                                <dd className="font-medium text-slate-900">${Number(product.cost_price).toFixed(2)}</dd>
                            </div>
                            <div>
                                <dt className="text-slate-500">Sale Price</dt>
                                <dd className="font-medium text-slate-900">${Number(product.sale_price).toFixed(2)}</dd>
                            </div>
                            {product.description && (
                                <div className="col-span-2">
                                    <dt className="text-slate-500">Description</dt>
                                    <dd className="text-slate-900">{product.description}</dd>
                                </div>
                            )}
                        </dl>
                    </div>

                    <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                        <h2 className="text-base font-semibold text-slate-900 border-b border-slate-100 pb-2">Stock Levels</h2>
                        {product.stock_levels && product.stock_levels.length > 0 ? (
                            <ul className="space-y-3">
                                {product.stock_levels.map((sl) => (
                                    <li key={sl.warehouse_id} className="flex items-center justify-between">
                                        <span className="text-sm text-slate-600">{sl.warehouse_name}</span>
                                        <StockLevelBadge quantity={sl.available} reorderPoint={product.reorder_point} />
                                    </li>
                                ))}
                            </ul>
                        ) : (
                            <p className="text-sm text-slate-400">No stock recorded yet.</p>
                        )}
                        <div className="border-t border-slate-100 pt-3 flex items-center justify-between">
                            <span className="text-sm font-medium text-slate-700">Total</span>
                            <StockLevelBadge quantity={product.total_quantity ?? 0} reorderPoint={product.reorder_point} />
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
