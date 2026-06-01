import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import type { PageProps } from '@/types';
import type { Project } from '@/types/finance';

interface Props extends PageProps {
    projects: Project[];
}

const statusColors: Record<string, string> = {
    draft: 'bg-slate-100 text-slate-600',
    active: 'bg-green-50 text-green-700',
    completed: 'bg-blue-50 text-blue-700',
    cancelled: 'bg-red-50 text-red-700',
};

export default function ProjectIndex({ projects }: Props) {
    return (
        <AppLayout>
            <Head title="Projects" />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold text-slate-900">Projects</h1>
                    <Link href="/finance/projects/create"><Button>New Project</Button></Link>
                </div>

                <div className="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <table className="min-w-full divide-y divide-slate-200">
                        <thead className="bg-slate-50">
                            <tr>
                                <th className="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Contact</th>
                                <th className="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Time Entries</th>
                                <th className="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Budget</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Starts</th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Ends</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {projects.length === 0 && (
                                <tr>
                                    <td colSpan={7} className="px-4 py-8 text-center text-sm text-slate-400">
                                        No projects yet. Create one to get started.
                                    </td>
                                </tr>
                            )}
                            {projects.map((project) => (
                                <tr key={project.id} className="hover:bg-slate-50">
                                    <td className="px-4 py-3 text-sm font-medium text-slate-900">
                                        <Link href={`/finance/projects/${project.id}`} className="hover:text-indigo-600">
                                            {project.name}
                                        </Link>
                                    </td>
                                    <td className="px-4 py-3 text-sm">
                                        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${statusColors[project.status] ?? 'bg-slate-100 text-slate-600'}`}>
                                            {project.status}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3 text-sm text-slate-600">
                                        {project.contact?.name ?? '—'}
                                    </td>
                                    <td className="px-4 py-3 text-sm text-right text-slate-600">
                                        {project.time_entries_count ?? 0}
                                    </td>
                                    <td className="px-4 py-3 text-sm text-right text-slate-600">
                                        {project.budget != null ? project.budget.toFixed(2) : '—'}
                                    </td>
                                    <td className="px-4 py-3 text-sm text-slate-600">
                                        {project.starts_on ?? '—'}
                                    </td>
                                    <td className="px-4 py-3 text-sm text-slate-600">
                                        {project.ends_on ?? '—'}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
