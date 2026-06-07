import React from 'react';
import { Head, router } from '@inertiajs/react';

interface RmaRequest {
    id: number;
    rma_number: string | null;
    type: string;
    status: string;
    contact_name: string | null;
    reason: string;
    disposition: string;
    requested_date: string | null;
    received_date: string | null;
    inspected_date: string | null;
    notes: string | null;
    items: { id: number; description: string | null; quantity_requested: number; quantity_received: number; condition: string }[];
}

export default function Show({ rmaRequest }: { rmaRequest: RmaRequest }) {
    const id = rmaRequest.id;
    return (
        <>
            <Head title={rmaRequest.rma_number ?? `RMA #${id}`} />
            <div className="p-6 max-w-3xl">
                <div className="flex justify-between items-start mb-6">
                    <div>
                        <h1 className="text-2xl font-bold">{rmaRequest.rma_number ?? `RMA #${id}`}</h1>
                        <p className="text-gray-500 capitalize">{rmaRequest.type.replace('_', ' ')} · {rmaRequest.status}</p>
                    </div>
                    <div className="space-x-2">
                        {rmaRequest.status === 'pending' && <button onClick={() => router.post(`/inventory/rma-requests/${id}/approve`)} className="bg-blue-600 text-white px-4 py-2 rounded">Approve</button>}
                        {rmaRequest.status === 'approved' && <button onClick={() => router.post(`/inventory/rma-requests/${id}/receive`)} className="bg-green-600 text-white px-4 py-2 rounded">Receive</button>}
                        {rmaRequest.status === 'received' && <button onClick={() => router.post(`/inventory/rma-requests/${id}/inspect`)} className="bg-purple-600 text-white px-4 py-2 rounded">Inspect</button>}
                        {rmaRequest.status === 'inspected' && <button onClick={() => router.post(`/inventory/rma-requests/${id}/close`)} className="bg-gray-600 text-white px-4 py-2 rounded">Close</button>}
                    </div>
                </div>
                <div className="grid grid-cols-2 gap-4 mb-4">
                    <div><span className="font-medium">Disposition:</span> <span className="capitalize">{rmaRequest.disposition}</span></div>
                    <div><span className="font-medium">Contact:</span> {rmaRequest.contact_name ?? '—'}</div>
                    <div><span className="font-medium">Requested:</span> {rmaRequest.requested_date ?? '—'}</div>
                    <div><span className="font-medium">Received:</span> {rmaRequest.received_date ?? '—'}</div>
                </div>
                <div className="mb-4"><span className="font-medium">Reason:</span> {rmaRequest.reason}</div>
                {rmaRequest.items.length > 0 && (
                    <table className="w-full border rounded">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="p-2 text-left">Item</th>
                                <th className="p-2 text-right">Qty Req.</th>
                                <th className="p-2 text-right">Qty Recv.</th>
                                <th className="p-2 text-left">Condition</th>
                            </tr>
                        </thead>
                        <tbody>
                            {rmaRequest.items.map(item => (
                                <tr key={item.id} className="border-t">
                                    <td className="p-2">{item.description ?? '—'}</td>
                                    <td className="p-2 text-right">{item.quantity_requested}</td>
                                    <td className="p-2 text-right">{item.quantity_received}</td>
                                    <td className="p-2 capitalize">{item.condition}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>
        </>
    );
}
