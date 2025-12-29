import { Head, Link } from '@inertiajs/react';
import React, { PropsWithChildren, useState, useEffect } from 'react';
import { BarChart3, Users, Brain, PlayCircle, Sparkles, Settings, Activity } from 'lucide-react';
import { Card, Badge, Dropdown, DropdownItem } from 'flowbite-react';
import { route } from 'ziggy-js';

interface Props {
    auth?: {
        user?: {
            id: number;
            name: string;
            username?: string;
            email: string;
            is_admin?: boolean;
        } | null;
    };
}

interface SidebarItemProps {
    icon: React.ComponentType<{ className?: string }>;
    label: string;
    active?: boolean;
    href?: string;
}

const SidebarItem: React.FC<SidebarItemProps> = ({ icon: Icon, label, active, href }) => {
    const baseClasses = `w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors`;
    
    const activeClasses = active
        ? "bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400"
        : "text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white";

    if (!href) {
        return (
            <button
                className={`${baseClasses} ${activeClasses} cursor-not-allowed opacity-50`}
                type="button"
                disabled
            >
                <Icon className="h-5 w-5" />
                <span>{label}</span>
            </button>
        );
    }

    return (
        <Link
            href={href}
            className={`${baseClasses} ${activeClasses}`}
        >
            <Icon className="h-5 w-5" />
            <span>{label}</span>
        </Link>
    );
};

export default function AdminLayout({ auth, children }: PropsWithChildren<Props>) {
    const currentPath = window.location.pathname;
    const isOverview = currentPath === '/admin/dashboard' || currentPath === '/admin';
    const isProblems = currentPath.includes('/admin/problems');

    const [themePreference, setThemePreference] = useState<'light' | 'dark' | 'system'>(() => {
        // Check localStorage, default to 'system'
        if (typeof window !== 'undefined') {
            const stored = localStorage.getItem('theme-preference');
            if (stored === 'light' || stored === 'dark' || stored === 'system') {
                return stored;
            }
        }
        return 'system';
    });

    // Apply theme changes to document
    useEffect(() => {
        const root = document.documentElement;
        
        const applyTheme = (isDark: boolean) => {
            if (isDark) {
                root.classList.add('dark');
            } else {
                root.classList.remove('dark');
            }
        };

        if (themePreference === 'system') {
            // Use system preference
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            applyTheme(mediaQuery.matches);
            
            // Listen for system preference changes
            const handleChange = (e: MediaQueryListEvent) => applyTheme(e.matches);
            mediaQuery.addEventListener('change', handleChange);
            
            return () => mediaQuery.removeEventListener('change', handleChange);
        } else {
            // Use user preference
            applyTheme(themePreference === 'dark');
        }
        
        localStorage.setItem('theme-preference', themePreference);
    }, [themePreference]);

    const handleThemeChange = (newTheme: 'light' | 'dark' | 'system') => {
        setThemePreference(newTheme);
    };

    return (
        <div className="h-screen w-full bg-gray-50 dark:bg-gray-900 flex overflow-hidden">
            <Head title="Admin Dashboard" />
            
            {/* Sidebar */}
            <aside className="hidden lg:flex lg:w-64 xl:w-72 flex-col border-r border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                <div className="flex items-center gap-3 px-6 py-6 border-b border-gray-200 dark:border-gray-800">
                    <div className="h-10 w-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <Brain className="h-6 w-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div className="flex flex-col">
                        <span className="text-base font-bold text-gray-900 dark:text-white">AlgoDrill</span>
                        <span className="text-xs text-gray-500 dark:text-gray-400">Admin Portal</span>
                    </div>
                </div>

                <nav className="flex-1 px-3 py-4 space-y-1">
                    <SidebarItem icon={BarChart3} label="Overview" active={isOverview} href={route('admin.dashboard')} />
                    <SidebarItem icon={Activity} label="Sessions" />
                    <SidebarItem icon={Users} label="Users" />
                    <SidebarItem icon={PlayCircle} label="Problems" active={isProblems} href={route('admin.problems.index')} />
                    <SidebarItem icon={Sparkles} label="AI Coach" />
                    <SidebarItem icon={Settings} label="Settings" />
                </nav>

                <div className="px-4 pb-4 border-t border-gray-200 dark:border-gray-800 pt-4">
                    <Card className="shadow-sm">
                        <div className="space-y-3">
                            <div className="flex items-center justify-between">
                                <h3 className="text-sm font-semibold text-gray-900 dark:text-white">Today's Snapshot</h3>
                                <Badge color="success" className="text-xs">Live</Badge>
                            </div>
                            <div className="space-y-2 text-sm">
                                <div className="flex items-center justify-between text-gray-600 dark:text-gray-400">
                                    <span>Active sessions</span>
                                    <span className="font-semibold text-gray-900 dark:text-white">27</span>
                                </div>
                                <div className="flex items-center justify-between text-gray-600 dark:text-gray-400">
                                    <span>Coach latency p95</span>
                                    <span className="font-semibold text-gray-900 dark:text-white">1.7s</span>
                                </div>
                                <div className="flex items-center justify-between text-gray-600 dark:text-gray-400">
                                    <span>Reveal actions</span>
                                    <span className="font-semibold text-gray-900 dark:text-white">38</span>
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>
            </aside>

            {/* Main Content */}
            <main className="flex-1 flex flex-col overflow-auto min-h-screen">
                {children}
            </main>
        </div>
    );
}
