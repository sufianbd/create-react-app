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
    created_at?: string;
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
    contact?: { id: number; name: string } | null;
    items?: InvoiceItem[];
    payments?: Payment[];
    subtotal?: number;
    tax_total?: number;
    total?: number;
    amount_paid?: number;
    amount_due?: number;
    transitions?: InvoiceStatus[];
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
    contact?: { id: number; name: string } | null;
    items?: BillItem[];
    payments?: BillPayment[];
    subtotal?: number;
    tax_total?: number;
    total?: number;
    amount_paid?: number;
    amount_due?: number;
    transitions?: BillStatus[];
}
