import { Head, router, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import { usePermission } from '@/Hooks/usePermission';
import type { PageProps } from '@/types';
import type { PerformanceReview } from '@/types/hr';

interface Props extends PageProps {
    performanceReview: PerformanceReview;
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

function StarRating({ rating, size = 'md' }: { rating: number | null; size?: 'sm' | 'md' }) {
    if (rating === null) return <span className="text-sm text-slate-400">Not rated</span>;
    const cls = size === 'sm' ? 'h-4 w-4' : 'h-5 w-5';
    return (
        <span className="flex items-center gap-0.5">
            {[1, 2, 3, 4, 5].map((n) => (
                <svg
                    key={n}
                    className={`${cls} ${n <= rating ? 'text-amber-400' : 'text-slate-200'}`}
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            ))}
        </span>
    );
}

function CompleteForm({ review }: { review: PerformanceReview }) {
    const { data, setData, post, processing, errors } = useForm({ overall_rating: '' });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post(`/hr/performance-reviews/${review.id}/complete`);
    }

    return (
        <form onSubmit={submit} className="flex items-end gap-3">
            <div>
                <label className="block text-xs font-medium text-slate-600 mb-1">Overall Rating *</label>
                <select
                    value={data.overall_rating}
                    onChange={(e) => setData('overall_rating', e.target.value)}
                    className="rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                >
                    <option value="">Select…</option>
                    {[1, 2, 3, 4, 5].map((n) => (
                        <option key={n} value={n}>{n}</option>
                    ))}
                </select>
                {errors.overall_rating && <p className="text-xs text-red-600 mt-1">{errors.overall_rating}</p>}
            </div>
            <Button type="submit" disabled={processing} className="bg-green-600 hover:bg-green-700">
                {processing ? 'Completing…' : 'Complete Review'}
            </Button>
        </form>
    );
}

export default function ShowPerformanceReview({ performanceReview: review }: Props) {
    const { can } = usePermission();

    function startReview() {
        router.post(`/hr/performance-reviews/${review.id}/start`);
    }

    function deleteReview() {
        if (confirm('Delete this review? This cannot be undone.')) {
            router.delete(`/hr/performance-reviews/${review.id}`);
        }
    }

    function toggleGoal(goalId: number, achieved: boolean) {
        router.patch(`/hr/performance-reviews/${review.id}/goals/${goalId}`, { achieved });
    }

    const employeeName = review.employee
        ? `${review.employee.first_name} ${review.employee.last_name}`
        : '—';

    return (
        <AppLayout>
            <Head title={`Performance Review — ${employeeName}`} />
            <div className="max-w-4xl space-y-6">
                {/* Header */}
                <div className="flex items-start justify-between">
                    <div>
                        <div className="flex items-center gap-3">
                            <h1 className="text-2xl font-semibold text-slate-900">Performance Review</h1>
                            <StatusBadge status={review.status} />
                        </div>
                        <p className="text-sm text-slate-500 mt-1">
                            {employeeName} · {review.period_start} — {review.period_end}
                        </p>
                    </div>
                    <a href="/hr/performance-reviews" className="text-sm text-slate-600 hover:text-slate-900">
                        ← Back to Reviews
                    </a>
                </div>

                {/* Meta */}
                <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <dl className="grid grid-cols-2 gap-4 sm:grid-cols-3">
                        <div>
                            <dt className="text-xs font-medium text-slate-500 uppercase tracking-wide">Employee</dt>
                            <dd className="mt-1 text-sm font-medium text-slate-900">{employeeName}</dd>
                        </div>
                        <div>
                            <dt className="text-xs font-medium text-slate-500 uppercase tracking-wide">Reviewer</dt>
                            <dd className="mt-1 text-sm text-slate-900">{review.reviewer?.name ?? '—'}</dd>
                        </div>
                        <div>
                            <dt className="text-xs font-medium text-slate-500 uppercase tracking-wide">Period</dt>
                            <dd className="mt-1 text-sm text-slate-900">{review.period_start} — {review.period_end}</dd>
                        </div>
                        <div>
                            <dt className="text-xs font-medium text-slate-500 uppercase tracking-wide">Overall Rating</dt>
                            <dd className="mt-1"><StarRating rating={review.overall_rating} /></dd>
                        </div>
                        {review.average_competency_rating !== null && (
                            <div>
                                <dt className="text-xs font-medium text-slate-500 uppercase tracking-wide">Avg Competency</dt>
                                <dd className="mt-1 text-sm font-medium text-slate-900">{review.average_competency_rating} / 5</dd>
                            </div>
                        )}
                        {review.completed_at && (
                            <div>
                                <dt className="text-xs font-medium text-slate-500 uppercase tracking-wide">Completed At</dt>
                                <dd className="mt-1 text-sm text-slate-900">{review.completed_at}</dd>
                            </div>
                        )}
                    </dl>
                    {review.comments && (
                        <div className="mt-4 pt-4 border-t border-slate-100">
                            <dt className="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Comments</dt>
                            <p className="text-sm text-slate-700 whitespace-pre-wrap">{review.comments}</p>
                        </div>
                    )}
                </div>

                {/* Goals */}
                {review.goals && review.goals.length > 0 && (
                    <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 className="text-base font-semibold text-slate-900 mb-4">Goals</h2>
                        <ul className="space-y-3">
                            {review.goals.map((goal) => (
                                <li key={goal.id} className="flex items-start gap-3 border border-slate-100 rounded-md p-3">
                                    {can('hr.update') && review.status !== 'draft' ? (
                                        <input
                                            type="checkbox"
                                            checked={goal.achieved}
                                            onChange={(e) => toggleGoal(goal.id, e.target.checked)}
                                            className="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                    ) : (
                                        <span className={`mt-0.5 h-4 w-4 rounded-sm border flex items-center justify-center flex-shrink-0 ${goal.achieved ? 'bg-green-100 border-green-400' : 'bg-slate-50 border-slate-300'}`}>
                                            {goal.achieved && (
                                                <svg className="h-3 w-3 text-green-600" viewBox="0 0 12 12" fill="currentColor">
                                                    <path d="M3.72 6.276L5.27 7.83 8.28 4.18" stroke="currentColor" strokeWidth="1.5" fill="none" strokeLinecap="round" strokeLinejoin="round" />
                                                </svg>
                                            )}
                                        </span>
                                    )}
                                    <div className="flex-1 min-w-0">
                                        <p className={`text-sm font-medium ${goal.achieved ? 'line-through text-slate-400' : 'text-slate-900'}`}>
                                            {goal.title}
                                        </p>
                                        {goal.description && <p className="text-xs text-slate-500 mt-0.5">{goal.description}</p>}
                                        {goal.achievement_notes && <p className="text-xs text-slate-600 mt-1 italic">{goal.achievement_notes}</p>}
                                    </div>
                                    <span className={`flex-shrink-0 text-xs rounded-full px-2 py-0.5 ${goal.achieved ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'}`}>
                                        {goal.achieved ? 'Achieved' : 'Pending'}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}

                {/* Competencies */}
                {review.competencies && review.competencies.length > 0 && (
                    <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <h2 className="text-base font-semibold text-slate-900">Competencies</h2>
                            {review.average_competency_rating !== null && (
                                <span className="text-sm text-slate-600">
                                    Average: <span className="font-semibold text-slate-900">{review.average_competency_rating}</span> / 5
                                </span>
                            )}
                        </div>
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="text-left text-xs font-medium text-slate-500 uppercase tracking-wide border-b border-slate-200">
                                    <th className="pb-2 pr-4">Competency</th>
                                    <th className="pb-2 pr-4">Rating</th>
                                    <th className="pb-2">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                {review.competencies.map((comp) => (
                                    <tr key={comp.id} className="border-b border-slate-100 last:border-0">
                                        <td className="py-3 pr-4 font-medium text-slate-900">{comp.name}</td>
                                        <td className="py-3 pr-4"><StarRating rating={comp.rating} size="sm" /></td>
                                        <td className="py-3 text-slate-600">{comp.notes ?? '—'}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}

                {/* Actions */}
                {can('hr.update') && (
                    <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                        <h2 className="text-base font-semibold text-slate-900">Actions</h2>

                        {review.status === 'draft' && (
                            <div className="flex items-center gap-3">
                                <Button onClick={startReview} className="bg-blue-600 hover:bg-blue-700">
                                    Start Review
                                </Button>
                                {can('hr.delete') && (
                                    <button
                                        onClick={deleteReview}
                                        className="rounded-md bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100 border border-red-200"
                                    >
                                        Delete Review
                                    </button>
                                )}
                            </div>
                        )}

                        {review.status === 'in_review' && (
                            <CompleteForm review={review} />
                        )}

                        {review.status === 'completed' && (
                            <p className="text-sm text-slate-500">This review has been completed.</p>
                        )}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
