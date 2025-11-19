import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';
import React from 'react';

interface Props {
    auth?: {
        user?: {
            id: number;
            name: string;
            username?: string;
            email: string;
        } | null;
    };
    flash?: {
        message?: string;
        error?: string;
    };
}

export default function AppLayout({
    auth,
    flash,
    children,
}: PropsWithChildren<Props>) {
    return (
        <div className="bg-gray-50 dark:bg-gray-900">
            <nav className="bg-white dark:bg-gray-800 shadow">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex">
                            <div className="shrink-0 flex items-center">
                                <Link href="/" className="text-xl font-bold text-gray-900 dark:text-white">
                                    AlgoDrill
                                </Link>
                            </div>
                        </div>
                        <div className="flex items-center gap-4">
                            {auth?.user ? (
                                <>
                                    <span className="text-gray-700 dark:text-gray-300">
                                        {auth.user.name}
                                    </span>
                                    <Link
                                        href="/dashboard"
                                        className="text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
                                    >
                                        Dashboard
                                    </Link>
                                    <Link
                                        href="/logout"
                                        method="post"
                                        className="text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
                                    >
                                        Logout
                                    </Link>
                                </>
                            ) : (
                                <>
                                    <Link
                                        href="/login"
                                        className="text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
                                    >
                                        Login
                                    </Link>
                                    <Link
                                        href="/register"
                                        className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                                    >
                                        Register
                                    </Link>
                                </>
                            )}
                        </div>
                    </div>
                </div>
            </nav>

            {flash?.message && (
                <div className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    {flash.message}
                </div>
            )}

            {flash?.error && (
                <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    {flash.error}
                </div>
            )}

            <main className="py-6 min-h-screen">
                {children}
            </main>
        </div>
    );
}

