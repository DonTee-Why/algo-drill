import { Head, router, useForm } from '@inertiajs/react';
import React, { useState, useEffect } from 'react';
import DashboardLayout from '../Layouts/DashboardLayout';
import { Card, Button, Badge, Select, Textarea, Alert } from 'flowbite-react';
import { CheckCircle, XCircle, Lock, Clock, Copy, Upload, History, Square } from 'lucide-react';
import { route } from 'ziggy-js';
import ReactMarkdown from 'react-markdown';
import MonacoEditor from '../../Components/MonacoEditor';

interface Props {
    auth?: {
        user?: {
            id: number;
            name: string;
            email: string;
        } | null;
    };
    session: {
        id: string;
        state: string;
        created_at: string;
    };
    problem: {
        id: string;
        title: string;
        difficulty: string;
        tags: string[];
        constraints: string[];
        description_md: string;
        signatures: Array<{
            lang: string;
            function_name: string;
            params: any[];
            returns: any[];
        }>;
    };
    stageProgress: Array<{
        stage: string;
        label: string;
        isCurrent: boolean;
        isLocked: boolean;
        isCompleted: boolean;
        totalAttempts: number;
        completedAttempts: number;
    }>;
    testResults: {
        summary: {
            passed: number;
            failed: number;
            total: number;
        };
        cases: Array<{
            id?: number;
            status: string;
            timeMs?: number;
            got?: any;
            expected?: any;
            input?: any;
        }>;
    } | null;
    latestAttempt: {
        payload: any;
        coach_msg: string | null;
        rubric_scores: any;
        created_at: string;
    } | null;
    attempts: Array<{
        id: string;
        stage: string;
        coach_msg: string | null;
        rubric_scores: any;
        passed: boolean;
        created_at: string;
    }>;
}

const STAGE_LABELS: Record<string, string> = {
    CLARIFY: 'Clarify',
    APPROACH: 'Approach',
    PSEUDOCODE: 'Pseudocode',
    BRUTE_FORCE: 'Brute Force',
    OPTIMIZE: 'Optimize',
    DONE: 'Done',
};

const STAGE_MAX_SCORES: Record<string, number> = {
    CLARIFY: 12,
    APPROACH: 6,
    PSEUDOCODE: 9,
    BRUTE_FORCE: 9,
    OPTIMIZE: 6,
};

export default function Show({
    auth,
    session,
    problem,
    stageProgress,
    testResults,
    latestAttempt,
    attempts,
}: Props) {
    const [selectedLang, setSelectedLang] = useState<string>(
        problem.signatures[0]?.lang || 'javascript'
    );
    const [code, setCode] = useState<string>(
        latestAttempt?.payload?.code || getDefaultCode(problem.signatures.find(s => s.lang === selectedLang))
    );
    const [textInput, setTextInput] = useState<string>(
        latestAttempt?.payload?.text || latestAttempt?.payload?.user_input || ''
    );

    const currentSignature = problem.signatures.find(s => s.lang === selectedLang);
    const currentStage = session.state;
    const isCodeStage = currentStage === 'BRUTE_FORCE' || currentStage === 'OPTIMIZE';
    const isTextStage = currentStage === 'CLARIFY' || currentStage === 'APPROACH' || currentStage === 'PSEUDOCODE';
    const isHybridStage = currentStage === 'OPTIMIZE';

    const { data, setData, post, processing, errors } = useForm({
        code: code,
        lang: selectedLang,
        text: textInput,
        complexityAnalysis: latestAttempt?.payload?.complexityAnalysis || '',
        optimizationTechnique: latestAttempt?.payload?.optimizationTechnique || '',
        tradeoffs: latestAttempt?.payload?.tradeoffs || '',
    });

    useEffect(() => {
        setData('code', code);
        setData('lang', selectedLang);
        setData('text', textInput);
    }, [code, selectedLang, textInput]);

    function getDefaultCode(signature: typeof problem.signatures[0] | undefined): string {
        if (!signature) return '';
        
        const params = signature.params.map((p: any) => p.name || 'param').join(', ');
        const returnType = signature.returns?.[0]?.type || 'any';
        
        if (signature.lang === 'javascript') {
            return `function ${signature.function_name}(${params}) {\n    // Your code here\n    \n}`;
        } else if (signature.lang === 'python') {
            return `def ${signature.function_name}(${params}):\n    # Your code here\n    pass`;
        } else if (signature.lang === 'php') {
            return `function ${signature.function_name}(${params}) {\n    // Your code here\n    \n}`;
        }
        return '';
    }

    function formatSignature(signature: typeof problem.signatures[0]): string {
        if (!signature) return '';
        
        const params = signature.params.map((p: any) => {
            const type = p.type || '';
            return `${p.name}: ${type}`;
        }).join(', ');
        
        const returnType = signature.returns?.[0]?.type || 'any';
        
        if (signature.lang === 'javascript') {
            return `function ${signature.function_name}(${params}): ${returnType}`;
        } else if (signature.lang === 'python') {
            return `def ${signature.function_name}(${params}) -> ${returnType}:`;
        } else if (signature.lang === 'php') {
            return `function ${signature.function_name}(${params}): ${returnType}`;
        }
        return '';
    }

    function handleRunTests() {
        post(route('sessions.submit', session.id), {
            preserveScroll: true,
            onSuccess: () => {
                // Test results will be updated via Inertia
            },
        });
    }

    function handleSubmit() {
        post(route('sessions.submit', session.id), {
            preserveScroll: true,
        });
    }

    function getDifficultyColor(difficulty: string): string {
        const colors: Record<string, string> = {
            Easy: 'green',
            Medium: 'yellow',
            Hard: 'red',
        };
        return colors[difficulty] || 'gray';
    }

    function getStageStatusColor(stage: typeof stageProgress[0]): string {
        if (stage.isCompleted) return 'green';
        if (stage.isCurrent) return 'blue';
        if (stage.isLocked) return 'gray';
        return 'gray';
    }

    function calculateStageScore(rubricScores: any, stage: string): number {
        if (!rubricScores) return 0;
        const maxScore = STAGE_MAX_SCORES[stage] || 0;
        const totalScore = Object.values(rubricScores).reduce((sum: number, score: any) => {
            if (typeof score === 'number') return sum + score;
            if (score?.score) return sum + score.score;
            return sum;
        }, 0);
        return Math.min(totalScore, maxScore);
    }

    const currentStageProgress = stageProgress.find(s => s.stage === currentStage);
    const maxScore = STAGE_MAX_SCORES[currentStage] || 0;
    const currentScore = latestAttempt ? calculateStageScore(latestAttempt.rubric_scores, currentStage) : 0;

    return (
        <DashboardLayout auth={auth}>
            <Head title={`${problem.title} - Session`} />
            <div className="h-[calc(100vh-4rem)] flex gap-4 p-4 bg-gray-50 dark:bg-gray-900">
                {/* Left Panel - Problem Details & Progress */}
                <div className="w-2/6 shrink-0 overflow-y-auto space-y-4">
                    <Card className="dark:bg-gray-800">
                        <div className="space-y-4">
                            <div>
                                <h1 className="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                    {problem.title}
                                </h1>
                                <div className="flex items-center gap-2 mb-3">
                                    <Badge color={getDifficultyColor(problem.difficulty)}>
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
                </div>

                {/* Middle Panel - Current Stage Editor */}
                <div className="flex-1 flex flex-col overflow-hidden mb-4 h-fit">
                    <Card className="flex-1 flex flex-col dark:bg-gray-800 overflow-hidden">
                        <div className="mb-4">
                            <div className="flex items-center justify-between mb-2">
                                <div>
                                    <h2 className="text-lg font-bold text-gray-900 dark:text-white">
                                        ALGODRILL SESSION
                                    </h2>
                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                        Stage: {STAGE_LABELS[currentStage] || currentStage} | Session ID: {session.id.slice(0, 13)}
                                    </p>
                                </div>
                            </div>
                            <p className="text-xs text-gray-500 dark:text-gray-400 mb-4">
                                Only the current stage is editable. Previous stages are locked in this demo; future stages will unlock as you submit.
                            </p>
                        </div>

                        <div className="flex-1 flex flex-col space-y-4 overflow-hidden">
                            {isCodeStage && (
                                <>
                                    <div className="flex items-center gap-2">
                                        <Select
                                            value={selectedLang}
                                            onChange={(e) => {
                                                setSelectedLang(e.target.value);
                                                const sig = problem.signatures.find(s => s.lang === e.target.value);
                                                if (sig && !code) {
                                                    setCode(getDefaultCode(sig));
                                                }
                                            }}
                                            className="w-40"
                                        >
                                            {problem.signatures.map((sig) => (
                                                <option key={sig.lang} value={sig.lang}>
                                                    {sig.lang.charAt(0).toUpperCase() + sig.lang.slice(1)}
                                                </option>
                                            ))}
                                        </Select>
                                    </div>

                                    <div className="flex-1">
                                        <MonacoEditor
                                            value={code}
                                            onChange={setCode}
                                            language={selectedLang}
                                            height="400px"
                                        />
                                    </div>

                                    {isHybridStage && (
                                        <div className="space-y-3">
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    Complexity Analysis
                                                </label>
                                                <Textarea
                                                    value={data.complexityAnalysis}
                                                    onChange={(e) => setData('complexityAnalysis', e.target.value)}
                                                    rows={2}
                                                    placeholder="Time: O(n), Space: O(1)..."
                                                    className="dark:bg-gray-900"
                                                />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    Optimization Technique
                                                </label>
                                                <Textarea
                                                    value={data.optimizationTechnique}
                                                    onChange={(e) => setData('optimizationTechnique', e.target.value)}
                                                    rows={2}
                                                    placeholder="Used hash map to achieve O(n) lookup..."
                                                    className="dark:bg-gray-900"
                                                />
                                            </div>
                                            <div>
                                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    Tradeoffs
                                                </label>
                                                <Textarea
                                                    value={data.tradeoffs}
                                                    onChange={(e) => setData('tradeoffs', e.target.value)}
                                                    rows={2}
                                                    placeholder="Trading space for time..."
                                                    className="dark:bg-gray-900"
                                                />
                                            </div>
                                        </div>
                                    )}

                                    <div className="flex gap-2">
                                        <Button
                                            onClick={handleRunTests}
                                            disabled={processing}
                                            color="gray"
                                            className="flex-1"
                                        >
                                            Run Tests
                                        </Button>
                                        <Button
                                            onClick={handleSubmit}
                                            disabled={processing}
                                            color="blue"
                                            className="flex-1"
                                        >
                                            Submit {STAGE_LABELS[currentStage]}
                                        </Button>
                                    </div>
                                </>
                            )}

                            {isTextStage && (
                                <>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {currentStage === 'CLARIFY' && 'Clarifications (inputs, outputs, constraints, examples)'}
                                            {currentStage === 'APPROACH' && 'High-level Approach & Strategy'}
                                            {currentStage === 'PSEUDOCODE' && 'Detailed Pseudocode'}
                                        </label>
                                        <Textarea
                                            value={textInput}
                                            onChange={(e) => setTextInput(e.target.value)}
                                            rows={12}
                                            placeholder="Enter your response here..."
                                            className="dark:bg-gray-900"
                                        />
                                    </div>
                                    <Button
                                        onClick={handleSubmit}
                                        disabled={processing}
                                        color="blue"
                                        className="w-full"
                                    >
                                        Submit {STAGE_LABELS[currentStage]}
                                    </Button>
                                </>
                            )}
                        </div>
                    </Card>
                </div>

                {/* Right Panel - Test Results, Feedback, History */}
                <div className="w-80 shrink-0 overflow-y-auto space-y-4">
                    {/* Top Bar */}
                    {/* <div className="flex items-center justify-between p-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
                        <div className="flex items-center gap-2">
                            <button className="p-2 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 rounded cursor-pointer">
                                <Copy className="w-4 h-4" />
                            </button>
                            <button className="p-2 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 rounded cursor-pointer">
                                <Upload className="w-4 h-4" />
                            </button>
                            <button className="p-2 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 rounded cursor-pointer">
                                <History className="w-4 h-4" />
                            </button>
                        </div>
                        <Button color="red" size="xs">
                            <Square className="w-4 h-4 mr-1" />
                            Stop
                        </Button>
                    </div> */}

                    <Card className="dark:bg-gray-800">
                        <h3 className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">
                            Stage Progress
                        </h3>
                        <div className="space-y-3">
                            {stageProgress.map((stage) => {
                                const isCompleted = stage.isCompleted;
                                const isCurrent = stage.isCurrent;
                                const isLocked = stage.isLocked;
                                
                                return (
                                    <div key={stage.stage} className="space-y-1">
                                        <div className="flex items-center justify-between text-sm">
                                            <div className="flex items-center gap-2">
                                                {isCompleted ? (
                                                    <CheckCircle className="w-4 h-4 text-green-500" />
                                                ) : isCurrent ? (
                                                    <Clock className="w-4 h-4 text-blue-500" />
                                                ) : isLocked ? (
                                                    <Lock className="w-4 h-4 text-gray-400" />
                                                ) : (
                                                    <Square className="w-4 h-4 text-gray-400" />
                                                )}
                                                <span className={`font-medium ${
                                                    isCompleted ? 'text-green-600 dark:text-green-400' :
                                                    isCurrent ? 'text-blue-600 dark:text-blue-400' :
                                                    'text-gray-500 dark:text-gray-400'
                                                }`}>
                                                    {STAGE_LABELS[stage.stage] || stage.label}
                                                </span>
                                            </div>
                                            {!isLocked && (
                                                <span className={`text-xs ${
                                                    isCompleted ? 'text-green-600 dark:text-green-400' :
                                                    isCurrent ? 'text-blue-600 dark:text-blue-400' :
                                                    'text-gray-500 dark:text-gray-400'
                                                }`}>
                                                    {isCompleted ? `${stage.completedAttempts}/${stage.completedAttempts}` :
                                                        isCurrent ? `${stage.totalAttempts}/${stage.totalAttempts}+` :
                                                        '0/0'}
                                                </span>
                                            )}
                                        </div>
                                        {isCurrent && (
                                            <div className="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                                <div
                                                    className="bg-blue-600 h-1.5 rounded-full transition-all"
                                                    style={{ width: `${Math.min((currentScore / maxScore) * 100, 100)}%` }}
                                                />
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    </Card>

                    {/* <Card className="dark:bg-gray-800">
                        <h3 className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">
                            Time Summary
                        </h3>
                        <div className="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <div className="flex justify-between">
                                <span>Total:</span>
                                <span className="font-medium">10:00</span>
                            </div>
                            {stageProgress.map((stage) => (
                                <div key={stage.stage} className="flex justify-between">
                                    <span>{STAGE_LABELS[stage.stage] || stage.label}:</span>
                                    <span className="font-medium">
                                        {stage.isCurrent ? '01:00' : stage.isCompleted ? '02:00' : '00:00'}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </Card> */}
                    {/* Test Results */}
                    {testResults && (
                        <Card className="dark:bg-gray-800">
                            <h3 className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                Test Results
                            </h3>
                            <div className="mb-3">
                                <span className="text-sm font-medium text-gray-900 dark:text-white">
                                    {testResults.summary.passed} passed / {testResults.summary.failed} failed
                                </span>
                            </div>
                            <div className="space-y-2">
                                {testResults.cases.map((testCase, idx) => (
                                    <div
                                        key={idx}
                                        className={`p-2 rounded text-xs ${
                                            testCase.status === 'pass'
                                                ? 'bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200'
                                                : 'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-200'
                                        }`}
                                    >
                                        <div className="flex items-center gap-2 mb-1">
                                            {testCase.status === 'pass' ? (
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
                                        {testCase.status === 'fail' && (
                                            <div className="text-xs opacity-75 mt-1">
                                                Expected: {JSON.stringify(testCase.expected)}<br />
                                                Got: {JSON.stringify(testCase.got)}
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </Card>
                    )}

                    {/* Coach Feedback */}
                    {latestAttempt && (
                        <Card className="dark:bg-gray-800">
                            <h3 className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                Coach Feedback
                            </h3>
                            <div className="space-y-3">
                                <div>
                                    <span className="text-xs text-gray-500 dark:text-gray-400">Stage:</span>
                                    <Badge
                                        color={latestAttempt.rubric_scores?.passed ? 'green' : 'red'}
                                        className="ml-2"
                                    >
                                        {STAGE_LABELS[currentStage]}
                                    </Badge>
                                    <Badge
                                        color={latestAttempt.rubric_scores?.passed ? 'green' : 'red'}
                                        className="ml-2"
                                    >
                                        {latestAttempt.rubric_scores?.passed ? 'Passed' : 'Needs Work'}
                                    </Badge>
                                </div>
                                {latestAttempt.rubric_scores && (
                                    <div>
                                        <span className="text-xs text-gray-500 dark:text-gray-400 mb-1 block">
                                            Rubric Scores:
                                        </span>
                                        <div className="space-y-1 text-xs">
                                            {Object.entries(latestAttempt.rubric_scores).map(([key, value]) => {
                                                if (key === 'passed') return null;
                                                const score = typeof value === 'number' ? value : (value as any)?.score || 0;
                                                return (
                                                    <div key={key} className="flex justify-between">
                                                        <span className="text-gray-600 dark:text-gray-400">{key}:</span>
                                                        <span className="font-medium">{score}</span>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    </div>
                                )}
                                {latestAttempt.coach_msg && (
                                    <div>
                                        <p className="text-sm text-gray-700 dark:text-gray-300">
                                            {latestAttempt.coach_msg}
                                        </p>
                                    </div>
                                )}
                            </div>
                        </Card>
                    )}

                    {/* Attempt History */}
                    <Card className="dark:bg-gray-800">
                        <h3 className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                            Attempt History
                        </h3>
                        <div className="space-y-3 max-h-96 overflow-y-auto">
                            {attempts.length === 0 ? (
                                <p className="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                    No attempts yet
                                </p>
                            ) : (
                                attempts.reduce((acc: any[], attempt, idx) => {
                                    const prevAttempt = idx > 0 ? attempts[idx - 1] : null;
                                    const isNewStage = !prevAttempt || prevAttempt.stage !== attempt.stage;
                                    
                                    if (isNewStage) {
                                        acc.push(
                                            <div key={`stage-${attempt.stage}`} className="font-medium text-xs text-gray-500 dark:text-gray-400 mb-2">
                                                {STAGE_LABELS[attempt.stage] || attempt.stage}
                                            </div>
                                        );
                                    }
                                    
                                    const time = new Date(attempt.created_at).toLocaleTimeString('en-US', {
                                        hour: '2-digit',
                                        minute: '2-digit',
                                        second: '2-digit',
                                    });
                                    
                                    acc.push(
                                        <div
                                            key={attempt.id}
                                            className={`p-2 rounded text-xs ${
                                                attempt.passed
                                                    ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800'
                                                    : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800'
                                            }`}
                                        >
                                            <div className="flex items-center gap-2 mb-1">
                                                {attempt.passed ? (
                                                    <CheckCircle className="w-3 h-3 text-green-600 dark:text-green-400" />
                                                ) : (
                                                    <XCircle className="w-3 h-3 text-red-600 dark:text-red-400" />
                                                )}
                                                <span className="font-medium">
                                                    {attempt.passed ? 'Passed' : 'Failed'} attempt at {time}
                                                </span>
                                            </div>
                                            {attempt.coach_msg && (
                                                <p className="text-xs opacity-75 mt-1">
                                                    {attempt.coach_msg}
                                                </p>
                                            )}
                                            {attempt.rubric_scores && (
                                                <div className="text-xs opacity-75 mt-1">
                                                    Score: {calculateStageScore(attempt.rubric_scores, attempt.stage)}/{STAGE_MAX_SCORES[attempt.stage] || 0}
                                                </div>
                                            )}
                                        </div>
                                    );
                                    
                                    return acc;
                                }, [])
                            )}
                        </div>
                </Card>
                </div>
            </div>
        </DashboardLayout>
    );
}
