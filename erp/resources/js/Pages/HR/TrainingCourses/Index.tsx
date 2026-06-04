import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Table } from '@/Components/Common/Table';
import { Button } from '@/Components/Common/Button';
import { Pagination } from '@/Components/Inventory/Pagination';
import { usePermission } from '@/Hooks/usePermission';
import type { PageProps } from '@/types';
import type { Paginator } from '@/types/inventory';
import type { TrainingCourse } from '@/types/hr';

interface Props extends PageProps {
    courses: Paginator<TrainingCourse>;
}

const TYPE_COLORS: Record<string, string> = {
    internal:      'bg-slate-100 text-slate-700',
    external:      'bg-blue-100 text-blue-700',
    online:        'bg-indigo-100 text-indigo-700',
    certification: 'bg-green-100 text-green-700',
};

function TypeBadge({ type }: { type: string }) {
    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${TYPE_COLORS[type] ?? 'bg-slate-100 text-slate-700'}`}>
            {type}
        </span>
    );
}

export default function TrainingCoursesIndex({ courses }: Props) {
    const { can } = usePermission();

    return (
        <AppLayout>
            <Head title="Training Courses" />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">Training Courses</h1>
                        <p className="text-sm text-slate-500 mt-1">{courses.total} courses</p>
                    </div>
                    {can('hr.create') && (
                        <Link href="/hr/training-courses/create">
                            <Button>New Course</Button>
                        </Link>
                    )}
                </div>

                <div className="rounded-lg border border-slate-200 bg-white shadow-sm">
                    <Table
                        columns={[
                            {
                                key: 'title',
                                header: 'Title',
                                render: (c) => (
                                    <Link href={`/hr/training-courses/${c.id}`} className="font-medium text-slate-900 hover:text-indigo-600">
                                        {c.title}
                                    </Link>
                                ),
                            },
                            {
                                key: 'provider',
                                header: 'Provider',
                                render: (c) => (
                                    <span className="text-sm text-slate-700">{c.provider ?? '—'}</span>
                                ),
                            },
                            {
                                key: 'type',
                                header: 'Type',
                                render: (c) => <TypeBadge type={c.type} />,
                            },
                            {
                                key: 'duration_hours',
                                header: 'Duration',
                                render: (c) => (
                                    <span className="text-sm text-slate-700">
                                        {c.duration_hours != null ? `${c.duration_hours}h` : '—'}
                                    </span>
                                ),
                            },
                            {
                                key: 'is_active',
                                header: 'Active',
                                render: (c) => (
                                    <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${c.is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'}`}>
                                        {c.is_active ? 'Yes' : 'No'}
                                    </span>
                                ),
                            },
                            {
                                key: 'records_count',
                                header: 'Records',
                                render: (c) => (
                                    <span className="text-sm text-slate-700">{c.training_records_count ?? 0}</span>
                                ),
                            },
                            {
                                key: 'actions',
                                header: '',
                                render: (c) => (
                                    <Link href={`/hr/training-courses/${c.id}`} className="text-sm text-indigo-600 hover:text-indigo-800">
                                        View
                                    </Link>
                                ),
                            },
                        ]}
                        data={courses.data}
                        emptyMessage="No training courses found."
                    />
                    <Pagination paginator={courses} />
                </div>
            </div>
        </AppLayout>
    );
}
