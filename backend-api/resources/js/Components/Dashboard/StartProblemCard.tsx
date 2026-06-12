import React from 'react';
import { Card, Button } from 'flowbite-react';
import type { StartProblemCardProps } from './types';

export default function StartProblemCard({ onBrowse }: StartProblemCardProps) {
    return (
        <Card className="rounded-xl shadow-md col-span-1 md:col-span-2">
            <div className="flex items-center justify-between gap-8">
                <div className="space-y-2 flex-1">
                    <h5 className="text-xl font-bold text-gray-900 dark:text-white">Start a new problem</h5>
                    <p className="text-sm text-gray-500 dark:text-gray-400">
                        Pick a problem and go through Clarify → Brute Force → Pseudocode → Code → Test → Optimize.
                    </p>
                </div>
                <Button
                    color="blue"
                    onClick={onBrowse}
                    className="cursor-pointer hover:bg-blue-600 hover:text-white whitespace-nowrap"
                >
                    Browse problems
                </Button>
            </div>
        </Card>
    );
}
