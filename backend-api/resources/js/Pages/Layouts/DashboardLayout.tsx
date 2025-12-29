import { Link, router, usePage } from '@inertiajs/react';
import { PropsWithChildren, useState, useEffect, useMemo } from 'react';
import React from 'react';
import { Navbar, Avatar, Dropdown, DropdownHeader, DropdownItem, DropdownDivider } from 'flowbite-react';
import { route } from 'ziggy-js';

interface Props {
    auth?: {
        user?: {
            id: number;
            name: string;
            username?: string;
            email: string;
        } | null;
    };
}

export default function DashboardLayout({
    auth,
    children,
}: PropsWithChildren<Props>) {
    const { url } = usePage();
    
    // Detect if on a session show page (not the index)
    const isSessionPage = useMemo(() => {
        return url.startsWith('/sessions/') && url !== '/sessions';
    }, [url]);
    
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [isCollapsed, setIsCollapsed] = useState(false);
    const [isHovering, setIsHovering] = useState(false);
    
    // Auto-collapse sidebar on session pages
    useEffect(() => {
        setIsCollapsed(isSessionPage);
    }, [isSessionPage]);
    
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
    
    // Determine if sidebar should show expanded (collapsed but hovering, or not collapsed)
    const showExpanded = !isCollapsed || isHovering;

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
        <div className="bg-gray-200 dark:bg-gray-900 min-h-screen">
            <Navbar fluid className="fixed top-0 z-50 w-full border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                <div className="flex items-center justify-between w-full px-4">
                    <div className="flex items-center gap-4">
                        <button
                            type="button"
                            onClick={() => setSidebarOpen(!sidebarOpen)}
                            className="p-2 text-gray-500 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400 md:hidden"
                        >
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <Link href="/dashboard" className="flex items-center">
                            <span className="text-xl font-bold text-gray-900 dark:text-white">AlgoDrill</span>
                        </Link>
                    </div>
                    <div className="flex items-center gap-4">
                        <span className="text-sm text-gray-700 dark:text-gray-300 hidden lg:block">
                            Welcome back, {auth?.user?.name || 'User'}!
                        </span>

                        {/* Theme Selector */}
                        <Dropdown
                            label={
                                <button
                                    type="button"
                                    className="p-2 text-gray-500 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400 cursor-pointer"
                                    aria-label="Theme selector"
                                >
                                    {themePreference === 'system' ? (
                                        <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clipRule="evenodd" />
                                        </svg>
                                    ) : themePreference === 'dark' ? (
                                        <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                                        </svg>
                                    ) : (
                                        <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fillRule="evenodd" clipRule="evenodd" />
                                        </svg>
                                    )}
                                </button>
                            }
                            arrowIcon={false}
                            inline
                        >
                            <DropdownItem onClick={() => handleThemeChange('light')}>
                                <div className="flex items-center gap-2">
                                    <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fillRule="evenodd" clipRule="evenodd" />
                                    </svg>
                                    <span>Light</span>
                                    {themePreference === 'light' && <span className="ml-auto">✓</span>}
                                </div>
                            </DropdownItem>
                            <DropdownItem onClick={() => handleThemeChange('dark')}>
                                <div className="flex items-center gap-2">
                                    <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                                    </svg>
                                    <span>Dark</span>
                                    {themePreference === 'dark' && <span className="ml-auto">✓</span>}
                                </div>
                            </DropdownItem>
                            <DropdownItem onClick={() => handleThemeChange('system')}>
                                <div className="flex items-center gap-2">
                                    <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fillRule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clipRule="evenodd" />
                                    </svg>
                                    <span>System</span>
                                    {themePreference === 'system' && <span className="ml-auto">✓</span>}
                                </div>
                            </DropdownItem>
                        </Dropdown>

                        <Dropdown
                            label={
                                <Avatar
                                    alt="User settings"
                                    img={`https://ui-avatars.com/api/?name=${encodeURIComponent(auth?.user?.name || 'User')}&background=random`}
                                    rounded
                                />
                            }
                            arrowIcon={false}
                            inline
                        >
                            <DropdownHeader>
                                <span className="block text-sm font-medium">{auth?.user?.name}</span>
                                <span className="block text-sm font-medium truncate">{auth?.user?.email}</span>
                            </DropdownHeader>
                            <DropdownItem onClick={() => router.visit('#')}>
                                Profile
                            </DropdownItem>
                            <DropdownItem onClick={() => router.visit('#')}>
                                Settings
                            </DropdownItem>
                            <DropdownDivider />
                            <DropdownItem onClick={() => router.post('/logout')}>
                                Sign out
                            </DropdownItem>
                        </Dropdown>
                    </div>
                </div>
            </Navbar>

            <div className="flex pt-16 h-100vh">
                {/* Sidebar */}
                <aside
                    aria-label="Sidebar navigation"
                    onMouseEnter={() => isCollapsed && setIsHovering(true)}
                    onMouseLeave={() => setIsHovering(false)}
                    className={`fixed left-0 top-16 z-40 h-[calc(100vh-4rem)] bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300 ease-in-out ${
                        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                    } md:translate-x-0 ${
                        showExpanded ? 'w-64' : 'w-16'
                    }`}
                >
                    <div className="h-full flex flex-col py-4 overflow-hidden">
                        <nav className="flex-1 px-3 space-y-1">
                            <Link 
                                href="/dashboard" 
                                className="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
                            >
                                <svg className="w-5 h-5 shrink-0 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                <span className={`ml-3 whitespace-nowrap transition-opacity duration-200 ${showExpanded ? 'opacity-100' : 'opacity-0 w-0'}`}>
                                    Dashboard
                                </span>
                            </Link>
                            <Link 
                                href="/problems" 
                                className="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
                            >
                                <svg className="w-5 h-5 shrink-0 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span className={`ml-3 whitespace-nowrap transition-opacity duration-200 ${showExpanded ? 'opacity-100' : 'opacity-0 w-0'}`}>
                                    Problems
                                </span>
                            </Link>
                            <Link 
                                href={route('sessions.index')} 
                                className="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
                            >
                                <svg className="w-5 h-5 shrink-0 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span className={`ml-3 whitespace-nowrap transition-opacity duration-200 ${showExpanded ? 'opacity-100' : 'opacity-0 w-0'}`}>
                                    Sessions
                                </span>
                            </Link>
                            <Link 
                                href="#" 
                                className="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
                            >
                                <svg className="w-5 h-5 shrink-0 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span className={`ml-3 whitespace-nowrap transition-opacity duration-200 ${showExpanded ? 'opacity-100' : 'opacity-0 w-0'}`}>
                                    Settings
                                </span>
                            </Link>
                        </nav>
                        
                        {/* Collapse/Expand Toggle Button */}
                        <div className="px-3 mt-auto">
                            <button
                                onClick={() => setIsCollapsed(!isCollapsed)}
                                className="flex items-center justify-center w-full p-2 text-gray-500 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400 transition-colors cursor-pointer"
                                title={isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'}
                            >
                                <svg 
                                    className={`w-5 h-5 transition-transform duration-300 ${isCollapsed && !isHovering ? 'rotate-180' : ''}`} 
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24"
                                >
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                                </svg>
                                <span className={`ml-3 whitespace-nowrap transition-opacity duration-200 ${showExpanded ? 'opacity-100' : 'opacity-0 w-0'}`}>
                                    Collapse
                                </span>
                            </button>
                        </div>
                    </div>
                </aside>

                {/* Main content */}
                <div className={`flex-1 transition-all duration-300 ease-in-out ${showExpanded ? 'md:ml-64' : 'md:ml-16'} min-h-screen overflow-auto`}>
                    <main className={`p-4 ${isSessionPage ? 'max-w-full' : 'max-w-7xl'} mx-auto mb-4`}>
                        {children}
                    </main>
                </div>
            </div>

            {/* Overlay for mobile sidebar */}
            {sidebarOpen && (
                <div
                    className="fixed inset-0 z-30 bg-gray-900/50 md:hidden"
                    onClick={() => setSidebarOpen(false)}
                />
            )}
        </div>
    );
}

