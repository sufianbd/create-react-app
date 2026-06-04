import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Table } from '@/Components/Common/Table';
import { Button } from '@/Components/Common/Button';
import { Pagination } from '@/Components/Inventory/Pagination';
import { usePermission } from '@/Hooks/usePermission';
import type { PageProps } from '@/types';
import type { Paginator } from '@/types/inventory';
import type { JobApplication } from '@/types/hr';

interface Props extends PageProps {
    applications: Paginator<JobApplication>;
}

const STAGE_COLORS: Record<string, string> = {
    applied:   'bg-blue-100 text-blue-700',
    screening: 'bg-purple-100 text-purple-700',
    interview: 'bg-amber-100 text-amber-700',
    offer:     'bg-orange-100 text-orange-700',
    hired:     'bg-green-100 text-green-700',
    rejected:  'bg-red-100 text-red-700',
};

function StageBadge({ stage }: { stage: string }) {
    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${STAGE_COLORS[stage] ?? 'bg-slate-100 text-slate-700'}`}>
            {stage}
        </span>
    );
}

function StarRating({ rating }: { rating: number | null }) {
    if (rating === null) return <span className="text-sm text-slate-400">—</span>;
    return (
        <span className="flex items-center gap-0.5">
            {[1, 2, 3, 4, 5].map((n) => (
                <svg
                    key={n}
                    className={`h-4 w-4 ${n <= rating ? 'text-amber-400' : 'text-slate-200'}`}
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            ))}
        </span>
    );
}

export default function JobApplicationsIndex({ applications }: Props) {
    const { can } = usePermission();

    return (
        <AppLayout>
            <Head title="Job Applications" />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">Job Applications</h1>
                        <p className="text-sm text-slate-500 mt-1">{applications.total} applications</p>
                    </div>
                    {can('hr.create') && (
                        <Link href="/hr/job-applications/create">
                            <Button>New Application</Button>
                        </Link>
                    )}
                </div>

                <div className="rounded-lg border border-slate-200 bg-white shadow-sm">
                    <Table
                        columns={[
                            {
                                key: 'applicant_name',
                                header: 'Applicant',
                                render: (r) => (
                                    <Link href={`/hr/job-applications/${r.id}`} className="font-medium text-slate-900 hover:text-indigo-600">
                                        {r.applicant_name}
                                    </Link>
                                ),
                            },
                            {
                                key: 'applicant_email',
                                header: 'Email',
                                render: (r) => (
                                    <span className="text-sm text-slate-700">{r.applicant_email}</span>
                                ),
                            },
                            {
                                key: 'job_position',
                                header: 'Position',
                                render: (r) => (
                                    <span className="text-sm text-slate-700">{r.job_position?.title ?? '—'}</span>
                                ),
                            },
                            {
                                key: 'stage',
                                header: 'Stage',
                                render: (r) => <StageBadge stage={r.stage} />,
                            },
                            {
                                key: 'source',
                                header: 'Source',
                                render: (r) => (
                                    <span className="text-sm text-slate-700">{r.source ?? '—'}</span>
                                ),
                            },
                            {
                                key: 'rating',
                                header: 'Rating',
                                render: (r) => <StarRating rating={r.rating} />,
                            },
                            {
                                key: 'actions',
                                header: '',
                                render: (r) => (
                                    <Link href={`/hr/job-applications/${r.id}`} className="text-sm text-indigo-600 hover:text-indigo-800">
                                        View
                                    </Link>
                                ),
                            },
                        ]}
                        data={applications.data}
                        emptyMessage="No applications found."
                    />
                    <Pagination paginator={applications} />
                </div>
            </div>
        </AppLayout>
    );
}
