import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Table } from '@/Components/Common/Table';
import { Button } from '@/Components/Common/Button';
import { usePermission } from '@/Hooks/usePermission';
import type { PageProps } from '@/types';
import type { Category } from '@/types/inventory';

interface Props extends PageProps {
    categories: Category[];
}

export default function CategoriesIndex({ categories }: Props) {
    const { can } = usePermission();

    function handleDelete(id: number, name: string) {
        if (!confirm(`Delete category "${name}"?`)) return;
        router.delete(`/inventory/categories/${id}`);
    }

    return (
        <AppLayout>
            <Head title="Categories" />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">Categories</h1>
                        <p className="text-sm text-slate-500 mt-1">{categories.length} categories</p>
                    </div>
                    {can('inventory.create') && (
                        <Link href="/inventory/categories/create">
                            <Button>Add Category</Button>
                        </Link>
                    )}
                </div>
                <div className="rounded-lg border border-slate-200 bg-white shadow-sm">
                    <Table
                        columns={[
                            { key: 'name', header: 'Name', render: (c) => <span className="font-medium text-slate-900">{c.name}</span> },
                            { key: 'slug', header: 'Slug', className: 'font-mono text-xs text-slate-500' },
                            { key: 'parent', header: 'Parent', render: (c) => c.parent?.name ?? '—' },
                            { key: 'products_count', header: 'Products', render: (c) => c.products_count ?? 0 },
                            { key: 'actions', header: '', render: (c) => (
                                <div className="flex gap-3">
                                    {can('inventory.update') && (
                                        <Link href={`/inventory/categories/${c.id}/edit`} className="text-sm text-indigo-600 hover:text-indigo-800">Edit</Link>
                                    )}
                                    {can('inventory.delete') && (
                                        <button onClick={() => handleDelete(c.id, c.name)} className="text-sm text-red-600 hover:text-red-800">Delete</button>
                                    )}
                                </div>
                            )},
                        ]}
                        data={categories}
                        emptyMessage="No categories found."
                    />
                </div>
            </div>
        </AppLayout>
    );
}
