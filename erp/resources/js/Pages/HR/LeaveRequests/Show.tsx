import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import { LeaveStatusBadge } from '@/Components/HR/LeaveStatusBadge';
import { usePermission } from '@/Hooks/usePermission';
import type { PageProps } from '@/types';
import type { LeaveRequest } from '@/types/hr';

interface Props extends PageProps {
    leaveRequest: LeaveRequest;
    can?: { update?: boolean };
}

export default function LeaveRequestShow({ leaveRequest, can: permCan }: Props) {
    const { can } = usePermission();
    const canUpdate = permCan?.update ?? can('hr.update');

    function handleApprove() {
        if (!confirm('Approve this leave request?')) return;
        router.post(`/hr/leave-requests/${leaveRequest.id}/approve`);
    }

    function handleReject() {
        if (!confirm('Reject this leave request?')) return;
        router.post(`/hr/leave-requests/${leaveRequest.id}/reject`);
    }

    return (
        <AppLayout>
            <Head title={`Leave Request #${leaveRequest.id}`} />
            <div className="mx-auto max-w-2xl space-y-6">
                <div className="flex items-start justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">
                            Leave Request #{leaveRequest.id}
                        </h1>
                        <div className="mt-1 flex items-center gap-3">
                            <LeaveStatusBadge status={leaveRequest.status} />
                            <span className="text-sm text-slate-500">
                                {leaveRequest.employee?.full_name}
                            </span>
                        </div>
                    </div>
                    {canUpdate && leaveRequest.status === 'pending' && (
                        <div className="flex gap-2">
                            <Button onClick={handleApprove}>Approve</Button>
                            <Button variant="secondary" onClick={handleReject}>Reject</Button>
                        </div>
                    )}
                </div>

                <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <dl className="grid grid-cols-2 gap-x-8 gap-y-4">
                        {[
                            { label: 'Employee',   value: leaveRequest.employee?.full_name ?? '—' },
                            { label: 'Type',       value: leaveRequest.leave_type ?? leaveRequest.type ?? '—' },
                            { label: 'Start Date', value: leaveRequest.start_date },
                            { label: 'End Date',   value: leaveRequest.end_date },
                            { label: 'Days',       value: String(leaveRequest.days) },
                            { label: 'Reason',     value: leaveRequest.reason ?? leaveRequest.notes ?? '—' },
                            { label: 'Approved By', value: leaveRequest.approver ?? '—' },
                            { label: 'Approved At', value: leaveRequest.approved_at ?? '—' },
                        ].map(({ label, value }) => (
                            <div key={label}>
                                <dt className="text-xs text-slate-500">{label}</dt>
                                <dd className="mt-0.5 text-sm font-medium text-slate-800">{value}</dd>
                            </div>
                        ))}
                    </dl>
                </div>

                <Link href="/hr/leave-requests">
                    <Button variant="secondary">Back to Leave Requests</Button>
                </Link>
            </div>
        </AppLayout>
    );
}
