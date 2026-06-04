import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import { Table } from '@/Components/Common/Table';
import { usePermission } from '@/Hooks/usePermission';
import type { PageProps } from '@/types';
import type { TrainingCourse, EmployeeTrainingRecord } from '@/types/hr';

interface Props extends PageProps {
    course: TrainingCourse & { training_records: EmployeeTrainingRecord[] };
}

const TYPE_COLORS: Record<string, string> = {
    internal:      'bg-slate-100 text-slate-700',
    external:      'bg-blue-100 text-blue-700',
    online:        'bg-indigo-100 text-indigo-700',
    certification: 'bg-green-100 text-green-700',
};

export default function TrainingCourseShow({ course }: Props) {
    const { can } = usePermission();

    function handleDelete() {
        if (confirm('Delete this training course?')) {
            router.delete(`/hr/training-courses/${course.id}`);
        }
    }

    return (
        <AppLayout>
            <Head title={course.title} />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <nav className="text-sm text-slate-500 mb-1">
                            <Link href="/hr/training-courses" className="hover:text-indigo-600">Training Courses</Link>
                            {' / '}
                            <span>{course.title}</span>
                        </nav>
                        <h1 className="text-2xl font-semibold text-slate-900">{course.title}</h1>
                    </div>
                    <div className="flex items-center gap-2">
                        {can('hr.create') && (
                            <Link href={`/hr/training-records/create?training_course_id=${course.id}`}>
                                <Button variant="secondary">Add Record</Button>
                            </Link>
                        )}
                        {can('hr.delete') && (
                            <Button variant="danger" onClick={handleDelete}>Delete</Button>
                        )}
                    </div>
                </div>

                <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <dl className="grid grid-cols-2 gap-4 sm:grid-cols-3">
                        <div>
                            <dt className="text-xs font-medium text-slate-500 uppercase tracking-wide">Provider</dt>
                            <dd className="mt-1 text-sm text-slate-900">{course.provider ?? '—'}</dd>
                        </div>
                        <div>
                            <dt className="text-xs font-medium text-slate-500 uppercase tracking-wide">Type</dt>
                            <dd className="mt-1">
                                <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${TYPE_COLORS[course.type] ?? 'bg-slate-100 text-slate-700'}`}>
                                    {course.type}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt className="text-xs font-medium text-slate-500 uppercase tracking-wide">Duration</dt>
                            <dd className="mt-1 text-sm text-slate-900">
                                {course.duration_hours != null ? `${course.duration_hours}h` : '—'}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-xs font-medium text-slate-500 uppercase tracking-wide">Status</dt>
                            <dd className="mt-1">
                                <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${course.is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'}`}>
                                    {course.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </dd>
                        </div>
                        {course.description && (
                            <div className="col-span-2 sm:col-span-3">
                                <dt className="text-xs font-medium text-slate-500 uppercase tracking-wide">Description</dt>
                                <dd className="mt-1 text-sm text-slate-900 whitespace-pre-line">{course.description}</dd>
                            </div>
                        )}
                    </dl>
                </div>

                <div>
                    <h2 className="text-lg font-semibold text-slate-900 mb-3">Recent Training Records</h2>
                    <div className="rounded-lg border border-slate-200 bg-white shadow-sm">
                        <Table
                            columns={[
                                {
                                    key: 'employee',
                                    header: 'Employee',
                                    render: (r) => (
                                        <span className="text-sm text-slate-900">
                                            {r.employee ? `${r.employee.first_name} ${r.employee.last_name}` : '—'}
                                        </span>
                                    ),
                                },
                                {
                                    key: 'completed_date',
                                    header: 'Completed',
                                    render: (r) => <span className="text-sm text-slate-700">{r.completed_date}</span>,
                                },
                                {
                                    key: 'passed',
                                    header: 'Passed',
                                    render: (r) => (
                                        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${r.passed ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
                                            {r.passed ? 'Yes' : 'No'}
                                        </span>
                                    ),
                                },
                                {
                                    key: 'actions',
                                    header: '',
                                    render: (r) => (
                                        <Link href={`/hr/training-records/${r.id}`} className="text-sm text-indigo-600 hover:text-indigo-800">
                                            View
                                        </Link>
                                    ),
                                },
                            ]}
                            data={course.training_records ?? []}
                            emptyMessage="No training records yet."
                        />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
