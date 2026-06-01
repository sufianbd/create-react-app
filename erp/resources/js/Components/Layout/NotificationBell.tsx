import { Link, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import type { PageProps } from '@/types';

interface Notification {
    type: string;
    label: string;
    href: string;
    count: number;
    severity: 'warning' | 'info' | 'error';
}

const SEVERITY_CLASSES: Record<Notification['severity'], string> = {
    warning: 'border-l-amber-400 bg-amber-50 text-amber-800',
    info:    'border-l-blue-400 bg-blue-50 text-blue-800',
    error:   'border-l-red-400 bg-red-50 text-red-800',
};

const BADGE_CLASSES: Record<Notification['severity'], string> = {
    warning: 'bg-amber-200 text-amber-800',
    info:    'bg-blue-200 text-blue-800',
    error:   'bg-red-200 text-red-800',
};

export function NotificationBell() {
    const { notifications = [] } = usePage<PageProps & { notifications: Notification[] }>().props;
    const total = notifications.reduce((s: number, n: Notification) => s + n.count, 0);
    const [open, setOpen] = useState(false);
    const ref = useRef<HTMLDivElement>(null);

    useEffect(() => {
        function handleClickOutside(e: MouseEvent) {
            if (ref.current && !ref.current.contains(e.target as Node)) {
                setOpen(false);
            }
        }
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    return (
        <div ref={ref} className="relative">
            <button
                onClick={() => setOpen((o) => !o)}
                aria-label="Notifications"
                className="relative flex h-9 w-9 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400"
            >
                <svg className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth={1.75} viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round"
                        d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                {total > 0 && (
                    <span className="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                        {total > 9 ? '9+' : total}
                    </span>
                )}
            </button>

            {open && (
                <div className="absolute right-0 top-10 z-50 w-80 rounded-xl border border-slate-200 bg-white shadow-lg">
                    <div className="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                        <span className="text-sm font-semibold text-slate-700">Notifications</span>
                        {total > 0 && (
                            <span className="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">
                                {total}
                            </span>
                        )}
                    </div>

                    {notifications.length === 0 ? (
                        <div className="px-4 py-6 text-center text-sm text-slate-400">
                            No notifications
                        </div>
                    ) : (
                        <ul className="divide-y divide-slate-50 max-h-72 overflow-y-auto">
                            {notifications.map((n: Notification) => (
                                <li key={n.type}>
                                    <Link
                                        href={n.href}
                                        onClick={() => setOpen(false)}
                                        className={`flex items-center gap-3 px-4 py-3 text-sm hover:opacity-90 border-l-4 ${SEVERITY_CLASSES[n.severity]}`}
                                    >
                                        <span className="flex-1">{n.label}</span>
                                        <span className={`rounded-full px-1.5 py-0.5 text-xs font-bold ${BADGE_CLASSES[n.severity]}`}>
                                            {n.count}
                                        </span>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            )}
        </div>
    );
}
