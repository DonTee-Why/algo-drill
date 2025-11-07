import React from 'react';
import AppLayout from './Layouts/App';

interface Props {
    auth?: {
        user?: {
            id: number;
            name: string;
            username?: string;
            email: string;
            preferred_languages?: string[];
        } | null;
    };
}

export default function Dashboard({ auth }: Props) {
    return (
        <AppLayout auth={auth}>
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-6">
                    Welcome, {auth?.user?.name || 'User'}!
                </h1>
                <div className="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <p className="text-gray-700 dark:text-gray-300">
                        This is your dashboard. More features coming soon!
                    </p>
                    {auth?.user?.preferred_languages && auth.user.preferred_languages.length > 0 && (
                        <div className="mt-4">
                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">Preferred Languages:</p>
                            <div className="flex gap-2">
                                {auth.user.preferred_languages.map((lang) => (
                                    <span
                                        key={lang}
                                        className="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-sm"
                                    >
                                        {lang}
                                    </span>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

