import React from 'react';
import { Badge, Card } from 'flowbite-react';
import ReactMarkdown from 'react-markdown';
import type { Problem } from './types';

interface ProblemCardProps {
    problem: Problem;
    difficultyColor: (difficulty: string) => string;
}

export default function ProblemCard({ problem, difficultyColor }: ProblemCardProps) {
    return (
        <Card className="dark:bg-gray-800">
            <div className="space-y-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        {problem.title}
                    </h1>
                    <div className="flex items-center gap-2 mb-3">
                        <Badge color={difficultyColor(problem.difficulty)}>
                            {problem.difficulty}
                        </Badge>
                        {problem.tags.map((tag, idx) => (
                            <Badge key={idx} color="gray" className="text-xs">
                                {tag}
                            </Badge>
                        ))}
                    </div>
                </div>

                <div>
                    <h3 className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Problem Statement
                    </h3>
                    <div className="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap [&_*]:overflow-wrap-break-word [&_pre]:whitespace-pre-wrap [&_code]:whitespace-pre-wrap">
                        <ReactMarkdown>{problem.description_md}</ReactMarkdown>
                    </div>
                </div>

                <div>
                    <h3 className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Constraints
                    </h3>
                    <ul className="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        {problem.constraints.map((constraint, idx) => (
                            <li key={idx}>• {constraint}</li>
                        ))}
                    </ul>
                </div>
            </div>
        </Card>
    );
}

