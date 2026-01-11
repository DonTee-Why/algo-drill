import React from 'react';
import { Card } from 'flowbite-react';
import { CheckCircle, Clock, Lock, Square } from 'lucide-react';
import type { StageProgressItem } from './types';

interface StageProgressProps {
    stageProgress: StageProgressItem[];
    viewingStage: string | null;
    onSelectStage: (stage: string | null) => void;
    currentScore: number;
    maxScore: number;
    stageLabels: Record<string, string>;
    currentStage: string;
    activeStage: string;
}

export default function StageProgress({
    stageProgress,
    viewingStage,
    onSelectStage,
    currentScore,
    maxScore,
    stageLabels,
    currentStage,
}: StageProgressProps) {
    return (
        <Card className="dark:bg-gray-800">
            <h3 className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">
                Stage Progress
            </h3>
            <div className="space-y-3">
                {stageProgress.map((stage) => {
                    const isCompleted = stage.isCompleted;
                    const isCurrent = stage.isCurrent;
                    const isLocked = stage.isLocked;
                    const isViewing = viewingStage === stage.stage;

                    return (
                        <div key={stage.stage} className="space-y-1">
                            <div
                                className={`flex items-center justify-between text-sm ${
                                    isCompleted
                                        ? 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 rounded p-1 -m-1'
                                        : ''
                                }`}
                                onClick={() => {
                                    if (isCompleted) {
                                        onSelectStage(isViewing ? null : stage.stage);
                                    }
                                }}
                            >
                                <div className="flex items-center gap-2">
                                    {isCompleted ? (
                                        <CheckCircle className="w-4 h-4 text-green-500" />
                                    ) : isCurrent ? (
                                        <Clock className="w-4 h-4 text-blue-500" />
                                    ) : isLocked ? (
                                        <Lock className="w-4 h-4 text-gray-400" />
                                    ) : (
                                        <Square className="w-4 h-4 text-gray-400" />
                                    )}
                                    <span
                                        className={`font-medium ${
                                            isViewing
                                                ? 'text-green-500 dark:text-green-500'
                                                : isCompleted
                                                    ? 'text-green-400 dark:text-green-300'
                                                    : isCurrent
                                                        ? 'text-blue-600 dark:text-blue-400'
                                                        : 'text-gray-500 dark:text-gray-400'
                                        }`}
                                    >
                                        {stageLabels[stage.stage] || stage.label}
                                        {isViewing && ' (viewing)'}
                                    </span>
                                </div>
                                {!isLocked && (
                                    <span
                                        className={`text-xs ${
                                            isCompleted
                                                ? 'text-green-600 dark:text-green-400'
                                                : isCurrent
                                                    ? 'text-blue-600 dark:text-blue-400'
                                                    : 'text-gray-500 dark:text-gray-400'
                                        }`}
                                    >
                                        {isCompleted
                                            ? `${stage.completedAttempts}/${stage.completedAttempts}`
                                            : isCurrent
                                                ? `${stage.totalAttempts}/${stage.totalAttempts}+`
                                                : '0/0'}
                                    </span>
                                )}
                            </div>
                            {isCurrent && (
                                <div className="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                    <div
                                        className="bg-blue-600 h-1.5 rounded-full transition-all"
                                        style={{ width: `${Math.min((currentScore / maxScore) * 100, 100)}%` }}
                                    />
                                </div>
                            )}
                        </div>
                    );
                })}
            </div>
        </Card>
    );
}

