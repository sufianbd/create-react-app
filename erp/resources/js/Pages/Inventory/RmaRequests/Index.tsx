import React from 'react';
import { Head, Link, router } from '@inertiajs/react';

interface RmaRequest {
    id: number;
    rma_number: string | null;
    type: string;
    status: string;
    contact_name: string | null;
    reason: string;
    disposition: string;
}

interface Props {
    rmaRequests: { data: RmaRequest[]; current_page: number; last_page: number };
}

const STATUS_COLORS: Record<string, string> = {
    pending:   'bg-yellow-100 text-yellow-800',
    approved:  'bg-blue-100 text-blue-800',
    received:  'bg-indigo-100 text-indigo-800',
    inspected: 'bg-purple-100 text-purple-800',
    closed:    'bg-green-100 text-green-800',
    rejected:  'bg-red-100 text-red-800',
};

export default function Index({ rmaRequests }: Props) {
    return (
        <>
            <Head title="RMA Requests" />
            <div className="p-6">
                <div className="flex justify-between items-center mb-4">
                    <h1 className="text-2xl font-bold">RMA Requests</h1>
                    <Link href="/inventory/rma-requests/create" className="bg-blue-600 text-white px-4 py-2 rounded">
                        New RMA
                    </Link>
                </div>
                <table className="w-full border rounded">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="p-3 text-left">RMA #</th>
                            <th className="p-3 text-left">Type</th>
                            <th className="p-3 text-left">Status</th>
                            <th className="p-3 text-left">Contact</th>
                            <th className="p-3 text-left">Disposition</th>
                            <th className="p-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {rmaRequests.data.map(r => (
                            <tr key={r.id} className="border-t">
                                <td className="p-3">
                                    <Link href={`/inventory/rma-requests/${r.id}`} className="text-blue-600 hover:underline">
                                        {r.rma_number ?? `#${r.id}`}
                                    </Link>
                                </td>
                                <td className="p-3 capitalize">{r.type.replace('_', ' ')}</td>
                                <td className="p-3">
                                    <span className={`px-2 py-1 rounded text-xs font-medium ${STATUS_COLORS[r.status] ?? 'bg-gray-100 text-gray-800'}`}>
                                        {r.status}
                                    </span>
                                </td>
                                <td className="p-3">{r.contact_name ?? '—'}</td>
                                <td className="p-3 capitalize">{r.disposition}</td>
                                <td className="p-3 space-x-2">
                                    {r.status === 'pending' && (
                                        <button onClick={() => router.post(`/inventory/rma-requests/${r.id}/approve`)} className="text-blue-600 text-sm hover:underline">Approve</button>
                                    )}
                                    {r.status === 'approved' && (
                                        <button onClick={() => router.post(`/inventory/rma-requests/${r.id}/receive`)} className="text-green-600 text-sm hover:underline">Receive</button>
                                    )}
                                    <Link href={`/inventory/rma-requests/${r.id}/edit`} className="text-gray-600 text-sm hover:underline">Edit</Link>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </>
    );
}
