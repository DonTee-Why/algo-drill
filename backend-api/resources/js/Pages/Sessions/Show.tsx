import { Head } from '@inertiajs/react';
import React from 'react';
import DashboardLayout from '../Layouts/DashboardLayout';
import { Card } from 'flowbite-react';

interface Props {
    auth?: {
        user?: {
            id: number;
            name: string;
            email: string;
        } | null;
    };
}

export default function Show({ auth }: Props) {
    return (
        <DashboardLayout auth={auth}>
            <Head title="Coaching Session" />
            <div className="max-w-[1400px] mx-auto px-8 py-8">
                <Card>
                    <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                        Coaching Session
                    </h1>
                    <p className="text-gray-600 dark:text-gray-400">
                        This is a placeholder for the coaching session interface. 
                        The full coaching session UI will be implemented here.
                    </p>
                </Card>
            </div>
        </DashboardLayout>
    );
}

