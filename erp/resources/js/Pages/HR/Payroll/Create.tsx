import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import type { PageProps } from '@/types';

interface EmployeeOption {
    id: number;
    full_name: string;
    position?: string;
    department?: string;
    salary_type: string;
    salary_amount: number | string;
}

interface PayrollItemRow {
    employee_id: number | '';
    gross_salary: string;
    deductions: string;
    notes: string;
}

interface Props extends PageProps {
    employees: EmployeeOption[];
}

const emptyItem = (): PayrollItemRow => ({
    employee_id: '',
    gross_salary: '',
    deductions: '0',
    notes: '',
});

export default function PayrollCreate({ employees }: Props) {
    const [form, setForm] = useState({
        period_start: '',
        period_end: '',
        notes: '',
    });
    const [items, setItems] = useState<PayrollItemRow[]>([emptyItem()]);
    const [errors, setErrors] = useState<Record<string, string>>({});

    function updateItem(idx: number, patch: Partial<PayrollItemRow>) {
        setItems((prev) => prev.map((row, i) => i === idx ? { ...row, ...patch } : row));
    }

    function addItem() {
        setItems((prev) => [...prev, emptyItem()]);
    }

    function removeItem(idx: number) {
        setItems((prev) => prev.filter((_, i) => i !== idx));
    }

    function prefillSalary(idx: number, employeeId: number | '') {
        if (!employeeId) return;
        const emp = employees.find((e) => e.id === employeeId);
        if (emp) {
            updateItem(idx, {
                employee_id: employeeId,
                gross_salary: String(emp.salary_amount ?? ''),
            });
        } else {
            updateItem(idx, { employee_id: employeeId });
        }
    }

    function submit(e: React.FormEvent) {
        e.preventDefault();
        setErrors({});

        router.post('/hr/payroll', { ...form, items } as any, {
            onError: (errs) => setErrors(errs as Record<string, string>),
        });
    }

    const totalGross = items.reduce((s, r) => s + (parseFloat(r.gross_salary) || 0), 0);
    const totalDeductions = items.reduce((s, r) => s + (parseFloat(r.deductions) || 0), 0);
    const totalNet = totalGross - totalDeductions;

    const selectedIds = new Set(items.map((r) => r.employee_id).filter(Boolean));

    return (
        <AppLayout>
            <Head title="New Payroll Run" />
            <div className="space-y-6 max-w-5xl">
                <div>
                    <h1 className="text-2xl font-semibold text-slate-900">New Payroll Run</h1>
                    <p className="text-sm text-slate-500 mt-1">Create a payroll run and add employee pay items.</p>
                </div>

                <form onSubmit={submit} className="space-y-6">
                    {/* Period */}
                    <div className="rounded-lg border border-slate-200 bg-white shadow-sm p-5">
                        <h2 className="text-sm font-semibold text-slate-700 mb-4">Pay Period</h2>
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label className="block text-xs font-medium text-slate-700 mb-1">
                                    Start Date <span className="text-red-500">*</span>
                                </label>
                                <input type="date" value={form.period_start}
                                    onChange={(e) => setForm({ ...form, period_start: e.target.value })}
                                    className="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none" />
                                {errors.period_start && <p className="text-xs text-red-500 mt-1">{errors.period_start}</p>}
                            </div>
                            <div>
                                <label className="block text-xs font-medium text-slate-700 mb-1">
                                    End Date <span className="text-red-500">*</span>
                                </label>
                                <input type="date" value={form.period_end}
                                    onChange={(e) => setForm({ ...form, period_end: e.target.value })}
                                    className="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none" />
                                {errors.period_end && <p className="text-xs text-red-500 mt-1">{errors.period_end}</p>}
                            </div>
                            <div>
                                <label className="block text-xs font-medium text-slate-700 mb-1">Notes</label>
                                <input value={form.notes}
                                    onChange={(e) => setForm({ ...form, notes: e.target.value })}
                                    className="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none" />
                            </div>
                        </div>
                    </div>

                    {/* Pay Items */}
                    <div className="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                        <div className="flex items-center justify-between px-5 py-3 border-b border-slate-200">
                            <h2 className="text-sm font-semibold text-slate-700">Pay Items</h2>
                            <Button type="button" variant="secondary" size="sm" onClick={addItem}>+ Add Row</Button>
                        </div>
                        <table className="w-full text-sm">
                            <thead className="bg-slate-50 text-xs text-slate-500 uppercase">
                                <tr>
                                    <th className="px-4 py-2 text-left font-medium">Employee</th>
                                    <th className="px-4 py-2 text-right font-medium">Gross</th>
                                    <th className="px-4 py-2 text-right font-medium">Deductions</th>
                                    <th className="px-4 py-2 text-right font-medium">Net</th>
                                    <th className="px-4 py-2 text-left font-medium">Notes</th>
                                    <th className="px-4 py-2 w-8"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {items.map((item, idx) => {
                                    const net = (parseFloat(item.gross_salary) || 0) - (parseFloat(item.deductions) || 0);
                                    return (
                                        <tr key={idx}>
                                            <td className="px-4 py-2">
                                                <select
                                                    value={item.employee_id}
                                                    onChange={(e) => prefillSalary(idx, e.target.value ? Number(e.target.value) : '')}
                                                    className="w-full rounded-md border border-slate-300 px-2 py-1 text-sm focus:border-indigo-500 focus:outline-none">
                                                    <option value="">Select…</option>
                                                    {employees.map((emp) => (
                                                        <option key={emp.id} value={emp.id}
                                                            disabled={selectedIds.has(emp.id) && item.employee_id !== emp.id}>
                                                            {emp.full_name}{emp.department ? ` (${emp.department})` : ''}
                                                        </option>
                                                    ))}
                                                </select>
                                            </td>
                                            <td className="px-4 py-2">
                                                <input type="number" min="0" step="0.01" value={item.gross_salary}
                                                    onChange={(e) => updateItem(idx, { gross_salary: e.target.value })}
                                                    className="w-full rounded-md border border-slate-300 px-2 py-1 text-sm text-right focus:border-indigo-500 focus:outline-none" />
                                            </td>
                                            <td className="px-4 py-2">
                                                <input type="number" min="0" step="0.01" value={item.deductions}
                                                    onChange={(e) => updateItem(idx, { deductions: e.target.value })}
                                                    className="w-full rounded-md border border-slate-300 px-2 py-1 text-sm text-right focus:border-indigo-500 focus:outline-none" />
                                            </td>
                                            <td className="px-4 py-2 text-right font-medium text-slate-900">
                                                {net.toLocaleString(undefined, { minimumFractionDigits: 2 })}
                                            </td>
                                            <td className="px-4 py-2">
                                                <input value={item.notes}
                                                    onChange={(e) => updateItem(idx, { notes: e.target.value })}
                                                    className="w-full rounded-md border border-slate-300 px-2 py-1 text-sm focus:border-indigo-500 focus:outline-none" />
                                            </td>
                                            <td className="px-4 py-2">
                                                {items.length > 1 && (
                                                    <button type="button" onClick={() => removeItem(idx)}
                                                        className="text-slate-400 hover:text-red-500 text-lg leading-none">&times;</button>
                                                )}
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                            <tfoot className="bg-slate-50 border-t border-slate-200 text-sm font-semibold">
                                <tr>
                                    <td className="px-4 py-2 text-slate-600">Totals</td>
                                    <td className="px-4 py-2 text-right text-slate-900">
                                        {totalGross.toLocaleString(undefined, { minimumFractionDigits: 2 })}
                                    </td>
                                    <td className="px-4 py-2 text-right text-slate-900">
                                        {totalDeductions.toLocaleString(undefined, { minimumFractionDigits: 2 })}
                                    </td>
                                    <td className="px-4 py-2 text-right text-slate-900">
                                        {totalNet.toLocaleString(undefined, { minimumFractionDigits: 2 })}
                                    </td>
                                    <td colSpan={2}></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div className="flex justify-end gap-3">
                        <Button type="button" variant="secondary" onClick={() => router.visit('/hr/payroll')}>Cancel</Button>
                        <Button type="submit">Create Payroll Run</Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
