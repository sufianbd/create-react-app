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
}

export default function CreatePerformanceReview({ employees }: Props) {
    const { data, setData, post, processing, errors } = useForm<{
        employee_id: string;
        reviewer_id: string;
        review_period: string;
        review_date: string;
        overall_rating: string;
        strengths: string;
        improvements: string;
        goals: string;
        reviewer_notes: string;
    }>({
        employee_id:    '',
        reviewer_id:    '',
        review_period:  '',
        review_date:    '',
        overall_rating: '',
        strengths:      '',
        improvements:   '',
        goals:          '',
        reviewer_notes: '',
    });

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

                <form onSubmit={submit} className="space-y-6">
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
                                <label className="block text-sm font-medium text-slate-700 mb-1">Review Period *</label>
                                <input
                                    type="text"
                                    value={data.review_period}
                                    onChange={(e) => setData('review_period', e.target.value)}
                                    placeholder="e.g. Q1 2026"
                                    className="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                />
                                {errors.review_period && <p className="text-sm text-red-600 mt-1">{errors.review_period}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Review Date *</label>
                                <input
                                    type="date"
                                    value={data.review_date}
                                    onChange={(e) => setData('review_date', e.target.value)}
                                    className="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                />
                                {errors.review_date && <p className="text-sm text-red-600 mt-1">{errors.review_date}</p>}
                            </div>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">Overall Rating (1–5)</label>
                            <input
                                type="number"
                                min="1"
                                max="5"
                                step="0.1"
                                value={data.overall_rating}
                                onChange={(e) => setData('overall_rating', e.target.value)}
                                className="w-32 rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                placeholder="Optional"
                            />
                            {errors.overall_rating && <p className="text-sm text-red-600 mt-1">{errors.overall_rating}</p>}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">Strengths</label>
                            <textarea
                                value={data.strengths}
                                onChange={(e) => setData('strengths', e.target.value)}
                                rows={3}
                                className="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                placeholder="Employee strengths…"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">Improvements</label>
                            <textarea
                                value={data.improvements}
                                onChange={(e) => setData('improvements', e.target.value)}
                                rows={3}
                                className="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                placeholder="Areas for improvement…"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">Goals</label>
                            <textarea
                                value={data.goals}
                                onChange={(e) => setData('goals', e.target.value)}
                                rows={3}
                                className="w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                placeholder="Goals for next period…"
                            />
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
