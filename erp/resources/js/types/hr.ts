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

export interface PerformanceReviewGoal {
    id: number;
    title: string;
    description: string | null;
    achieved: boolean;
    achievement_notes: string | null;
}

export interface PerformanceReviewCompetency {
    id: number;
    name: string;
    rating: number | null;
    notes: string | null;
}

export interface PerformanceReview {
    id: number;
    employee_id: number;
    reviewer_id: number | null;
    period_start: string;
    period_end: string;
    status: 'draft' | 'in_review' | 'completed';
    overall_rating: number | null;
    comments: string | null;
    completed_at: string | null;
    average_competency_rating: number | null;
    employee?: Employee;
    reviewer?: { id: number; name: string } | null;
    goals?: PerformanceReviewGoal[];
    competencies?: PerformanceReviewCompetency[];
    created_at: string;
}

export interface TrainingCourse {
    id: number;
    title: string;
    provider: string | null;
    type: 'internal' | 'external' | 'online' | 'certification';
    duration_hours: number | null;
    description: string | null;
    is_active: boolean;
    training_records_count?: number;
    created_at: string;
}

export interface EmployeeTrainingRecord {
    id: number;
    employee_id: number;
    training_course_id: number | null;
    course_title: string;
    completed_date: string;
    expiry_date: string | null;
    score: number | null;
    passed: boolean;
    certificate_number: string | null;
    notes: string | null;
    is_expired: boolean;
    is_expiring: boolean;
    employee?: Employee;
    training_course?: TrainingCourse | null;
    created_at: string;
}

export interface JobPosition {
    id: number;
    title: string;
    department_id: number | null;
    location: string | null;
    employment_type: 'full_time' | 'part_time' | 'contract' | 'internship';
    description: string | null;
    requirements: string | null;
    openings: number;
    status: 'draft' | 'open' | 'closed' | 'on_hold';
    posted_at: string | null;
    closed_at: string | null;
    open_applications_count: number;
    applications_count?: number;
    department?: Department;
    applications?: JobApplication[];
    created_at: string;
}

export interface JobApplication {
    id: number;
    job_position_id: number;
    applicant_name: string;
    applicant_email: string;
    applicant_phone: string | null;
    cover_letter: string | null;
    source: string | null;
    stage: 'applied' | 'screening' | 'interview' | 'offer' | 'hired' | 'rejected';
    notes: string | null;
    rating: number | null;
    rejected_at: string | null;
    hired_at: string | null;
    job_position?: JobPosition;
    created_at: string;
}

export interface AttendanceRecord {
    id: number;
    employee_id: number;
    work_date: string;
    clock_in: string | null;
    clock_out: string | null;
    break_minutes: number;
    status: 'present' | 'absent' | 'half_day' | 'holiday' | 'leave';
    notes: string | null;
    worked_hours: number | null;
    is_late: boolean;
    employee?: Employee;
    created_at: string;
}

export interface WorkSchedule {
    id: number;
    name: string;
    is_default: boolean;
    monday_start: string | null;
    monday_end: string | null;
    tuesday_start: string | null;
    tuesday_end: string | null;
    wednesday_start: string | null;
    wednesday_end: string | null;
    thursday_start: string | null;
    thursday_end: string | null;
    friday_start: string | null;
    friday_end: string | null;
    saturday_start: string | null;
    saturday_end: string | null;
    sunday_start: string | null;
    sunday_end: string | null;
    created_at: string;
}

export interface LoanRepayment {
    id: number;
    employee_loan_id: number;
    amount: number;
    payment_date: string;
    notes: string | null;
    created_at: string;
}

export interface EmployeeLoan {
    id: number;
    employee_id: number;
    type: 'loan' | 'advance';
    amount: number;
    outstanding_balance: number;
    interest_rate: number;
    status: 'pending' | 'active' | 'completed' | 'cancelled';
    approved_by: number | null;
    approved_at: string | null;
    disbursed_at: string | null;
    purpose: string | null;
    notes: string | null;
    repayment_start_date: string | null;
    total_repaid: number;
    is_fully_repaid: boolean;
    employee?: Employee;
    repayments?: LoanRepayment[];
    created_at: string;
}

export interface ShiftTemplate {
    id: number;
    name: string;
    start_time: string;
    end_time: string;
    break_minutes: number;
    days_of_week: number[] | null;
    color: string;
    is_active: boolean;
    duration_hours: number;
    assignments_count?: number;
    created_at: string;
}

export interface ShiftAssignment {
    id: number;
    shift_template_id: number;
    employee_id: number;
    assigned_date: string;
    notes: string | null;
    status: 'scheduled' | 'completed' | 'absent' | 'swapped';
    is_upcoming: boolean;
    shift_template?: ShiftTemplate;
    employee?: { id: number; first_name: string; last_name: string };
    created_at: string;
}

export interface ExpenseClaimItem {
    id: number;
    expense_claim_id: number;
    category: string;
    description: string;
    amount: number;
    expense_date: string;
    receipt_reference: string | null;
}

export interface ExpenseClaim {
    id: number;
    employee_id: number;
    title: string;
    description: string | null;
    status: 'draft' | 'submitted' | 'approved' | 'rejected' | 'paid';
    total_amount: number;
    total_items: number;
    submitted_at: string | null;
    approved_by: number | null;
    approved_at: string | null;
    paid_at: string | null;
    rejection_reason: string | null;
    notes: string | null;
    employee?: { id: number; first_name: string; last_name: string };
    approved_by_user?: { id: number; name: string } | null;
    items?: ExpenseClaimItem[];
    created_at: string;
}
