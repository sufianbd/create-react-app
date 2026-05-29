import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import { Pagination } from '@/Components/Inventory/Pagination';
import type { PageProps } from '@/types';
import type { Paginator } from '@/types/inventory';

interface AuditLogEntry {
    id: number;
    event: string;
    model: string;
    model_id: number;
    user: string;
    old_values: Record<string, unknown> | null;
    new_values: Record<string, unknown> | null;
    ip_address?: string;
    created_at: string;
    created_at_raw: string;
}

interface Props extends PageProps {
    logs: Paginator<AuditLogEntry>;
    filters: { event?: string; model?: string };
}

const EVENT_COLORS: Record<string, string> = {
    created: 'bg-green-100 text-green-700',
    updated: 'bg-blue-100 text-blue-700',
    deleted: 'bg-red-100 text-red-600',
};

function ValueDiff({ old: oldVal, nw }: { old: Record<string, unknown> | null; nw: Record<string, unknown> | null }) {
    const keys = Array.from(new Set([...Object.keys(oldVal ?? {}), ...Object.keys(nw ?? {})]));
    if (keys.length === 0) return null;

    return (
        <div className="mt-2 space-y-1">
            {keys.map((k) => (
                <div key={k} className="text-xs">
                    <span className="font-medium text-slate-600">{k}:</span>{' '}
                    {oldVal?.[k] !== undefined && (
                        <span className="text-red-600 line-through mr-1">{String(oldVal[k])}</span>
                    )}
                    {nw?.[k] !== undefined && (
                        <span className="text-green-700">{String(nw[k])}</span>
                    )}
                </div>
            ))}
        </div>
    );
}

export default function AuditLogIndex({ logs, filters }: Props) {
    const [event, setEvent] = useState(filters.event ?? '');
    const [model, setModel] = useState(filters.model ?? '');
    const [expanded, setExpanded] = useState<number | null>(null);

    function applyFilters(e: React.FormEvent) {
        e.preventDefault();
        router.get('/admin/audit-log', {
            event: event || undefined,
            model: model || undefined,
        }, { preserveState: true, replace: true });
    }

    return (
        <AppLayout>
            <Head title="Audit Log" />
            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold text-slate-900">Audit Log</h1>
                    <p className="text-sm text-slate-500 mt-1">{logs.total} entries</p>
                </div>

                {/* Filters */}
                <form onSubmit={applyFilters} className="flex flex-wrap gap-3">
                    <select value={event} onChange={(e) => setEvent(e.target.value)}
                        className="rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none">
                        <option value="">All events</option>
                        <option value="created">Created</option>
                        <option value="updated">Updated</option>
                        <option value="deleted">Deleted</option>
                    </select>
                    <input value={model} onChange={(e) => setModel(e.target.value)}
                        placeholder="Filter by model…"
                        className="rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none" />
                    <Button type="submit" variant="secondary" size="sm">Apply</Button>
                    {(filters.event || filters.model) && (
                        <Button type="button" variant="secondary" size="sm"
                            onClick={() => { setEvent(''); setModel(''); router.get('/admin/audit-log', {}, { replace: true }); }}>
                            Clear
                        </Button>
                    )}
                </form>

                <div className="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <table className="w-full text-sm">
                        <thead className="bg-slate-50 border-b border-slate-200 text-xs text-slate-500 uppercase">
                            <tr>
                                <th className="px-4 py-2 text-left font-medium">Event</th>
                                <th className="px-4 py-2 text-left font-medium">Model</th>
                                <th className="px-4 py-2 text-left font-medium">User</th>
                                <th className="px-4 py-2 text-left font-medium">When</th>
                                <th className="px-4 py-2 w-8"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {logs.data.map((log) => (
                                <>
                                    <tr key={log.id} className="hover:bg-slate-50 cursor-pointer"
                                        onClick={() => setExpanded(expanded === log.id ? null : log.id)}>
                                        <td className="px-4 py-3">
                                            <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium capitalize ${EVENT_COLORS[log.event] ?? 'bg-slate-100 text-slate-600'}`}>
                                                {log.event}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 font-medium text-slate-900">
                                            {log.model} <span className="text-slate-400 font-normal">#{log.model_id}</span>
                                        </td>
                                        <td className="px-4 py-3 text-slate-600">{log.user}</td>
                                        <td className="px-4 py-3 text-slate-500" title={log.created_at_raw}>{log.created_at}</td>
                                        <td className="px-4 py-3 text-slate-400 text-center">
                                            {(log.old_values || log.new_values) && (
                                                <span>{expanded === log.id ? '▲' : '▼'}</span>
                                            )}
                                        </td>
                                    </tr>
                                    {expanded === log.id && (
                                        <tr key={`${log.id}-detail`} className="bg-slate-50">
                                            <td colSpan={5} className="px-4 py-3">
                                                <ValueDiff old={log.old_values} nw={log.new_values} />
                                                {log.ip_address && (
                                                    <p className="text-xs text-slate-400 mt-2">IP: {log.ip_address}</p>
                                                )}
                                            </td>
                                        </tr>
                                    )}
                                </>
                            ))}
                            {logs.data.length === 0 && (
                                <tr>
                                    <td colSpan={5} className="px-4 py-8 text-center text-sm text-slate-400">
                                        No audit log entries found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                    <Pagination paginator={logs} />
                </div>
            </div>
        </AppLayout>
    );
}
