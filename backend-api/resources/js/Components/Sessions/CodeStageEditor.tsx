import React from 'react';
import { Button, Select } from 'flowbite-react';
import MonacoEditor from '../MonacoEditor';
import type { ProblemSignature } from './types';

interface CodeStageEditorProps {
    code: string;
    onChangeCode: (value: string) => void;
    selectedLang: string;
    signatures: ProblemSignature[];
    onLanguageChange: (lang: string) => void;
    isViewingPastStage: boolean;
    onRunTests: () => void;
    onSubmit: () => void;
    isRunningTests: boolean;
    processing: boolean;
    currentStageLabel: string;
}

export default function CodeStageEditor({
    code,
    onChangeCode,
    selectedLang,
    signatures,
    onLanguageChange,
    isViewingPastStage,
    onRunTests,
    onSubmit,
    isRunningTests,
    processing,
    currentStageLabel,
}: CodeStageEditorProps) {
    return (
        <div className="flex-1 flex flex-col space-y-4 overflow-hidden">
            <div className="flex items-center gap-2">
                <Select
                    value={selectedLang}
                    onChange={(e) => onLanguageChange(e.target.value)}
                    className="w-40"
                    disabled={isViewingPastStage}
                >
                    {signatures.map((sig) => (
                        <option key={sig.lang} value={sig.lang}>
                            {sig.lang.charAt(0).toUpperCase() + sig.lang.slice(1)}
                        </option>
                    ))}
                </Select>
            </div>

            <div className="flex-1">
                <MonacoEditor
                    value={code}
                    onChange={onChangeCode}
                    language={selectedLang}
                    height="400px"
                    readOnly={isViewingPastStage}
                />
            </div>

            {!isViewingPastStage && (
                <div className="flex gap-2">
                    <Button
                        onClick={onRunTests}
                        disabled={processing || isRunningTests || !code.trim()}
                        color="gray"
                        className="flex-1"
                    >
                        {isRunningTests ? 'Running...' : 'Run Tests'}
                    </Button>
                    <Button
                        onClick={onSubmit}
                        disabled={processing}
                        color="blue"
                        className="flex-1"
                    >
                        Submit {currentStageLabel}
                    </Button>
                </div>
            )}
        </div>
    );
}

