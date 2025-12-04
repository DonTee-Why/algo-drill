import { Head, Link, router } from '@inertiajs/react';
import React, { useState } from 'react';
import DashboardLayout from '../Layouts/DashboardLayout';
import { Button, TextInput, Badge, Table, TableHead, TableHeadCell, TableBody, TableRow, TableCell, Tooltip } from 'flowbite-react';
import { route } from 'ziggy-js';

interface Problem {
    id: string;
    title: string;
    slug: string;
    difficulty: 'Easy' | 'Medium' | 'Hard';
    tags: string[];
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

interface Filters {
    search?: string;
    difficulty?: string;
    tags?: string[];
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
    filters: Filters;
}

export default function Index({ auth, problems, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [difficulty, setDifficulty] = useState<string>(filters.difficulty || 'All');
    const [startingSession, setStartingSession] = useState<string | null>(null);

    const handleFilter = () => {
        const params: Record<string, string> = {};
        
        if (search) {
            params.search = search;
        }
        
        if (difficulty && difficulty !== 'All') {
            params.difficulty = difficulty;
        }

        router.get(route('problems.index'), params, { preserveScroll: true, replace: true });
    };

    const handleStartSession = (problemId: string) => {
        setStartingSession(problemId);
        router.post(
            route('sessions.store'),
            { problem_id: problemId },
            {
                onFinish: () => setStartingSession(null),
                preserveScroll: true,
            }
        );
    };

    const difficultyColors: Record<string, string> = {
        Easy: 'success',
        Medium: 'warning',
        Hard: 'failure',
    };

    return (
        <DashboardLayout auth={auth}>
            <Head title="Problems" />
            <div className="max-w-[1400px] mx-auto px-8 py-8">
                <div className="mb-6">
                    <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-3">
                        Problem Library
                    </h1>
                    <p className="text-gray-600 dark:text-gray-400">
                        Choose a problem to start your guided coding session
                    </p>
                </div>

                {/* Filters */}
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Search
                            </label>
                            <TextInput
                                type="text"
                                placeholder="Search problems..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                onKeyDown={(e) => e.key === 'Enter' && handleFilter()}
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Difficulty
                            </label>
                            <div className="flex gap-2">
                                {['All', 'Easy', 'Medium', 'Hard'].map((diff) => (
                                    <Button
                                        key={diff}
                                        size="sm"
                                        color={difficulty === diff ? 'blue' : 'gray'}
                                        onClick={() => setDifficulty(diff)}
                                        className="cursor-pointer"
                                    >
                                        {diff}
                                    </Button>
                                ))}
                            </div>
                        </div>
                    </div>
                    <Button onClick={handleFilter} color="blue" className="cursor-pointer">
                        Apply Filters
                    </Button>
                </div>

                {/* Problems Table */}
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableHeadCell>Title</TableHeadCell>
                                <TableHeadCell>Difficulty</TableHeadCell>
                                <TableHeadCell>Tags</TableHeadCell>
                                <TableHeadCell>Action</TableHeadCell>
                            </TableRow>
                        </TableHead>
                        <TableBody className="divide-y">
                            {problems.data.map((problem) => (
                                <TableRow
                                    key={problem.id}
                                    className="bg-white dark:border-gray-700 dark:bg-gray-800"
                                >
                                    <TableCell className="whitespace-nowrap font-medium text-gray-900 dark:text-white">
                                        {problem.title}
                                        {problem.is_premium && (
                                            <Badge color="warning" className="ml-2">
                                                Premium
                                            </Badge>
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        <Badge color={difficultyColors[problem.difficulty] as any}>
                                            {problem.difficulty}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex gap-1 flex-wrap">
                                            {problem.tags.slice(0, 3).map((tag) => (
                                                <span
                                                    key={tag}
                                                    className="px-2 py-1 text-xs bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded"
                                                >
                                                    {tag}
                                                </span>
                                            ))}
                                            {problem.tags.length > 3 && (
                                                <span className="px-2 py-1 text-xs text-gray-500 dark:text-gray-400">
                                                    +{problem.tags.length - 3}
                                                </span>
                                            )}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex items-center gap-2">
                                            <Button
                                                size="sm"
                                                color="blue"
                                                onClick={() => handleStartSession(problem.id)}
                                                disabled={startingSession === problem.id}
                                                className="cursor-pointer"
                                            >
                                                {startingSession === problem.id ? 'Starting...' : 'Start'}
                                            </Button>
                                            <Tooltip content="View problem details">
                                                <Link
                                                    href={route('problems.show', problem.slug)}
                                                    className="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                >
                                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </Link>
                                            </Tooltip>
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
        </DashboardLayout>
    );
}

