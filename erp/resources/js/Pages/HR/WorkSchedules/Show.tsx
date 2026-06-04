import { Head, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { usePermission } from '@/Hooks/usePermission';
import type { PageProps } from '@/types';
import type { WorkSchedule } from '@/types/hr';

interface Props extends PageProps {
    workSchedule: WorkSchedule;
}

const DAYS = [
    { key: 'monday',    label: 'Monday' },
    { key: 'tuesday',   label: 'Tuesday' },
    { key: 'wednesday', label: 'Wednesday' },
    { key: 'thursday',  label: 'Thursday' },
    { key: 'friday',    label: 'Friday' },
    { key: 'saturday',  label: 'Saturday' },
    { key: 'sunday',    label: 'Sunday' },
] as const;

type DayKey = typeof DAYS[number]['key'];

export default function ShowWorkSchedule({ workSchedule }: Props) {
    const { can } = usePermission();

    function deleteSchedule() {
        if (confirm('Delete this work schedule? This cannot be undone.')) {
            router.delete(`/hr/work-schedules/${workSchedule.id}`);
        }
    }

    return (
        <AppLayout>
            <Head title={`Work Schedule — ${workSchedule.name}`} />
            <div className="max-w-3xl space-y-6">
                {/* Header */}
                <div className="flex items-start justify-between">
                    <div>
                        <div className="flex items-center gap-3">
                            <h1 className="text-2xl font-semibold text-slate-900">{workSchedule.name}</h1>
                            {workSchedule.is_default && (
                                <span className="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-700">
                                    Default
                                </span>
                            )}
                        </div>
                        <p className="text-sm text-slate-500 mt-1">Work schedule details</p>
                    </div>
                    <a href="/hr/work-schedules" className="text-sm text-slate-600 hover:text-slate-900">
                        ← Back to Schedules
                    </a>
                </div>

                {/* Daily Hours Table */}
                <div className="rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div className="px-6 py-4 border-b border-slate-200">
                        <h2 className="text-base font-semibold text-slate-900">Daily Hours</h2>
                    </div>
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="text-left text-xs font-medium text-slate-500 uppercase tracking-wide border-b border-slate-200">
                                <th className="px-6 py-3">Day</th>
                                <th className="px-6 py-3">Start</th>
                                <th className="px-6 py-3">End</th>
                                <th className="px-6 py-3">Hours</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {DAYS.map(({ key, label }) => {
                                const start = workSchedule[`${key}_start` as `${DayKey}_start`];
                                const end   = workSchedule[`${key}_end`   as `${DayKey}_end`];
                                const isOff = !start;
                                return (
                                    <tr key={key} className={isOff ? 'bg-slate-50' : ''}>
                                        <td className="px-6 py-3 font-medium text-slate-900">{label}</td>
                                        <td className="px-6 py-3 text-slate-700">{start ?? <span className="text-slate-400">—</span>}</td>
                                        <td className="px-6 py-3 text-slate-700">{end ?? <span className="text-slate-400">—</span>}</td>
                                        <td className="px-6 py-3 text-slate-500">
                                            {isOff ? (
                                                <span className="text-slate-400 italic">Day off</span>
                                            ) : (
                                                <span>—</span>
                                            )}
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>

                {/* Actions */}
                {can('hr.delete') && (
                    <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 className="text-base font-semibold text-slate-900 mb-4">Actions</h2>
                        <button
                            onClick={deleteSchedule}
                            className="rounded-md bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100 border border-red-200"
                        >
                            Delete Schedule
                        </button>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
