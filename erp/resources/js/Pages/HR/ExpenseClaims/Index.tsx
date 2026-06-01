import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Table } from '@/Components/Common/Table';
import { Button } from '@/Components/Common/Button';
import { Pagination } from '@/Components/Inventory/Pagination';
import { ExpenseStatusBadge } from '@/Components/HR/ExpenseStatusBadge';
import { usePermission } from '@/Hooks/usePermission';
import type { PageProps } from '@/types';
import type { Paginator } from '@/types/inventory';

export type ExpenseStatus = 'draft' | 'submitted' | 'approved' | 'rejected' | 'reimbursed';

interface ExpenseClaim {
    id: number;
    employee_id: number;
    employee_name?: string;
    employee?: { id: number; full_name: string } | null;
    title: string;
    category: string;
    amount: number;
    currency_code: string;
    expense_date: string;
    status: ExpenseStatus;
    created_at?: string;
}

interface Props extends PageProps {
    claims: Paginator<ExpenseClaim>;
    filters: { status?: string };
    categories: string[];
}

const STATUS_TABS: Array<{ value: ExpenseStatus | ''; label: string }> = [
    { value: '', label: 'All' },
    { value: 'draft', label: 'Draft' },
    { value: 'submitted', label: 'Submitted' },
    { value: 'approved', label: 'Approved' },
    { value: 'rejected', label: 'Rejected' },
    { value: 'reimbursed', label: 'Reimbursed' },
];

export default function ExpenseClaimsIndex({ claims, filters }: Props) {
    const { can } = usePermission();

    function setStatus(status: string) {
        router.get('/hr/expense-claims', { ...filters, status: status || undefined }, { preserveState: true, replace: true });
    }

    return (
        <AppLayout>
            <Head title="Expense Claims" />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">Expense Claims</h1>
                        <p className="text-sm text-slate-500 mt-1">{claims.total} claims</p>
                    </div>
                    {can('hr.create') && (
                        <Link href="/hr/expense-claims/create">
                            <Button>New Expense Claim</Button>
                        </Link>
                    )}
                </div>

                {/* Status tabs */}
                <div className="flex gap-1 border-b border-slate-200">
                    {STATUS_TABS.map((tab) => (
                        <button
                            key={tab.value}
                            onClick={() => setStatus(tab.value)}
                            className={[
                                'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
                                (filters.status ?? '') === tab.value
                                    ? 'border-indigo-600 text-indigo-700'
                                    : 'border-transparent text-slate-500 hover:text-slate-700',
                            ].join(' ')}
                        >
                            {tab.label}
                        </button>
                    ))}
                </div>

                <div className="rounded-lg border border-slate-200 bg-white shadow-sm">
                    <Table
                        columns={[
                            {
                                key: 'employee',
                                header: 'Employee',
                                render: (r) => (
                                    <Link href={`/hr/expense-claims/${r.id}`} className="font-medium text-slate-900 hover:text-indigo-600">
                                        {r.employee?.full_name ?? r.employee_name ?? '—'}
                                    </Link>
                                ),
                            },
                            {
                                key: 'title',
                                header: 'Title',
                                render: (r) => <span className="text-sm text-slate-700">{r.title}</span>,
                            },
                            {
                                key: 'category',
                                header: 'Category',
                                render: (r) => <span className="text-sm text-slate-700 capitalize">{r.category}</span>,
                            },
                            {
                                key: 'amount',
                                header: 'Amount',
                                render: (r) => (
                                    <span className="text-sm font-medium text-slate-900">
                                        {r.currency_code} {Number(r.amount).toFixed(2)}
                                    </span>
                                ),
                            },
                            {
                                key: 'expense_date',
                                header: 'Date',
                                render: (r) => <span className="text-sm text-slate-500">{r.expense_date}</span>,
                            },
                            {
                                key: 'status',
                                header: 'Status',
                                render: (r) => <ExpenseStatusBadge status={r.status} />,
                            },
                            {
                                key: 'actions',
                                header: '',
                                render: (r) => (
                                    <Link href={`/hr/expense-claims/${r.id}`} className="text-sm text-indigo-600 hover:text-indigo-800">
                                        View
                                    </Link>
                                ),
                            },
                        ]}
                        data={claims.data}
                        emptyMessage="No expense claims found."
                    />
                    <Pagination paginator={claims} />
                </div>
            </div>
        </AppLayout>
    );
}
