import { Link } from '@inertiajs/react';

interface Store {
    store_name: string;
    store_slug: string;
    currency_code: string;
}

interface StoreProductDetail {
    id: number;
    store_price: number;
    compare_price: number | null;
    is_featured: boolean;
    short_description: string | null;
    long_description: string | null;
    meta_title: string | null;
    meta_description: string | null;
    discount_percent: number;
    product: { name: string; sku: string | null } | null;
    category: { name: string } | null;
}

interface Props {
    store: Store;
    storeProduct: StoreProductDetail;
}

export default function StorefrontProduct({ store, storeProduct }: Props) {
    const productName = storeProduct.product?.name ?? 'Product';

    return (
        <div className="min-h-screen bg-gray-50">
            <header className="bg-white shadow-sm">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                    <Link href={`/store/${store.store_slug}`} className="text-xl font-bold text-gray-900 hover:text-indigo-600">
                        {store.store_name}
                    </Link>
                    <nav className="flex items-center gap-6 text-sm">
                        <Link href={`/store/${store.store_slug}/products`} className="text-gray-600 hover:text-gray-900">Products</Link>
                        <Link href={`/store/${store.store_slug}/checkout`} className="text-gray-600 hover:text-gray-900">Checkout</Link>
                    </nav>
                </div>
            </header>

            <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <Link href={`/store/${store.store_slug}/products`} className="text-sm text-gray-500 hover:text-gray-700 mb-4 inline-block">
                    ← Back to Products
                </Link>

                <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-0">
                        {/* Image */}
                        <div className="h-80 md:h-auto bg-gray-100 flex items-center justify-center">
                            <svg className="h-24 w-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>

                        {/* Details */}
                        <div className="p-8">
                            {storeProduct.category && (
                                <p className="text-xs text-gray-400 uppercase tracking-wide mb-1">{storeProduct.category.name}</p>
                            )}
                            <h1 className="text-2xl font-bold text-gray-900 mb-2">{productName}</h1>
                            {storeProduct.product?.sku && (
                                <p className="text-xs text-gray-400 font-mono mb-4">SKU: {storeProduct.product.sku}</p>
                            )}

                            {storeProduct.short_description && (
                                <p className="text-sm text-gray-600 mb-4">{storeProduct.short_description}</p>
                            )}

                            <div className="flex items-center gap-3 mb-6">
                                <span className="text-3xl font-bold text-gray-900">
                                    {store.currency_code} {storeProduct.store_price.toFixed(2)}
                                </span>
                                {storeProduct.compare_price != null && storeProduct.compare_price > storeProduct.store_price && (
                                    <>
                                        <span className="text-xl text-gray-400 line-through">
                                            {store.currency_code} {storeProduct.compare_price.toFixed(2)}
                                        </span>
                                        <span className="bg-red-100 text-red-700 text-sm font-medium px-2 py-0.5 rounded">
                                            -{storeProduct.discount_percent}%
                                        </span>
                                    </>
                                )}
                            </div>

                            <Link
                                href={`/store/${store.store_slug}/checkout`}
                                className="block w-full text-center bg-indigo-600 text-white font-semibold py-3 rounded-lg hover:bg-indigo-700 transition"
                            >
                                Buy Now
                            </Link>
                        </div>
                    </div>

                    {storeProduct.long_description && (
                        <div className="p-8 border-t border-gray-100">
                            <h2 className="text-lg font-semibold text-gray-900 mb-3">Description</h2>
                            <div className="prose prose-sm text-gray-600 whitespace-pre-line">
                                {storeProduct.long_description}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
