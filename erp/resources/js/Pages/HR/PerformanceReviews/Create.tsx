import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import type { PageProps } from '@/types';

interface EmployeeOption {
    id: number;
    first_name: string;
    last_name: string;
}

interface Props extends PageProps {
    employees: EmployeeOption[];
    employeeId?: number | null;
}

interface GoalRow {
    title: string;
    description: string;
}

interface CompetencyRow {
    name: string;
    rating: string;
    notes: string;
}

const DEFAULT_COMPETENCIES: CompetencyRow[] = [
    { name: 'Communication',     rating: '', notes: '' },
    { name: 'Teamwork',          rating: '', notes: '' },
    { name: 'Technical Skills',  rating: '', notes: '' },
    { name: 'Initiative',        rating: '', notes: '' },
];

export default function CreatePerformanceReview({ employees, employeeId }: Props) {
    const { data, setData, post, processing, errors } = useForm<{
        employee_id: string;
        period_start: string;
        period_end: string;
        comments: string;
        goals: GoalRow[];
        competencies: CompetencyRow[];
    }>({
        employee_id:  employeeId ? String(employeeId) : '',
        period_start: '',
        period_end:   '',
        comments:     '',
        goals:        [{ title: '', description: '' }],
        competencies: DEFAULT_COMPETENCIES,
    });

    function addGoal() {
        setData('goals', [...data.goals, { title: '', description: '' }]);
    }

    function removeGoal(index: number) {
        setData('goals', data.goals.filter((_, i) => i !== index));
    }

    function updateGoal(index: number, field: keyof GoalRow, value: string) {
        const updated = data.goals.map((g, i) => i === index ? { ...g, [field]: value } : g);
        setData('goals', updated);
    }

    function addCompetency() {
        setData('competencies', [...data.competencies, { name: '', rating: '', notes: '' }]);
    }

    function removeCompetency(index: number) {
        setData('competencies', data.competencies.filter((_, i) => i !== index));
    }

    function updateCompetency(index: number, field: keyof CompetencyRow, value: string) {
        const updated = data.competencies.map((c, i) => i === index ? { ...c, [field]: value } : c);
        setData('competencies', updated);
    }

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post('/hr/performance-reviews');
    }

    return (
        <AppLayout>
            <Head title="New Performance Review" />
            <div className="max-w-3xl space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold text-slate-900">New Performance Review</h1>
                    <p className="text-sm text-slate-500 mt-1">Create a new employee performance review</p>
                </div>

                <form onSubmit={submit} className="space-y-8">
                    {/* Basic Info */}
                    <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                        <h2 className="text-base font-semibold text-slate-900">Review Details</h2>

                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">Employee *</label>
                            <select
                                value={data.employee_id}
                                onChange={(e) => setData('employee_id', e.target.value)}
                                className="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            >
                                <option value="">Select employee…</option>
                                {employees.map((emp) => (
                                    <option key={emp.id} value={emp.id}>
                                        {emp.first_name} {emp.last_name}
                                    </option>
                                ))}
                            </select>
                            {errors.employee_id && <p className="text-sm text-red-600 mt-1">{errors.employee_id}</p>}
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Period Start *</label>
                                <input
                                    type="date"
                                    value={data.period_start}
                                    onChange={(e) => setData('period_start', e.target.value)}
                                    className="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                />
                                {errors.period_start && <p className="text-sm text-red-600 mt-1">{errors.period_start}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Period End *</label>
                                <input
                                    type="date"
                                    value={data.period_end}
                                    onChange={(e) => setData('period_end', e.target.value)}
                                    className="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                />
                                {errors.period_end && <p className="text-sm text-red-600 mt-1">{errors.period_end}</p>}
                            </div>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">Comments</label>
                            <textarea
                                value={data.comments}
                                onChange={(e) => setData('comments', e.target.value)}
                                rows={3}
                                className="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                placeholder="Optional comments…"
                            />
                        </div>
                    </div>

                    {/* Goals */}
                    <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                        <div className="flex items-center justify-between">
                            <h2 className="text-base font-semibold text-slate-900">Goals</h2>
                            <button type="button" onClick={addGoal} className="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                + Add Goal
                            </button>
                        </div>

                        {data.goals.map((goal, i) => (
                            <div key={i} className="border border-slate-100 rounded-md p-4 space-y-3">
                                <div className="flex items-start justify-between gap-2">
                                    <div className="flex-1 space-y-3">
                                        <div>
                                            <label className="block text-xs font-medium text-slate-600 mb-1">Title *</label>
                                            <input
                                                type="text"
                                                value={goal.title}
                                                onChange={(e) => updateGoal(i, 'title', e.target.value)}
                                                className="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                placeholder="Goal title"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-xs font-medium text-slate-600 mb-1">Description</label>
                                            <input
                                                type="text"
                                                value={goal.description}
                                                onChange={(e) => updateGoal(i, 'description', e.target.value)}
                                                className="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                placeholder="Optional description"
                                            />
                                        </div>
                                    </div>
                                    {data.goals.length > 1 && (
                                        <button type="button" onClick={() => removeGoal(i)} className="text-slate-400 hover:text-red-500 mt-6">
                                            <svg className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fillRule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4z" clipRule="evenodd" />
                                            </svg>
                                        </button>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>

                    {/* Competencies */}
                    <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                        <div className="flex items-center justify-between">
                            <h2 className="text-base font-semibold text-slate-900">Competencies</h2>
                            <button type="button" onClick={addCompetency} className="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                + Add Competency
                            </button>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="text-left text-xs font-medium text-slate-500 uppercase tracking-wide">
                                        <th className="pb-2 pr-4">Name</th>
                                        <th className="pb-2 pr-4">Rating (1-5)</th>
                                        <th className="pb-2 pr-4">Notes</th>
                                        <th className="pb-2"></th>
                                    </tr>
                                </thead>
                                <tbody className="space-y-2">
                                    {data.competencies.map((comp, i) => (
                                        <tr key={i} className="border-t border-slate-100">
                                            <td className="py-2 pr-4">
                                                <input
                                                    type="text"
                                                    value={comp.name}
                                                    onChange={(e) => updateCompetency(i, 'name', e.target.value)}
                                                    className="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                    placeholder="Competency name"
                                                />
                                            </td>
                                            <td className="py-2 pr-4">
                                                <select
                                                    value={comp.rating}
                                                    onChange={(e) => updateCompetency(i, 'rating', e.target.value)}
                                                    className="w-24 rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                >
                                                    <option value="">—</option>
                                                    {[1, 2, 3, 4, 5].map((n) => (
                                                        <option key={n} value={n}>{n}</option>
                                                    ))}
                                                </select>
                                            </td>
                                            <td className="py-2 pr-4">
                                                <input
                                                    type="text"
                                                    value={comp.notes}
                                                    onChange={(e) => updateCompetency(i, 'notes', e.target.value)}
                                                    className="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                    placeholder="Optional notes"
                                                />
                                            </td>
                                            <td className="py-2">
                                                <button type="button" onClick={() => removeCompetency(i)} className="text-slate-400 hover:text-red-500">
                                                    <svg className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Creating…' : 'Create Review'}
                        </Button>
                        <a href="/hr/performance-reviews" className="text-sm text-slate-600 hover:text-slate-900">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
