import React from 'react';
import { Button, Textarea } from 'flowbite-react';

interface TextStageFormProps {
    activeStage: string;
    textValue: string;
    onChange: (value: string) => void;
    readOnly: boolean;
    onSubmit: () => void;
    processing: boolean;
    stageLabel: string;
}

export default function TextStageForm({
    activeStage,
    textValue,
    onChange,
    readOnly,
    onSubmit,
    processing,
    stageLabel,
}: TextStageFormProps) {
    return (
        <div>
            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                {activeStage === 'APPROACH' && 'High-level Approach & Strategy'}
                {activeStage === 'PSEUDOCODE' && 'Detailed Pseudocode'}
            </label>
            <Textarea
                value={textValue}
                onChange={(e) => onChange(e.target.value)}
                rows={activeStage === 'PSEUDOCODE' ? 25 : 12}
                placeholder="Enter your response here..."
                className="dark:bg-gray-900"
                readOnly={readOnly}
            />
            {!readOnly && (
                <Button
                    onClick={onSubmit}
                    disabled={processing}
                    color="blue"
                    className="w-full mt-3"
                >
                    Submit {stageLabel}
                </Button>
            )}
        </div>
    );
}

