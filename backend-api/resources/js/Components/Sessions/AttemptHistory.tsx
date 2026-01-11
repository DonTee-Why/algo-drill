import React from 'react';
import { Card } from 'flowbite-react';
import { CheckCircle, XCircle } from 'lucide-react';
import type { Attempt } from './types';

interface AttemptHistoryProps {
    attempts: Attempt[];
    stageLabels: Record<string, string>;
    calculateStageScore: (rubricScores: any, stage: string) => number;
    stageMaxScores: Record<string, number>;
}

export default function AttemptHistory({
    attempts,
    stageLabels,
    calculateStageScore,
    stageMaxScores,
}: AttemptHistoryProps) {
    return (
        <Card className="dark:bg-gray-800">
            <h3 className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                Attempt History
            </h3>
            <div className="space-y-3 max-h-96 overflow-y-auto">
                {attempts.length === 0 ? (
                    <p className="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                        No attempts yet
                    </p>
                ) : (
                    attempts.reduce((acc: any[], attempt, idx) => {
                        const prevAttempt = idx > 0 ? attempts[idx - 1] : null;
                        const isNewStage = !prevAttempt || prevAttempt.stage !== attempt.stage;

                        if (isNewStage) {
                            acc.push(
                                <div
                                    key={`stage-${attempt.stage}`}
                                    className="font-medium text-xs text-gray-500 dark:text-gray-400 mb-2"
                                >
                                    {stageLabels[attempt.stage] || attempt.stage}
                                </div>,
                            );
                        }

                        const time = new Date(attempt.created_at).toLocaleTimeString('en-US', {
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit',
                        });

                        acc.push(
                            <div
                                key={attempt.id || `${attempt.stage}-${idx}`}
                                className={`p-2 rounded text-xs ${
                                    attempt.passed
                                        ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800'
                                        : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800'
                                }`}
                            >
                                <div className="flex items-center gap-2 mb-1">
                                    {attempt.passed ? (
                                        <CheckCircle className="w-3 h-3 text-green-600 dark:text-green-400" />
                                    ) : (
                                        <XCircle className="w-3 h-3 text-red-600 dark:text-red-400" />
                                    )}
                                    <span className="font-medium text-gray-500 dark:text-gray-400">
                                        {attempt.passed ? 'Passed' : 'Failed'} attempt at {time}
                                    </span>
                                </div>
                                {attempt.coach_msg && (
                                    <p className="text-xs text-gray-500 dark:text-gray-400 opacity-75 mt-1">
                                        {attempt.coach_msg}
                                    </p>
                                )}
                                {attempt.rubric_scores && (
                                    <div className="text-xs text-gray-500 dark:text-gray-400 opacity-75 mt-1">
                                        Score: {calculateStageScore(attempt.rubric_scores, attempt.stage)}/
                                        {stageMaxScores[attempt.stage] || 0}
                                    </div>
                                )}
                            </div>,
                        );

                        return acc;
                    }, [])
                )}
            </div>
        </Card>
    );
}

