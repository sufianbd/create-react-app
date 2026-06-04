import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import type { PageProps } from '@/types';
import type { ExchangeRate } from '@/types/finance';

interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Props extends PageProps {
    rates: Paginated<ExchangeRate>;
}

export default function ExchangeRatesIndex({ rates }: Props) {
    function handleDelete(id: number) {
        if (!confirm('Delete this exchange rate?')) return;
        router.delete(`/finance/exchange-rates/${id}`);
    }

    return (
        <AppLayout>
            <Head title="Exchange Rates" />
            <div className="mx-auto max-w-5xl space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold text-slate-900">Exchange Rates</h1>
                    <div className="flex items-center gap-3">
                        <Link href="/finance/exchange-rates/report">
                            <Button variant="secondary">Revaluation Report</Button>
                        </Link>
                        <Link href="/finance/exchange-rates/create">
                            <Button>Add Rate</Button>
                        </Link>
                    </div>
                </div>

                {/* Rates Table */}
                <div className="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div className="border-b border-slate-200 bg-slate-50 px-4 py-3 flex items-center justify-between">
                        <h2 className="text-sm font-medium text-slate-700">
                            Rates ({rates.total})
                        </h2>
                    </div>
                    {rates.data.length === 0 ? (
                        <p className="px-4 py-8 text-center text-sm text-slate-500">No exchange rates yet. Add one to get started.</p>
                    ) : (
                        <table className="w-full text-sm">
                            <thead className="bg-slate-50 text-xs text-slate-500 uppercase border-b border-slate-200">
                                <tr>
                                    <th className="px-4 py-2 text-left font-medium">Base</th>
                                    <th className="px-4 py-2 text-left font-medium">Quote</th>
                                    <th className="px-4 py-2 text-right font-medium">Rate</th>
                                    <th className="px-4 py-2 text-left font-medium">Effective Date</th>
                                    <th className="px-4 py-2 text-left font-medium">Source</th>
                                    <th className="px-4 py-2 w-16"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {rates.data.map((rate) => (
                                    <tr key={rate.id} className="hover:bg-slate-50">
                                        <td className="px-4 py-3 font-medium">{rate.base_currency}</td>
                                        <td className="px-4 py-3 font-medium">{rate.quote_currency}</td>
                                        <td className="px-4 py-3 text-right font-mono">{Number(rate.rate).toFixed(6)}</td>
                                        <td className="px-4 py-3 text-slate-600">{rate.effective_date}</td>
                                        <td className="px-4 py-3 text-slate-500">{rate.source ?? '—'}</td>
                                        <td className="px-4 py-3 text-right">
                                            <button
                                                type="button"
                                                onClick={() => handleDelete(rate.id)}
                                                className="text-slate-400 hover:text-red-600 text-xs"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )}
                </div>

                {/* Pagination */}
                {rates.last_page > 1 && (
                    <div className="flex justify-center gap-2">
                        {Array.from({ length: rates.last_page }, (_, i) => i + 1).map((page) => (
                            <Link
                                key={page}
                                href={`/finance/exchange-rates?page=${page}`}
                                className={[
                                    'px-3 py-1 rounded text-sm',
                                    page === rates.current_page
                                        ? 'bg-indigo-600 text-white'
                                        : 'border border-slate-300 text-slate-600 hover:bg-slate-50',
                                ].join(' ')}
                            >
                                {page}
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
