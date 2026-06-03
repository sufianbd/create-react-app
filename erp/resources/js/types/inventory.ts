export interface ProductCategory {
    id: number;
    name: string;
    slug: string | null;
    description: string | null;
    colour: string;
    products_count?: number;
}
export interface Category {
    id: number; name: string; slug: string; description?: string;
    parent_id?: number | null; parent?: { id: number; name: string } | null;
    children?: Category[]; products_count?: number;
}
export interface UnitOfMeasure { id: number; name: string; abbreviation: string; }
export interface Supplier {
    id: number; name: string; contact_person?: string; email?: string;
    phone?: string; address?: string; is_active: boolean; created_at?: string;
}
export interface Warehouse { id: number; name: string; location?: string; is_active: boolean; stock_levels_count?: number; }
export interface StockLevelInfo {
    warehouse_id: number; warehouse_name?: string;
    quantity: string; reserved_quantity: string; available: number;
}
export interface Product {
    id: number; sku: string; name: string; description?: string;
    category_id?: number | null;
    category?: ProductCategory | null;
    uom_id?: number | null; uom?: { id: number; name: string; abbreviation: string } | null;
    cost_price: string; sale_price: string; reorder_point: number;
    reorder_quantity?: number;
    preferred_supplier_id?: number | null;
    preferred_supplier?: { id: number; name: string } | null;
    is_active: boolean; stock_levels?: StockLevelInfo[]; total_quantity?: number;
    total_stock?: number; needs_reorder?: boolean; created_at?: string;
}
export interface StockMovement {
    id: number; product_id: number; warehouse_id: number;
    type: 'in' | 'out' | 'transfer' | 'adjustment'; quantity: string;
    reference?: string; notes?: string; created_by?: number;
    product?: { id: number; name: string; sku: string };
    warehouse?: { id: number; name: string };
    creator?: { id: number; name: string }; created_at: string;
}
export interface PurchaseOrderItem {
    id: number; product_id: number; product_name?: string; product_sku?: string;
    quantity: string; unit_cost: string; received_quantity: string; line_total?: number;
}
export interface PurchaseOrder {
    id: number; status: 'draft' | 'submitted' | 'approved' | 'received' | 'cancelled';
    expected_date?: string | null; notes?: string; total?: number;
    supplier?: { id: number; name: string } | null;
    warehouse?: { id: number; name: string } | null;
    items?: PurchaseOrderItem[]; created_by?: string | null; created_at?: string;
}
export interface Paginator<T> {
    data: T[]; current_page: number; last_page: number;
    per_page: number; total: number; from: number; to: number;
    next_page_url: string | null; prev_page_url: string | null;
}
export interface WarehouseTransfer {
    id: number;
    product_id: number;
    product?: { id: number; name: string; sku: string };
    from_warehouse_id: number;
    from_warehouse?: { id: number; name: string };
    to_warehouse_id: number;
    to_warehouse?: { id: number; name: string };
    quantity: number;
    reference: string | null;
    notes: string | null;
    status: 'pending' | 'completed' | 'cancelled';
    created_at: string;
}

export interface StockAdjustmentItem {
    id: number;
    product_id: number;
    expected_quantity: number;
    actual_quantity: number;
    difference: number;
    product?: { id: number; name: string; sku: string };
}

export interface StockAdjustment {
    id: number;
    warehouse_id: number;
    reference: string;
    reason: 'count' | 'damage' | 'theft' | 'expiry' | 'correction' | 'other';
    status: 'draft' | 'confirmed' | 'cancelled';
    adjusted_by: number | null;
    confirmed_at: string | null;
    notes: string | null;
    warehouse?: { id: number; name: string };
    adjuster?: { id: number; name: string } | null;
    items?: StockAdjustmentItem[];
    created_at: string;
}
