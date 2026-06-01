import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { BudgetStatusBadge } from '@/Components/Finance/BudgetStatusBadge';
import type { PageProps } from '@/types';

interface BudgetLine {
    id: number;
    account_id: number;
    account_code: string;
    account_name: string;
    account_type: string;
    period: number;
    budget: number;
    actual: number;
    variance: number;
    variance_pct: number | null;
}

interface Budget {
    id: number;
    name: string;
    year: number;
    period_type: string;
    status: 'draft' | 'active' | 'archived';
    notes: string | null;
}

interface Props extends PageProps {
    budget: Budget;
    lines: BudgetLine[];
    total_budget: number;
    total_actual: number;
    total_variance: number;
}

export default function Show({ budget, lines, total_budget, total_actual, total_variance }: Props) {
    function handleDelete() {
        if (!confirm(`Delete budget "${budget.name}"?`)) return;
        router.delete(`/finance/budgets/${budget.id}`);
    }

    return (
        <AppLayout>
            <Head title={`Budget: ${budget.name}`} />
            <div className="mx-auto max-w-6xl space-y-6">
                {/* Header */}
                <div className="flex items-start justify-between">
                    <div>
                        <div className="flex items-center gap-3">
                            <h1 className="text-2xl font-semibold text-slate-900">{budget.name}</h1>
                            <BudgetStatusBadge status={budget.status} />
                        </div>
                        <p className="mt-1 text-sm text-slate-500">
                            {budget.year} &mdash; <span className="capitalize">{budget.period_type}</span>
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        {budget.status === 'draft' && (
                            <button
                                onClick={handleDelete}
                                className="rounded-md border border-red-300 px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50"
                            >
                                Delete
                            </button>
                        )}
                        <Link href="/finance/budgets" className="rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            &larr; Back
                        </Link>
                    </div>
                </div>

                {/* Budget info card */}
                <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <dl className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div>
                            <dt className="text-xs font-medium text-slate-500 uppercase">Year</dt>
                            <dd className="mt-1 text-sm font-semibold text-slate-900">{budget.year}</dd>
                        </div>
                        <div>
                            <dt className="text-xs font-medium text-slate-500 uppercase">Period Type</dt>
                            <dd className="mt-1 text-sm font-semibold text-slate-900 capitalize">{budget.period_type}</dd>
                        </div>
                        <div>
                            <dt className="text-xs font-medium text-slate-500 uppercase">Status</dt>
                            <dd className="mt-1"><BudgetStatusBadge status={budget.status} /></dd>
                        </div>
                        {budget.notes && (
                            <div className="col-span-2">
                                <dt className="text-xs font-medium text-slate-500 uppercase">Notes</dt>
                                <dd className="mt-1 text-sm text-slate-700">{budget.notes}</dd>
                            </div>
                        )}
                    </dl>
                </div>

                {/* Variance table */}
                <div className="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h2 className="text-base font-semibold text-slate-900">Budget vs Actuals</h2>
                        <p className="mt-0.5 text-xs text-slate-500">Green = on track (variance &ge; 0), Red = over budget (variance &lt; 0)</p>
                    </div>
                    <table className="w-full text-sm">
                        <thead className="bg-slate-50 text-xs text-slate-500 uppercase border-b border-slate-200">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium w-24">Code</th>
                                <th className="px-4 py-3 text-left font-medium">Account</th>
                                <th className="px-4 py-3 text-left font-medium w-20">Type</th>
                                <th className="px-4 py-3 text-right font-medium w-28">Budget</th>
                                <th className="px-4 py-3 text-right font-medium w-28">Actual</th>
                                <th className="px-4 py-3 text-right font-medium w-28">Variance</th>
                                <th className="px-4 py-3 text-right font-medium w-20">Var %</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {lines.length === 0 ? (
                                <tr>
                                    <td colSpan={7} className="px-4 py-8 text-center text-slate-400">
                                        No budget lines found.
                                    </td>
                                </tr>
                            ) : lines.map((line) => {
                                const isOnTrack = line.variance >= 0;
                                return (
                                    <tr key={line.id} className={isOnTrack ? 'bg-green-50 hover:bg-green-100' : 'bg-red-50 hover:bg-red-100'}>
                                        <td className="px-4 py-3 font-mono text-slate-600">{line.account_code}</td>
                                        <td className="px-4 py-3 text-slate-800">{line.account_name}</td>
                                        <td className="px-4 py-3 capitalize text-slate-600">{line.account_type}</td>
                                        <td className="px-4 py-3 text-right text-slate-700">{line.budget.toFixed(2)}</td>
                                        <td className="px-4 py-3 text-right text-slate-700">{line.actual.toFixed(2)}</td>
                                        <td className={`px-4 py-3 text-right font-medium ${isOnTrack ? 'text-green-700' : 'text-red-700'}`}>
                                            {line.variance.toFixed(2)}
                                        </td>
                                        <td className={`px-4 py-3 text-right ${isOnTrack ? 'text-green-700' : 'text-red-700'}`}>
                                            {line.variance_pct !== null ? `${line.variance_pct}%` : '—'}
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                        <tfoot className="border-t-2 border-slate-200 bg-slate-50 font-semibold">
                            <tr>
                                <td colSpan={3} className="px-4 py-3 text-slate-900">Totals</td>
                                <td className="px-4 py-3 text-right text-slate-900">{total_budget.toFixed(2)}</td>
                                <td className="px-4 py-3 text-right text-slate-900">{total_actual.toFixed(2)}</td>
                                <td className={`px-4 py-3 text-right ${total_variance >= 0 ? 'text-green-700' : 'text-red-700'}`}>
                                    {total_variance.toFixed(2)}
                                </td>
                                <td className="px-4 py-3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
