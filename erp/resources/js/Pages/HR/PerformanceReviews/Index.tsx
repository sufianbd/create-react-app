import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Table } from '@/Components/Common/Table';
import { Button } from '@/Components/Common/Button';
import { Pagination } from '@/Components/Inventory/Pagination';
import { usePermission } from '@/Hooks/usePermission';
import type { PageProps } from '@/types';
import type { Paginator } from '@/types/inventory';
import type { PerformanceReview } from '@/types/hr';

interface Props extends PageProps {
    reviews: Paginator<PerformanceReview>;
}

const STATUS_COLORS: Record<string, string> = {
    draft:     'bg-slate-100 text-slate-700',
    in_review: 'bg-blue-100 text-blue-700',
    completed: 'bg-green-100 text-green-700',
};

function StatusBadge({ status }: { status: string }) {
    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${STATUS_COLORS[status] ?? 'bg-slate-100 text-slate-700'}`}>
            {status.replace('_', ' ')}
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

export default function PerformanceReviewsIndex({ reviews }: Props) {
    const { can } = usePermission();

    return (
        <AppLayout>
            <Head title="Performance Reviews" />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">Performance Reviews</h1>
                        <p className="text-sm text-slate-500 mt-1">{reviews.total} reviews</p>
                    </div>
                    {can('hr.create') && (
                        <Link href="/hr/performance-reviews/create">
                            <Button>New Review</Button>
                        </Link>
                    )}
                </div>

                <div className="rounded-lg border border-slate-200 bg-white shadow-sm">
                    <Table
                        columns={[
                            {
                                key: 'employee',
                                header: 'Employee',
                                render: (r) => (
                                    <Link href={`/hr/performance-reviews/${r.id}`} className="font-medium text-slate-900 hover:text-indigo-600">
                                        {r.employee ? `${r.employee.first_name} ${r.employee.last_name}` : '—'}
                                    </Link>
                                ),
                            },
                            {
                                key: 'period',
                                header: 'Period',
                                render: (r) => (
                                    <span className="text-sm text-slate-700">
                                        {r.period_start} — {r.period_end}
                                    </span>
                                ),
                            },
                            {
                                key: 'reviewer',
                                header: 'Reviewer',
                                render: (r) => (
                                    <span className="text-sm text-slate-700">
                                        {r.reviewer?.name ?? '—'}
                                    </span>
                                ),
                            },
                            {
                                key: 'overall_rating',
                                header: 'Overall Rating',
                                render: (r) => <StarRating rating={r.overall_rating} />,
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
                                    <Link href={`/hr/performance-reviews/${r.id}`} className="text-sm text-indigo-600 hover:text-indigo-800">
                                        View
                                    </Link>
                                ),
                            },
                        ]}
                        data={reviews.data}
                        emptyMessage="No performance reviews found."
                    />
                    <Pagination paginator={reviews} />
                </div>
            </div>
        </AppLayout>
    );
}
