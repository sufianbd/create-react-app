import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import { Input } from '@/Components/Common/Input';
import type { PageProps } from '@/types';
import type { Supplier, Warehouse, Product } from '@/types/inventory';

interface OrderItem {
    product_id: string;
    quantity: string;
    unit_cost: string;
}

interface POFormData {
    supplier_id: string;
    warehouse_id: string;
    expected_date: string;
    notes: string;
    items: OrderItem[];
    [key: string]: string | OrderItem[];
}

interface Props extends PageProps {
    suppliers: Supplier[];
    warehouses: Warehouse[];
    products: Product[];
}

export default function PurchaseOrderCreate({ suppliers, warehouses, products }: Props) {
    const { data, setData, post, processing, errors } = useForm<POFormData>({
        supplier_id: '',
        warehouse_id: '',
        expected_date: '',
        notes: '',
        items: [{ product_id: '', quantity: '', unit_cost: '' }],
    });

    function addItem() {
        setData('items', [...data.items, { product_id: '', quantity: '', unit_cost: '' }]);
    }

    function removeItem(idx: number) {
        setData('items', data.items.filter((_, i) => i !== idx));
    }

    function updateItem(idx: number, key: keyof OrderItem, value: string) {
        const updated = data.items.map((item, i) => i === idx ? { ...item, [key]: value } : item);
        setData('items', updated);
    }

    function handleProductSelect(idx: number, productId: string) {
        const product = products.find((p) => p.id.toString() === productId);
        const updated = data.items.map((item, i) =>
            i === idx ? { ...item, product_id: productId, unit_cost: product ? product.cost_price : '' } : item
        );
        setData('items', updated);
    }

    const total = data.items.reduce((sum, item) => {
        const qty = parseFloat(item.quantity) || 0;
        const cost = parseFloat(item.unit_cost) || 0;
        return sum + qty * cost;
    }, 0);

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post('/inventory/purchase-orders');
    }

    return (
        <AppLayout>
            <Head title="New Purchase Order" />
            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <Link href="/inventory/purchase-orders" className="text-sm text-slate-500 hover:text-slate-700">
                        ← Purchase Orders
                    </Link>
                    <h1 className="text-2xl font-semibold text-slate-900">New Purchase Order</h1>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                        <h2 className="text-base font-semibold text-slate-900">Order Details</h2>
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Supplier *</label>
                                <select value={data.supplier_id} onChange={(e) => setData('supplier_id', e.target.value)} required
                                    className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                    <option value="">Select supplier</option>
                                    {suppliers.filter(s => s.is_active).map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}
                                </select>
                                {(errors as Record<string, string>).supplier_id && <p className="mt-1 text-xs text-red-600">{(errors as Record<string, string>).supplier_id}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Destination Warehouse *</label>
                                <select value={data.warehouse_id} onChange={(e) => setData('warehouse_id', e.target.value)} required
                                    className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                    <option value="">Select warehouse</option>
                                    {warehouses.filter(w => w.is_active).map((w) => <option key={w.id} value={w.id}>{w.name}</option>)}
                                </select>
                                {(errors as Record<string, string>).warehouse_id && <p className="mt-1 text-xs text-red-600">{(errors as Record<string, string>).warehouse_id}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Expected Date</label>
                                <Input type="date" value={data.expected_date} onChange={(e) => setData('expected_date', e.target.value)} />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                                <Input value={data.notes} onChange={(e) => setData('notes', e.target.value)} />
                            </div>
                        </div>
                    </div>

                    <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                        <div className="flex items-center justify-between">
                            <h2 className="text-base font-semibold text-slate-900">Line Items</h2>
                            <Button type="button" variant="secondary" size="sm" onClick={addItem}>+ Add Item</Button>
                        </div>
                        <div className="space-y-3">
                            {data.items.map((item, idx) => (
                                <div key={idx} className="grid grid-cols-12 gap-3 items-end">
                                    <div className="col-span-5">
                                        {idx === 0 && <label className="block text-xs font-medium text-slate-500 mb-1">Product *</label>}
                                        <select value={item.product_id} onChange={(e) => handleProductSelect(idx, e.target.value)} required
                                            className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                            <option value="">Select product</option>
                                            {products.map((p) => <option key={p.id} value={p.id}>{p.name} ({p.sku})</option>)}
                                        </select>
                                    </div>
                                    <div className="col-span-2">
                                        {idx === 0 && <label className="block text-xs font-medium text-slate-500 mb-1">Qty *</label>}
                                        <Input type="number" min="1" step="1" value={item.quantity} onChange={(e) => updateItem(idx, 'quantity', e.target.value)} required placeholder="0" />
                                    </div>
                                    <div className="col-span-3">
                                        {idx === 0 && <label className="block text-xs font-medium text-slate-500 mb-1">Unit Cost *</label>}
                                        <Input type="number" min="0" step="0.01" value={item.unit_cost} onChange={(e) => updateItem(idx, 'unit_cost', e.target.value)} required placeholder="0.00" />
                                    </div>
                                    <div className="col-span-1 text-sm text-slate-600 pb-2">
                                        ${((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_cost) || 0)).toFixed(2)}
                                    </div>
                                    <div className="col-span-1">
                                        {data.items.length > 1 && (
                                            <button type="button" onClick={() => removeItem(idx)} className="pb-2 text-red-400 hover:text-red-600">
                                                ×
                                            </button>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                        <div className="flex justify-end border-t border-slate-200 pt-3">
                            <p className="text-sm font-semibold text-slate-900">Total: ${total.toFixed(2)}</p>
                        </div>
                    </div>

                    <div className="flex gap-3">
                        <Button type="submit" loading={processing}>Create Order (Draft)</Button>
                        <Button type="button" variant="secondary" onClick={() => window.history.back()}>Cancel</Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
