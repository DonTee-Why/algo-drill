import { Link } from '@inertiajs/react';
import React from 'react';
import { Card, Button } from 'flowbite-react';
import { route } from 'ziggy-js';
import { STAGE_LABELS } from '../Sessions/constants';
import type { ContinueSessionCardProps } from './types';

export default function ContinueSessionCard({ activeSessions, onStartNew }: ContinueSessionCardProps) {
    const lastActiveSession = activeSessions[0] ?? null;

    return (
        <Card className="rounded-xl shadow-md md:col-span-1">
            <div className="space-y-6">
                <div className="space-y-2">
                    <h5 className="text-xl font-bold text-gray-900 dark:text-white">Continue where you left off</h5>
                    <p className="text-sm text-gray-500 dark:text-gray-400">Jump back into your last active problem.</p>
                </div>

                {lastActiveSession ? (
                    <div className="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <p className="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-3">
                            Last Active
                        </p>
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="font-semibold text-gray-900 dark:text-white mb-1">
                                    {lastActiveSession.problem.title}
                                </p>
                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                    Current stage: {STAGE_LABELS[lastActiveSession.state] || lastActiveSession.state}
                                </p>
                            </div>
                            <Link href={route('sessions.show', lastActiveSession.id)}>
                                <Button size="md" color="blue" className="cursor-pointer hover:bg-blue-600 hover:text-white">
                                    Resume
                                </Button>
                            </Link>
                        </div>
                    </div>
                ) : (
                    <div className="rounded-lg border border-dashed border-gray-300 dark:border-gray-600 p-6 text-center">
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">No active sessions yet.</p>
                        <Button color="blue" onClick={onStartNew} className="cursor-pointer">
                            Start a problem
                        </Button>
                    </div>
                )}

                {activeSessions.length > 1 && (
                    <div>
                        <p className="text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wide mb-3">
                            Other Active Sessions
                        </p>
                        <div className="space-y-3">
                            {activeSessions.slice(1).map((session) => (
                                <div
                                    key={session.id}
                                    className="flex items-center justify-between border rounded-lg p-2 border-gray-200 dark:border-gray-700"
                                >
                                    <div>
                                        <p className="font-medium text-gray-900 dark:text-white">{session.problem.title}</p>
                                        <p className="text-sm text-gray-600 dark:text-gray-400">
                                            Stage: {STAGE_LABELS[session.state] || session.state}
                                        </p>
                                    </div>
                                    <Link href={route('sessions.show', session.id)}>
                                        <Button size="sm" color="light" className="text-blue-600 dark:text-blue-400 cursor-pointer">
                                            Resume
                                        </Button>
                                    </Link>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </Card>
    );
}
