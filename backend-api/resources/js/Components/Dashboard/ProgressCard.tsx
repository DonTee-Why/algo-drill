import React from 'react';
import { Card, Progress } from 'flowbite-react';
import type { ProgressCardProps } from './types';

export default function ProgressCard({ progress }: ProgressCardProps) {
    const progressPercentage = (progress.completed / progress.total) * 100;

    return (
        <Card className="rounded-xl shadow-md md:col-span-1">
            <div className="space-y-2">
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-bold text-gray-900 dark:text-white">My Progress</h2>
                    <span className="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        Weekly snapshot
                    </span>
                </div>
                <div className="text-sm text-gray-500 dark:text-gray-400">
                    Track how consistently you&apos;re practicing.
                </div>
            </div>
            <div className="space-y-5">
                <div className="space-y-2">
                    <div className="flex justify-between">
                        <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Problems completed
                        </span>
                        <span className="text-sm font-medium text-gray-900 dark:text-white">
                            {`${progress.completed}/${progress.total}`}
                        </span>
                    </div>
                    <Progress progress={progressPercentage} color="blue" size="md" />
                </div>
                <div className="grid grid-cols-3 gap-6">
                    <div className="text-center">
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">Avg stage score</p>
                        <p className="text-xl font-bold text-gray-900 dark:text-white">
                            {`${progress.avgScore.toFixed(1)}/3`}
                        </p>
                    </div>
                    <div className="text-center">
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">Time to BF</p>
                        <p className="text-xl font-bold text-gray-900 dark:text-white">
                            {`${progress.timeToBF}s`}
                        </p>
                    </div>
                    <div className="text-center">
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">Hints used</p>
                        <p className="text-xl font-bold text-gray-900 dark:text-white">{progress.hintsUsed}</p>
                    </div>
                </div>
            </div>
            <div className="mt-auto pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-between text-sm text-gray-600 dark:text-gray-400">
                <span>Today: 1 problem, 3 stages cleared</span>
                <span>2 hints used</span>
            </div>
        </Card>
    );
}
