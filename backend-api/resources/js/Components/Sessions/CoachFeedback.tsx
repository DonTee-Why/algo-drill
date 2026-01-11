import React from 'react';
import { Badge, Card } from 'flowbite-react';
import type { Attempt } from './types';

interface CoachFeedbackProps {
    displayedAttempt: Attempt | null;
    activeStage: string;
    stageLabels: Record<string, string>;
}

export default function CoachFeedback({ displayedAttempt, activeStage, stageLabels }: CoachFeedbackProps) {
    if (!displayedAttempt) return null;

    return (
        <Card className="dark:bg-gray-800">
            <h3 className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                Coach Feedback
            </h3>
            <div className="space-y-3">
                <div>
                    <span className="text-xs text-gray-500 dark:text-gray-400">Stage:</span>
                    <Badge
                        color={displayedAttempt.rubric_scores?.passed ? 'green' : 'red'}
                        className="ml-2 mb-2"
                    >
                        {stageLabels[activeStage] || activeStage}
                    </Badge>
                    <Badge
                        color={displayedAttempt.rubric_scores?.passed ? 'green' : 'red'}
                        className="ml-2 mb-2"
                    >
                        {displayedAttempt.rubric_scores?.passed ? 'Passed' : 'Needs Work'}
                    </Badge>
                </div>
                {displayedAttempt.coach_msg && (
                    <div>
                        <p className="text-sm text-gray-700 dark:text-gray-300">
                            {displayedAttempt.coach_msg}
                        </p>
                    </div>
                )}
            </div>
        </Card>
    );
}

