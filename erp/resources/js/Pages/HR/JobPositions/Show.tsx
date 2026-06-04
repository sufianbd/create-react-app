import { Head, Link, router, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import { usePermission } from '@/Hooks/usePermission';
import type { PageProps } from '@/types';
import type { JobPosition, JobApplication } from '@/types/hr';

interface Props extends PageProps {
    position: JobPosition;
}

const STATUS_COLORS: Record<string, string> = {
    draft:   'bg-slate-100 text-slate-700',
    open:    'bg-green-100 text-green-700',
    closed:  'bg-red-100 text-red-700',
    on_hold: 'bg-amber-100 text-amber-700',
};

const STAGE_COLORS: Record<string, string> = {
    applied:   'bg-blue-100 text-blue-700',
    screening: 'bg-purple-100 text-purple-700',
    interview: 'bg-amber-100 text-amber-700',
    offer:     'bg-orange-100 text-orange-700',
    hired:     'bg-green-100 text-green-700',
    rejected:  'bg-red-100 text-red-700',
};

function StatusBadge({ status }: { status: string }) {
    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${STATUS_COLORS[status] ?? 'bg-slate-100 text-slate-700'}`}>
            {status.replace('_', ' ')}
        </span>
    );
}

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

const EMPLOYMENT_TYPE_LABELS: Record<string, string> = {
    full_time:  'Full Time',
    part_time:  'Part Time',
    contract:   'Contract',
    internship: 'Internship',
};

export default function ShowJobPosition({ position }: Props) {
    const { can } = usePermission();

    function publishPosition() {
        router.post(`/hr/job-positions/${position.id}/publish`);
    }

    function closePosition() {
        if (confirm('Close this job position?')) {
            router.post(`/hr/job-positions/${position.id}/close`);
        }
    }

    function deletePosition() {
        if (confirm('Delete this job position? This cannot be undone.')) {
            router.delete(`/hr/job-positions/${position.id}`);
        }
    }

    return (
        <AppLayout>
            <Head title={position.title} />
            <div className="max-w-4xl space-y-6">
                {/* Header */}
                <div className="flex items-start justify-between">
                    <div>
                        <div className="flex items-center gap-3">
                            <h1 className="text-2xl font-semibold text-slate-900">{position.title}</h1>
                            <StatusBadge status={position.status} />
                        </div>
                        <p className="text-sm text-slate-500 mt-1">
                            {position.department?.name ?? 'No department'} ·{' '}
                            {EMPLOYMENT_TYPE_LABELS[position.employment_type] ?? position.employment_type}
                            {position.location ? ` · ${position.location}` : ''}
                        </p>
                    </div>
                    <a href="/hr/job-positions" className="text-sm text-slate-600 hover:text-slate-900">
                        ← Back to Positions
                    </a>
                </div>

                {/* Details */}
                <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <dl className="grid grid-cols-2 gap-4 sm:grid-cols-3">
                        <div>
                            <dt className="text-xs font-medium text-slate-500 uppercase tracking-wide">Openings</dt>
                            <dd className="mt-1 text-sm font-medium text-slate-900">{position.openings}</dd>
                        </div>
                        <div>
                            <dt className="text-xs font-medium text-slate-500 uppercase tracking-wide">Status</dt>
                            <dd className="mt-1"><StatusBadge status={position.status} /></dd>
                        </div>
                        {position.posted_at && (
                            <div>
                                <dt className="text-xs font-medium text-slate-500 uppercase tracking-wide">Posted At</dt>
                                <dd className="mt-1 text-sm text-slate-900">{position.posted_at}</dd>
                            </div>
                        )}
                        {position.closed_at && (
                            <div>
                                <dt className="text-xs font-medium text-slate-500 uppercase tracking-wide">Closed At</dt>
                                <dd className="mt-1 text-sm text-slate-900">{position.closed_at}</dd>
                            </div>
                        )}
                    </dl>

                    {position.description && (
                        <div className="mt-4 pt-4 border-t border-slate-100">
                            <dt className="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Description</dt>
                            <p className="text-sm text-slate-700 whitespace-pre-wrap">{position.description}</p>
                        </div>
                    )}

                    {position.requirements && (
                        <div className="mt-4 pt-4 border-t border-slate-100">
                            <dt className="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">Requirements</dt>
                            <p className="text-sm text-slate-700 whitespace-pre-wrap">{position.requirements}</p>
                        </div>
                    )}
                </div>

                {/* Actions */}
                {can('hr.create') && (
                    <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                        <h2 className="text-base font-semibold text-slate-900">Actions</h2>
                        <div className="flex items-center gap-3 flex-wrap">
                            {position.status === 'draft' && (
                                <Button onClick={publishPosition} className="bg-green-600 hover:bg-green-700">
                                    Publish Position
                                </Button>
                            )}
                            {position.status === 'open' && (
                                <button
                                    onClick={closePosition}
                                    className="rounded-md bg-amber-50 px-3 py-2 text-sm font-medium text-amber-700 hover:bg-amber-100 border border-amber-200"
                                >
                                    Close Position
                                </button>
                            )}
                            {can('hr.create') && (
                                <Link href={`/hr/job-applications/create?job_position_id=${position.id}`}>
                                    <Button>Add Application</Button>
                                </Link>
                            )}
                            {can('hr.delete') && (
                                <button
                                    onClick={deletePosition}
                                    className="rounded-md bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100 border border-red-200"
                                >
                                    Delete Position
                                </button>
                            )}
                        </div>
                    </div>
                )}

                {/* Applications */}
                <div className="rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div className="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                        <h2 className="text-base font-semibold text-slate-900">Applications</h2>
                        <span className="text-sm text-slate-500">{position.applications?.length ?? 0} total</span>
                    </div>
                    {position.applications && position.applications.length > 0 ? (
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="text-left text-xs font-medium text-slate-500 uppercase tracking-wide border-b border-slate-200">
                                    <th className="px-6 py-3">Applicant</th>
                                    <th className="px-6 py-3">Email</th>
                                    <th className="px-6 py-3">Stage</th>
                                    <th className="px-6 py-3">Rating</th>
                                    <th className="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {position.applications.map((app: JobApplication) => (
                                    <tr key={app.id} className="border-b border-slate-100 last:border-0 hover:bg-slate-50">
                                        <td className="px-6 py-3 font-medium text-slate-900">{app.applicant_name}</td>
                                        <td className="px-6 py-3 text-slate-600">{app.applicant_email}</td>
                                        <td className="px-6 py-3"><StageBadge stage={app.stage} /></td>
                                        <td className="px-6 py-3"><StarRating rating={app.rating} /></td>
                                        <td className="px-6 py-3">
                                            <Link href={`/hr/job-applications/${app.id}`} className="text-indigo-600 hover:text-indigo-800">
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    ) : (
                        <p className="px-6 py-8 text-sm text-slate-500 text-center">No applications yet.</p>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
