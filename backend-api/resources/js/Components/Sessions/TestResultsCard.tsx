import React from 'react';
import { Card } from 'flowbite-react';
import { CheckCircle, XCircle } from 'lucide-react';
import type { TestResults } from './types';

interface TestResultsCardProps {
    results: TestResults | null;
}

export default function TestResultsCard({ results }: TestResultsCardProps) {
    if (!results) return null;

    return (
        <Card className="dark:bg-gray-800">
            <h3 className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                Test Results
            </h3>
            <>
                {results.error && (
                    <div className="mb-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                        <div className="flex items-center gap-2 text-amber-800 dark:text-amber-200">
                            <svg className="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    fillRule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clipRule="evenodd"
                                />
                            </svg>
                            <span className="text-sm font-medium">{results.error}</span>
                        </div>
                    </div>
                )}
                <div className="mb-3">
                    <span className="text-sm font-medium text-gray-900 dark:text-white">
                        {results.summary.passed} passed / {results.summary.failed} failed
                    </span>
                </div>
                <div className="space-y-2">
                    {results.cases && results.cases.length > 0 ? (
                        results.cases.map((testCase: any, idx: number) => (
                            <div
                                key={idx}
                                className={`p-2 rounded text-xs ${
                                    testCase.status === 'passed' || testCase.status === 'pass'
                                        ? 'bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200'
                                        : 'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-200'
                                }`}
                            >
                                <div className="flex items-center gap-2 mb-1">
                                    {testCase.status === 'passed' || testCase.status === 'pass' ? (
                                        <CheckCircle className="w-3 h-3" />
                                    ) : (
                                        <XCircle className="w-3 h-3" />
                                    )}
                                    <span className="font-medium">Case #{idx + 1}</span>
                                </div>
                                {testCase.input && (
                                    <div className="text-xs opacity-75">
                                        Input: {JSON.stringify(testCase.input)}
                                    </div>
                                )}
                                {(testCase.status === 'failed' || testCase.status === 'fail') && (
                                    <div className="text-xs opacity-75 mt-1">
                                        Expected: {JSON.stringify(testCase.expected)}
                                        <br />
                                        Got: {JSON.stringify(testCase.got)}
                                    </div>
                                )}
                                {testCase.error && (
                                    <div className="text-xs opacity-75 mt-1 text-red-600 dark:text-red-400">
                                        Error: {testCase.error}
                                    </div>
                                )}
                            </div>
                        ))
                    ) : (
                        <div className="text-xs text-gray-500 dark:text-gray-400">No test cases run yet</div>
                    )}
                </div>
            </>
        </Card>
    );
}

