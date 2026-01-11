import React from 'react';
import { Textarea } from 'flowbite-react';

interface OptimizeFieldsProps {
    complexityAnalysis: string;
    optimizationTechnique: string;
    tradeoffs: string;
    setComplexityAnalysis: (value: string) => void;
    setOptimizationTechnique: (value: string) => void;
    setTradeoffs: (value: string) => void;
    readOnly: boolean;
}

export default function OptimizeFields({
    complexityAnalysis,
    optimizationTechnique,
    tradeoffs,
    setComplexityAnalysis,
    setOptimizationTechnique,
    setTradeoffs,
    readOnly,
}: OptimizeFieldsProps) {
    return (
        <div className="space-y-3">
            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Complexity Analysis
                </label>
                <Textarea
                    value={complexityAnalysis}
                    onChange={(e) => setComplexityAnalysis(e.target.value)}
                    rows={2}
                    placeholder="Time: O(n), Space: O(1)..."
                    className="dark:bg-gray-900"
                    readOnly={readOnly}
                />
            </div>
            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Optimization Technique
                </label>
                <Textarea
                    value={optimizationTechnique}
                    onChange={(e) => setOptimizationTechnique(e.target.value)}
                    rows={2}
                    placeholder="Used hash map to achieve O(n) lookup..."
                    className="dark:bg-gray-900"
                    readOnly={readOnly}
                />
            </div>
            <div>
                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Tradeoffs
                </label>
                <Textarea
                    value={tradeoffs}
                    onChange={(e) => setTradeoffs(e.target.value)}
                    rows={2}
                    placeholder="Trading space for time..."
                    className="dark:bg-gray-900"
                    readOnly={readOnly}
                />
            </div>
        </div>
    );
}

