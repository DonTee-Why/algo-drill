import { Link, router } from '@inertiajs/react';
import { PropsWithChildren, useState } from 'react';
import React from 'react';
import { Sidebar, SidebarItems, SidebarItemGroup, Navbar, Avatar, Dropdown, DropdownHeader, DropdownItem, DropdownDivider, Select } from 'flowbite-react';

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
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [theme, setTheme] = useState('dark');
    const [preferredLang, setPreferredLang] = useState('javascript');

    return (
        <div className="bg-gray-50 dark:bg-gray-900 min-h-screen">
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
                        
                        {/* Language Selector */}
                        <div className="hidden md:flex items-center gap-2">
                            <Select
                                id="headerLang"
                                value={preferredLang}
                                onChange={(e) => setPreferredLang(e.target.value)}
                                sizing="sm"
                                className="w-32"
                            >
                                <option value="javascript">JavaScript</option>
                                <option value="python">Python</option>
                                <option value="php">PHP</option>
                                <option value="java">Java</option>
                                <option value="cpp">C++</option>
                            </Select>
                        </div>

                        {/* Theme Toggle */}
                        <button
                            type="button"
                            onClick={() => setTheme(theme === 'dark' ? 'light' : 'dark')}
                            className="p-2 text-gray-500 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400"
                            aria-label="Toggle theme"
                        >
                            {theme === 'dark' ? (
                                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fillRule="evenodd" clipRule="evenodd" />
                                </svg>
                            ) : (
                                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                                </svg>
                            )}
                        </button>

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

            <div className="flex pt-16">
                {/* Sidebar */}
                <Sidebar
                    aria-label="Sidebar navigation"
                    className={`fixed left-0 top-16 z-40 h-[calc(100vh-4rem)] transition-transform ${
                        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                    } md:translate-x-0`}
                >
                    <SidebarItems>
                        <SidebarItemGroup>
                            <Link href="/dashboard" className="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                                <svg className="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                <span className="ml-3">Dashboard</span>
                            </Link>
                            <Link href="#" className="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                                <svg className="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span className="ml-3">Problems</span>
                            </Link>
                            <Link href="#" className="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                                <svg className="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span className="ml-3">Sessions</span>
                            </Link>
                            <Link href="#" className="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                                <svg className="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span className="ml-3">Settings</span>
                            </Link>
                        </SidebarItemGroup>
                    </SidebarItems>
                </Sidebar>

                {/* Main content */}
                <div className="flex-1 md:ml-64">
                    <main className="p-4 max-w-7xl mx-auto">
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

