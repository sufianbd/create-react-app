import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import type { PageProps } from '@/types';
import type { PriceList } from '@/types/finance';


interface Props extends PageProps {
    priceLists: PriceList[];
}

export default function PriceListIndex({ priceLists }: Props) {
    return (
        <AppLayout>
            <Head title="Price Lists" />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold text-slate-900">Price Lists</h1>
                    <Link href="/finance/price-lists/create"><Button>New Price List</Button></Link>
                </div>

                <div className="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <table className="min-w-full divide-y divide-slate-200">
                        <thead className="bg-slate-50">
                            <tr>
                                <th className="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Currency</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Global Discount %</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Items</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Contacts</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                                <th className="px-4 py-3" />
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {priceLists.length === 0 && (
                                <tr>
                                    <td colSpan={7} className="px-4 py-8 text-center text-sm text-slate-400">
                                        No price lists yet. Create one to get started.
                                    </td>
                                </tr>
                            )}
                            {priceLists.map((pl) => (
                                <tr key={pl.id} className="hover:bg-slate-50">
                                    <td className="px-4 py-3 text-sm font-medium text-slate-900">
                                        <Link href={`/finance/price-lists/${pl.id}`} className="hover:text-indigo-600">
                                            {pl.name}
                                        </Link>
                                    </td>
                                    <td className="px-4 py-3 text-sm text-slate-600">{pl.currency_code}</td>
                                    <td className="px-4 py-3 text-sm text-slate-600">{pl.discount_percent}%</td>
                                    <td className="px-4 py-3 text-sm text-slate-600">{pl.items_count ?? 0}</td>
                                    <td className="px-4 py-3 text-sm text-slate-600">{pl.contacts_count ?? 0}</td>
                                    <td className="px-4 py-3 text-sm">
                                        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${
                                            pl.is_active
                                                ? 'bg-green-50 text-green-700'
                                                : 'bg-slate-100 text-slate-500'
                                        }`}>
                                            {pl.is_active ? 'Active' : 'Inactive'}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3 text-right text-sm">
                                        <Link
                                            href={`/finance/price-lists/${pl.id}`}
                                            className="text-indigo-600 hover:text-indigo-800 font-medium"
                                        >
                                            View
                                        </Link>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
