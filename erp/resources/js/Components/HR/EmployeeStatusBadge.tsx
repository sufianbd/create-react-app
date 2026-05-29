import type { EmployeeStatus } from '@/types/hr';

const map: Record<EmployeeStatus, string> = {
    active:     'bg-green-100 text-green-700',
    on_leave:   'bg-amber-100 text-amber-700',
    terminated: 'bg-red-100 text-red-600',
};

const labels: Record<EmployeeStatus, string> = {
    active:     'Active',
    on_leave:   'On Leave',
    terminated: 'Terminated',
};

export function EmployeeStatusBadge({ status }: { status: EmployeeStatus }) {
    return (
        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${map[status] ?? 'bg-slate-100 text-slate-500'}`}>
            {labels[status] ?? status}
        </span>
    );
}
