import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import { usePermission } from '@/Hooks/usePermission';
import type { PageProps } from '@/types';
import type { Department } from '@/types/hr';

interface Props extends PageProps { departments: Department[]; }

export default function DepartmentsIndex({ departments }: Props) {
    const { can } = usePermission();
    const [editing, setEditing] = useState<Department | null>(null);
    const [showCreate, setShowCreate] = useState(false);

    const createForm = useForm({ name: '', description: '', is_active: true });
    const editForm   = useForm({ name: editing?.name ?? '', description: editing?.description ?? '', is_active: editing?.is_active ?? true });

    function submitCreate(e: React.FormEvent) {
        e.preventDefault();
        createForm.post('/hr/departments', {
            onSuccess: () => { createForm.reset(); setShowCreate(false); },
        });
    }

    function startEdit(dept: Department) {
        setEditing(dept);
        editForm.setData({ name: dept.name, description: dept.description ?? '', is_active: dept.is_active });
    }

    function submitEdit(e: React.FormEvent) {
        e.preventDefault();
        if (!editing) return;
        editForm.put(`/hr/departments/${editing.id}`, {
            onSuccess: () => setEditing(null),
        });
    }

    function handleDelete(id: number, name: string) {
        if (!confirm(`Delete department "${name}"?`)) return;
        router.delete(`/hr/departments/${id}`);
    }

    return (
        <AppLayout>
            <Head title="Departments" />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold text-slate-900">Departments</h1>
                    {can('hr.create') && (
                        <Button onClick={() => setShowCreate(!showCreate)}>Add Department</Button>
                    )}
                </div>

                {showCreate && (
                    <form onSubmit={submitCreate} className="rounded-lg border border-indigo-200 bg-indigo-50 p-4 shadow-sm space-y-3">
                        <h2 className="text-sm font-semibold text-indigo-900">New Department</h2>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <input placeholder="Name *" value={createForm.data.name}
                                    onChange={(e) => createForm.setData('name', e.target.value)}
                                    className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none" />
                                {createForm.errors.name && <p className="mt-1 text-xs text-red-500">{createForm.errors.name}</p>}
                            </div>
                            <div>
                                <input placeholder="Description" value={createForm.data.description}
                                    onChange={(e) => createForm.setData('description', e.target.value)}
                                    className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none" />
                            </div>
                        </div>
                        <div className="flex justify-end gap-2">
                            <Button type="button" variant="secondary" size="sm" onClick={() => setShowCreate(false)}>Cancel</Button>
                            <Button type="submit" size="sm" disabled={createForm.processing}>Create</Button>
                        </div>
                    </form>
                )}

                <div className="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <table className="w-full text-sm">
                        <thead className="bg-slate-50 border-b border-slate-200 text-xs text-slate-500 uppercase">
                            <tr>
                                <th className="px-4 py-2 text-left font-medium">Name</th>
                                <th className="px-4 py-2 text-left font-medium">Description</th>
                                <th className="px-4 py-2 text-right font-medium">Employees</th>
                                <th className="px-4 py-2 text-left font-medium">Status</th>
                                <th className="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {departments.map((dept) => (
                                <tr key={dept.id}>
                                    {editing?.id === dept.id ? (
                                        <td colSpan={5} className="px-4 py-3">
                                            <form onSubmit={submitEdit} className="flex items-center gap-3">
                                                <input value={editForm.data.name}
                                                    onChange={(e) => editForm.setData('name', e.target.value)}
                                                    className="flex-1 rounded-md border border-slate-300 px-2 py-1 text-sm focus:border-indigo-500 focus:outline-none" />
                                                <input value={editForm.data.description}
                                                    onChange={(e) => editForm.setData('description', e.target.value)}
                                                    className="flex-1 rounded-md border border-slate-300 px-2 py-1 text-sm focus:border-indigo-500 focus:outline-none" />
                                                <label className="flex items-center gap-1 text-xs">
                                                    <input type="checkbox" checked={editForm.data.is_active}
                                                        onChange={(e) => editForm.setData('is_active', e.target.checked)} />
                                                    Active
                                                </label>
                                                <Button type="submit" size="sm" disabled={editForm.processing}>Save</Button>
                                                <Button type="button" variant="secondary" size="sm" onClick={() => setEditing(null)}>Cancel</Button>
                                            </form>
                                        </td>
                                    ) : (
                                        <>
                                            <td className="px-4 py-3 font-medium text-slate-900">{dept.name}</td>
                                            <td className="px-4 py-3 text-slate-500">{dept.description ?? '—'}</td>
                                            <td className="px-4 py-3 text-right text-slate-600">{dept.employees_count ?? 0}</td>
                                            <td className="px-4 py-3">
                                                <span className={`text-xs ${dept.is_active ? 'text-green-600' : 'text-slate-400'}`}>
                                                    {dept.is_active ? 'Active' : 'Inactive'}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3">
                                                <div className="flex gap-3">
                                                    {can('hr.update') && (
                                                        <button onClick={() => startEdit(dept)} className="text-sm text-indigo-600 hover:text-indigo-800">Edit</button>
                                                    )}
                                                    {can('hr.delete') && (
                                                        <button onClick={() => handleDelete(dept.id, dept.name)} className="text-sm text-red-600 hover:text-red-800">Delete</button>
                                                    )}
                                                </div>
                                            </td>
                                        </>
                                    )}
                                </tr>
                            ))}
                            {departments.length === 0 && (
                                <tr>
                                    <td colSpan={5} className="px-4 py-8 text-center text-sm text-slate-400">No departments yet.</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
