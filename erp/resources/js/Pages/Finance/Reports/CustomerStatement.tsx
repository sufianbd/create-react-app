import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import type { PageProps } from '@/types';

interface ContactInfo { id: number; name: string; }
interface StatementRow { date: string; type: string; reference?: string | null; debit: number; credit: number; balance: number; }
interface Props extends PageProps {
    contacts: ContactInfo[];
    contact: ContactInfo | null;
    rows: StatementRow[];
    from: string;
    to: string;
    total_debit?: number;
    total_credit?: number;
    closing_balance?: number;
}

function fmt(n: number) { return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

export default function CustomerStatement({ contacts, contact, rows, from, to, total_debit, total_credit, closing_balance }: Props) {
    const [fromDate, setFromDate] = useState(from);
    const [toDate, setToDate] = useState(to);

    function applyFilter() {
        if (!contact) return;
        router.get(`/finance/reports/customer-statement/${contact.id}`, { from: fromDate, to: toDate }, { preserveState: true, replace: true });
    }

    return (
        <AppLayout>
            <Head title="Customer Statement" />
            <div className="space-y-6">
                <h1 className="text-2xl font-semibold text-slate-900">Customer Statement</h1>

                {/* Controls */}
                <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm flex flex-wrap items-center gap-4">
                    <div className="flex items-center gap-2">
                        <label className="text-sm text-slate-500 whitespace-nowrap">Customer</label>
                        <select value={contact?.id ?? ''}
                            onChange={(e) => {
                                if (e.target.value) router.get(`/finance/reports/customer-statement/${e.target.value}`, { from: fromDate, to: toDate });
                            }}
                            className="rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select customer…</option>
                            {contacts.map((c) => (
                                <option key={c.id} value={c.id}>{c.name}</option>
                            ))}
                        </select>
                    </div>
                    {contact && (
                        <>
                            <div className="flex items-center gap-2">
                                <label className="text-sm text-slate-500">From</label>
                                <input type="date" value={fromDate} onChange={(e) => setFromDate(e.target.value)}
                                    className="rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            </div>
                            <div className="flex items-center gap-2">
                                <label className="text-sm text-slate-500">To</label>
                                <input type="date" value={toDate} onChange={(e) => setToDate(e.target.value)}
                                    className="rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            </div>
                            <button onClick={applyFilter}
                                className="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700">
                                Apply
                            </button>
                        </>
                    )}
                </div>

                {contact ? (
                    <>
                        <div className="text-sm text-slate-600">
                            Statement for <span className="font-medium">{contact.name}</span>
                            <span className="ml-2 text-slate-400">({from} → {to})</span>
                        </div>
                        <div className="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                            <table className="w-full text-sm">
                                <thead className="bg-slate-50 text-xs text-slate-500 uppercase border-b border-slate-200">
                                    <tr>
                                        <th className="px-4 py-2 text-left font-medium">Date</th>
                                        <th className="px-4 py-2 text-left font-medium">Type</th>
                                        <th className="px-4 py-2 text-left font-medium">Reference</th>
                                        <th className="px-4 py-2 text-right font-medium">Debit</th>
                                        <th className="px-4 py-2 text-right font-medium">Credit</th>
                                        <th className="px-4 py-2 text-right font-medium">Balance</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {rows.length === 0 && (
                                        <tr><td colSpan={6} className="px-4 py-8 text-center text-slate-400">No transactions in this period.</td></tr>
                                    )}
                                    {rows.map((row, i) => (
                                        <tr key={i} className="hover:bg-slate-50">
                                            <td className="px-4 py-2 text-slate-500 whitespace-nowrap">{row.date}</td>
                                            <td className="px-4 py-2 text-slate-700">{row.type}</td>
                                            <td className="px-4 py-2 text-slate-500 font-mono text-xs">{row.reference ?? '—'}</td>
                                            <td className="px-4 py-2 text-right text-slate-600">{row.debit > 0 ? fmt(row.debit) : ''}</td>
                                            <td className="px-4 py-2 text-right text-slate-600">{row.credit > 0 ? fmt(row.credit) : ''}</td>
                                            <td className={`px-4 py-2 text-right font-medium ${row.balance < 0 ? 'text-green-600' : 'text-slate-900'}`}>
                                                {fmt(Math.abs(row.balance))}{row.balance < 0 ? ' Cr' : ''}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                                <tfoot className="border-t-2 border-slate-200 bg-slate-50">
                                    <tr className="font-semibold">
                                        <td colSpan={3} className="px-4 py-2 text-right text-slate-900">Totals</td>
                                        <td className="px-4 py-2 text-right text-slate-900">{fmt(total_debit ?? 0)}</td>
                                        <td className="px-4 py-2 text-right text-slate-900">{fmt(total_credit ?? 0)}</td>
                                        <td className="px-4 py-2 text-right text-slate-900">{fmt(Math.abs(closing_balance ?? 0))}{(closing_balance ?? 0) < 0 ? ' Cr' : ''}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div className="flex justify-end">
                            <div className="rounded-lg border border-slate-200 bg-white px-6 py-4 shadow-sm text-right">
                                <p className="text-xs text-slate-500">Closing Balance</p>
                                <p className="text-xl font-semibold text-slate-900">
                                    {fmt(Math.abs(closing_balance ?? 0))}{(closing_balance ?? 0) < 0 ? ' Cr' : ''}
                                </p>
                            </div>
                        </div>
                    </>
                ) : (
                    <div className="rounded-lg border border-slate-200 bg-white p-12 text-center shadow-sm">
                        <p className="text-slate-400">Select a customer above to view their account statement.</p>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
