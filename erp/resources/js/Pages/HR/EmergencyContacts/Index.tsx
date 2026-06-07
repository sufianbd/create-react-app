import React from 'react';
import { Head, Link, router } from '@inertiajs/react';

interface Contact {
    id: number;
    name: string;
    relationship: string;
    phone_primary: string;
    phone_secondary: string | null;
    email: string | null;
    is_primary: boolean;
}

interface Employee { id: number; first_name: string; last_name: string; }

export default function Index({ employee, contacts }: { employee: Employee; contacts: Contact[] }) {
    return (
        <>
            <Head title="Emergency Contacts" />
            <div className="p-6">
                <div className="flex justify-between items-center mb-4">
                    <div>
                        <h1 className="text-2xl font-bold">Emergency Contacts</h1>
                        <p className="text-gray-500">{employee.first_name} {employee.last_name}</p>
                    </div>
                    <Link href={`/hr/employees/${employee.id}/emergency-contacts/create`} className="bg-blue-600 text-white px-4 py-2 rounded">
                        Add Contact
                    </Link>
                </div>
                <table className="w-full border rounded">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="p-3 text-left">Name</th>
                            <th className="p-3 text-left">Relationship</th>
                            <th className="p-3 text-left">Phone (Primary)</th>
                            <th className="p-3 text-left">Email</th>
                            <th className="p-3 text-left">Primary</th>
                            <th className="p-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {contacts.map(c => (
                            <tr key={c.id} className="border-t">
                                <td className="p-3 font-medium">{c.name}</td>
                                <td className="p-3">{c.relationship}</td>
                                <td className="p-3">{c.phone_primary}</td>
                                <td className="p-3">{c.email ?? '—'}</td>
                                <td className="p-3">
                                    {c.is_primary
                                        ? <span className="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Primary</span>
                                        : <button onClick={() => router.post(`/hr/employees/${employee.id}/emergency-contacts/${c.id}/mark-primary`)} className="text-blue-600 text-sm hover:underline">Set Primary</button>
                                    }
                                </td>
                                <td className="p-3 space-x-2">
                                    <Link href={`/hr/emergency-contacts/${c.id}/edit`} className="text-gray-600 text-sm hover:underline">Edit</Link>
                                    <button onClick={() => router.delete(`/hr/emergency-contacts/${c.id}`)} className="text-red-600 text-sm hover:underline">Delete</button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </>
    );
}
