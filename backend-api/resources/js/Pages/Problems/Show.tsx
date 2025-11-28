import { Head } from '@inertiajs/react';
import React, { useState } from 'react';
import DashboardLayout from '../Layouts/DashboardLayout';
import { Button, Badge, Select, Card } from 'flowbite-react';
import ReactMarkdown from 'react-markdown';

interface Signature {
    id: string;
    lang: string;
    function_name: string;
    params: Array<{ name: string; type: string }>;
    returns: { type: string };
}

interface Problem {
    id: string;
    title: string;
    slug: string;
    difficulty: 'Easy' | 'Medium' | 'Hard';
    tags: string[];
    constraints: string[];
    description_md: string;
    is_premium: boolean;
    signatures: Signature[];
}

interface Props {
    auth?: {
        user?: {
            id: number;
            name: string;
            email: string;
        } | null;
    };
    problem: Problem;
}

export default function Show({ auth, problem }: Props) {
    const [selectedLang, setSelectedLang] = useState<string>(
        problem.signatures[0]?.lang || ''
    );

    const currentSignature = problem.signatures.find((sig) => sig.lang === selectedLang);

    const difficultyColors: Record<string, string> = {
        Easy: 'success',
        Medium: 'warning',
        Hard: 'failure',
    };

    const formatSignature = (sig: Signature) => {
        const params = sig.params.map((p) => `${p.name}: ${p.type}`).join(', ');
        return `function ${sig.function_name}(${params}): ${sig.returns.type}`;
    };

    return (
        <DashboardLayout auth={auth}>
            <Head title={problem.title} />
            <div className="max-w-[1400px] mx-auto px-8 py-8">
                {/* Header */}
                <div className="mb-6">
                    <div className="flex items-center gap-3 mb-3">
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                            {problem.title}
                        </h1>
                        <Badge color={difficultyColors[problem.difficulty] as any}>
                            {problem.difficulty}
                        </Badge>
                        {problem.is_premium && (
                            <Badge color="warning">Premium</Badge>
                        )}
                    </div>
                    <div className="flex gap-2 flex-wrap">
                        {problem.tags.map((tag) => (
                            <span
                                key={tag}
                                className="px-3 py-1 text-sm bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full"
                            >
                                {tag}
                            </span>
                        ))}
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Main Content */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Description */}
                        <Card>
                            <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-4">
                                Description
                            </h2>
                            <div className="prose dark:prose-invert max-w-none">
                                <ReactMarkdown>{problem.description_md}</ReactMarkdown>
                            </div>
                        </Card>

                        {/* Constraints */}
                        <Card>
                            <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-4">
                                Constraints
                            </h2>
                            <ul className="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300">
                                {problem.constraints.map((constraint, index) => (
                                    <li key={index}>{constraint}</li>
                                ))}
                            </ul>
                        </Card>
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Language Selection */}
                        <Card>
                            <h3 className="text-lg font-bold text-gray-900 dark:text-white mb-4">
                                Language
                            </h3>
                            <Select
                                value={selectedLang}
                                onChange={(e) => setSelectedLang(e.target.value)}
                                className="mb-4"
                            >
                                {problem.signatures.map((sig) => (
                                    <option key={sig.lang} value={sig.lang}>
                                        {sig.lang.charAt(0).toUpperCase() + sig.lang.slice(1)}
                                    </option>
                                ))}
                            </Select>

                            {/* Function Signature */}
                            {currentSignature && (
                                <div>
                                    <h4 className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Function Signature
                                    </h4>
                                    <pre className="bg-gray-100 dark:bg-gray-900 p-3 rounded text-sm overflow-x-auto">
                                        <code className="text-gray-800 dark:text-gray-200">
                                            {formatSignature(currentSignature)}
                                        </code>
                                    </pre>
                                </div>
                            )}
                        </Card>

                        {/* Action Button */}
                        <Button
                            color="blue"
                            size="lg"
                            className="w-full cursor-pointer"
                            onClick={() => {
                                // Placeholder for session creation
                                alert('Start Session - To be implemented');
                            }}
                        >
                            Start Session
                        </Button>
                    </div>
                </div>
            </div>
        </DashboardLayout>
    );
}

