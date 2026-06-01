import { Head, useForm } from '@inertiajs/react';
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
    const { data, setData, post, delete: destroy, processing, errors, reset } = useForm({
        currency_code: '',
        rate: '',
        date: new Date().toISOString().slice(0, 10),
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post('/finance/exchange-rates', {
            onSuccess: () => reset(),
        });
    }

    function handleDelete(id: number) {
        if (!confirm('Delete this exchange rate?')) return;
        destroy(`/finance/exchange-rates/${id}`);
    }

    return (
        <AppLayout>
            <Head title="Exchange Rates" />
            <div className="mx-auto max-w-4xl space-y-6">
                <h1 className="text-2xl font-semibold text-slate-900">Exchange Rates</h1>

                {/* Add Rate Form */}
                <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 className="text-sm font-semibold text-slate-700 mb-4">Add / Update Rate</h2>
                    <form onSubmit={submit} className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div>
                            <label className="block text-xs font-medium text-slate-700 mb-1">
                                Currency Code <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                maxLength={3}
                                placeholder="EUR"
                                value={data.currency_code}
                                onChange={(e) => setData('currency_code', e.target.value.toUpperCase())}
                                className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm uppercase focus:border-indigo-500 focus:outline-none"
                            />
                            {errors.currency_code && (
                                <p className="text-xs text-red-600 mt-1">{errors.currency_code}</p>
                            )}
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-slate-700 mb-1">
                                Rate (per 1 unit) <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                min="0.000001"
                                step="0.000001"
                                placeholder="1.080000"
                                value={data.rate}
                                onChange={(e) => setData('rate', e.target.value)}
                                className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                            />
                            {errors.rate && (
                                <p className="text-xs text-red-600 mt-1">{errors.rate}</p>
                            )}
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-slate-700 mb-1">
                                Date <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                value={data.date}
                                onChange={(e) => setData('date', e.target.value)}
                                className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                            />
                            {errors.date && (
                                <p className="text-xs text-red-600 mt-1">{errors.date}</p>
                            )}
                        </div>
                        <div className="flex items-end">
                            <Button type="submit" disabled={processing} className="w-full">
                                Save Rate
                            </Button>
                        </div>
                    </form>
                </div>

                {/* Rates Table */}
                <div className="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div className="border-b border-slate-200 bg-slate-50 px-4 py-3">
                        <h2 className="text-sm font-medium text-slate-700">
                            Rates ({rates.total})
                        </h2>
                    </div>
                    {rates.data.length === 0 ? (
                        <p className="px-4 py-8 text-center text-sm text-slate-500">No exchange rates yet.</p>
                    ) : (
                        <table className="w-full text-sm">
                            <thead className="bg-slate-50 text-xs text-slate-500 uppercase border-b border-slate-200">
                                <tr>
                                    <th className="px-4 py-2 text-left font-medium">Currency</th>
                                    <th className="px-4 py-2 text-right font-medium">Rate (USD/unit)</th>
                                    <th className="px-4 py-2 text-left font-medium">Date</th>
                                    <th className="px-4 py-2 w-16"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {rates.data.map((rate) => (
                                    <tr key={rate.id} className="hover:bg-slate-50">
                                        <td className="px-4 py-3 font-medium">{rate.currency_code}</td>
                                        <td className="px-4 py-3 text-right">{Number(rate.rate).toFixed(6)}</td>
                                        <td className="px-4 py-3 text-slate-500">{rate.date}</td>
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
            </div>
        </AppLayout>
    );
}
