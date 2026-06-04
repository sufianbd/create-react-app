import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Table } from '@/Components/Common/Table';
import { Button } from '@/Components/Common/Button';
import { Pagination } from '@/Components/Inventory/Pagination';
import { usePermission } from '@/Hooks/usePermission';
import type { PageProps } from '@/types';
import type { Paginator } from '@/types/inventory';
import type { JobPosition } from '@/types/hr';

interface Props extends PageProps {
    positions: Paginator<JobPosition>;
}

const STATUS_COLORS: Record<string, string> = {
    draft:   'bg-slate-100 text-slate-700',
    open:    'bg-green-100 text-green-700',
    closed:  'bg-red-100 text-red-700',
    on_hold: 'bg-amber-100 text-amber-700',
};

function StatusBadge({ status }: { status: string }) {
    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${STATUS_COLORS[status] ?? 'bg-slate-100 text-slate-700'}`}>
            {status.replace('_', ' ')}
        </span>
    );
}

const EMPLOYMENT_TYPE_LABELS: Record<string, string> = {
    full_time:  'Full Time',
    part_time:  'Part Time',
    contract:   'Contract',
    internship: 'Internship',
};

export default function JobPositionsIndex({ positions }: Props) {
    const { can } = usePermission();

    return (
        <AppLayout>
            <Head title="Job Positions" />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">Job Positions</h1>
                        <p className="text-sm text-slate-500 mt-1">{positions.total} positions</p>
                    </div>
                    {can('hr.create') && (
                        <Link href="/hr/job-positions/create">
                            <Button>New Position</Button>
                        </Link>
                    )}
                </div>

                <div className="rounded-lg border border-slate-200 bg-white shadow-sm">
                    <Table
                        columns={[
                            {
                                key: 'title',
                                header: 'Title',
                                render: (r) => (
                                    <Link href={`/hr/job-positions/${r.id}`} className="font-medium text-slate-900 hover:text-indigo-600">
                                        {r.title}
                                    </Link>
                                ),
                            },
                            {
                                key: 'department',
                                header: 'Department',
                                render: (r) => (
                                    <span className="text-sm text-slate-700">{r.department?.name ?? '—'}</span>
                                ),
                            },
                            {
                                key: 'employment_type',
                                header: 'Type',
                                render: (r) => (
                                    <span className="text-sm text-slate-700">{EMPLOYMENT_TYPE_LABELS[r.employment_type] ?? r.employment_type}</span>
                                ),
                            },
                            {
                                key: 'openings',
                                header: 'Openings',
                                render: (r) => (
                                    <span className="text-sm text-slate-700">{r.openings}</span>
                                ),
                            },
                            {
                                key: 'applications_count',
                                header: 'Applications',
                                render: (r) => (
                                    <span className="text-sm text-slate-700">{r.applications_count ?? 0}</span>
                                ),
                            },
                            {
                                key: 'status',
                                header: 'Status',
                                render: (r) => <StatusBadge status={r.status} />,
                            },
                            {
                                key: 'actions',
                                header: '',
                                render: (r) => (
                                    <Link href={`/hr/job-positions/${r.id}`} className="text-sm text-indigo-600 hover:text-indigo-800">
                                        View
                                    </Link>
                                ),
                            },
                        ]}
                        data={positions.data}
                        emptyMessage="No job positions found."
                    />
                    <Pagination paginator={positions} />
                </div>
            </div>
        </AppLayout>
    );
}
