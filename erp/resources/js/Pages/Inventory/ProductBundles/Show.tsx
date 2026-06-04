import { Head, Link, router, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import { usePermission } from '@/Hooks/usePermission';
import type { PageProps } from '@/types';
import type { Product } from '@/types/inventory';

interface Props extends PageProps {
    bundle: Product;
    products: { id: number; name: string; sku: string }[];
}

export default function ProductBundleShow({ bundle, products }: Props) {
    const { can } = usePermission();

    const addForm = useForm({
        component_product_id: '',
        quantity: '1',
    });

    const sufficient = bundle.stock_sufficient_for_bundle ?? true;

    function handleAddItem(e: React.FormEvent) {
        e.preventDefault();
        addForm.post(`/inventory/product-bundles/${bundle.id}/items`, {
            preserveScroll: true,
            onSuccess: () => addForm.reset(),
        });
    }

    function handleRemoveItem(itemId: number) {
        if (!confirm('Remove this component from the bundle?')) return;
        router.delete(`/inventory/product-bundles/${bundle.id}/items/${itemId}`, {
            preserveScroll: true,
        });
    }

    function handleDelete() {
        if (!confirm(`Delete bundle "${bundle.name}"?`)) return;
        router.delete(`/inventory/product-bundles/${bundle.id}`);
    }

    return (
        <AppLayout>
            <Head title={bundle.name} />
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Link
                            href="/inventory/product-bundles"
                            className="text-sm text-slate-500 hover:text-slate-700"
                        >
                            ← Bundles
                        </Link>
                        <h1 className="text-2xl font-semibold text-slate-900">{bundle.name}</h1>
                        <span
                            className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${
                                sufficient
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-red-100 text-red-700'
                            }`}
                        >
                            {sufficient ? 'Stock sufficient' : 'Stock insufficient'}
                        </span>
                    </div>
                    {can('inventory.delete') && (
                        <Button variant="danger" size="sm" onClick={handleDelete}>
                            Delete Bundle
                        </Button>
                    )}
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {/* Bundle details */}
                    <div className="lg:col-span-2 rounded-lg border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                        <h2 className="text-base font-semibold text-slate-900 border-b border-slate-100 pb-2">
                            Bundle Details
                        </h2>
                        <dl className="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            <div>
                                <dt className="text-slate-500">SKU</dt>
                                <dd className="font-medium text-slate-900">{bundle.sku || '—'}</dd>
                            </div>
                            <div>
                                <dt className="text-slate-500">Selling Price</dt>
                                <dd className="font-medium text-slate-900">
                                    {bundle.sale_price
                                        ? `$${Number(bundle.sale_price).toFixed(2)}`
                                        : '—'}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-slate-500">Status</dt>
                                <dd>
                                    <span
                                        className={`inline-flex rounded-full px-2 py-0.5 text-xs font-medium ${
                                            bundle.is_active
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-slate-100 text-slate-500'
                                        }`}
                                    >
                                        {bundle.is_active ? 'Active' : 'Inactive'}
                                    </span>
                                </dd>
                            </div>
                            {bundle.description && (
                                <div className="col-span-2">
                                    <dt className="text-slate-500">Description</dt>
                                    <dd className="text-slate-900">{bundle.description}</dd>
                                </div>
                            )}
                        </dl>
                    </div>

                    {/* Add component form */}
                    {can('inventory.create') && (
                        <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                            <h2 className="text-base font-semibold text-slate-900 border-b border-slate-100 pb-2">
                                Add Component
                            </h2>
                            <form onSubmit={handleAddItem} className="space-y-3">
                                <div>
                                    <label className="block text-xs text-slate-500 mb-1">Product</label>
                                    <select
                                        value={addForm.data.component_product_id}
                                        onChange={(e) =>
                                            addForm.setData('component_product_id', e.target.value)
                                        }
                                        className="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    >
                                        <option value="">Select product…</option>
                                        {products.map((p) => (
                                            <option key={p.id} value={p.id}>
                                                {p.name}{p.sku ? ` (${p.sku})` : ''}
                                            </option>
                                        ))}
                                    </select>
                                    {addForm.errors.component_product_id && (
                                        <p className="mt-1 text-xs text-red-600">
                                            {addForm.errors.component_product_id}
                                        </p>
                                    )}
                                </div>
                                <div>
                                    <label className="block text-xs text-slate-500 mb-1">Quantity</label>
                                    <input
                                        type="number"
                                        min="0.0001"
                                        step="0.0001"
                                        value={addForm.data.quantity}
                                        onChange={(e) => addForm.setData('quantity', e.target.value)}
                                        className="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    />
                                    {addForm.errors.quantity && (
                                        <p className="mt-1 text-xs text-red-600">
                                            {addForm.errors.quantity}
                                        </p>
                                    )}
                                </div>
                                <Button
                                    type="submit"
                                    size="sm"
                                    className="w-full"
                                    disabled={addForm.processing}
                                >
                                    {addForm.processing ? 'Adding…' : 'Add Component'}
                                </Button>
                            </form>
                        </div>
                    )}
                </div>

                {/* Components table */}
                <div className="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div className="border-b border-slate-200 bg-slate-50 px-4 py-3">
                        <h2 className="text-sm font-medium text-slate-700">Components</h2>
                    </div>
                    {bundle.bundle_items && bundle.bundle_items.length > 0 ? (
                        <table className="w-full text-sm">
                            <thead className="text-xs text-slate-500 uppercase">
                                <tr>
                                    <th className="px-4 py-2 text-left font-medium">Component</th>
                                    <th className="px-4 py-2 text-left font-medium">SKU</th>
                                    <th className="px-4 py-2 text-left font-medium">Required Qty</th>
                                    <th className="px-4 py-2 text-left font-medium">Current Stock</th>
                                    <th className="px-4 py-2 text-left font-medium">Stock Status</th>
                                    {can('inventory.delete') && (
                                        <th className="px-4 py-2 w-20"></th>
                                    )}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {bundle.bundle_items.map((item) => {
                                    const stock = Number(item.component_product?.stock_quantity ?? 0);
                                    const required = Number(item.quantity);
                                    const stockOk = stock >= required;
                                    return (
                                        <tr key={item.id} className="hover:bg-slate-50">
                                            <td className="px-4 py-2 font-medium text-slate-900">
                                                {item.component_product?.name ?? '—'}
                                            </td>
                                            <td className="px-4 py-2 text-slate-500">
                                                {item.component_product?.sku || '—'}
                                            </td>
                                            <td className="px-4 py-2 text-slate-700">
                                                {required.toFixed(4).replace(/\.?0+$/, '')}
                                            </td>
                                            <td className="px-4 py-2 text-slate-700">
                                                {stock.toFixed(4).replace(/\.?0+$/, '')}
                                            </td>
                                            <td className="px-4 py-2">
                                                <span
                                                    className={`inline-flex rounded-full px-2 py-0.5 text-xs font-medium ${
                                                        stockOk
                                                            ? 'bg-green-100 text-green-700'
                                                            : 'bg-red-100 text-red-700'
                                                    }`}
                                                >
                                                    {stockOk ? 'OK' : 'Low'}
                                                </span>
                                            </td>
                                            {can('inventory.delete') && (
                                                <td className="px-4 py-2 text-center">
                                                    <button
                                                        onClick={() => handleRemoveItem(item.id)}
                                                        className="text-xs text-red-500 hover:text-red-700"
                                                    >
                                                        Remove
                                                    </button>
                                                </td>
                                            )}
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    ) : (
                        <p className="px-4 py-6 text-center text-sm text-slate-400">
                            No components added yet.
                        </p>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
