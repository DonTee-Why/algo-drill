import { Head, Link, router } from '@inertiajs/react';
import React from 'react';
import AdminLayout from '../../Layouts/AdminLayout';
import { Button, Badge, Table, TableHead, TableHeadCell, TableBody, TableRow, TableCell } from 'flowbite-react';
import { route } from 'ziggy-js';

interface Problem {
    id: string;
    title: string;
    difficulty: 'Easy' | 'Medium' | 'Hard';
    is_premium: boolean;
    created_at: string;
}

interface PaginatedProblems {
    data: Problem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

interface Props {
    auth?: {
        user?: {
            id: number;
            name: string;
            email: string;
        } | null;
    };
    problems: PaginatedProblems;
}

export default function Index({ auth, problems }: Props) {
    const handleDelete = (problemId: string) => {
        if (confirm('Are you sure you want to delete this problem?')) {
            router.delete(route('admin.problems.destroy', problemId));
        }
    };

    const difficultyColors: Record<string, string> = {
        Easy: 'success',
        Medium: 'warning',
        Hard: 'failure',
    };

    return (
        <AdminLayout auth={auth}>
            <Head title="Admin - Problems" />
            <div className="w-full mx-auto px-8 py-8">
                <div className="flex items-center justify-between mb-6">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-3">
                            Manage Problems
                        </h1>
                        <p className="text-gray-600 dark:text-gray-400">
                            Create and manage coding problems
                        </p>
                    </div>
                    <Link href={route('admin.problems.create')}>
                        <Button color="blue" className="cursor-pointer">
                            Create New Problem
                        </Button>
                    </Link>
                </div>

                <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableHeadCell>ID</TableHeadCell>
                                <TableHeadCell>Title</TableHeadCell>
                                <TableHeadCell>Difficulty</TableHeadCell>
                                <TableHeadCell>Premium</TableHeadCell>
                                <TableHeadCell>Created</TableHeadCell>
                                <TableHeadCell>Actions</TableHeadCell>
                            </TableRow>
                        </TableHead>
                        <TableBody className="divide-y">
                            {problems.data.map((problem) => (
                                <TableRow
                                    key={problem.id}
                                    className="bg-white dark:border-gray-700 dark:bg-gray-800"
                                >
                                    <TableCell className="font-mono text-xs text-gray-600 dark:text-gray-400">
                                        {problem.id.substring(0, 8)}...
                                    </TableCell>
                                    <TableCell className="whitespace-nowrap font-medium text-gray-900 dark:text-white">
                                        {problem.title}
                                    </TableCell>
                                    <TableCell>
                                        <Badge color={difficultyColors[problem.difficulty] as any}>
                                            {problem.difficulty}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        {problem.is_premium ? (
                                            <Badge color="warning">Yes</Badge>
                                        ) : (
                                            <span className="text-gray-500">No</span>
                                        )}
                                    </TableCell>
                                    <TableCell className="text-gray-600 dark:text-gray-400">
                                        {new Date(problem.created_at).toLocaleDateString()}
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex gap-2">
                                            <Link
                                                href={route('admin.problems.edit', problem.id)}
                                                className="font-medium text-cyan-600 hover:underline dark:text-cyan-500"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                onClick={() => handleDelete(problem.id)}
                                                className="font-medium text-red-600 hover:underline dark:text-red-500 cursor-pointer"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>

                    {/* Pagination */}
                    {problems.last_page > 1 && (
                        <div className="flex justify-center gap-2 p-4">
                            {problems.links.map((link, index) => (
                                <button
                                    key={index}
                                    onClick={() => link.url && router.get(link.url)}
                                    disabled={!link.url}
                                    className={`px-3 py-1 rounded ${
                                        link.active
                                            ? 'bg-blue-600 text-white'
                                            : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                                    } ${!link.url ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}

