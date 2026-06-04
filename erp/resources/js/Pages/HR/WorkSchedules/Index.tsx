import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import { usePermission } from '@/Hooks/usePermission';
import type { PageProps } from '@/types';
import type { WorkSchedule } from '@/types/hr';

interface Props extends PageProps {
    schedules: WorkSchedule[];
}

export default function WorkSchedulesIndex({ schedules }: Props) {
    const { can } = usePermission();

    function deleteSchedule(id: number) {
        if (confirm('Delete this work schedule? This cannot be undone.')) {
            router.delete(`/hr/work-schedules/${id}`);
        }
    }

    return (
        <AppLayout>
            <Head title="Work Schedules" />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">Work Schedules</h1>
                        <p className="text-sm text-slate-500 mt-1">{schedules.length} schedule{schedules.length !== 1 ? 's' : ''}</p>
                    </div>
                    {can('hr.create') && (
                        <Link href="/hr/work-schedules/create">
                            <Button>New Schedule</Button>
                        </Link>
                    )}
                </div>

                <div className="rounded-lg border border-slate-200 bg-white shadow-sm divide-y divide-slate-200">
                    {schedules.length === 0 ? (
                        <p className="p-6 text-sm text-slate-500">No work schedules found.</p>
                    ) : (
                        schedules.map((schedule) => (
                            <div key={schedule.id} className="flex items-center justify-between p-4">
                                <div className="flex items-center gap-3">
                                    <Link
                                        href={`/hr/work-schedules/${schedule.id}`}
                                        className="font-medium text-slate-900 hover:text-indigo-600"
                                    >
                                        {schedule.name}
                                    </Link>
                                    {schedule.is_default && (
                                        <span className="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-700">
                                            Default
                                        </span>
                                    )}
                                </div>
                                <div className="flex items-center gap-3">
                                    <Link
                                        href={`/hr/work-schedules/${schedule.id}`}
                                        className="text-sm text-indigo-600 hover:text-indigo-800"
                                    >
                                        View
                                    </Link>
                                    {can('hr.delete') && (
                                        <button
                                            onClick={() => deleteSchedule(schedule.id)}
                                            className="text-sm text-red-600 hover:text-red-800"
                                        >
                                            Delete
                                        </button>
                                    )}
                                </div>
                            </div>
                        ))
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
