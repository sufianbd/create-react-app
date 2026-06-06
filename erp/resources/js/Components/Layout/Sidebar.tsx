import { Link, usePage } from '@inertiajs/react';
import { ReactNode, useState } from 'react';
import { usePermission } from '@/Hooks/usePermission';

interface NavItem {
    label: string;
    href: string;
    icon: ReactNode;
    permission?: string;
    children?: NavItem[];
}

interface SidebarProps {
    collapsed: boolean;
    onToggle: () => void;
}

const inventoryIcon = (
    <svg className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth={1.75} viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
    </svg>
);

const analyticsIcon = (
    <svg className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth={1.75} viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
    </svg>
);

const notificationBellIcon = (
    <svg className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth={1.75} viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
    </svg>
);

const navItems: NavItem[] = [
    {
        label: 'Dashboard',
        href: '/dashboard',
        icon: (
            <svg className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth={1.75} viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
        ),
    },
    {
        label: 'Notifications',
        href: '/notifications',
        icon: notificationBellIcon,
    },
    {
        label: 'Inventory',
        href: '/inventory/products',
        icon: inventoryIcon,
        permission: 'inventory.view',
        children: [
            { label: 'Products',        href: '/inventory/products',        icon: inventoryIcon },
            { label: 'Categories',          href: '/inventory/categories',          icon: inventoryIcon },
            { label: 'Product Categories', href: '/inventory/product-categories', icon: inventoryIcon },
            { label: 'Warehouses',      href: '/inventory/warehouses',      icon: inventoryIcon },
            { label: 'Suppliers',       href: '/inventory/suppliers',       icon: inventoryIcon },
            { label: 'Stock Movements', href: '/inventory/stock-movements', icon: inventoryIcon },
            { label: 'Purchase Orders', href: '/inventory/purchase-orders', icon: inventoryIcon },
            { label: 'Transfers',         href: '/inventory/warehouse-transfers',   icon: inventoryIcon },
            { label: 'Reorder',           href: '/inventory/reorder',               icon: inventoryIcon },
            { label: 'Stock Adjustments', href: '/inventory/stock-adjustments',     icon: inventoryIcon },
            { label: 'Requisitions',      href: '/inventory/purchase-requisitions', icon: inventoryIcon },
            { label: 'Assets',       href: '/inventory/assets',             icon: <span /> },
            { label: 'Maintenance',  href: '/inventory/asset-maintenances', icon: <span /> },
            { label: 'Bundles',      href: '/inventory/product-bundles',    icon: <span /> },
            { label: 'Warehouse Stock', href: '/inventory/warehouse-stock', icon: <span /> },
            { label: 'Transfers',       href: '/inventory/stock-transfers', icon: <span /> },
            { label: 'QC Checklists',   href: '/inventory/qc-checklists',   icon: <span /> },
            { label: 'QC Inspections',  href: '/inventory/qc-inspections',  icon: <span /> },
            { label: 'Costing',  href: '/inventory/costing',  icon: <span /> },
            { label: 'Demand Forecasts', href: '/inventory/demand-forecasts', icon: <span /> },
            { label: 'Warehouse Zones', href: '/inventory/warehouse-zones', icon: <span /> },
            { label: 'Bin Locations',      href: '/inventory/warehouse-bins',       icon: <span /> },
            { label: 'Prod. Attributes',   href: '/inventory/product-attributes',   icon: <span /> },
            { label: 'Product Variants',   href: '/inventory/product-variants',     icon: <span /> },
            { label: 'Vehicles',           href: '/inventory/vehicles',             icon: <span /> },
            { label: 'Supplier Reviews',   href: '/inventory/supplier-reviews',     icon: <span /> },
            { label: 'Supplier Contracts', href: '/inventory/supplier-contracts',   icon: <span /> },
            { label: 'Lot Numbers',        href: '/inventory/lot-numbers',          icon: <span /> },
            { label: 'Serial Numbers',     href: '/inventory/serial-numbers',       icon: <span /> },
            { label: 'Sales Orders',      href: '/inventory/sales-orders',          icon: <span /> },
            { label: 'Price Lists',         href: '/inventory/price-lists',           icon: <span /> },
            { label: 'Discounts',            href: '/inventory/customer-discounts',    icon: <span /> },
            { label: 'Units of Measure',   href: '/inventory/units-of-measure',       icon: <span /> },
            { label: 'Cycle Counts',       href: '/inventory/cycle-counts',            icon: <span /> },
            { label: 'Product Tags',        href: '/inventory/product-tags',            icon: <span /> },
        ],
    },
    {
        label: 'Finance',
        href: '/finance/invoices',
        icon: (
            <svg className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth={1.75} viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
            </svg>
        ),
        permission: 'finance.view',
        children: [
            { label: 'Invoices',          href: '/finance/invoices',                      icon: <span /> },
            { label: 'Recurring Invoices', href: '/finance/recurring-invoices',           icon: <span /> },
            { label: 'Quotes',            href: '/finance/quotes',                        icon: <span /> },
            { label: 'Sales Orders',      href: '/finance/sales-orders',                  icon: <span /> },
            { label: 'Delivery Notes',    href: '/finance/delivery-notes',               icon: <span /> },
            { label: 'Credit Notes',      href: '/finance/credit-notes',                  icon: <span /> },
            { label: 'Contacts',          href: '/finance/contacts',                      icon: <span /> },
            { label: 'Customer Groups',  href: '/finance/customer-groups',               icon: <span /> },
            { label: 'Journal Entries',   href: '/finance/journal-entries',               icon: <span /> },
            { label: 'Chart of Accounts', href: '/finance/accounts',                      icon: <span /> },
            { label: 'Bills (AP)',         href: '/finance/bills',                         icon: <span /> },
            { label: 'Trial Balance',     href: '/finance/reports/trial-balance',         icon: <span /> },
            { label: 'Profit & Loss',     href: '/finance/reports/profit-loss',           icon: <span /> },
            { label: 'Balance Sheet',     href: '/finance/reports/balance-sheet',         icon: <span /> },
            { label: 'Aged Receivables',  href: '/finance/reports/aged-receivables',      icon: <span /> },
            { label: 'Aged Payables',     href: '/finance/reports/aged-payables',         icon: <span /> },
            { label: 'Account Ledger',    href: '/finance/reports/account-ledger',        icon: <span /> },
            { label: 'Customer Statement', href: '/finance/reports/customer-statement',    icon: <span /> },
            { label: 'Supplier Statement', href: '/finance/reports/supplier-statement',   icon: <span /> },
            { label: 'VAT Report',         href: '/finance/reports/vat-report',          icon: <span /> },
            { label: 'Cash Flow',          href: '/finance/reports/cash-flow-forecast',  icon: <span /> },
            { label: 'Comparative P&L',    href: '/finance/reports/comparative-profit-loss', icon: <span /> },
            { label: 'Currencies',          href: '/finance/currencies',                        icon: <span /> },
            { label: 'Exchange Rates',      href: '/finance/exchange-rates',                    icon: <span /> },
            { label: 'Bank Accounts',       href: '/finance/bank-accounts',                    icon: <span /> },
            { label: 'Bank Transactions',    href: '/finance/bank-transactions',                icon: <span /> },
            { label: 'Reconciliations',      href: '/finance/bank-reconciliations',             icon: <span /> },
            { label: 'Budgets',             href: '/finance/budgets',                          icon: <span /> },
            { label: 'Reconciliation',      href: '/finance/reconciliation',                   icon: <span /> },
            { label: 'Fixed Assets',        href: '/finance/fixed-assets',                     icon: <span /> },
            { label: 'Price Lists',          href: '/finance/price-lists',                      icon: <span /> },
            { label: 'Projects',             href: '/finance/projects',                         icon: <span /> },
            { label: 'Batch Payments',       href: '/finance/batch-payments',                   icon: <span /> },
            { label: 'Doc Templates',        href: '/finance/document-templates',               icon: <span /> },
            { label: 'Subscriptions', href: '/finance/subscriptions', icon: <span /> },
            { label: 'Sub Plans',     href: '/finance/subscription-plans', icon: <span /> },
            { label: 'Commissions', href: '/finance/commissions', icon: <span /> },
            { label: 'Contracts', href: '/finance/contracts', icon: <span /> },
            { label: 'Returns',   href: '/finance/return-requests', icon: <span /> },
            { label: 'Tax Rates',   href: '/finance/tax-rates',   icon: <span /> },
            { label: 'Tax Groups',  href: '/finance/tax-groups',  icon: <span /> },
            { label: 'Service Agreements', href: '/finance/service-agreements', icon: <span /> },
            { label: 'Loyalty Programs', href: '/finance/loyalty-programs', icon: <span /> },
            { label: 'CRM / Leads',      href: '/finance/leads',           icon: <span /> },
            { label: 'Support Tickets',  href: '/finance/support-tickets', icon: <span /> },
            { label: 'Expense Claims',   href: '/finance/expense-claims',  icon: <span /> },
            { label: 'Vendor Bills',     href: '/finance/vendor-bills',    icon: <span /> },
            { label: 'Payment Terms',    href: '/finance/payment-terms',   icon: <span /> },
            { label: 'Petty Cash',       href: '/finance/petty-cash',       icon: <span /> },
            { label: 'Bank Transfers',    href: '/finance/bank-transfers',    icon: <span /> },
        ],
    },
    {
        label: 'HR',
        href: '/hr/employees',
        icon: (
            <svg className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth={1.75} viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
        ),
        permission: 'hr.view',
        children: [
            { label: 'Employees',   href: '/hr/employees',   icon: <span /> },
            { label: 'Departments', href: '/hr/departments', icon: <span /> },
            { label: 'Leave Types',    href: '/hr/leave-types',    icon: <span /> },
            { label: 'Leave Requests', href: '/hr/leave-requests', icon: <span /> },
            { label: 'Leave Balances', href: '/hr/leave-balances', icon: <span /> },
            { label: 'Payroll Runs',   href: '/hr/payroll-runs',   icon: <span /> },
            { label: 'Expense Claims',       href: '/hr/expense-claims',        icon: <span /> },
            { label: 'Onboarding',           href: '/hr/onboarding-templates',  icon: <span /> },
            { label: 'Performance Reviews',  href: '/hr/performance-reviews',   icon: <span /> },
            { label: 'Training Courses',     href: '/hr/training-courses',      icon: <span /> },
            { label: 'Enrollments',           href: '/hr/training-enrollments',  icon: <span /> },
            { label: 'Certifications',        href: '/hr/employee-certifications', icon: <span /> },
            { label: 'Job Positions',        href: '/hr/job-positions',         icon: <span /> },
            { label: 'Applications',         href: '/hr/job-applications',      icon: <span /> },
            { label: 'Attendance',      href: '/hr/attendance',      icon: <span /> },
            { label: 'Work Schedules',    href: '/hr/work-schedules',    icon: <span /> },
            { label: 'Schedules',          href: '/hr/employee-schedules', icon: <span /> },
            { label: 'Shift Templates',   href: '/hr/shift-templates',   icon: <span /> },
            { label: 'Shift Assignments', href: '/hr/shift-assignments', icon: <span /> },
            { label: 'Loans & Advances', href: '/hr/employee-loans', icon: <span /> },
            { label: 'Onboarding', href: '/hr/onboarding-checklists', icon: <span /> },
            { label: 'Employee Onboarding', href: '/hr/employee-onboardings', icon: <span /> },
            { label: 'Payroll', href: '/hr/payroll', icon: <span /> },
            { label: 'Disciplinary', href: '/hr/disciplinary-cases', icon: <span /> },
            { label: 'Grievances',   href: '/hr/grievances',          icon: <span /> },
            { label: 'Timesheets',    href: '/hr/timesheets',          icon: <span /> },
            { label: 'Benefit Plans',    href: '/hr/benefit-plans',     icon: <span /> },
            { label: 'Employee Benefits', href: '/hr/employee-benefits', icon: <span /> },
            { label: 'Skills', href: '/hr/employee-skills', icon: <span /> },
            { label: 'Skill Definitions', href: '/hr/skill-definitions', icon: <span /> },
            { label: 'Announcements', href: '/hr/announcements', icon: <span /> },
            { label: 'Exit Management', href: '/hr/employee-exits', icon: <span /> },
            { label: 'Position Changes', href: '/hr/position-changes', icon: <span /> },
            { label: 'Salary Grades', href: '/hr/salary-grades', icon: <span /> },
        ],
    },
    {
        label: 'Analytics',
        href: '/analytics',
        icon: analyticsIcon,
    },
    {
        label: 'System',
        href: '/core/audit-logs',
        icon: (
            <svg className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth={1.75} viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
            </svg>
        ),
        permission: 'finance.view',
        children: [
            { label: 'Audit Log', href: '/core/audit-logs', icon: <span /> },
        ],
    },
    {
        label: 'Admin',
        href: '/admin/users',
        icon: (
            <svg className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth={1.75} viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z" />
                <path strokeLinecap="round" strokeLinejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        ),
        permission: 'users.view',
        children: [
            { label: 'Users',     href: '/admin/users',     icon: <span /> },
            { label: 'Audit Log', href: '/admin/audit-log', icon: <span /> },
        ],
    },
    {
        label: 'Settings',
        href: '/settings',
        icon: (
            <svg className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth={1.75} viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path strokeLinecap="round" strokeLinejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        ),
        permission: 'roles.manage',
        children: [
            { label: 'Users', href: '/settings/users', icon: <span /> },
            { label: 'Company', href: '/settings/company', icon: <span /> },
            { label: 'Audit Log', href: '/settings/audit-log', icon: <span /> },
        ],
    },
];

function NavLink({
    item,
    collapsed,
    isActive,
    url,
}: {
    item: NavItem;
    collapsed: boolean;
    isActive: boolean;
    url: string;
}) {
    const [open, setOpen] = useState(isActive);
    const hasChildren = item.children && item.children.length > 0;

    if (hasChildren && !collapsed) {
        return (
            <div>
                <button
                    onClick={() => setOpen((o) => !o)}
                    className={[
                        'flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                        isActive ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
                    ].join(' ')}
                >
                    <span className={isActive ? 'text-indigo-600' : 'text-slate-400'}>{item.icon}</span>
                    <span className="flex-1 text-left">{item.label}</span>
                    <svg className={`h-4 w-4 transition-transform ${open ? 'rotate-180' : ''}`} viewBox="0 0 20 20" fill="currentColor">
                        <path fillRule="evenodd" d="M5.22 8.22a.75.75 0 011.06 0L10 11.94l3.72-3.72a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.22 9.28a.75.75 0 010-1.06z" clipRule="evenodd" />
                    </svg>
                </button>
                {open && (
                    <ul className="ml-4 mt-1 space-y-1 border-l border-slate-200 pl-2">
                        {item.children!.map((child) => (
                            <li key={child.href}>
                                <Link
                                    href={child.href}
                                    className={[
                                        'flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm transition-colors',
                                        url.startsWith(child.href)
                                            ? 'bg-indigo-50 font-medium text-indigo-700'
                                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
                                    ].join(' ')}
                                >
                                    {child.label}
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        );
    }

    return (
        <Link
            href={item.href}
            title={collapsed ? item.label : undefined}
            className={[
                'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                isActive
                    ? 'bg-indigo-50 text-indigo-700'
                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
                collapsed ? 'justify-center' : '',
            ].join(' ')}
        >
            <span className={isActive ? 'text-indigo-600' : 'text-slate-400'}>
                {item.icon}
            </span>
            {!collapsed && <span>{item.label}</span>}
        </Link>
    );
}

export function Sidebar({ collapsed, onToggle }: SidebarProps) {
    const { url } = usePage();
    const { can } = usePermission();

    const visibleItems = navItems.filter((item) =>
        !item.permission || can(item.permission)
    );

    return (
        <aside
            className={[
                'flex h-full flex-col border-r border-slate-200 bg-white transition-all duration-200',
                collapsed ? 'w-16' : 'w-60',
            ].join(' ')}
        >
            {/* Logo */}
            <div
                className={[
                    'flex h-16 shrink-0 items-center border-b border-slate-200 px-4',
                    collapsed ? 'justify-center' : 'gap-3',
                ].join(' ')}
            >
                <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-600">
                    <svg className="h-5 w-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11.644 1.59a.75.75 0 01.712 0l9.75 5.25a.75.75 0 010 1.32l-9.75 5.25a.75.75 0 01-.712 0l-9.75-5.25a.75.75 0 010-1.32l9.75-5.25z" />
                        <path d="M3.265 10.602l7.668 4.129a2.25 2.25 0 002.134 0l7.668-4.13 1.37.739a.75.75 0 010 1.32l-9.75 5.25a.75.75 0 01-.71 0l-9.75-5.25a.75.75 0 010-1.32l1.37-.738z" />
                        <path d="M10.933 19.231l-7.668-4.13-1.37.739a.75.75 0 000 1.32l9.75 5.25c.221.12.489.12.71 0l9.75-5.25a.75.75 0 000-1.32l-1.37-.738-7.668 4.13a2.25 2.25 0 01-2.134-.001z" />
                    </svg>
                </div>
                {!collapsed && (
                    <span className="text-base font-semibold text-slate-900">
                        ERP Suite
                    </span>
                )}
            </div>

            {/* Navigation */}
            <nav className="flex-1 overflow-y-auto px-2 py-4">
                <ul className="space-y-1">
                    {visibleItems.map((item) => (
                        <li key={item.href}>
                            <NavLink
                                item={item}
                                collapsed={collapsed}
                                isActive={url.startsWith(item.href)}
                                url={url}
                            />
                        </li>
                    ))}
                </ul>
            </nav>

            {/* Collapse toggle */}
            <div className="border-t border-slate-200 p-2">
                <button
                    onClick={onToggle}
                    aria-label={collapsed ? 'Expand sidebar' : 'Collapse sidebar'}
                    className="flex w-full items-center justify-center rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                >
                    <svg
                        className={`h-5 w-5 transition-transform duration-200 ${collapsed ? 'rotate-180' : ''}`}
                        fill="none"
                        stroke="currentColor"
                        strokeWidth={1.75}
                        viewBox="0 0 24 24"
                    >
                        <path strokeLinecap="round" strokeLinejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
                    </svg>
                </button>
            </div>
        </aside>
    );
}
