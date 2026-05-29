export type EmploymentType = 'full_time' | 'part_time' | 'contract';
export type EmployeeStatus = 'active' | 'on_leave' | 'terminated';
export type SalaryType = 'hourly' | 'monthly';
export type LeaveStatus = 'pending' | 'approved' | 'rejected';
export type PayrollStatus = 'draft' | 'processed';

export interface Department {
    id: number;
    name: string;
    description?: string;
    is_active: boolean;
    employees_count?: number;
}

export interface Employee {
    id: number;
    employee_number?: string;
    first_name: string;
    last_name: string;
    full_name: string;
    email?: string;
    phone?: string;
    position?: string;
    employment_type: EmploymentType;
    status: EmployeeStatus;
    start_date: string;
    end_date?: string | null;
    salary_type: SalaryType;
    salary_amount: number | string;
    department_id?: number | null;
    user_id?: number | null;
    department?: { id: number; name: string } | null;
    user?: { id: number; name: string } | null;
    created_at?: string;
}

export interface LeaveType {
    id: number;
    name: string;
    days_per_year: number;
    is_paid: boolean;
    is_active: boolean;
}

export interface LeaveRequest {
    id: number;
    employee: { id: number; full_name: string };
    leave_type?: string;
    start_date: string;
    end_date: string;
    days: number;
    status: LeaveStatus;
    notes?: string;
    reviewed_by?: string;
    reviewed_at?: string;
}

export interface PayrollItem {
    id: number;
    employee?: { id: number; full_name: string; position?: string } | null;
    gross_salary: number | string;
    deductions: number | string;
    net_salary: number | string;
    notes?: string;
}

export interface PayrollRun {
    id: number;
    period_start: string;
    period_end: string;
    status: PayrollStatus;
    notes?: string;
    total_gross?: number;
    total_net?: number;
    items_count?: number;
    items?: PayrollItem[];
    creator?: string | null;
    created_at?: string;
}
