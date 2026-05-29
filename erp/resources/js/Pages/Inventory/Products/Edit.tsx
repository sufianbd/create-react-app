import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { ProductForm } from '@/Components/Inventory/ProductForm';
import type { PageProps } from '@/types';
import type { Category, Product, UnitOfMeasure } from '@/types/inventory';

interface Props extends PageProps {
    product: Product;
    categories: Category[];
    uoms: UnitOfMeasure[];
}

export default function ProductEdit({ product, categories, uoms }: Props) {
    return (
        <AppLayout>
            <Head title={`Edit ${product.name}`} />
            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <Link href="/inventory/products" className="text-sm text-slate-500 hover:text-slate-700">
                        ← Products
                    </Link>
                    <h1 className="text-2xl font-semibold text-slate-900">Edit Product</h1>
                </div>
                <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <ProductForm
                        categories={categories}
                        uoms={uoms}
                        defaults={{
                            sku: product.sku,
                            name: product.name,
                            description: product.description ?? '',
                            category_id: product.category_id?.toString() ?? '',
                            uom_id: product.uom_id?.toString() ?? '',
                            cost_price: product.cost_price,
                            sale_price: product.sale_price,
                            reorder_point: product.reorder_point.toString(),
                            is_active: product.is_active,
                        }}
                        action={`/inventory/products/${product.id}`}
                        method="put"
                        submitLabel="Update Product"
                        cancelHref="/inventory/products"
                    />
                </div>
            </div>
        </AppLayout>
    );
}
