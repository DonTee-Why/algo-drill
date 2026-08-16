import React from 'react';
import { Button, Textarea } from 'flowbite-react';

interface ApproachStageFormProps {
    strategy: string;
    justification: string;
    complexity: string;
    setStrategy: (value: string) => void;
    setJustification: (value: string) => void;
    setComplexity: (value: string) => void;
    readOnly: boolean;
    onSubmit: () => void;
    processing: boolean;
    stageLabel: string;
}

export default function ApproachStageForm({
    strategy,
    justification,
    complexity,
    setStrategy,
    setJustification,
    setComplexity,
    readOnly,
    onSubmit,
    processing,
    stageLabel,
}: ApproachStageFormProps) {
    return (
        <div className="space-y-4">
            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Strategy
                </label>
                <Textarea
                    value={strategy}
                    onChange={(e) => setStrategy(e.target.value)}
                    rows={4}
                    placeholder="State a clear high-level algorithmic idea..."
                    className="dark:bg-gray-900"
                    readOnly={readOnly}
                />
            </div>
            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Why it works
                </label>
                <Textarea
                    value={justification}
                    onChange={(e) => setJustification(e.target.value)}
                    rows={4}
                    placeholder="Explain why this approach solves the problem..."
                    className="dark:bg-gray-900"
                    readOnly={readOnly}
                />
            </div>
            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Rough time and space complexity
                </label>
                <Textarea
                    value={complexity}
                    onChange={(e) => setComplexity(e.target.value)}
                    rows={3}
                    placeholder="State rough time and space complexity..."
                    className="dark:bg-gray-900"
                    readOnly={readOnly}
                />
            </div>
            {!readOnly && (
                <Button
                    onClick={onSubmit}
                    disabled={processing}
                    color="blue"
                    className="w-full"
                >
                    Submit {stageLabel}
                </Button>
            )}
        </div>
    );
}
