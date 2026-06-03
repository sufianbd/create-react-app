export type EmploymentType = 'full_time' | 'part_time' | 'contract' | 'intern';
export type EmployeeStatus = 'active' | 'on_leave' | 'terminated';
export type SalaryType = 'hourly' | 'monthly';
export type LeaveStatus = 'pending' | 'approved' | 'rejected' | 'cancelled';
export type PayrollStatus = 'draft' | 'processed' | 'paid';

export interface Department {
    id: number;
    name: string;
    description?: string;
    is_active?: boolean;
    employees_count?: number;
    created_at?: string;
}

export interface Employee {
    id: number;
    employee_number?: string;
    code?: string;
    first_name: string;
    last_name: string;
    full_name: string;
    email?: string;
    phone?: string;
    position?: string;
    job_title?: string;
    employment_type: EmploymentType;
    status: EmployeeStatus;
    start_date?: string;
    hire_date?: string;
    end_date?: string | null;
    termination_date?: string | null;
    salary_type?: SalaryType;
    salary_amount?: number | string;
    salary?: number | string;
    department_id?: number | null;
    user_id?: number | null;
    department?: { id: number; name: string } | null;
    user?: { id: number; name: string } | null;
    leave_requests?: LeaveRequest[];
    created_at?: string;
}

export interface LeaveType {
    id: number;
    name: string;
    days_per_year?: number;
    is_paid?: boolean;
    is_active?: boolean;
}

export interface LeaveRequest {
    id: number;
    employee_id?: number;
    employee?: { id: number; full_name: string } | null;
    leave_type_id?: number | null;
    leave_type?: string;
    type?: string;
    start_date: string;
    end_date: string;
    days: number;
    reason?: string;
    notes?: string;
    status: LeaveStatus;
    approved_by?: number | null;
    approved_at?: string | null;
    reviewed_by?: number | null;
    reviewed_at?: string | null;
    approver?: string | null;
    created_at?: string;
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
    period_label?: string;
    period_start: string;
    period_end: string;
    status: PayrollStatus;
    notes?: string;
    total_gross?: number | string;
    total_deductions?: number | string;
    total_net?: number | string;
    employee_count?: number;
    processed_at?: string | null;
    items_count?: number;
    items?: PayrollItem[];
    creator?: string | null;
    created_at?: string;
}

export interface OnboardingTemplateTask {
    id: number;
    title: string;
    description: string | null;
    due_days: number;
    sort_order: number;
}

export interface OnboardingTemplate {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
    tasks?: OnboardingTemplateTask[];
    tasks_count?: number;
    onboardings_count?: number;
    created_at: string;
}

export interface EmployeeOnboardingTask {
    id: number;
    title: string;
    description: string | null;
    due_date: string | null;
    completed_at: string | null;
    is_completed: boolean;
    sort_order: number;
}

export interface EmployeeOnboarding {
    id: number;
    employee_id: number;
    template_id: number | null;
    title: string;
    status: 'in_progress' | 'completed' | 'cancelled';
    started_at: string;
    completed_at: string | null;
    progress: number;
    tasks?: EmployeeOnboardingTask[];
    created_at: string;
}
