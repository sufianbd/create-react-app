import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Button } from '@/Components/Common/Button';
import type { PageProps } from '@/types';

type Props = PageProps;

export default function TrainingCoursesCreate(_props: Props) {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        provider: '',
        type: 'internal' as 'internal' | 'external' | 'online' | 'certification',
        duration_hours: '',
        description: '',
        is_active: true,
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post('/hr/training-courses');
    }

    return (
        <AppLayout>
            <Head title="New Training Course" />
            <div className="mx-auto max-w-2xl space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold text-slate-900">New Training Course</h1>
                    <p className="text-sm text-slate-500 mt-1">Define a reusable training course.</p>
                </div>

                <form onSubmit={submit} className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">
                            Title <span className="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            value={data.title}
                            onChange={(e) => setData('title', e.target.value)}
                            className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                        {errors.title && <p className="mt-1 text-xs text-red-600">{errors.title}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">Provider</label>
                        <input
                            type="text"
                            value={data.provider}
                            onChange={(e) => setData('provider', e.target.value)}
                            className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                        {errors.provider && <p className="mt-1 text-xs text-red-600">{errors.provider}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">
                            Type <span className="text-red-500">*</span>
                        </label>
                        <select
                            value={data.type}
                            onChange={(e) => setData('type', e.target.value as typeof data.type)}
                            className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                            <option value="internal">Internal</option>
                            <option value="external">External</option>
                            <option value="online">Online</option>
                            <option value="certification">Certification</option>
                        </select>
                        {errors.type && <p className="mt-1 text-xs text-red-600">{errors.type}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">Duration (hours)</label>
                        <input
                            type="number"
                            step="0.5"
                            min="0"
                            value={data.duration_hours}
                            onChange={(e) => setData('duration_hours', e.target.value)}
                            className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                        {errors.duration_hours && <p className="mt-1 text-xs text-red-600">{errors.duration_hours}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700 mb-1">Description</label>
                        <textarea
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            rows={4}
                            className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                        {errors.description && <p className="mt-1 text-xs text-red-600">{errors.description}</p>}
                    </div>

                    <div className="flex items-center gap-2">
                        <input
                            id="is_active"
                            type="checkbox"
                            checked={data.is_active}
                            onChange={(e) => setData('is_active', e.target.checked)}
                            className="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        />
                        <label htmlFor="is_active" className="text-sm font-medium text-slate-700">Active</label>
                    </div>

                    <div className="flex items-center gap-3 pt-2">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Saving…' : 'Create Course'}
                        </Button>
                        <a href="/hr/training-courses" className="text-sm text-slate-600 hover:text-slate-900">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
