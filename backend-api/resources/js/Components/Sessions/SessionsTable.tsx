import { Link } from '@inertiajs/react';
import React from 'react';
import { Badge, Table, TableHead, TableHeadCell, TableBody, TableRow, TableCell, Button } from 'flowbite-react';
import { Clock, CheckCircle } from 'lucide-react';
import { route } from 'ziggy-js';
import { STAGE_LABELS } from './constants';
import type { SessionsTableProps } from './types';
import { formatRelativeDate, getDifficultyBadgeColor, getStageBadgeColor } from './utils/display';

export default function SessionsTable({ sessions }: SessionsTableProps) {
    return (
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
                {sessions.map((session) => (
                    <TableRow key={session.id} className="bg-white dark:border-gray-700 dark:bg-gray-800">
                        <TableCell className="whitespace-nowrap font-medium text-gray-900 dark:text-white">
                            <div className="flex items-center gap-2">
                                <Link
                                    href={route('problems.show', session.problem.slug)}
                                    className="hover:underline"
                                >
                                    {session.problem.title}
                                </Link>
                                <Badge color={getDifficultyBadgeColor(session.problem.difficulty)}>
                                    {session.problem.difficulty}
                                </Badge>
                            </div>
                        </TableCell>
                        <TableCell>
                            <Badge color={getStageBadgeColor(session.state)}>
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
                            {formatRelativeDate(session.updated_at)}
                        </TableCell>
                        <TableCell>
                            <Link href={route('sessions.show', session.id)}>
                                <Button size="xs" color="blue" className="cursor-pointer">
                                    {session.state === 'DONE' ? 'View' : 'Continue'}
                                </Button>
                            </Link>
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
