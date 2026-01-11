import React from 'react';
import { Button, Textarea } from 'flowbite-react';

interface ClarifyStageFormProps {
    inputsOutputs: string;
    constraints: string;
    examples: string;
    setInputsOutputs: (value: string) => void;
    setConstraints: (value: string) => void;
    setExamples: (value: string) => void;
    readOnly: boolean;
    onSubmit: () => void;
    processing: boolean;
    stageLabel: string;
}

export default function ClarifyStageForm({
    inputsOutputs,
    constraints,
    examples,
    setInputsOutputs,
    setConstraints,
    setExamples,
    readOnly,
    onSubmit,
    processing,
    stageLabel,
}: ClarifyStageFormProps) {
    return (
        <div className="space-y-4">
            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Inputs & Outputs
                </label>
                <Textarea
                    value={inputsOutputs}
                    onChange={(e) => setInputsOutputs(e.target.value)}
                    rows={4}
                    placeholder="Describe the inputs and expected outputs..."
                    className="dark:bg-gray-900"
                    readOnly={readOnly}
                />
            </div>
            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Constraints
                </label>
                <Textarea
                    value={constraints}
                    onChange={(e) => setConstraints(e.target.value)}
                    rows={4}
                    placeholder="Mention relevant constraints..."
                    className="dark:bg-gray-900"
                    readOnly={readOnly}
                />
            </div>
            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Examples (include at least one edge case)
                </label>
                <Textarea
                    value={examples}
                    onChange={(e) => setExamples(e.target.value)}
                    rows={6}
                    placeholder="Provide at least 2 examples, including 1 edge case..."
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

