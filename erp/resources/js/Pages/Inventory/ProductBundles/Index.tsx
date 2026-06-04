import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Table } from '@/Components/Common/Table';
import { Button } from '@/Components/Common/Button';
import { Pagination } from '@/Components/Inventory/Pagination';
import { usePermission } from '@/Hooks/usePermission';
import type { PageProps } from '@/types';
import type { Product, Paginator } from '@/types/inventory';

interface Props extends PageProps {
    bundles: Paginator<Product>;
}

export default function ProductBundlesIndex({ bundles }: Props) {
    const { can } = usePermission();

    return (
        <AppLayout>
            <Head title="Product Bundles" />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">Product Bundles</h1>
                        <p className="text-sm text-slate-500 mt-1">{bundles.total} bundles total</p>
                    </div>
                    {can('inventory.create') && (
                        <Link href="/inventory/product-bundles/create">
                            <Button>New Bundle</Button>
                        </Link>
                    )}
                </div>

                <div className="rounded-lg border border-slate-200 bg-white shadow-sm">
                    <Table
                        columns={[
                            {
                                key: 'name',
                                header: 'Bundle Name',
                                render: (b) => (
                                    <Link
                                        href={`/inventory/product-bundles/${b.id}`}
                                        className="font-medium text-indigo-600 hover:text-indigo-800"
                                    >
                                        {b.name}
                                    </Link>
                                ),
                            },
                            {
                                key: 'sku',
                                header: 'SKU',
                                render: (b) => b.sku || <span className="text-slate-400">—</span>,
                            },
                            {
                                key: 'sale_price',
                                header: 'Selling Price',
                                render: (b) =>
                                    b.sale_price
                                        ? `$${Number(b.sale_price).toFixed(2)}`
                                        : <span className="text-slate-400">—</span>,
                            },
                            {
                                key: 'bundle_items',
                                header: 'Components',
                                render: (b) => b.bundle_items?.length ?? 0,
                            },
                            {
                                key: 'is_active',
                                header: 'Status',
                                render: (b) => (
                                    <span
                                        className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${
                                            b.is_active
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-slate-100 text-slate-500'
                                        }`}
                                    >
                                        {b.is_active ? 'Active' : 'Inactive'}
                                    </span>
                                ),
                            },
                        ]}
                        data={bundles.data}
                        emptyMessage="No product bundles found."
                    />
                    <Pagination paginator={bundles} />
                </div>
            </div>
        </AppLayout>
    );
}
