import { Head } from '@inertiajs/react';
import { useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import type { PageProps } from '@/types';

interface Product {
    id: number;
    name: string;
    sku: string;
    sale_price: number;
}

interface ItemRow {
    product_id: string | number;
    unit_price: string | number;
}

interface Props extends PageProps {
    products: Product[];
}

const CURRENCIES = ['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY', 'INR', 'SGD'];

export default function PriceListCreate({ products }: Props) {
    const { data, setData, post, processing, errors } = useForm<{
        name: string;
        description: string;
        currency_code: string;
        discount_percent: string | number;
        is_active: boolean;
        items: ItemRow[];
    }>({
        name: '',
        description: '',
        currency_code: 'USD',
        discount_percent: 0,
        is_active: true,
        items: [],
    });

    function addItem() {
        setData('items', [...data.items, { product_id: '', unit_price: '' }]);
    }

    function removeItem(index: number) {
        setData('items', data.items.filter((_, i) => i !== index));
    }

    function updateItem(index: number, field: keyof ItemRow, value: string | number) {
        const updated = data.items.map((item, i) =>
            i === index ? { ...item, [field]: value } : item
        );
        setData('items', updated);
    }

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post('/finance/price-lists');
    }

    return (
        <AppLayout>
            <Head title="New Price List" />
            <div className="mx-auto max-w-3xl space-y-6">
                <h1 className="text-2xl font-semibold text-slate-900">New Price List</h1>
                <form onSubmit={submit} className="space-y-6">
                    {/* Header fields */}
                    <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">
                                Name <span className="text-red-500">*</span>
                            </label>
                            <input
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            />
                            {errors.name && <p className="mt-1 text-xs text-red-500">{errors.name}</p>}
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">Description</label>
                            <textarea
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                                rows={2}
                                className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Currency</label>
                                <select
                                    value={data.currency_code}
                                    onChange={(e) => setData('currency_code', e.target.value)}
                                    className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                                >
                                    {CURRENCIES.map((c) => (
                                        <option key={c} value={c}>{c}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">
                                    Global Discount % (0–100)
                                </label>
                                <input
                                    type="number"
                                    min={0}
                                    max={100}
                                    step={0.01}
                                    value={data.discount_percent}
                                    onChange={(e) => setData('discount_percent', e.target.value)}
                                    className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                />
                                {errors.discount_percent && (
                                    <p className="mt-1 text-xs text-red-500">{errors.discount_percent}</p>
                                )}
                            </div>
                        </div>
                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                checked={data.is_active}
                                onChange={(e) => setData('is_active', e.target.checked)}
                                className="rounded border-slate-300 text-indigo-600"
                            />
                            <span className="text-slate-700">Active</span>
                        </label>
                    </div>

                    {/* Product overrides */}
                    <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                        <div className="flex items-center justify-between">
                            <h2 className="text-base font-semibold text-slate-900">Product Price Overrides</h2>
                            <Button type="button" variant="secondary" onClick={addItem}>
                                + Add Product Override
                            </Button>
                        </div>
                        {data.items.length === 0 && (
                            <p className="text-sm text-slate-400">
                                No product overrides. The global discount (if any) will apply to all products.
                            </p>
                        )}
                        {data.items.map((item, index) => (
                            <div key={index} className="flex items-center gap-3">
                                <select
                                    value={item.product_id}
                                    onChange={(e) => updateItem(index, 'product_id', e.target.value)}
                                    className="flex-1 rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                                >
                                    <option value="">Select product...</option>
                                    {products.map((p) => (
                                        <option key={p.id} value={p.id}>
                                            [{p.sku}] {p.name} (default: {p.sale_price})
                                        </option>
                                    ))}
                                </select>
                                <input
                                    type="number"
                                    min={0}
                                    step={0.0001}
                                    placeholder="Unit price"
                                    value={item.unit_price}
                                    onChange={(e) => updateItem(index, 'unit_price', e.target.value)}
                                    className="w-32 rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                />
                                <button
                                    type="button"
                                    onClick={() => removeItem(index)}
                                    className="text-red-500 hover:text-red-700 text-sm font-medium"
                                >
                                    Remove
                                </button>
                            </div>
                        ))}
                    </div>

                    <div className="flex justify-end gap-3">
                        <Button type="button" variant="secondary" onClick={() => history.back()}>Cancel</Button>
                        <Button type="submit" disabled={processing}>Create Price List</Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
