import React from 'react';
import { Head, useForm } from '@inertiajs/react';

export default function Create() {
    const { data, setData, post, errors } = useForm({
        type: 'outbound',
        carrier: '',
        tracking_number: '',
        service_level: 'standard',
        origin_address: '',
        destination_address: '',
        ship_date: '',
        estimated_delivery: '',
        weight_kg: '',
        freight_cost: '',
        notes: '',
    });

    return (
        <>
            <Head title="New Shipment" />
            <div className="p-6 max-w-2xl">
                <h1 className="text-2xl font-bold mb-6">New Shipment</h1>
                <form onSubmit={e => { e.preventDefault(); post('/inventory/shipments'); }}>
                    <div className="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label className="block text-sm font-medium mb-1">Type</label>
                            <select
                                value={data.type}
                                onChange={e => setData('type', e.target.value)}
                                className="w-full border rounded px-3 py-2"
                            >
                                <option value="outbound">Outbound</option>
                                <option value="inbound">Inbound</option>
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium mb-1">Service Level</label>
                            <select
                                value={data.service_level}
                                onChange={e => setData('service_level', e.target.value)}
                                className="w-full border rounded px-3 py-2"
                            >
                                <option value="standard">Standard</option>
                                <option value="express">Express</option>
                                <option value="overnight">Overnight</option>
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium mb-1">Carrier</label>
                            <input
                                value={data.carrier}
                                onChange={e => setData('carrier', e.target.value)}
                                className="w-full border rounded px-3 py-2"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium mb-1">Tracking Number</label>
                            <input
                                value={data.tracking_number}
                                onChange={e => setData('tracking_number', e.target.value)}
                                className="w-full border rounded px-3 py-2"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium mb-1">Ship Date</label>
                            <input
                                type="date"
                                value={data.ship_date}
                                onChange={e => setData('ship_date', e.target.value)}
                                className="w-full border rounded px-3 py-2"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium mb-1">Est. Delivery</label>
                            <input
                                type="date"
                                value={data.estimated_delivery}
                                onChange={e => setData('estimated_delivery', e.target.value)}
                                className="w-full border rounded px-3 py-2"
                            />
                        </div>
                    </div>
                    <div className="mb-4">
                        <label className="block text-sm font-medium mb-1">Origin Address</label>
                        <textarea
                            value={data.origin_address}
                            onChange={e => setData('origin_address', e.target.value)}
                            rows={2}
                            className="w-full border rounded px-3 py-2"
                        />
                    </div>
                    <div className="mb-4">
                        <label className="block text-sm font-medium mb-1">Destination Address</label>
                        <textarea
                            value={data.destination_address}
                            onChange={e => setData('destination_address', e.target.value)}
                            rows={2}
                            className="w-full border rounded px-3 py-2"
                        />
                    </div>
                    <div className="mb-6">
                        <label className="block text-sm font-medium mb-1">Notes</label>
                        <textarea
                            value={data.notes}
                            onChange={e => setData('notes', e.target.value)}
                            rows={3}
                            className="w-full border rounded px-3 py-2"
                        />
                    </div>
                    <button type="submit" className="bg-blue-600 text-white px-6 py-2 rounded">
                        Create Shipment
                    </button>
                </form>
            </div>
        </>
    );
}
