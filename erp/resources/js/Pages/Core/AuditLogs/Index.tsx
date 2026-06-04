import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import type { PageProps } from '@/types';
import type { Paginator } from '@/types/inventory';
import type { AuditLog } from '@/types/audit';

interface Props extends PageProps {
    logs: Paginator<AuditLog>;
    filters: {
        event?: string;
        user_id?: string;
        auditable_type?: string;
        tenant_id?: string;
        date_from?: string;
        date_to?: string;
    };
}

const EVENT_COLORS: Record<string, string> = {
    created:  'bg-green-100 text-green-700',
    updated:  'bg-blue-100 text-blue-700',
    deleted:  'bg-red-100 text-red-700',
    login:    'bg-slate-100 text-slate-700',
    logout:   'bg-slate-100 text-slate-700',
};

function shortType(type: string | null): string {
    if (!type) return '—';
    return type.split('\\').pop() ?? type;
}

export default function AuditLogsIndex({ logs, filters }: Props) {
    const { data, setData, get } = useForm({
        event:          filters.event ?? '',
        auditable_type: filters.auditable_type ?? '',
        date_from:      filters.date_from ?? '',
        date_to:        filters.date_to ?? '',
    });

    function applyFilter(e: React.FormEvent) {
        e.preventDefault();
        get('/audit-logs');
    }

    const hasFilters = Object.values(filters).some(Boolean);

    return (
        <AppLayout>
            <Head title="Audit Log" />
            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold text-slate-900">Audit Log</h1>
                    <p className="text-sm text-slate-500 mt-1">System-wide activity trail — {logs.total} entries</p>
                </div>

                <form onSubmit={applyFilter} className="flex flex-wrap items-end gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <div>
                        <label className="block text-xs font-medium text-slate-500 mb-1">Event</label>
                        <select value={data.event} onChange={(e) => setData('event', e.target.value)}
                            className="rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none">
                            <option value="">All events</option>
                            <option value="created">Created</option>
                            <option value="updated">Updated</option>
                            <option value="deleted">Deleted</option>
                            <option value="login">Login</option>
                        </select>
                    </div>
                    <div>
                        <label className="block text-xs font-medium text-slate-500 mb-1">Model Type</label>
                        <input type="text" placeholder="e.g. Invoice" value={data.auditable_type}
                            onChange={(e) => setData('auditable_type', e.target.value)}
                            className="rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none w-36" />
                    </div>
                    <div>
                        <label className="block text-xs font-medium text-slate-500 mb-1">From</label>
                        <input type="date" value={data.date_from} onChange={(e) => setData('date_from', e.target.value)}
                            className="rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none" />
                    </div>
                    <div>
                        <label className="block text-xs font-medium text-slate-500 mb-1">To</label>
                        <input type="date" value={data.date_to} onChange={(e) => setData('date_to', e.target.value)}
                            className="rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none" />
                    </div>
                    <button type="submit"
                        className="rounded-md bg-indigo-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-indigo-700">
                        Filter
                    </button>
                    {hasFilters && (
                        <Link href="/audit-logs" className="text-sm text-slate-500 hover:text-slate-700">Clear</Link>
                    )}
                </form>

                <div className="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <table className="w-full text-sm">
                        <thead className="bg-slate-50 text-xs text-slate-500 uppercase border-b border-slate-200">
                            <tr>
                                <th className="px-4 py-2 text-left font-medium">Time</th>
                                <th className="px-4 py-2 text-left font-medium">User</th>
                                <th className="px-4 py-2 text-left font-medium">Event</th>
                                <th className="px-4 py-2 text-left font-medium">Model</th>
                                <th className="px-4 py-2 text-left font-medium">ID</th>
                                <th className="px-4 py-2 text-left font-medium">Changes</th>
                                <th className="px-4 py-2 text-left font-medium">IP</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-50">
                            {logs.data.length === 0 ? (
                                <tr><td colSpan={7} className="px-4 py-8 text-center text-slate-400">No audit entries found.</td></tr>
                            ) : logs.data.map((entry) => (
                                <tr key={entry.id} className="hover:bg-slate-50 align-top">
                                    <td className="px-4 py-3 text-slate-500 whitespace-nowrap text-xs">
                                        {new Date(entry.created_at).toLocaleString()}
                                    </td>
                                    <td className="px-4 py-3 font-medium text-slate-800">
                                        {entry.user?.name ?? <span className="text-slate-400">System</span>}
                                    </td>
                                    <td className="px-4 py-3">
                                        <span className={`inline-flex rounded-full px-2 py-0.5 text-xs font-medium ${EVENT_COLORS[entry.event] ?? 'bg-slate-100 text-slate-600'}`}>
                                            {entry.event}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3 text-slate-700">{shortType(entry.auditable_type)}</td>
                                    <td className="px-4 py-3 text-slate-500">
                                        {entry.auditable_id ? `#${entry.auditable_id}` : '—'}
                                    </td>
                                    <td className="px-4 py-3 max-w-xs">
                                        {(entry.new_values && Object.keys(entry.new_values).length > 0) ? (
                                            <details className="text-xs">
                                                <summary className="cursor-pointer text-indigo-600 hover:underline">View changes</summary>
                                                <pre className="mt-1 bg-slate-50 rounded p-2 text-slate-600 overflow-x-auto text-xs max-h-32">
                                                    {JSON.stringify({ old: entry.old_values, new: entry.new_values }, null, 2)}
                                                </pre>
                                            </details>
                                        ) : '—'}
                                    </td>
                                    <td className="px-4 py-3 text-xs text-slate-400">{entry.ip_address ?? '—'}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {logs.last_page > 1 && (
                    <div className="flex justify-center gap-2">
                        {logs.prev_page_url && (
                            <Link href={logs.prev_page_url} className="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50">
                                &larr; Previous
                            </Link>
                        )}
                        <span className="px-3 py-1.5 text-sm text-slate-500">Page {logs.current_page} of {logs.last_page}</span>
                        {logs.next_page_url && (
                            <Link href={logs.next_page_url} className="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50">
                                Next &rarr;
                            </Link>
                        )}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
