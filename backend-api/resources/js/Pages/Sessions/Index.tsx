import { Head } from '@inertiajs/react';
import React from 'react';
import SessionsEmptyState from '../../Components/Sessions/SessionsEmptyState';
import SessionsIndexHeader from '../../Components/Sessions/SessionsIndexHeader';
import SessionsPagination from '../../Components/Sessions/SessionsPagination';
import SessionsTable from '../../Components/Sessions/SessionsTable';
import type { SessionsIndexProps } from '../../Components/Sessions/types';
import DashboardLayout from '../Layouts/DashboardLayout';
import { Card } from 'flowbite-react';

export default function Index({ auth, sessions }: SessionsIndexProps) {
    return (
        <DashboardLayout auth={auth}>
            <Head title="My Sessions" />
            <div className="max-w-7xl mx-auto px-4 py-8">
                <SessionsIndexHeader />

                {sessions.data.length === 0 ? (
                    <SessionsEmptyState />
                ) : (
                    <Card className="dark:bg-gray-800">
                        <SessionsTable sessions={sessions.data} />
                        <SessionsPagination sessions={sessions} />
                    </Card>
                )}
            </div>
        </DashboardLayout>
    );
}
