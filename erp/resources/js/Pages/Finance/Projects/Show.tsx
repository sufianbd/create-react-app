import { Head } from '@inertiajs/react';
import { useForm, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import type { PageProps } from '@/types';
import type { Project, ProjectTimeEntry, Contact } from '@/types/finance';
import AttachmentPanel from '@/Components/Finance/AttachmentPanel';
import { usePermission } from '@/Hooks/usePermission';

interface Props extends PageProps {
    project: Project;
    contacts: Contact[];
}

const statusColors: Record<string, string> = {
    draft: 'bg-slate-100 text-slate-600',
    active: 'bg-green-50 text-green-700',
    completed: 'bg-blue-50 text-blue-700',
    cancelled: 'bg-red-50 text-red-700',
};

export default function ProjectShow({ project, contacts }: Props) {
    const { can } = usePermission();
    const [selectedEntries, setSelectedEntries] = useState<number[]>([]);

    const timeForm = useForm({
        description: '',
        hours: '',
        billable: true,
        entry_date: new Date().toISOString().split('T')[0],
    });

    const editForm = useForm({
        name: project.name,
        description: project.description ?? '',
        status: project.status,
        budget: project.budget != null ? String(project.budget) : '',
        contact_id: project.contact_id != null ? String(project.contact_id) : '',
        starts_on: project.starts_on ?? '',
        ends_on: project.ends_on ?? '',
    });

    function submitTimeEntry(e: React.FormEvent) {
        e.preventDefault();
        timeForm.post(`/finance/projects/${project.id}/time-entries`, {
            onSuccess: () => timeForm.reset(),
        });
    }

    function submitEdit(e: React.FormEvent) {
        e.preventDefault();
        editForm.patch(`/finance/projects/${project.id}`);
    }

    function toggleEntry(id: number) {
        setSelectedEntries((prev) =>
            prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]
        );
    }

    function markBilled() {
        router.post(`/finance/projects/${project.id}/mark-billed`, {
            entry_ids: selectedEntries,
        }, {
            onSuccess: () => setSelectedEntries([]),
        });
    }

    return (
        <AppLayout>
            <Head title={project.name} />
            <div className="mx-auto max-w-5xl space-y-6">
                {/* Header */}
                <div className="flex items-start justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">{project.name}</h1>
                        <div className="mt-1 flex items-center gap-3">
                            <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${statusColors[project.status] ?? 'bg-slate-100 text-slate-600'}`}>
                                {project.status}
                            </span>
                            {project.contact && (
                                <span className="text-sm text-slate-500">{project.contact.name}</span>
                            )}
                        </div>
                    </div>
                    <div className="text-right text-sm text-slate-500">
                        {project.budget != null && (
                            <div>Budget: <span className="font-medium text-slate-900">{project.budget.toFixed(2)}</span></div>
                        )}
                        {project.starts_on && <div>Start: {project.starts_on}</div>}
                        {project.ends_on && <div>End: {project.ends_on}</div>}
                    </div>
                </div>

                {project.description && (
                    <p className="text-sm text-slate-600">{project.description}</p>
                )}

                {/* Stats */}
                <div className="grid grid-cols-2 gap-4">
                    <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                        <div className="text-sm text-slate-500">Total Hours</div>
                        <div className="text-2xl font-semibold text-slate-900">{(project.total_hours ?? 0).toFixed(1)}</div>
                    </div>
                    <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                        <div className="text-sm text-slate-500">Billable Hours (Unbilled)</div>
                        <div className="text-2xl font-semibold text-slate-900">{(project.billable_hours ?? 0).toFixed(1)}</div>
                    </div>
                </div>

                {/* Time Entries */}
                <div className="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div className="flex items-center justify-between px-4 py-3 border-b border-slate-200">
                        <h2 className="text-base font-semibold text-slate-900">Time Entries</h2>
                        {selectedEntries.length > 0 && (
                            <Button onClick={markBilled} variant="secondary">
                                Mark {selectedEntries.length} as Billed
                            </Button>
                        )}
                    </div>
                    <table className="min-w-full divide-y divide-slate-200">
                        <thead className="bg-slate-50">
                            <tr>
                                <th className="w-8 px-4 py-3" />
                                <th className="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">User</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Description</th>
                                <th className="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Hours</th>
                                <th className="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Billable</th>
                                <th className="px-4 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Billed</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {(project.time_entries ?? []).length === 0 && (
                                <tr>
                                    <td colSpan={7} className="px-4 py-6 text-center text-sm text-slate-400">
                                        No time entries yet.
                                    </td>
                                </tr>
                            )}
                            {(project.time_entries ?? []).map((entry: ProjectTimeEntry) => (
                                <tr key={entry.id} className="hover:bg-slate-50">
                                    <td className="px-4 py-3">
                                        {!entry.billed && (
                                            <input
                                                type="checkbox"
                                                checked={selectedEntries.includes(entry.id)}
                                                onChange={() => toggleEntry(entry.id)}
                                                className="rounded border-slate-300 text-indigo-600"
                                            />
                                        )}
                                    </td>
                                    <td className="px-4 py-3 text-sm text-slate-600">{entry.entry_date}</td>
                                    <td className="px-4 py-3 text-sm text-slate-600">{entry.user?.name ?? '—'}</td>
                                    <td className="px-4 py-3 text-sm text-slate-900">{entry.description}</td>
                                    <td className="px-4 py-3 text-sm text-right text-slate-700">{entry.hours.toFixed(2)}</td>
                                    <td className="px-4 py-3 text-center">
                                        <input type="checkbox" checked={entry.billable} readOnly className="rounded border-slate-300 text-indigo-600" />
                                    </td>
                                    <td className="px-4 py-3 text-center">
                                        {entry.billed ? (
                                            <span className="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-green-50 text-green-700">Billed</span>
                                        ) : (
                                            <span className="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-slate-100 text-slate-500">Unbilled</span>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Log Time Form */}
                <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 className="text-base font-semibold text-slate-900 mb-4">Log Time</h2>
                    <form onSubmit={submitTimeEntry} className="space-y-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="col-span-2">
                                <label className="block text-sm font-medium text-slate-700 mb-1">
                                    Description <span className="text-red-500">*</span>
                                </label>
                                <input
                                    value={timeForm.data.description}
                                    onChange={(e) => timeForm.setData('description', e.target.value)}
                                    className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                />
                                {timeForm.errors.description && <p className="mt-1 text-xs text-red-500">{timeForm.errors.description}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">
                                    Hours <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    min={0.1}
                                    step={0.1}
                                    value={timeForm.data.hours}
                                    onChange={(e) => timeForm.setData('hours', e.target.value)}
                                    className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                />
                                {timeForm.errors.hours && <p className="mt-1 text-xs text-red-500">{timeForm.errors.hours}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Date</label>
                                <input
                                    type="date"
                                    value={timeForm.data.entry_date}
                                    onChange={(e) => timeForm.setData('entry_date', e.target.value)}
                                    className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                />
                            </div>
                        </div>
                        <div className="flex items-center gap-4">
                            <label className="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    checked={timeForm.data.billable}
                                    onChange={(e) => timeForm.setData('billable', e.target.checked)}
                                    className="rounded border-slate-300 text-indigo-600"
                                />
                                <span className="text-slate-700">Billable</span>
                            </label>
                            <Button type="submit" disabled={timeForm.processing}>Log Time</Button>
                        </div>
                    </form>
                </div>

                {/* Attachments */}
                <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <AttachmentPanel
                        attachments={project.attachments ?? []}
                        modelType="projects"
                        modelId={project.id}
                        canDelete={can('finance.delete')}
                    />
                </div>

                {/* Edit Project */}
                <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 className="text-base font-semibold text-slate-900 mb-4">Edit Project</h2>
                    <form onSubmit={submitEdit} className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">Name</label>
                            <input
                                value={editForm.data.name}
                                onChange={(e) => editForm.setData('name', e.target.value)}
                                className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            />
                            {editForm.errors.name && <p className="mt-1 text-xs text-red-500">{editForm.errors.name}</p>}
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">Description</label>
                            <textarea
                                value={editForm.data.description}
                                onChange={(e) => editForm.setData('description', e.target.value)}
                                rows={2}
                                className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Status</label>
                                <select
                                    value={editForm.data.status}
                                    onChange={(e) => editForm.setData('status', e.target.value as typeof editForm.data.status)}
                                    className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Budget</label>
                                <input
                                    type="number"
                                    min={0}
                                    step={0.01}
                                    value={editForm.data.budget}
                                    onChange={(e) => editForm.setData('budget', e.target.value)}
                                    className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                />
                            </div>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1">Contact</label>
                            <select
                                value={editForm.data.contact_id}
                                onChange={(e) => editForm.setData('contact_id', e.target.value)}
                                className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                            >
                                <option value="">None</option>
                                {contacts.map((c) => (
                                    <option key={c.id} value={c.id}>{c.name}</option>
                                ))}
                            </select>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Start Date</label>
                                <input
                                    type="date"
                                    value={editForm.data.starts_on}
                                    onChange={(e) => editForm.setData('starts_on', e.target.value)}
                                    className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">End Date</label>
                                <input
                                    type="date"
                                    value={editForm.data.ends_on}
                                    onChange={(e) => editForm.setData('ends_on', e.target.value)}
                                    className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                />
                            </div>
                        </div>
                        <div className="flex justify-end">
                            <Button type="submit" disabled={editForm.processing}>Save Changes</Button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
