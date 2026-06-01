import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import type { PageProps } from '@/types';
import type { PriceList } from '@/types/finance';

interface Props extends PageProps {
    priceList: PriceList;
}

export default function PriceListShow({ priceList }: Props) {
    function handleDelete() {
        if (!confirm(`Delete "${priceList.name}"? This cannot be undone.`)) return;
        router.delete(`/finance/price-lists/${priceList.id}`);
    }

    return (
        <AppLayout>
            <Head title={priceList.name} />
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-start justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">{priceList.name}</h1>
                        {priceList.description && (
                            <p className="mt-1 text-sm text-slate-500">{priceList.description}</p>
                        )}
                    </div>
                    <div className="flex gap-2">
                        <Link href={`/finance/price-lists`}>
                            <Button variant="secondary">Back to List</Button>
                        </Link>
                        <Button variant="danger" onClick={handleDelete}>Delete</Button>
                    </div>
                </div>

                {/* Header card */}
                <div className="grid grid-cols-3 gap-4">
                    <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                        <p className="text-xs text-slate-500 uppercase tracking-wide">Currency</p>
                        <p className="mt-1 text-xl font-semibold text-slate-900">{priceList.currency_code}</p>
                    </div>
                    <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                        <p className="text-xs text-slate-500 uppercase tracking-wide">Global Discount</p>
                        <p className="mt-1 text-xl font-semibold text-slate-900">{priceList.discount_percent}%</p>
                    </div>
                    <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                        <p className="text-xs text-slate-500 uppercase tracking-wide">Status</p>
                        <p className={`mt-1 text-xl font-semibold ${priceList.is_active ? 'text-green-600' : 'text-slate-500'}`}>
                            {priceList.is_active ? 'Active' : 'Inactive'}
                        </p>
                    </div>
                </div>

                {/* Items table */}
                <div className="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div className="px-4 py-3 border-b border-slate-200">
                        <h2 className="text-base font-semibold text-slate-900">Product Price Overrides</h2>
                    </div>
                    <table className="min-w-full divide-y divide-slate-200">
                        <thead className="bg-slate-50">
                            <tr>
                                <th className="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">SKU</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Product</th>
                                <th className="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Default Price</th>
                                <th className="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Price List Price</th>
                                <th className="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Difference %</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {(!priceList.items || priceList.items.length === 0) && (
                                <tr>
                                    <td colSpan={5} className="px-4 py-6 text-center text-sm text-slate-400">
                                        No product-specific overrides. The global discount applies.
                                    </td>
                                </tr>
                            )}
                            {priceList.items?.map((item) => {
                                const defaultPrice = item.product?.sale_price ?? 0;
                                const diff = defaultPrice > 0
                                    ? ((item.unit_price - defaultPrice) / defaultPrice * 100).toFixed(1)
                                    : '—';
                                const diffNum = parseFloat(diff);

                                return (
                                    <tr key={item.id} className="hover:bg-slate-50">
                                        <td className="px-4 py-3 text-sm font-mono text-slate-600">{item.product?.sku}</td>
                                        <td className="px-4 py-3 text-sm text-slate-900">{item.product?.name}</td>
                                        <td className="px-4 py-3 text-sm text-right text-slate-600">
                                            {defaultPrice.toFixed(2)}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-right font-medium text-slate-900">
                                            {item.unit_price.toFixed(2)}
                                        </td>
                                        <td className={`px-4 py-3 text-sm text-right font-medium ${
                                            isNaN(diffNum) ? 'text-slate-400'
                                                : diffNum < 0 ? 'text-green-600'
                                                : diffNum > 0 ? 'text-red-500'
                                                : 'text-slate-500'
                                        }`}>
                                            {isNaN(diffNum) ? '—' : `${diffNum > 0 ? '+' : ''}${diff}%`}
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>

                {/* Assigned Contacts */}
                <div className="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div className="px-4 py-3 border-b border-slate-200">
                        <h2 className="text-base font-semibold text-slate-900">Assigned Contacts</h2>
                    </div>
                    {(!priceList.contacts || priceList.contacts.length === 0) ? (
                        <p className="px-4 py-6 text-sm text-slate-400">No contacts assigned to this price list.</p>
                    ) : (
                        <ul className="divide-y divide-slate-100">
                            {priceList.contacts.map((c) => (
                                <li key={c.id} className="flex items-center justify-between px-4 py-3">
                                    <span className="text-sm text-slate-900">{c.name}</span>
                                    <span className="text-xs text-slate-400 capitalize">{c.type}</span>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
