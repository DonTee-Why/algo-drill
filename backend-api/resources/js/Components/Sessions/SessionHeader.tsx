import React from 'react';
import { Button } from 'flowbite-react';

interface SessionHeaderProps {
    viewingStage: string | null;
    currentStage: string;
    sessionId: string;
    stageLabels: Record<string, string>;
    onBackToCurrent: () => void;
}

export default function SessionHeader({
    viewingStage,
    currentStage,
    sessionId,
    stageLabels,
    onBackToCurrent,
}: SessionHeaderProps) {
    const isViewingPastStage = viewingStage !== null;

    return (
        <div className="mb-4">
            <div className="flex items-center justify-between mb-2">
                <div>
                    <h2 className="text-lg font-bold text-gray-900 dark:text-white">
                        ALGODRILL SESSION
                    </h2>
                    <p className="text-sm text-gray-600 dark:text-gray-400">
                        {isViewingPastStage ? (
                            <>
                                Viewing: {stageLabels[viewingStage!] || viewingStage} | Current:{' '}
                                {stageLabels[currentStage] || currentStage} | Session ID: {sessionId.slice(0, 13)}
                            </>
                        ) : (
                            <>
                                Stage: {stageLabels[currentStage] || currentStage} | Session ID: {sessionId.slice(0, 13)}
                            </>
                        )}
                    </p>
                </div>
                {isViewingPastStage && (
                    <Button onClick={onBackToCurrent} color="gray" size="xs">
                        Back to Current Stage
                    </Button>
                )}
            </div>
            <p className="text-xs text-gray-500 dark:text-gray-400 mb-4">
                {isViewingPastStage
                    ? 'You are viewing a past stage. Click \"Back to Current Stage\" to return to editing.'
                    : 'Only the current stage is editable. Click on completed stages to view your past submissions.'}
            </p>
        </div>
    );
}

