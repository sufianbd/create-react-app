import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import { ExpenseStatusBadge } from '@/Components/HR/ExpenseStatusBadge';
import type { PageProps } from '@/types';

type ExpenseStatus = 'draft' | 'submitted' | 'approved' | 'rejected' | 'reimbursed';

interface ExpenseClaim {
    id: number;
    employee_id: number;
    employee_name?: string;
    employee?: { id: number; full_name: string } | null;
    submitted_by?: number | null;
    submitted_by_name?: string | null;
    title: string;
    description?: string | null;
    expense_date: string;
    amount: number;
    currency_code: string;
    category: string;
    status: ExpenseStatus;
    reviewed_by?: number | null;
    reviewed_by_name?: string | null;
    reviewed_at?: string | null;
    review_notes?: string | null;
    created_at?: string;
}

interface Props extends PageProps {
    claim: ExpenseClaim;
    categories: string[];
    can?: {
        update?: boolean;
        delete?: boolean;
        approve?: boolean;
    };
}

export default function ExpenseClaimShow({ claim, can: permCan }: Props) {
    const [showRejectForm, setShowRejectForm] = useState(false);
    const { data, setData, post: rejectPost, processing: rejectProcessing, errors: rejectErrors, reset } = useForm({ notes: '' });

    function handleSubmit() {
        if (!confirm('Submit this expense claim for review?')) return;
        router.post(`/hr/expense-claims/${claim.id}/submit`);
    }

    function handleApprove() {
        if (!confirm('Approve this expense claim?')) return;
        router.post(`/hr/expense-claims/${claim.id}/approve`);
    }

    function handleRejectSubmit(e: React.FormEvent) {
        e.preventDefault();
        rejectPost(`/hr/expense-claims/${claim.id}/reject`, {
            onSuccess: () => {
                setShowRejectForm(false);
                reset();
            },
        });
    }

    function handleReimburse() {
        if (!confirm('Mark this claim as reimbursed?')) return;
        router.post(`/hr/expense-claims/${claim.id}/reimburse`);
    }

    function handleDelete() {
        if (!confirm('Delete this expense claim? This cannot be undone.')) return;
        router.delete(`/hr/expense-claims/${claim.id}`);
    }

    const canApprove = permCan?.approve ?? false;
    const canDelete  = permCan?.delete ?? false;

    return (
        <AppLayout>
            <Head title={`Expense Claim: ${claim.title}`} />
            <div className="mx-auto max-w-2xl space-y-6">
                {/* Header */}
                <div className="flex items-start justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">{claim.title}</h1>
                        <div className="mt-1 flex items-center gap-3">
                            <ExpenseStatusBadge status={claim.status} />
                            <span className="text-sm text-slate-500">
                                {claim.employee?.full_name ?? claim.employee_name ?? '—'}
                            </span>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        {/* Draft: Submit button */}
                        {claim.status === 'draft' && (
                            <Button onClick={handleSubmit}>Submit Claim</Button>
                        )}
                        {/* Submitted: Approve / Reject for managers/admins */}
                        {claim.status === 'submitted' && canApprove && !showRejectForm && (
                            <>
                                <Button onClick={handleApprove}>Approve</Button>
                                <Button variant="secondary" onClick={() => setShowRejectForm(true)}>Reject</Button>
                            </>
                        )}
                        {/* Approved: Mark reimbursed */}
                        {claim.status === 'approved' && canApprove && (
                            <Button onClick={handleReimburse}>Mark Reimbursed</Button>
                        )}
                        {/* Delete draft */}
                        {canDelete && claim.status === 'draft' && (
                            <Button variant="danger" onClick={handleDelete}>Delete</Button>
                        )}
                    </div>
                </div>

                {/* Reject form (inline) */}
                {showRejectForm && claim.status === 'submitted' && canApprove && (
                    <form onSubmit={handleRejectSubmit} className="rounded-lg border border-red-200 bg-red-50 p-4 space-y-3">
                        <p className="text-sm font-medium text-red-800">Rejection reason (required)</p>
                        <textarea
                            value={data.notes}
                            onChange={(e) => setData('notes', e.target.value)}
                            rows={3}
                            className="w-full rounded-md border border-red-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none"
                            placeholder="Explain why this claim is being rejected…"
                        />
                        {rejectErrors.notes && <p className="text-xs text-red-600">{rejectErrors.notes}</p>}
                        <div className="flex gap-2">
                            <Button type="submit" variant="danger" disabled={rejectProcessing}>
                                {rejectProcessing ? 'Rejecting…' : 'Confirm Rejection'}
                            </Button>
                            <Button type="button" variant="secondary" onClick={() => { setShowRejectForm(false); reset(); }}>
                                Cancel
                            </Button>
                        </div>
                    </form>
                )}

                {/* Details card */}
                <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <dl className="grid grid-cols-2 gap-x-8 gap-y-4">
                        {[
                            { label: 'Employee',    value: claim.employee?.full_name ?? claim.employee_name ?? '—' },
                            { label: 'Category',    value: claim.category ? claim.category.charAt(0).toUpperCase() + claim.category.slice(1) : '—' },
                            { label: 'Expense Date', value: claim.expense_date },
                            { label: 'Amount',      value: `${claim.currency_code} ${Number(claim.amount).toFixed(2)}` },
                            { label: 'Status',      value: <ExpenseStatusBadge status={claim.status} /> },
                            { label: 'Submitted By', value: claim.submitted_by_name ?? '—' },
                            { label: 'Reviewed By',  value: claim.reviewed_by_name ?? '—' },
                            { label: 'Reviewed At',  value: claim.reviewed_at ?? '—' },
                        ].map(({ label, value }) => (
                            <div key={label}>
                                <dt className="text-xs text-slate-500">{label}</dt>
                                <dd className="mt-0.5 text-sm font-medium text-slate-800">{value}</dd>
                            </div>
                        ))}
                    </dl>

                    {claim.description && (
                        <div className="mt-4 pt-4 border-t border-slate-100">
                            <dt className="text-xs text-slate-500 mb-1">Description</dt>
                            <dd className="text-sm text-slate-700">{claim.description}</dd>
                        </div>
                    )}

                    {claim.review_notes && (
                        <div className="mt-4 pt-4 border-t border-slate-100">
                            <dt className="text-xs text-slate-500 mb-1">Review Notes</dt>
                            <dd className="text-sm text-slate-700">{claim.review_notes}</dd>
                        </div>
                    )}
                </div>

                <Link href="/hr/expense-claims">
                    <Button variant="secondary">Back to Expense Claims</Button>
                </Link>
            </div>
        </AppLayout>
    );
}
