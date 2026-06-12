import { Link } from '@inertiajs/react';
import React from 'react';
import { route } from 'ziggy-js';
import type { SessionsPaginationProps } from './types';

export default function SessionsPagination({ sessions }: SessionsPaginationProps) {
    if (sessions.last_page <= 1) {
        return null;
    }

    return (
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
                        <span className="font-medium">{(sessions.current_page - 1) * sessions.per_page + 1}</span> to{' '}
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
                                                ? 'z-10 bg-blue-600 text-white focus:z-20 focus-visible:outline focus-visible:outline-offset-2 focus-visible:outline-blue-600'
                                                : 'text-gray-900 dark:text-gray-300 ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 focus:z-20 focus:outline-offset-0'
                                        }`}
                                    >
                                        {page}
                                    </Link>
                                );
                            }

                            if (page === sessions.current_page - 2 || page === sessions.current_page + 2) {
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
    );
}
