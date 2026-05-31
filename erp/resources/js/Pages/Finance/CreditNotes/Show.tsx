import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import { CreditNoteStatusBadge } from '@/Components/Finance/CreditNoteStatusBadge';
import { usePermission } from '@/Hooks/usePermission';
import type { PageProps } from '@/types';
import type { CreditNote } from '@/types/finance';

interface Props extends PageProps { creditNote: CreditNote; }

export default function CreditNoteShow({ creditNote }: Props) {
    const { can } = usePermission();

    function transition(action: string, confirmMsg: string) {
        if (!confirm(confirmMsg)) return;
        router.patch(`/finance/credit-notes/${creditNote.id}/${action}`);
    }

    return (
        <AppLayout>
            <Head title={creditNote.number ?? `Credit Note #${creditNote.id}`} />
            <div className="mx-auto max-w-4xl space-y-6">
                {/* Header */}
                <div className="flex items-start justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">
                            {creditNote.number ?? `Credit Note #${creditNote.id}`}
                        </h1>
                        <div className="mt-1 flex items-center gap-3">
                            <CreditNoteStatusBadge status={creditNote.status} />
                            <span className="text-sm text-slate-500">Issued: {creditNote.issue_date}</span>
                        </div>
                    </div>
                    <div className="flex gap-2 flex-wrap justify-end">
                        {can('finance.update') && (
                            <>
                                {creditNote.status === 'draft' && (
                                    <Button onClick={() => transition('issue', 'Mark this credit note as issued?')}>Issue</Button>
                                )}
                                {creditNote.status === 'issued' && (
                                    <Button onClick={() => transition('apply', 'Mark this credit note as applied?')}>Mark Applied</Button>
                                )}
                                {(creditNote.status === 'draft' || creditNote.status === 'issued') && (
                                    <Button variant="secondary" onClick={() => transition('cancel', 'Cancel this credit note? This action cannot be undone.')}>Cancel</Button>
                                )}
                            </>
                        )}
                        {can('finance.delete') && creditNote.status === 'draft' && (
                            <Button variant="secondary" onClick={() => {
                                if (confirm('Delete this credit note?')) {
                                    router.delete(`/finance/credit-notes/${creditNote.id}`);
                                }
                            }}>Delete</Button>
                        )}
                    </div>
                </div>

                {/* Contact + Invoice */}
                <div className="grid gap-4 sm:grid-cols-2">
                    {creditNote.contact && (
                        <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                            <p className="text-xs text-slate-500 mb-1">Customer</p>
                            <p className="font-medium text-slate-900">{creditNote.contact.name}</p>
                        </div>
                    )}
                    {creditNote.invoice && (
                        <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                            <p className="text-xs text-slate-500 mb-1">Linked Invoice</p>
                            <Link href={`/finance/invoices/${creditNote.invoice.id}`}
                                className="font-medium text-indigo-600 hover:text-indigo-800">
                                {creditNote.invoice.number ?? `Invoice #${creditNote.invoice.id}`}
                            </Link>
                        </div>
                    )}
                </div>

                {/* Reason */}
                {creditNote.reason && (
                    <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                        <p className="text-xs text-slate-500 mb-1">Reason</p>
                        <p className="text-sm text-slate-700">{creditNote.reason}</p>
                    </div>
                )}

                {/* Line items */}
                <div className="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <table className="w-full text-sm">
                        <thead className="bg-slate-50 text-xs text-slate-500 uppercase border-b border-slate-200">
                            <tr>
                                <th className="px-4 py-2 text-left font-medium">Description</th>
                                <th className="px-4 py-2 text-right font-medium">Qty</th>
                                <th className="px-4 py-2 text-right font-medium">Unit Price</th>
                                <th className="px-4 py-2 text-right font-medium">Tax</th>
                                <th className="px-4 py-2 text-right font-medium">Line Total</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {(creditNote.items ?? []).map((item, i) => (
                                <tr key={item.id ?? i}>
                                    <td className="px-4 py-3">{item.description}</td>
                                    <td className="px-4 py-3 text-right">{Number(item.quantity).toFixed(2)}</td>
                                    <td className="px-4 py-3 text-right">{Number(item.unit_price).toFixed(2)}</td>
                                    <td className="px-4 py-3 text-right">{Number(item.tax_rate).toFixed(1)}%</td>
                                    <td className="px-4 py-3 text-right font-medium">{Number(item.line_total ?? 0).toFixed(2)}</td>
                                </tr>
                            ))}
                        </tbody>
                        <tfoot className="border-t-2 border-slate-200 bg-slate-50">
                            <tr>
                                <td colSpan={4} className="px-4 py-2 text-right text-sm text-slate-500">Subtotal</td>
                                <td className="px-4 py-2 text-right">{Number(creditNote.subtotal ?? 0).toFixed(2)}</td>
                            </tr>
                            <tr>
                                <td colSpan={4} className="px-4 py-2 text-right text-sm text-slate-500">Tax</td>
                                <td className="px-4 py-2 text-right">{Number(creditNote.tax_total ?? 0).toFixed(2)}</td>
                            </tr>
                            <tr className="font-semibold">
                                <td colSpan={4} className="px-4 py-2 text-right text-slate-900">Total</td>
                                <td className="px-4 py-2 text-right text-slate-900">{Number(creditNote.total ?? 0).toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {/* Notes */}
                {creditNote.notes && (
                    <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                        <p className="text-xs text-slate-500 mb-1">Notes</p>
                        <p className="text-sm text-slate-700">{creditNote.notes}</p>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
