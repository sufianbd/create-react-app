import { Head, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import { usePermission } from '@/Hooks/usePermission';
import type { PageProps } from '@/types';
import type { PayrollRun } from '@/types/hr';

interface Props extends PageProps {
    run: PayrollRun;
}

function fmt(n: number | string | undefined): string {
    if (n === undefined || n === null) return '—';
    return Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

const STATUS_COLORS: Record<string, string> = {
    draft:     'bg-slate-100 text-slate-600',
    processed: 'bg-green-100 text-green-700',
};

export default function PayrollShow({ run }: Props) {
    const { can } = usePermission();

    return (
        <AppLayout>
            <Head title={`Payroll Run #${run.id}`} />
            <div className="space-y-6 max-w-5xl">
                {/* Header */}
                <div className="flex items-start justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">Payroll Run #{run.id}</h1>
                        <p className="text-sm text-slate-500 mt-1">
                            {run.period_start} → {run.period_end}
                        </p>
                    </div>
                    <div className="flex items-center gap-3">
                        <span className={`inline-flex items-center rounded-full px-3 py-1 text-sm font-medium capitalize ${STATUS_COLORS[run.status] ?? 'bg-slate-100 text-slate-600'}`}>
                            {run.status}
                        </span>
                        {can('hr.update') && run.status === 'draft' && (
                            <Button onClick={() => router.patch(`/hr/payroll/${run.id}/process`)}>
                                Process Run
                            </Button>
                        )}
                    </div>
                </div>

                {/* Summary */}
                <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    {[
                        { label: 'Employees', value: run.items?.length ?? 0 },
                        { label: 'Total Gross', value: fmt(run.total_gross) },
                        { label: 'Total Deductions', value: fmt(run.items?.reduce((s, i) => s + Number(i.deductions), 0)) },
                        { label: 'Total Net', value: fmt(run.total_net) },
                    ].map((s) => (
                        <div key={s.label} className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                            <p className="text-xs font-medium text-slate-500">{s.label}</p>
                            <p className="mt-1 text-xl font-bold text-slate-900">{s.value}</p>
                        </div>
                    ))}
                </div>

                {/* Items table */}
                <div className="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div className="px-5 py-3 border-b border-slate-200">
                        <h2 className="text-sm font-semibold text-slate-700">Pay Items</h2>
                    </div>
                    <table className="w-full text-sm">
                        <thead className="bg-slate-50 border-b border-slate-200 text-xs text-slate-500 uppercase">
                            <tr>
                                <th className="px-4 py-2 text-left font-medium">Employee</th>
                                <th className="px-4 py-2 text-left font-medium">Position</th>
                                <th className="px-4 py-2 text-right font-medium">Gross</th>
                                <th className="px-4 py-2 text-right font-medium">Deductions</th>
                                <th className="px-4 py-2 text-right font-medium">Net</th>
                                <th className="px-4 py-2 text-left font-medium">Notes</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {(run.items ?? []).map((item) => (
                                <tr key={item.id}>
                                    <td className="px-4 py-3 font-medium text-slate-900">
                                        {item.employee?.full_name ?? '—'}
                                    </td>
                                    <td className="px-4 py-3 text-slate-500">
                                        {item.employee?.position ?? '—'}
                                    </td>
                                    <td className="px-4 py-3 text-right text-slate-700">{fmt(item.gross_salary)}</td>
                                    <td className="px-4 py-3 text-right text-slate-600">{fmt(item.deductions)}</td>
                                    <td className="px-4 py-3 text-right font-semibold text-slate-900">{fmt(item.net_salary)}</td>
                                    <td className="px-4 py-3 text-slate-500">{item.notes ?? '—'}</td>
                                </tr>
                            ))}
                            {(run.items ?? []).length === 0 && (
                                <tr>
                                    <td colSpan={6} className="px-4 py-8 text-center text-sm text-slate-400">
                                        No pay items.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                        {(run.items ?? []).length > 0 && (
                            <tfoot className="bg-slate-50 border-t border-slate-200 text-sm font-semibold">
                                <tr>
                                    <td colSpan={2} className="px-4 py-2 text-slate-600">Totals</td>
                                    <td className="px-4 py-2 text-right text-slate-900">{fmt(run.total_gross)}</td>
                                    <td className="px-4 py-2 text-right text-slate-900">
                                        {fmt(run.items?.reduce((s, i) => s + Number(i.deductions), 0))}
                                    </td>
                                    <td className="px-4 py-2 text-right text-slate-900">{fmt(run.total_net)}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        )}
                    </table>
                </div>

                {run.notes && (
                    <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                        <p className="text-xs font-medium text-slate-500 mb-1">Notes</p>
                        <p className="text-sm text-slate-700">{run.notes}</p>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
