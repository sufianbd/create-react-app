import React from 'react';
import { Head, Link, router } from '@inertiajs/react';

interface Shipment {
    id: number;
    shipment_number: string | null;
    type: string;
    status: string;
    carrier: string | null;
    tracking_number: string | null;
    ship_date: string | null;
    estimated_delivery: string | null;
}

interface Props {
    shipments: { data: Shipment[]; current_page: number; last_page: number };
}

export default function Index({ shipments }: Props) {
    const badge = (s: string) => {
        const colors: Record<string, string> = {
            pending:    'bg-yellow-100 text-yellow-800',
            'in-transit': 'bg-blue-100 text-blue-800',
            delivered:  'bg-green-100 text-green-800',
            returned:   'bg-orange-100 text-orange-800',
            cancelled:  'bg-red-100 text-red-800',
        };
        return colors[s] ?? 'bg-gray-100 text-gray-800';
    };

    return (
        <>
            <Head title="Shipments" />
            <div className="p-6">
                <div className="flex justify-between items-center mb-4">
                    <h1 className="text-2xl font-bold">Shipments</h1>
                    <Link href="/inventory/shipments/create" className="bg-blue-600 text-white px-4 py-2 rounded">
                        New Shipment
                    </Link>
                </div>
                <table className="w-full border rounded">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="p-3 text-left">Number</th>
                            <th className="p-3 text-left">Type</th>
                            <th className="p-3 text-left">Status</th>
                            <th className="p-3 text-left">Carrier</th>
                            <th className="p-3 text-left">Tracking</th>
                            <th className="p-3 text-left">Ship Date</th>
                            <th className="p-3 text-left">Est. Delivery</th>
                            <th className="p-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {shipments.data.map(s => (
                            <tr key={s.id} className="border-t">
                                <td className="p-3">
                                    <Link href={`/inventory/shipments/${s.id}`} className="text-blue-600 hover:underline">
                                        {s.shipment_number ?? `#${s.id}`}
                                    </Link>
                                </td>
                                <td className="p-3 capitalize">{s.type}</td>
                                <td className="p-3">
                                    <span className={`px-2 py-1 rounded text-xs font-medium ${badge(s.status)}`}>
                                        {s.status}
                                    </span>
                                </td>
                                <td className="p-3">{s.carrier ?? '—'}</td>
                                <td className="p-3">{s.tracking_number ?? '—'}</td>
                                <td className="p-3">{s.ship_date ?? '—'}</td>
                                <td className="p-3">{s.estimated_delivery ?? '—'}</td>
                                <td className="p-3 space-x-2">
                                    {s.status === 'pending' && (
                                        <button
                                            onClick={() => router.post(`/inventory/shipments/${s.id}/dispatch`)}
                                            className="text-blue-600 hover:underline text-sm"
                                        >
                                            Dispatch
                                        </button>
                                    )}
                                    {s.status === 'in-transit' && (
                                        <button
                                            onClick={() => router.post(`/inventory/shipments/${s.id}/deliver`)}
                                            className="text-green-600 hover:underline text-sm"
                                        >
                                            Mark Delivered
                                        </button>
                                    )}
                                    <Link href={`/inventory/shipments/${s.id}/edit`} className="text-gray-600 hover:underline text-sm">
                                        Edit
                                    </Link>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </>
    );
}
