import { Head } from '@inertiajs/react';
import { AppLayout } from '@/Layouts/AppLayout';
import { Badge } from '@/Components/Common/Badge';
import { useAuth } from '@/Hooks/useAuth';
import { usePermission } from '@/Hooks/usePermission';

const stats = [
    { label: 'Total Users',     value: '—', color: 'bg-indigo-500' },
    { label: 'Inventory Items', value: '—', color: 'bg-emerald-500' },
    { label: 'Open Invoices',   value: '—', color: 'bg-amber-500' },
    { label: 'Revenue MTD',     value: '—', color: 'bg-violet-500' },
];

export default function Dashboard() {
    const { user } = useAuth();
    const { hasRole } = usePermission();

    return (
        <AppLayout title="Dashboard">
            <Head title="Dashboard" />

            {/* Welcome banner */}
            <div className="mb-8 rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
                <div className="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 className="text-xl font-semibold text-slate-900">
                            Welcome back, {user?.name} 👋
                        </h2>
                        <p className="mt-1 text-sm text-slate-500">
                            Here's what's happening in your organization today.
                        </p>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        {user?.roles.map((role) => (
                            <Badge key={role} color="indigo">
                                {role}
                            </Badge>
                        ))}
                    </div>
                </div>
            </div>

            {/* Stats grid */}
            <div className="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {stats.map((stat) => (
                    <div
                        key={stat.label}
                        className="rounded-xl bg-white border border-slate-200 p-5 shadow-sm"
                    >
                        <div className={`mb-3 h-1 w-12 rounded-full ${stat.color}`} />
                        <p className="text-sm font-medium text-slate-500">{stat.label}</p>
                        <p className="mt-1 text-2xl font-bold text-slate-900">{stat.value}</p>
                    </div>
                ))}
            </div>

            {/* Module quick-access cards */}
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <ModuleCard
                    title="Inventory"
                    description="Manage products, stock levels, and purchase orders."
                    href="/inventory"
                    available={false}
                    color="emerald"
                />
                <ModuleCard
                    title="Finance"
                    description="Track invoices, expenses, and financial reports."
                    href="/finance"
                    available={false}
                    color="amber"
                />
                {hasRole('super-admin') && (
                    <ModuleCard
                        title="Administration"
                        description="Manage users, roles, tenants, and system settings."
                        href="/admin"
                        available={false}
                        color="violet"
                    />
                )}
            </div>
        </AppLayout>
    );
}

interface ModuleCardProps {
    title: string;
    description: string;
    href: string;
    available: boolean;
    color: 'emerald' | 'amber' | 'violet' | 'indigo';
}

const colorMap: Record<ModuleCardProps['color'], string> = {
    emerald: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    amber:   'bg-amber-50 text-amber-700 border-amber-200',
    violet:  'bg-violet-50 text-violet-700 border-violet-200',
    indigo:  'bg-indigo-50 text-indigo-700 border-indigo-200',
};

function ModuleCard({ title, description, href: _href, available, color }: ModuleCardProps) {
    return (
        <div
            className={[
                'rounded-xl border p-5 transition-shadow',
                available ? 'cursor-pointer hover:shadow-md' : 'opacity-60',
                colorMap[color],
            ].join(' ')}
        >
            <div className="flex items-center justify-between">
                <h3 className="font-semibold">{title}</h3>
                {!available && (
                    <span className="text-xs font-medium opacity-70">Phase 2</span>
                )}
            </div>
            <p className="mt-2 text-sm opacity-80">{description}</p>
        </div>
    );
}
