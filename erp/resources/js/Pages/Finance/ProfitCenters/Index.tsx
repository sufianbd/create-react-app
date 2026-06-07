import React from 'react';
import { Head, Link, router } from '@inertiajs/react';

interface ProfitCenter {
    id: number;
    code: string;
    name: string;
    type: string;
    status: string;
    budget: number | null;
}

interface Props {
    profitCenters: { data: ProfitCenter[]; current_page: number; last_page: number };
}

export default function Index({ profitCenters }: Props) {
    return (
        <>
            <Head title="Profit Centers" />
            <div className="p-6">
                <div className="flex justify-between items-center mb-4">
                    <h1 className="text-2xl font-bold">Profit Centers</h1>
                    <Link href="/finance/profit-centers/create" className="bg-blue-600 text-white px-4 py-2 rounded">
                        New Center
                    </Link>
                </div>
                <table className="w-full border rounded">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="p-3 text-left">Code</th>
                            <th className="p-3 text-left">Name</th>
                            <th className="p-3 text-left">Type</th>
                            <th className="p-3 text-left">Status</th>
                            <th className="p-3 text-right">Budget</th>
                            <th className="p-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {profitCenters.data.map(pc => (
                            <tr key={pc.id} className="border-t">
                                <td className="p-3 font-mono">{pc.code}</td>
                                <td className="p-3">
                                    <Link href={`/finance/profit-centers/${pc.id}`} className="text-blue-600 hover:underline">
                                        {pc.name}
                                    </Link>
                                </td>
                                <td className="p-3 capitalize">{pc.type}</td>
                                <td className="p-3">
                                    <span className={`px-2 py-1 rounded text-xs font-medium ${pc.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`}>
                                        {pc.status}
                                    </span>
                                </td>
                                <td className="p-3 text-right">{pc.budget != null ? pc.budget.toLocaleString() : '—'}</td>
                                <td className="p-3 space-x-2">
                                    {pc.status === 'active' ? (
                                        <button onClick={() => router.post(`/finance/profit-centers/${pc.id}/deactivate`)} className="text-yellow-600 hover:underline text-sm">Deactivate</button>
                                    ) : (
                                        <button onClick={() => router.post(`/finance/profit-centers/${pc.id}/activate`)} className="text-green-600 hover:underline text-sm">Activate</button>
                                    )}
                                    <Link href={`/finance/profit-centers/${pc.id}/edit`} className="text-gray-600 hover:underline text-sm">Edit</Link>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </>
    );
}
