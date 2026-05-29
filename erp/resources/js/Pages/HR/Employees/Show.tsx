import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import { EmployeeStatusBadge } from '@/Components/HR/EmployeeStatusBadge';
import { usePermission } from '@/Hooks/usePermission';
import type { PageProps } from '@/types';
import type { Employee, LeaveRequest, LeaveStatus } from '@/types/hr';

interface Props extends PageProps {
    employee: Employee;
    leaveRequests: LeaveRequest[];
}

const LEAVE_STATUS_COLORS: Record<LeaveStatus, string> = {
    pending:  'bg-amber-100 text-amber-700',
    approved: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-600',
};

const EMPLOYMENT_LABELS: Record<string, string> = {
    full_time: 'Full-time',
    part_time: 'Part-time',
    contract:  'Contract',
};

export default function EmployeeShow({ employee, leaveRequests }: Props) {
    const { can } = usePermission();

    return (
        <AppLayout>
            <Head title={employee.full_name} />
            <div className="mx-auto max-w-4xl space-y-6">
                <div className="flex items-start justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">{employee.full_name}</h1>
                        <div className="mt-1 flex items-center gap-3">
                            <EmployeeStatusBadge status={employee.status} />
                            {employee.employee_number && (
                                <span className="font-mono text-sm text-slate-400">{employee.employee_number}</span>
                            )}
                        </div>
                    </div>
                    {can('hr.update') && (
                        <Link href={`/hr/employees/${employee.id}/edit`}>
                            <Button variant="secondary">Edit</Button>
                        </Link>
                    )}
                </div>

                {/* Details */}
                <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 className="text-sm font-semibold text-slate-700 mb-4">Employee Details</h2>
                    <dl className="grid grid-cols-2 gap-x-8 gap-y-4 sm:grid-cols-3">
                        {[
                            { label: 'Position',        value: employee.position ?? '—' },
                            { label: 'Department',      value: employee.department?.name ?? '—' },
                            { label: 'Employment Type', value: EMPLOYMENT_LABELS[employee.employment_type] ?? employee.employment_type },
                            { label: 'Email',           value: employee.email ?? '—' },
                            { label: 'Phone',           value: employee.phone ?? '—' },
                            { label: 'Start Date',      value: employee.start_date },
                            { label: 'End Date',        value: employee.end_date ?? '—' },
                            { label: 'Salary',          value: `${Number(employee.salary_amount).toLocaleString()} (${employee.salary_type})` },
                            { label: 'Linked User',     value: employee.user?.name ?? '—' },
                        ].map(({ label, value }) => (
                            <div key={label}>
                                <dt className="text-xs text-slate-500">{label}</dt>
                                <dd className="mt-0.5 text-sm font-medium text-slate-800">{value}</dd>
                            </div>
                        ))}
                    </dl>
                </div>

                {/* Leave history */}
                <div className="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div className="border-b border-slate-200 bg-slate-50 px-4 py-3">
                        <h2 className="text-sm font-medium text-slate-700">Leave History</h2>
                    </div>
                    {leaveRequests.length === 0 ? (
                        <p className="px-4 py-6 text-sm text-slate-400 text-center">No leave requests.</p>
                    ) : (
                        <table className="w-full text-sm">
                            <thead className="text-xs text-slate-500 uppercase">
                                <tr>
                                    <th className="px-4 py-2 text-left font-medium">Type</th>
                                    <th className="px-4 py-2 text-left font-medium">Period</th>
                                    <th className="px-4 py-2 text-right font-medium">Days</th>
                                    <th className="px-4 py-2 text-left font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {leaveRequests.map((lr) => (
                                    <tr key={lr.id}>
                                        <td className="px-4 py-3">{lr.leave_type ?? '—'}</td>
                                        <td className="px-4 py-3 text-slate-500">{lr.start_date} → {lr.end_date}</td>
                                        <td className="px-4 py-3 text-right">{lr.days}</td>
                                        <td className="px-4 py-3">
                                            <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium capitalize ${LEAVE_STATUS_COLORS[lr.status]}`}>
                                                {lr.status}
                                            </span>
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
