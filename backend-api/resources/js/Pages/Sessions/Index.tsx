import { Head, Link } from '@inertiajs/react';
import React from 'react';
import DashboardLayout from '../Layouts/DashboardLayout';
import { Card, Badge, Table, TableHead, TableHeadCell, TableBody, TableRow, TableCell, Button } from 'flowbite-react';
import { route } from 'ziggy-js';
import { PlayCircle, Clock, CheckCircle, XCircle, Lock } from 'lucide-react';

interface Props {
    auth?: {
        user?: {
            id: number;
            name: string;
            email: string;
        } | null;
    };
    sessions: {
        data: Array<{
            id: string;
            state: string;
            created_at: string;
            updated_at: string;
            problem: {
                id: string;
                title: string;
                slug: string;
                difficulty: string;
            };
        }>;
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

const STAGE_LABELS: Record<string, string> = {
    CLARIFY: 'Clarify',
    APPROACH: 'Approach',
    PSEUDOCODE: 'Pseudocode',
    BRUTE_FORCE: 'Brute Force',
    OPTIMIZE: 'Optimize',
    DONE: 'Done',
};

export default function Index({ auth, sessions }: Props) {
    function getDifficultyColor(difficulty: string): string {
        const colors: Record<string, string> = {
            Easy: 'green',
            Medium: 'yellow',
            Hard: 'red',
        };
        return colors[difficulty] || 'gray';
    }

    function getStageColor(state: string): string {
        if (state === 'DONE') return 'green';
        return 'blue';
    }

    function formatDate(dateString: string): string {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now.getTime() - date.getTime();
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins} min${diffMins > 1 ? 's' : ''} ago`;
        if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
        if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
        return date.toLocaleDateString();
    }

    return (
        <DashboardLayout auth={auth}>
            <Head title="My Sessions" />
            <div className="max-w-7xl mx-auto px-4 py-8">
                <div className="mb-6">
                    <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        My Sessions
                    </h1>
                    <p className="text-gray-600 dark:text-gray-400">
                        View and continue your coding practice sessions
                    </p>
                </div>

                {sessions.data.length === 0 ? (
                    <Card className="dark:bg-gray-800">
                        <div className="text-center py-12">
                            <Clock className="w-16 h-16 text-gray-400 mx-auto mb-4" />
                            <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                No sessions yet
                            </h3>
                            <p className="text-gray-600 dark:text-gray-400 mb-6">
                                Start a new session by selecting a problem from the Problems page.
                            </p>
                            <Link href={route('problems.index')}>
                                <Button color="blue">
                                    Browse Problems
                                </Button>
                            </Link>
                        </div>
                    </Card>
                ) : (
                    <Card className="dark:bg-gray-800">
                        <Table hoverable>
                            <TableHead>
                                <TableHeadCell>Problem</TableHeadCell>
                                <TableHeadCell>Stage</TableHeadCell>
                                <TableHeadCell>Status</TableHeadCell>
                                <TableHeadCell>Last Updated</TableHeadCell>
                                <TableHeadCell>
                                    <span className="sr-only">Actions</span>
                                </TableHeadCell>
                            </TableHead>
                            <TableBody className="divide-y">
                                {sessions.data.map((session) => (
                                    <TableRow
                                        key={session.id}
                                        className="bg-white dark:border-gray-700 dark:bg-gray-800"
                                    >
                                        <TableCell className="whitespace-nowrap font-medium text-gray-900 dark:text-white">
                                            <div className="flex items-center gap-2">
                                                <Link
                                                    href={route('problems.show', session.problem.slug)}
                                                    className="hover:underline"
                                                >
                                                    {session.problem.title}
                                                </Link>
                                                <Badge color={getDifficultyColor(session.problem.difficulty)}>
                                                    {session.problem.difficulty}
                                                </Badge>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge color={getStageColor(session.state)}>
                                                {STAGE_LABELS[session.state] || session.state}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            {session.state === 'DONE' ? (
                                                <div className="flex items-center gap-1 text-green-600 dark:text-green-400">
                                                    <CheckCircle className="w-4 h-4" />
                                                    <span className="text-sm">Completed</span>
                                                </div>
                                            ) : (
                                                <div className="flex items-center gap-1 text-blue-600 dark:text-blue-400">
                                                    <Clock className="w-4 h-4" />
                                                    <span className="text-sm">In Progress</span>
                                                </div>
                                            )}
                                        </TableCell>
                                        <TableCell className="text-gray-600 dark:text-gray-400">
                                            {formatDate(session.updated_at)}
                                        </TableCell>
                                        <TableCell>
                                            <Link href={route('sessions.show', session.id)}>
                                                <Button size="xs" color="blue">
                                                    {session.state === 'DONE' ? 'View' : 'Continue'}
                                                </Button>
                                            </Link>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>

                        {/* Pagination */}
                        {sessions.last_page > 1 && (
                            <div className="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3 sm:px-6 mt-4">
                                <div className="flex flex-1 justify-between sm:hidden">
                                    {sessions.current_page > 1 && (
                                        <Link
                                            href={`${route('sessions.index')}?page=${sessions.current_page - 1}`}
                                            className="relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                                        >
                                            Previous
                                        </Link>
                                    )}
                                    {sessions.current_page < sessions.last_page && (
                                        <Link
                                            href={`${route('sessions.index')}?page=${sessions.current_page + 1}`}
                                            className="relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                                        >
                                            Next
                                        </Link>
                                    )}
                                </div>
                                <div className="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                    <div>
                                        <p className="text-sm text-gray-700 dark:text-gray-300">
                                            Showing{' '}
                                            <span className="font-medium">
                                                {(sessions.current_page - 1) * sessions.per_page + 1}
                                            </span>{' '}
                                            to{' '}
                                            <span className="font-medium">
                                                {Math.min(sessions.current_page * sessions.per_page, sessions.total)}
                                            </span>{' '}
                                            of <span className="font-medium">{sessions.total}</span> results
                                        </p>
                                    </div>
                                    <div>
                                        <nav className="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                            {sessions.current_page > 1 && (
                                                <Link
                                                    href={`${route('sessions.index')}?page=${sessions.current_page - 1}`}
                                                    className="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 focus:z-20 focus:outline-offset-0"
                                                >
                                                    <span className="sr-only">Previous</span>
                                                    Previous
                                                </Link>
                                            )}
                                            {Array.from({ length: sessions.last_page }, (_, i) => i + 1).map((page) => {
                                                if (
                                                    page === 1 ||
                                                    page === sessions.last_page ||
                                                    (page >= sessions.current_page - 1 && page <= sessions.current_page + 1)
                                                ) {
                                                    return (
                                                        <Link
                                                            key={page}
                                                            href={`${route('sessions.index')}?page=${page}`}
                                                            className={`relative inline-flex items-center px-4 py-2 text-sm font-semibold ${
                                                                page === sessions.current_page
                                                                    ? 'z-10 bg-blue-600 text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600'
                                                                    : 'text-gray-900 dark:text-gray-300 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 focus:z-20 focus:outline-offset-0'
                                                            }`}
                                                        >
                                                            {page}
                                                        </Link>
                                                    );
                                                } else if (
                                                    page === sessions.current_page - 2 ||
                                                    page === sessions.current_page + 2
                                                ) {
                                                    return (
                                                        <span
                                                            key={page}
                                                            className="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 ring-1 ring-inset ring-gray-300 dark:ring-gray-600"
                                                        >
                                                            ...
                                                        </span>
                                                    );
                                                }
                                                return null;
                                            })}
                                            {sessions.current_page < sessions.last_page && (
                                                <Link
                                                    href={`${route('sessions.index')}?page=${sessions.current_page + 1}`}
                                                    className="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 focus:z-20 focus:outline-offset-0"
                                                >
                                                    <span className="sr-only">Next</span>
                                                    Next
                                                </Link>
                                            )}
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        )}
                    </Card>
                )}
            </div>
        </DashboardLayout>
    );
}

