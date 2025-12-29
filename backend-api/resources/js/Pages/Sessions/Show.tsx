import { Head, router, useForm, usePage } from '@inertiajs/react';
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
        selected_lang: string;
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
    stageAttempts: Record<string, {
        payload: any;
        coach_msg: string | null;
        rubric_scores: any;
        created_at: string;
    }>;
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
    stageAttempts,
    attempts,
}: Props) {
    const { flash } = usePage().props as { flash?: { success?: string; error?: string; message?: string } };
    const [showFlash, setShowFlash] = useState(true);
    
    // Track which stage is being viewed (null means viewing current stage)
    const [viewingStage, setViewingStage] = useState<string | null>(null);
    
    // Auto-dismiss flash messages after 5 seconds
    useEffect(() => {
        if (flash && (flash.success || flash.error || flash.message)) {
            setShowFlash(true);
            const timer = setTimeout(() => {
                setShowFlash(false);
            }, 5000);
            
            return () => clearTimeout(timer);
        } else {
            setShowFlash(false);
        }
    }, [flash]);
    
    const [selectedLang, setSelectedLang] = useState<string>(
        session.selected_lang || 'javascript'
    );
    
    // Determine which attempt to display based on viewingStage
    const displayedAttempt = viewingStage && stageAttempts[viewingStage] 
        ? stageAttempts[viewingStage] 
        : latestAttempt;
    
    const [code, setCode] = useState<string>(
        displayedAttempt?.payload?.code || getDefaultCode(problem.signatures.find(s => s.lang === selectedLang))
    );
    const [textInput, setTextInput] = useState<string>(
        displayedAttempt?.payload?.text || displayedAttempt?.payload?.user_input || ''
    );

    const currentSignature = problem.signatures.find(s => s.lang === selectedLang);
    const currentStage = session.state;
    const activeStage = viewingStage || currentStage;
    const isCodeStage = activeStage === 'BRUTE_FORCE' || activeStage === 'OPTIMIZE';
    const isTextStage = activeStage === 'CLARIFY' || activeStage === 'APPROACH' || activeStage === 'PSEUDOCODE';
    const isHybridStage = activeStage === 'OPTIMIZE';
    const isViewingPastStage = viewingStage !== null;

    const { data, setData, post, processing, errors } = useForm({
        stage: session.state,
        payload: {
            code: code,
            lang: selectedLang,
            text: textInput,
            complexityAnalysis: latestAttempt?.payload?.complexityAnalysis || '',
            optimizationTechnique: latestAttempt?.payload?.optimizationTechnique || '',
            tradeoffs: latestAttempt?.payload?.tradeoffs || '',
        },
    });

    useEffect(() => {
        setData('payload.code', code);
        setData('payload.lang', selectedLang);
        setData('payload.text', textInput);
        setData('stage', session.state);
    }, [code, selectedLang, textInput, session.state]);

    // Update displayed content when viewingStage changes
    useEffect(() => {
        const attempt = viewingStage && stageAttempts[viewingStage] 
            ? stageAttempts[viewingStage] 
            : latestAttempt;
        
        if (attempt) {
            if (isTextStage) {
                setTextInput(attempt.payload?.text || attempt.payload?.user_input || '');
            }
            if (isCodeStage) {
                setCode(attempt.payload?.code || getDefaultCode(problem.signatures.find(s => s.lang === selectedLang)));
            }
        } else {
            // Reset inputs if no attempt
            if (isTextStage) {
                setTextInput('');
            }
            if (isCodeStage) {
                const sig = problem.signatures.find(s => s.lang === selectedLang);
                setCode(getDefaultCode(sig));
            }
        }
    }, [viewingStage, stageAttempts, latestAttempt, isTextStage, isCodeStage, selectedLang, problem.signatures]);

    // Reset inputs when session state changes (after moving to next stage)
    useEffect(() => {
        // Only reset if we're viewing the current stage
        if (viewingStage === null) {
            // If latestAttempt is null or doesn't match current stage, reset inputs
            const shouldReset = !latestAttempt || (latestAttempt && Object.keys(latestAttempt.payload || {}).length === 0);
            
            if (isTextStage && shouldReset) {
                setTextInput('');
            }
            if (isCodeStage && shouldReset) {
                const sig = problem.signatures.find(s => s.lang === selectedLang);
                setCode(getDefaultCode(sig));
            }
            if (isHybridStage && shouldReset) {
                setData('payload.complexityAnalysis', '');
                setData('payload.optimizationTechnique', '');
                setData('payload.tradeoffs', '');
            }
        }
    }, [session.state, latestAttempt, viewingStage]);

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

    function buildPayload(): Record<string, any> {
        const payload: Record<string, any> = {};
        
        if (isTextStage) {
            payload.text = textInput;
        }
        
        if (isCodeStage) {
            payload.code = code;
            payload.lang = selectedLang;
        }
        
        if (isHybridStage) {
            payload.code = code;
            payload.lang = selectedLang;
            if (data.payload.complexityAnalysis) {
                payload.complexityAnalysis = data.payload.complexityAnalysis;
            }
            if (data.payload.optimizationTechnique) {
                payload.optimizationTechnique = data.payload.optimizationTechnique;
            }
            if (data.payload.tradeoffs) {
                payload.tradeoffs = data.payload.tradeoffs;
            }
        }
        
        return payload;
    }

    function handleRunTests() {
        // Build payload with only fields needed for current stage
        const payload = buildPayload();
        
        router.post(route('sessions.submit', session.id), {
            stage: session.state,
            payload: payload,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Test results will be updated via Inertia
            },
        });
    }

    function handleSubmit() {
        const payload = buildPayload();
        
        router.post(route('sessions.submit', session.id), {
            stage: session.state,
            payload: payload,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Reset inputs after successful submission
                if (isTextStage) {
                    setTextInput('');
                }
                if (isCodeStage) {
                    const sig = problem.signatures.find(s => s.lang === selectedLang);
                    setCode(getDefaultCode(sig));
                }
                if (isHybridStage) {
                    setData('payload.complexityAnalysis', '');
                    setData('payload.optimizationTechnique', '');
                    setData('payload.tradeoffs', '');
                }
            },
        });
    }

    function handleLanguageChange(newLang: string) {
        setSelectedLang(newLang);
        const sig = problem.signatures.find(s => s.lang === newLang);
        if (sig) {
            setCode(getDefaultCode(sig));
        }
        
        // Persist language change to backend
        router.patch(route('sessions.updateLanguage', session.id), {
            lang: newLang,
        }, {
            preserveScroll: true,
            preserveState: true,
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
    const maxScore = STAGE_MAX_SCORES[activeStage] || 0;
    const currentScore = displayedAttempt ? calculateStageScore(displayedAttempt.rubric_scores, activeStage) : 0;

    return (
        <DashboardLayout auth={auth}>
            <Head title={`${problem.title} - Session`} />
            <div className="h-[calc(100vh-4rem)] flex gap-4 p-4 bg-gray-50 dark:bg-gray-900">
                {/* Flash Messages */}
                {showFlash && flash && (flash.success || flash.error || flash.message) && (
                    <div className="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-2xl px-4">
                        {flash.success && (
                            <Alert 
                                color="success" 
                                className="mb-4" 
                                onDismiss={() => setShowFlash(false)}
                            >
                                {flash.success}
                            </Alert>
                        )}
                        {flash.error && (
                            <Alert 
                                color="failure" 
                                className="mb-4"
                                onDismiss={() => setShowFlash(false)}
                            >
                                {flash.error}
                            </Alert>
                        )}
                        {flash.message && (
                            <Alert 
                                color="info" 
                                className="mb-4"
                                onDismiss={() => setShowFlash(false)}
                            >
                                {flash.message}
                            </Alert>
                        )}
                    </div>
                )}
                
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
                                        {isViewingPastStage ? (
                                            <>
                                                Viewing: {STAGE_LABELS[viewingStage!] || viewingStage} | 
                                                Current: {STAGE_LABELS[currentStage] || currentStage} | 
                                                Session ID: {session.id.slice(0, 13)}
                                            </>
                                        ) : (
                                            <>
                                                Stage: {STAGE_LABELS[currentStage] || currentStage} | Session ID: {session.id.slice(0, 13)}
                                            </>
                                        )}
                                    </p>
                                </div>
                                {isViewingPastStage && (
                                    <Button
                                        onClick={() => setViewingStage(null)}
                                        color="gray"
                                        size="xs"
                                    >
                                        Back to Current Stage
                                    </Button>
                                )}
                            </div>
                            <p className="text-xs text-gray-500 dark:text-gray-400 mb-4">
                                {isViewingPastStage 
                                    ? 'You are viewing a past stage. Click "Back to Current Stage" to return to editing.'
                                    : 'Only the current stage is editable. Click on completed stages to view your past submissions.'}
                            </p>
                        </div>

                        <div className="flex-1 flex flex-col space-y-4 overflow-hidden">
                            {/* Validation Errors */}
                            {(errors.stage || errors.payload || Object.keys(errors).length > 0) && (
                                <Alert color="failure" className="mb-4">
                                    <div className="space-y-1">
                                        {errors.stage && <div>Stage: {errors.stage}</div>}
                                        {errors.payload && <div>Payload: {errors.payload}</div>}
                                        {errors['payload.code'] && <div>Code: {errors['payload.code']}</div>}
                                        {errors['payload.lang'] && <div>Language: {errors['payload.lang']}</div>}
                                        {errors['payload.text'] && <div>Text: {errors['payload.text']}</div>}
                                        {errors['payload.complexityAnalysis'] && <div>Complexity Analysis: {errors['payload.complexityAnalysis']}</div>}
                                        {errors['payload.optimizationTechnique'] && <div>Optimization Technique: {errors['payload.optimizationTechnique']}</div>}
                                        {errors['payload.tradeoffs'] && <div>Tradeoffs: {errors['payload.tradeoffs']}</div>}
                                    </div>
                                </Alert>
                            )}
                            
                            {isCodeStage && (
                                <>
                                    <div className="flex items-center gap-2">
                                        <Select
                                            value={selectedLang}
                                            onChange={(e) => handleLanguageChange(e.target.value)}
                                            className="w-40"
                                            disabled={isViewingPastStage}
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
                                            readOnly={isViewingPastStage}
                                        />
                                    </div>

                                    {!isViewingPastStage && (
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
                                    )}
                                </>
                            )}

                            {isHybridStage && (
                                <div className="space-y-3">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Complexity Analysis
                                        </label>
                                        <Textarea
                                            value={displayedAttempt?.payload?.complexityAnalysis || data.payload.complexityAnalysis}
                                            onChange={(e) => setData('payload.complexityAnalysis', e.target.value)}
                                            rows={2}
                                            placeholder="Time: O(n), Space: O(1)..."
                                            className="dark:bg-gray-900"
                                            readOnly={isViewingPastStage}
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Optimization Technique
                                        </label>
                                        <Textarea
                                            value={displayedAttempt?.payload?.optimizationTechnique || data.payload.optimizationTechnique}
                                            onChange={(e) => setData('payload.optimizationTechnique', e.target.value)}
                                            rows={2}
                                            placeholder="Used hash map to achieve O(n) lookup..."
                                            className="dark:bg-gray-900"
                                            readOnly={isViewingPastStage}
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Tradeoffs
                                        </label>
                                        <Textarea
                                            value={displayedAttempt?.payload?.tradeoffs || data.payload.tradeoffs}
                                            onChange={(e) => setData('payload.tradeoffs', e.target.value)}
                                            rows={2}
                                            placeholder="Trading space for time..."
                                            className="dark:bg-gray-900"
                                            readOnly={isViewingPastStage}
                                        />
                                    </div>
                                </div>
                            )}

                            {isTextStage && (
                                <>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {activeStage === 'CLARIFY' && 'Clarifications (inputs, outputs, constraints, examples)'}
                                            {activeStage === 'APPROACH' && 'High-level Approach & Strategy'}
                                            {activeStage === 'PSEUDOCODE' && 'Detailed Pseudocode'}
                                        </label>
                                        <Textarea
                                            value={textInput}
                                            onChange={(e) => setTextInput(e.target.value)}
                                            rows={activeStage === 'PSEUDOCODE' ? 25 : 12}
                                            placeholder="Enter your response here..."
                                            className="dark:bg-gray-900"
                                            readOnly={isViewingPastStage}
                                        />
                                    </div>
                                    {!isViewingPastStage && (
                                        <Button
                                            onClick={handleSubmit}
                                            disabled={processing}
                                            color="blue"
                                            className="w-full"
                                        >
                                            Submit {STAGE_LABELS[currentStage]}
                                        </Button>
                                    )}
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
                                const isViewing = viewingStage === stage.stage;
                                
                                return (
                                    <div key={stage.stage} className="space-y-1">
                                        <div 
                                            className={`flex items-center justify-between text-sm ${
                                                isCompleted ? 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 rounded p-1 -m-1' : ''
                                            }`}
                                            onClick={() => {
                                                if (isCompleted) {
                                                    setViewingStage(isViewing ? null : stage.stage);
                                                }
                                            }}
                                        >
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
                                                    isViewing ? 'text-green-500 dark:text-green-500' :
                                                    isCompleted ? 'text-green-400 dark:text-green-300' :
                                                    isCurrent ? 'text-blue-600 dark:text-blue-400' :
                                                    'text-gray-500 dark:text-gray-400'
                                                }`}>
                                                    {STAGE_LABELS[stage.stage] || stage.label}
                                                    {isViewing && ' (viewing)'}
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
                    {displayedAttempt && (
                        <Card className="dark:bg-gray-800">
                            <h3 className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                Coach Feedback
                                {isViewingPastStage && ` (${STAGE_LABELS[viewingStage!]})`}
                            </h3>
                            <div className="space-y-3">
                                <div>
                                    <span className="text-xs text-gray-500 dark:text-gray-400">Stage:</span>
                                    <Badge
                                        color={displayedAttempt.rubric_scores?.passed ? 'green' : 'red'}
                                        className="ml-2"
                                    >
                                        {STAGE_LABELS[activeStage]}
                                    </Badge>
                                    <Badge
                                        color={displayedAttempt.rubric_scores?.passed ? 'green' : 'red'}
                                        className="ml-2"
                                    >
                                        {displayedAttempt.rubric_scores?.passed ? 'Passed' : 'Needs Work'}
                                    </Badge>
                                </div>
                                {displayedAttempt.rubric_scores && (
                                    <div>
                                        <span className="text-xs text-gray-500 dark:text-gray-400 mb-1 block">
                                            Rubric Scores:
                                        </span>
                                        <div className="space-y-1 text-xs">
                                            {Object.entries(displayedAttempt.rubric_scores).map(([key, value]) => {
                                                if (key === 'passed') return null;
                                                const score = typeof value === 'number' ? value : (value as any)?.score || 0;
                                                return (
                                                    <div key={key} className="flex justify-between">
                                                        <span className="text-gray-600 dark:text-gray-400">{key}:</span>
                                                        <span className="font-medium text-gray-500 dark:text-gray-500">{score}</span>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    </div>
                                )}
                                {displayedAttempt.coach_msg && (
                                    <div>
                                        <p className="text-sm text-gray-700 dark:text-gray-300">
                                            {displayedAttempt.coach_msg}
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
                                                <span className="font-medium text-gray-500 dark:text-gray-400">
                                                    {attempt.passed ? 'Passed' : 'Failed'} attempt at {time}
                                                </span>
                                            </div>
                                            {attempt.coach_msg && (
                                                <p className="text-xs text-gray-500 dark:text-gray-400 opacity-75 mt-1">
                                                    {attempt.coach_msg}
                                                </p>
                                            )}
                                            {attempt.rubric_scores && (
                                                <div className="text-xs text-gray-500 dark:text-gray-400 opacity-75 mt-1">
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
