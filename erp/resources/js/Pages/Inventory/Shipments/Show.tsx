import React from 'react';
import { Head, router } from '@inertiajs/react';

interface ShipmentItem { id: number; description: string | null; quantity: number; sku: string | null; }
interface Shipment {
    id: number;
    shipment_number: string | null;
    type: string;
    status: string;
    carrier: string | null;
    tracking_number: string | null;
    service_level: string | null;
    origin_address: string | null;
    destination_address: string | null;
    ship_date: string | null;
    estimated_delivery: string | null;
    actual_delivery: string | null;
    weight_kg: number | null;
    freight_cost: number | null;
    notes: string | null;
    items: ShipmentItem[];
}

export default function Show({ shipment }: { shipment: Shipment }) {
    return (
        <>
            <Head title={`Shipment ${shipment.shipment_number ?? shipment.id}`} />
            <div className="p-6 max-w-3xl">
                <div className="flex justify-between items-start mb-6">
                    <div>
                        <h1 className="text-2xl font-bold">{shipment.shipment_number ?? `Shipment #${shipment.id}`}</h1>
                        <p className="text-gray-500 capitalize">{shipment.type} · {shipment.status}</p>
                    </div>
                    <div className="space-x-2">
                        {shipment.status === 'pending' && (
                            <button
                                onClick={() => router.post(`/inventory/shipments/${shipment.id}/dispatch`)}
                                className="bg-blue-600 text-white px-4 py-2 rounded"
                            >
                                Dispatch
                            </button>
                        )}
                        {shipment.status === 'in-transit' && (
                            <button
                                onClick={() => router.post(`/inventory/shipments/${shipment.id}/deliver`)}
                                className="bg-green-600 text-white px-4 py-2 rounded"
                            >
                                Mark Delivered
                            </button>
                        )}
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-4 mb-6">
                    <div><span className="font-medium">Carrier:</span> {shipment.carrier ?? '—'}</div>
                    <div><span className="font-medium">Tracking:</span> {shipment.tracking_number ?? '—'}</div>
                    <div><span className="font-medium">Service:</span> {shipment.service_level ?? '—'}</div>
                    <div><span className="font-medium">Ship Date:</span> {shipment.ship_date ?? '—'}</div>
                    <div><span className="font-medium">Est. Delivery:</span> {shipment.estimated_delivery ?? '—'}</div>
                    <div><span className="font-medium">Actual Delivery:</span> {shipment.actual_delivery ?? '—'}</div>
                </div>

                {shipment.items.length > 0 && (
                    <div>
                        <h2 className="text-lg font-semibold mb-2">Items</h2>
                        <table className="w-full border rounded">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="p-2 text-left">SKU</th>
                                    <th className="p-2 text-left">Description</th>
                                    <th className="p-2 text-right">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                {shipment.items.map(item => (
                                    <tr key={item.id} className="border-t">
                                        <td className="p-2">{item.sku ?? '—'}</td>
                                        <td className="p-2">{item.description ?? '—'}</td>
                                        <td className="p-2 text-right">{item.quantity}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </>
    );
}
