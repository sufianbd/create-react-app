import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import type { PageProps } from '@/types';

interface Account {
    id: number;
    code: string;
    name: string;
    type: string;
}

interface LineItem {
    account_id: string | number;
    period: number;
    amount: string | number;
    notes?: string;
}

interface Props extends PageProps {
    accounts: Account[];
}

export default function Create({ accounts }: Props) {
    const { data, setData, post, processing, errors } = useForm<{
        name: string;
        year: number;
        period_type: string;
        notes: string;
        lines: LineItem[];
    }>({
        name: '',
        year: new Date().getFullYear(),
        period_type: 'annual',
        notes: '',
        lines: [{ account_id: '', period: 0, amount: '' }],
    });

    function addLine() {
        setData('lines', [...data.lines, { account_id: '', period: 0, amount: '' }]);
    }

    function removeLine(idx: number) {
        setData('lines', data.lines.filter((_, i) => i !== idx));
    }

    function updateLine(idx: number, field: keyof LineItem, value: string | number) {
        const updated = data.lines.map((line, i) =>
            i === idx ? { ...line, [field]: value } : line
        );
        setData('lines', updated);
    }

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post('/finance/budgets');
    }

    return (
        <AppLayout>
            <Head title="New Budget" />
            <div className="mx-auto max-w-3xl space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">New Budget</h1>
                        <p className="mt-1 text-sm text-slate-500">Set expected revenue and expense amounts per account</p>
                    </div>
                    <Link href="/finance/budgets" className="text-sm text-slate-600 hover:text-slate-900">
                        &larr; Back to Budgets
                    </Link>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Budget Header */}
                    <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                        <h2 className="text-base font-semibold text-slate-900">Budget Details</h2>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="col-span-2">
                                <label className="block text-sm font-medium text-slate-700 mb-1">Name <span className="text-red-500">*</span></label>
                                <input
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                                    placeholder="e.g. FY2026 Annual Budget"
                                />
                                {errors.name && <p className="mt-1 text-xs text-red-600">{errors.name}</p>}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Year <span className="text-red-500">*</span></label>
                                <input
                                    type="number"
                                    value={data.year}
                                    onChange={(e) => setData('year', parseInt(e.target.value))}
                                    min={2000}
                                    max={2100}
                                    className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                                />
                                {errors.year && <p className="mt-1 text-xs text-red-600">{errors.year}</p>}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Period Type <span className="text-red-500">*</span></label>
                                <select
                                    value={data.period_type}
                                    onChange={(e) => setData('period_type', e.target.value)}
                                    className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                                >
                                    <option value="annual">Annual</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                </select>
                                {errors.period_type && <p className="mt-1 text-xs text-red-600">{errors.period_type}</p>}
                            </div>

                            <div className="col-span-2">
                                <label className="block text-sm font-medium text-slate-700 mb-1">Notes</label>
                                <textarea
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    rows={2}
                                    className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                                    placeholder="Optional notes about this budget"
                                />
                            </div>
                        </div>
                    </div>

                    {/* Budget Lines */}
                    <div className="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div className="border-b border-slate-200 px-6 py-4 flex items-center justify-between">
                            <h2 className="text-base font-semibold text-slate-900">Budget Lines</h2>
                            <button
                                type="button"
                                onClick={addLine}
                                className="text-sm text-indigo-600 hover:text-indigo-800 font-medium"
                            >
                                + Add Line
                            </button>
                        </div>

                        {errors.lines && <p className="px-6 py-2 text-xs text-red-600">{errors.lines}</p>}

                        <table className="w-full text-sm">
                            <thead className="bg-slate-50 text-xs text-slate-500 uppercase border-b border-slate-200">
                                <tr>
                                    <th className="px-4 py-2 text-left font-medium">Account</th>
                                    <th className="px-4 py-2 text-left font-medium w-24">Period</th>
                                    <th className="px-4 py-2 text-right font-medium w-32">Amount</th>
                                    <th className="px-4 py-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {data.lines.map((line, idx) => (
                                    <tr key={idx}>
                                        <td className="px-4 py-2">
                                            <select
                                                value={line.account_id}
                                                onChange={(e) => updateLine(idx, 'account_id', e.target.value)}
                                                className="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm focus:border-indigo-500 focus:outline-none"
                                            >
                                                <option value="">Select account…</option>
                                                {accounts.map((account) => (
                                                    <option key={account.id} value={account.id}>
                                                        {account.code} — {account.name} ({account.type})
                                                    </option>
                                                ))}
                                            </select>
                                        </td>
                                        <td className="px-4 py-2">
                                            <input
                                                type="number"
                                                value={line.period}
                                                onChange={(e) => updateLine(idx, 'period', parseInt(e.target.value))}
                                                min={0}
                                                max={12}
                                                className="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm focus:border-indigo-500 focus:outline-none"
                                                placeholder="0"
                                            />
                                        </td>
                                        <td className="px-4 py-2">
                                            <input
                                                type="number"
                                                value={line.amount}
                                                onChange={(e) => updateLine(idx, 'amount', e.target.value)}
                                                min={0}
                                                step="0.01"
                                                className="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm text-right focus:border-indigo-500 focus:outline-none"
                                                placeholder="0.00"
                                            />
                                        </td>
                                        <td className="px-4 py-2 text-center">
                                            {data.lines.length > 1 && (
                                                <button
                                                    type="button"
                                                    onClick={() => removeLine(idx)}
                                                    className="text-slate-400 hover:text-red-600"
                                                    aria-label="Remove line"
                                                >
                                                    &times;
                                                </button>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Actions */}
                    <div className="flex items-center justify-end gap-3">
                        <Link href="/finance/budgets" className="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {processing ? 'Saving…' : 'Create Budget'}
                        </button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
