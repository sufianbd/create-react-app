export type AccountType = 'asset' | 'liability' | 'equity' | 'income' | 'expense';

export interface Account {
    id: number;
    code: string;
    name: string;
    type: AccountType;
    description?: string;
    is_active: boolean;
    parent_id?: number | null;
    parent?: { id: number; code: string; name: string } | null;
    balance?: number;
}

export interface JournalLine {
    id?: number;
    account_id: number;
    account?: { id: number; code: string; name: string } | null;
    debit: number | string;
    credit: number | string;
    description?: string;
}

export interface JournalEntry {
    id: number;
    date: string;
    reference?: string;
    description: string;
    status: 'draft' | 'posted';
    total_debits?: number;
    total_credits?: number;
    lines?: JournalLine[];
    creator?: { id: number; name: string } | null;
    created_at?: string;
}

export type ContactType = 'customer' | 'vendor' | 'both';

export interface Contact {
    id: number;
    name: string;
    email?: string;
    phone?: string;
    address?: string;
    type: ContactType;
    notes?: string;
    is_active: boolean;
    price_list_id?: number | null;
    created_at?: string;
    vendor_profile?: VendorProfile;
    vendor_evaluations?: VendorEvaluation[];
}

export interface PriceList {
    id: number;
    name: string;
    description: string | null;
    currency_code: string;
    discount_percent: number;
    is_active: boolean;
    items_count?: number;
    contacts_count?: number;
    items?: PriceListItem[];
    contacts?: Contact[];
}

export interface PriceListItem {
    id: number;
    price_list_id: number;
    product_id: number;
    product?: { id: number; name: string; sku: string; sale_price: number };
    unit_price: number;
}

export interface InvoiceItem {
    id?: number;
    description: string;
    quantity: number | string;
    unit_price: number | string;
    tax_rate: number | string;
    subtotal?: number;
    tax?: number;
    line_total?: number;
}

export type PaymentMethod = 'cash' | 'bank_transfer' | 'cheque' | 'card' | 'other';

export interface Payment {
    id: number;
    amount: number | string;
    payment_date: string;
    method: PaymentMethod;
    reference?: string;
}

export type InvoiceStatus = 'draft' | 'sent' | 'paid' | 'cancelled';

export interface Invoice {
    id: number;
    number?: string;
    status: InvoiceStatus;
    issue_date: string;
    due_date?: string | null;
    notes?: string;
    is_overdue?: boolean;
    currency_code?: string;
    exchange_rate?: number;
    contact?: { id: number; name: string } | null;
    items?: InvoiceItem[];
    payments?: Payment[];
    subtotal?: number;
    tax_total?: number;
    total?: number;
    base_total?: number;
    amount_paid?: number;
    amount_due?: number;
    transitions?: InvoiceStatus[];
    attachments?: Attachment[];
    creator?: string | null;
    created_at?: string;
}

export interface TrialBalanceRow {
    id: number;
    code: string;
    name: string;
    type: AccountType;
    parent_name?: string;
    total_debit: number;
    total_credit: number;
}

export type BillStatus = 'draft' | 'received' | 'paid' | 'cancelled';

export interface BillItem {
    id?: number;
    description: string;
    quantity: number | string;
    unit_price: number | string;
    tax_rate: number | string;
    line_total?: number;
}

export interface BillPayment {
    id: number;
    amount: number | string;
    payment_date: string;
    method: PaymentMethod;
    reference?: string | null;
}

export interface Bill {
    id: number;
    number?: string | null;
    status: BillStatus;
    issue_date: string;
    due_date?: string | null;
    notes?: string | null;
    is_overdue?: boolean;
    currency_code?: string;
    exchange_rate?: number;
    contact?: { id: number; name: string } | null;
    items?: BillItem[];
    payments?: BillPayment[];
    subtotal?: number;
    tax_total?: number;
    total?: number;
    base_total?: number;
    amount_paid?: number;
    amount_due?: number;
    transitions?: BillStatus[];
    attachments?: Attachment[];
}

export type QuoteStatus = 'draft' | 'sent' | 'accepted' | 'declined' | 'cancelled';

export interface QuoteItem {
    id?: number;
    description: string;
    quantity: number;
    unit_price: number;
    tax_rate: number;
    line_total?: number;
}

export interface Quote {
    id: number;
    number?: string;
    status: QuoteStatus;
    issue_date: string;
    expiry_date?: string;
    notes?: string;
    currency_code?: string;
    exchange_rate?: number;
    contact?: { id: number; name: string } | null;
    items?: QuoteItem[];
    subtotal?: number;
    tax_total?: number;
    total?: number;
    base_total?: number;
    transitions?: string[];
    created_by?: string;
    created_at?: string;
}

export type CreditNoteStatus = 'draft' | 'issued' | 'applied' | 'void';
export interface CreditNoteItem {
    id: number;
    description: string;
    quantity: number;
    unit_price: number;
    tax_rate: number;
    line_total: number;
}
export interface CreditNote {
    id: number;
    reference: string;
    contact_id: number | null;
    original_invoice_id: number | null;
    original_bill_id: number | null;
    type: 'sale' | 'purchase';
    status: CreditNoteStatus;
    issue_date: string;
    currency_code: string;
    exchange_rate: number;
    subtotal: number;
    tax_total: number;
    total: number;
    amount_applied: number;
    amount_remaining: number;
    notes: string | null;
    contact?: Contact;
    invoice?: Invoice | null;
    bill?: Bill | null;
    items?: CreditNoteItem[];
    created_at: string;
}

export type SalesOrderStatus = 'draft' | 'confirmed' | 'fulfilled' | 'invoiced' | 'cancelled';
export interface SalesOrderItem {
    id?: number; product_id?: number | null; product_name?: string; product_sku?: string;
    description: string; quantity: number; unit_price: number; tax_rate: number;
    quantity_fulfilled?: number; line_total?: number;
    product?: { id: number; name: string; sku: string } | null;
}
export interface SalesOrder {
    id: number; number?: string; reference?: string | null; status: SalesOrderStatus;
    order_date: string; expected_date?: string | null; notes?: string | null;
    currency_code?: string; exchange_rate?: number;
    contact?: { id: number; name: string } | null;
    contact_id?: number | null;
    warehouse?: { id: number; name: string } | null;
    invoice?: { id: number; number?: string } | null;
    generated_invoice?: { id: number; number?: string } | null;
    items?: SalesOrderItem[]; subtotal?: number; tax_total?: number; total?: number;
    transitions?: string[]; created_by?: string; created_at?: string;
}

export type RecurringFrequency = 'weekly' | 'monthly' | 'quarterly' | 'yearly';
export type RecurringStatus = 'active' | 'paused' | 'ended';
export interface RecurringInvoiceItem {
    id?: number; description: string; quantity: number;
    unit_price: number; tax_rate: number; line_total?: number;
}
export interface RecurringInvoice {
    id: number; status: RecurringStatus; frequency: RecurringFrequency;
    reference_prefix?: string; interval?: number;
    start_date: string; next_run_date: string; end_date?: string;
    due_days: number; auto_send: boolean;
    currency_code?: string; exchange_rate?: number;
    notes?: string; last_generated_at?: string; generated_count: number;
    contact?: { id: number; name: string } | null;
    items?: RecurringInvoiceItem[]; subtotal?: number; tax_total?: number; total?: number;
    invoices?: Invoice[]; created_by?: string; created_at?: string;
}

export interface ExchangeRate {
    id: number;
    tenant_id?: number;
    currency_code: string;
    rate: number | string;
    date: string;
    created_at?: string;
    updated_at?: string;
}

export interface BankAccount {
    id: number;
    name: string;
    account_number: string | null;
    bank_name: string | null;
    currency_code: string;
    opening_balance: number;
    balance?: number;
    unreconciled_count?: number;
}

export interface BankTransaction {
    id: number;
    bank_account_id: number;
    bank_account?: BankAccount;
    transaction_date: string;
    description: string | null;
    amount: number;
    reference: string | null;
    reconciled: boolean;
    payment_id: number | null;
    journal_entry_id: number | null;
    imported_at: string | null;
}

export interface Project {
    id: number;
    name: string;
    description: string | null;
    status: 'draft' | 'active' | 'completed' | 'cancelled';
    budget: number | null;
    contact_id: number | null;
    invoice_id: number | null;
    starts_on: string | null;
    ends_on: string | null;
    contact?: Contact;
    invoice?: { id: number; reference: string };
    time_entries?: ProjectTimeEntry[];
    time_entries_count?: number;
    total_hours?: number;
    billable_hours?: number;
    attachments?: Attachment[];
}

export interface ProjectTimeEntry {
    id: number;
    project_id: number;
    user_id: number;
    description: string;
    hours: number;
    billable: boolean;
    billed: boolean;
    entry_date: string;
    user?: { id: number; name: string };
}

export interface Attachment {
    id: number;
    filename: string;
    disk: string;
    path: string;
    mime_type: string | null;
    size: number | null;
    uploaded_by: number | null;
    uploader?: { id: number; name: string };
    created_at: string;
}

export interface BatchPaymentItem {
    id: number;
    payable_type: string;
    payable_id: number;
    amount: number;
    payment_date: string;
    payment_method: string;
    reference: string | null;
    batch_payment_id: number | null;
    invoice?: { id: number; number?: string };
}

export interface BatchPayment {
    id: number;
    reference: string;
    payment_date: string;
    payment_method: 'bank_transfer' | 'cheque' | 'cash' | 'card' | 'other';
    type: 'received' | 'made';
    total_amount: number;
    notes: string | null;
    payments_count?: number;
    payments?: BatchPaymentItem[];
    created_at: string;
}
export interface DeliveryNoteItem {
    id: number;
    product_id: number | null;
    description: string;
    quantity: number;
    product?: { id: number; name: string; sku: string } | null;
}

export interface DeliveryNote {
    id: number;
    reference: string;
    sales_order_id: number | null;
    invoice_id: number | null;
    contact_id: number | null;
    status: 'draft' | 'dispatched' | 'delivered';
    dispatch_date: string | null;
    delivery_date: string | null;
    carrier: string | null;
    tracking_number: string | null;
    notes: string | null;
    contact?: Contact | null;
    salesOrder?: SalesOrder | null;
    invoice?: Invoice | null;
    items?: DeliveryNoteItem[];
    created_at: string;
}

export interface VendorProfile {
    id: number | null;
    contact_id: number;
    credit_limit: number | null;
    payment_terms_days: number;
    preferred_currency: string | null;
    bank_name: string | null;
    bank_account_number: string | null;
    bank_routing_number: string | null;
    notes: string | null;
    is_over_credit_limit: boolean;
}

export interface VendorEvaluation {
    id: number;
    contact_id: number;
    evaluated_by: number;
    evaluation_date: string;
    quality_rating: number;
    delivery_rating: number;
    price_rating: number;
    communication_rating: number;
    overall_rating: number;
    comments: string | null;
    evaluator?: { id: number; name: string };
    created_at: string;
}

export interface CustomerPortalToken {
    id: number;
    contact_id: number;
    email: string;
    expires_at: string | null;
    last_accessed_at: string | null;
    is_expired: boolean;
    portal_url?: string;
}
