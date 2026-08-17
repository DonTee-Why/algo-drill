import { Head, usePage } from '@inertiajs/react';
import React, { useEffect, useMemo, useState } from 'react';
import { Alert } from 'flowbite-react';
import { route } from 'ziggy-js';
import DashboardLayout from '../Layouts/DashboardLayout';
import ProblemCard from '../../Components/Sessions/ProblemCard';
import SessionHeader from '../../Components/Sessions/SessionHeader';
import CodeStageEditor from '../../Components/Sessions/CodeStageEditor';
import TextStageForm from '../../Components/Sessions/TextStageForm';
import ClarifyStageForm from '../../Components/Sessions/ClarifyStageForm';
import ApproachStageForm from '../../Components/Sessions/ApproachStageForm';
import OptimizeFields from '../../Components/Sessions/OptimizeFields';
import StageProgress from '../../Components/Sessions/StageProgress';
import TestResultsCard from '../../Components/Sessions/TestResultsCard';
import CoachFeedback from '../../Components/Sessions/CoachFeedback';
import AttemptHistory from '../../Components/Sessions/AttemptHistory';
import { STAGE_LABELS, STAGE_MAX_SCORES } from '../../Components/Sessions/constants';
import { useDraftManagement } from '../../Components/Sessions/hooks/useDraftManagement';
import { useSessionForm } from '../../Components/Sessions/hooks/useSessionForm';
import type { ShowProps } from '../../Components/Sessions/types';

function approachFieldsFromPayload(payload?: Record<string, any> | null): {
    strategy: string;
    justification: string;
    complexity: string;
} {
    return {
        strategy: payload?.strategy || payload?.text || '',
        justification: payload?.justification || '',
        complexity: payload?.complexity || '',
    };
}

export default function Show({
    auth,
    session,
    problem,
    stageProgress,
    testResults,
    latestAttempt,
    stageAttempts,
    attempts,
}: ShowProps) {
    const { flash } = usePage().props as { flash?: { success?: string; error?: string; message?: string } };
    const [showFlash, setShowFlash] = useState(true);
    const [viewingStage, setViewingStage] = useState<string | null>(null);
    const [selectedLang, setSelectedLang] = useState<string>(session.selected_lang || 'javascript');

    const displayedAttempt = useMemo(() => {
        return viewingStage && stageAttempts[viewingStage] ? stageAttempts[viewingStage] : latestAttempt;
    }, [latestAttempt, stageAttempts, viewingStage]);

    const [textInput, setTextInput] = useState<string>(
        displayedAttempt?.payload?.steps_text || displayedAttempt?.payload?.text || displayedAttempt?.payload?.user_input || '',
    );
    const [inputsOutputs, setInputsOutputs] = useState<string>(displayedAttempt?.payload?.inputs_outputs || '');
    const [constraints, setConstraints] = useState<string>(displayedAttempt?.payload?.constraints || '');
    const [examples, setExamples] = useState<string>(displayedAttempt?.payload?.examples || '');
    const initialApproach = approachFieldsFromPayload(displayedAttempt?.payload);
    const [strategy, setStrategy] = useState<string>(initialApproach.strategy);
    const [justification, setJustification] = useState<string>(initialApproach.justification);
    const [complexity, setComplexity] = useState<string>(initialApproach.complexity);

    const currentStage = session.state;
    const activeStage = viewingStage || currentStage;
    const isCodeStage = activeStage === 'BRUTE_FORCE' || activeStage === 'OPTIMIZE';
    const isTextStage = activeStage === 'CLARIFY' || activeStage === 'APPROACH' || activeStage === 'PSEUDOCODE';
    const isHybridStage = activeStage === 'OPTIMIZE';
    const isViewingPastStage = viewingStage !== null;

    useEffect(() => {
        if (flash && (flash.success || flash.error || flash.message)) {
            setShowFlash(true);
            const timer = setTimeout(() => setShowFlash(false), 5000);
            return () => clearTimeout(timer);
        }
        setShowFlash(false);
    }, [flash]);

    const {
        code,
        setCode,
        getInitialCode,
        saveDraftCheckpoint,
        handleLanguageChange: draftHandleLanguageChange,
        getDefaultCode,
    } = useDraftManagement({
        session,
        problem,
        displayedAttempt: displayedAttempt || null,
        selectedLang,
        isCodeStage,
    });

    const persistLanguage = (lang: string) => {
        setSelectedLang(lang);
        (window as any).axios
            .patch(route('sessions.updateLanguage', session.id), { lang }, { preserveScroll: true, preserveState: true })
            .catch(() => {});
    };

    const {
        data,
        setData,
        errors,
        processing,
        localTestResults,
        setLocalTestResults,
        isRunningTests,
        handleRunTests,
        handleSubmit,
    } = useSessionForm({
        session,
        code,
        selectedLang,
        textInput,
        inputsOutputs,
        constraints,
        examples,
        strategy,
        justification,
        complexity,
        isCodeStage,
        isTextStage,
        isHybridStage,
        initialComplexityAnalysis: latestAttempt?.payload?.complexityAnalysis || '',
        initialOptimizationTechnique: latestAttempt?.payload?.optimizationTechnique || '',
        initialTradeoffs: latestAttempt?.payload?.tradeoffs || '',
        testResults,
        onSubmitSuccess: () => {
            if (isTextStage) {
                if (currentStage === 'CLARIFY') {
                    setInputsOutputs('');
                    setConstraints('');
                    setExamples('');
                } else if (currentStage === 'APPROACH') {
                    setStrategy('');
                    setJustification('');
                    setComplexity('');
                } else {
                    setTextInput('');
                }
            }
            if (isCodeStage) {
                const sig = problem.signatures.find((s) => s.lang === selectedLang);
                setCode(getDefaultCode(sig));
            }
            if (isHybridStage) {
                setData('payload.complexityAnalysis', '');
                setData('payload.optimizationTechnique', '');
                setData('payload.tradeoffs', '');
            }
        },
    });

    useEffect(() => {
        const attempt = viewingStage && stageAttempts[viewingStage] ? stageAttempts[viewingStage] : latestAttempt;

        if (attempt) {
            if (isTextStage) {
                if (activeStage === 'CLARIFY') {
                    setInputsOutputs(attempt.payload?.inputs_outputs || '');
                    setConstraints(attempt.payload?.constraints || '');
                    setExamples(attempt.payload?.examples || '');
                } else if (activeStage === 'APPROACH') {
                    const fields = approachFieldsFromPayload(attempt.payload);
                    setStrategy(fields.strategy);
                    setJustification(fields.justification);
                    setComplexity(fields.complexity);
                } else {
                    setTextInput(attempt.payload?.steps_text || attempt.payload?.text || attempt.payload?.user_input || '');
                }
            }
            if (isCodeStage) {
                const attemptCode = attempt.payload?.code;
                const attemptLang = attempt.payload?.lang;
                if (attemptCode && attemptLang === selectedLang) {
                    setCode(attemptCode);
                } else {
                    setCode(getInitialCode(selectedLang));
                }
            }
        } else {
            if (isTextStage) {
                if (activeStage === 'CLARIFY') {
                    setInputsOutputs('');
                    setConstraints('');
                    setExamples('');
                } else if (activeStage === 'APPROACH') {
                    setStrategy('');
                    setJustification('');
                    setComplexity('');
                } else {
                    setTextInput('');
                }
            }
            if (isCodeStage) {
                setCode(getInitialCode(selectedLang));
            }
        }
    }, [activeStage, getInitialCode, isCodeStage, isTextStage, latestAttempt, selectedLang, stageAttempts, viewingStage, setCode]);

    useEffect(() => {
        if (viewingStage === null) {
            const shouldReset = !latestAttempt || (latestAttempt && Object.keys(latestAttempt.payload || {}).length === 0);

            if (isTextStage && shouldReset) {
                if (currentStage === 'CLARIFY') {
                    setInputsOutputs('');
                    setConstraints('');
                    setExamples('');
                } else if (currentStage === 'APPROACH') {
                    setStrategy('');
                    setJustification('');
                    setComplexity('');
                } else {
                    setTextInput('');
                }
            }
            if (isCodeStage && shouldReset) {
                setCode(getInitialCode(selectedLang));
            }
            if (isHybridStage && shouldReset) {
                setData('payload.complexityAnalysis', '');
                setData('payload.optimizationTechnique', '');
                setData('payload.tradeoffs', '');
            }
        }
    }, [currentStage, getInitialCode, isCodeStage, isHybridStage, isTextStage, latestAttempt, selectedLang, setCode, setData, viewingStage]);

    useEffect(() => {
        setData('payload.code', code);
        setData('payload.lang', selectedLang);
        setData('payload.text', textInput);
        setData('payload.inputs_outputs', inputsOutputs);
        setData('payload.constraints', constraints);
        setData('payload.examples', examples);
        setData('payload.strategy', strategy);
        setData('payload.justification', justification);
        setData('payload.complexity', complexity);
        setData('stage', session.state);
    }, [code, complexity, constraints, examples, inputsOutputs, justification, selectedLang, session.state, setData, strategy, textInput]);

    const onRunTests = async () => {
        saveDraftCheckpoint();
        await handleRunTests();
    };

    const onLanguageChange = (newLang: string) => {
        draftHandleLanguageChange(newLang, persistLanguage);
    };

    const getDifficultyColor = (difficulty: string): string => {
        const colors: Record<string, string> = {
            Easy: 'green',
            Medium: 'yellow',
            Hard: 'red',
        };
        return colors[difficulty] || 'gray';
    };

    const calculateStageScore = (rubricScores: any, stage: string): number => {
        if (!rubricScores) return 0;
        const maxScore = STAGE_MAX_SCORES[stage] || 0;
        const totalScore = Object.values(rubricScores).reduce((sum: number, score: any) => {
            if (typeof score === 'number') return sum + score;
            if (score?.score) return sum + score.score;
            return sum;
        }, 0);
        return Math.min(totalScore, maxScore);
    };

    const currentScore = displayedAttempt ? calculateStageScore(displayedAttempt.rubric_scores, activeStage) : 0;
    const maxScore = STAGE_MAX_SCORES[activeStage] || 0;

    return (
        <DashboardLayout auth={auth}>
            <Head title={`${problem.title} - Session`} />
            <div className="h-[calc(100vh-4rem)] flex gap-4 p-4 bg-gray-50 dark:bg-gray-900">
                {showFlash && flash && (flash.success || flash.error || flash.message) && (
                    <div className="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-2xl px-4">
                        {flash.success && (
                            <Alert color="success" className="mb-4" onDismiss={() => setShowFlash(false)}>
                                {flash.success}
                            </Alert>
                        )}
                        {flash.error && (
                            <Alert color="failure" className="mb-4" onDismiss={() => setShowFlash(false)}>
                                {flash.error}
                            </Alert>
                        )}
                        {flash.message && (
                            <Alert color="info" className="mb-4" onDismiss={() => setShowFlash(false)}>
                                {flash.message}
                            </Alert>
                        )}
                    </div>
                )}

                <div className="w-3/10 shrink-0 overflow-y-auto space-y-4">
                    <ProblemCard problem={problem} difficultyColor={getDifficultyColor} />
                </div>

                <div className="flex-1 flex flex-col overflow-hidden mb-4 h-fit">
                    <SessionHeader
                        viewingStage={viewingStage}
                        currentStage={currentStage}
                        sessionId={session.id}
                        stageLabels={STAGE_LABELS}
                        onBackToCurrent={() => setViewingStage(null)}
                    />

                    <div className="flex-1 flex flex-col space-y-4 overflow-hidden">
                        {(errors.stage || errors.payload || Object.keys(errors).length > 0) && (
                            <Alert color="failure" className="mb-4">
                                <div className="space-y-1">
                                    {errors.stage && <div>Stage: {errors.stage}</div>}
                                    {errors.payload && <div>Payload: {errors.payload}</div>}
                                    {errors['payload.code'] && <div>Code: {errors['payload.code']}</div>}
                                    {errors['payload.lang'] && <div>Language: {errors['payload.lang']}</div>}
                                    {errors['payload.text'] && <div>Text: {errors['payload.text']}</div>}
                                    {errors['payload.steps_text'] && <div>Pseudocode: {errors['payload.steps_text']}</div>}
                                    {errors['payload.inputs_outputs'] && <div>Inputs & Outputs: {errors['payload.inputs_outputs']}</div>}
                                    {errors['payload.constraints'] && <div>Constraints: {errors['payload.constraints']}</div>}
                                    {errors['payload.examples'] && <div>Examples: {errors['payload.examples']}</div>}
                                    {errors['payload.strategy'] && <div>Strategy: {errors['payload.strategy']}</div>}
                                    {errors['payload.justification'] && <div>Why it works: {errors['payload.justification']}</div>}
                                    {errors['payload.complexity'] && <div>Complexity: {errors['payload.complexity']}</div>}
                                    {errors['payload.complexityAnalysis'] && <div>Complexity Analysis: {errors['payload.complexityAnalysis']}</div>}
                                    {errors['payload.optimizationTechnique'] && <div>Optimization Technique: {errors['payload.optimizationTechnique']}</div>}
                                    {errors['payload.tradeoffs'] && <div>Tradeoffs: {errors['payload.tradeoffs']}</div>}
                                </div>
                            </Alert>
                        )}

                        {isCodeStage && (
                            <CodeStageEditor
                                code={code}
                                onChangeCode={setCode}
                                selectedLang={selectedLang}
                                signatures={problem.signatures}
                                onLanguageChange={onLanguageChange}
                                isViewingPastStage={isViewingPastStage}
                                onRunTests={onRunTests}
                                onSubmit={handleSubmit}
                                isRunningTests={isRunningTests}
                                processing={processing}
                                currentStageLabel={STAGE_LABELS[currentStage] || currentStage}
                            />
                        )}

                        {isHybridStage && (
                            <OptimizeFields
                                complexityAnalysis={displayedAttempt?.payload?.complexityAnalysis || data.payload.complexityAnalysis}
                                optimizationTechnique={displayedAttempt?.payload?.optimizationTechnique || data.payload.optimizationTechnique}
                                tradeoffs={displayedAttempt?.payload?.tradeoffs || data.payload.tradeoffs}
                                setComplexityAnalysis={(value) => setData('payload.complexityAnalysis', value)}
                                setOptimizationTechnique={(value) => setData('payload.optimizationTechnique', value)}
                                setTradeoffs={(value) => setData('payload.tradeoffs', value)}
                                readOnly={isViewingPastStage}
                            />
                        )}

                        {isTextStage && (
                            <>
                                {activeStage === 'CLARIFY' ? (
                                    <ClarifyStageForm
                                        inputsOutputs={inputsOutputs}
                                        constraints={constraints}
                                        examples={examples}
                                        setInputsOutputs={setInputsOutputs}
                                        setConstraints={setConstraints}
                                        setExamples={setExamples}
                                        readOnly={isViewingPastStage}
                                        onSubmit={handleSubmit}
                                        processing={processing}
                                        stageLabel={STAGE_LABELS[currentStage] || currentStage}
                                    />
                                ) : activeStage === 'APPROACH' ? (
                                    <ApproachStageForm
                                        strategy={strategy}
                                        justification={justification}
                                        complexity={complexity}
                                        setStrategy={setStrategy}
                                        setJustification={setJustification}
                                        setComplexity={setComplexity}
                                        readOnly={isViewingPastStage}
                                        onSubmit={handleSubmit}
                                        processing={processing}
                                        stageLabel={STAGE_LABELS[currentStage] || currentStage}
                                    />
                                ) : (
                                    <TextStageForm
                                        activeStage={activeStage}
                                        textValue={textInput}
                                        onChange={setTextInput}
                                        readOnly={isViewingPastStage}
                                        onSubmit={handleSubmit}
                                        processing={processing}
                                        stageLabel={STAGE_LABELS[currentStage] || currentStage}
                                    />
                                )}
                            </>
                        )}
                    </div>
                </div>

                <div className="w-80 shrink-0 scrollbar-none overflow-y-auto space-y-4">
                    <StageProgress
                        stageProgress={stageProgress}
                        viewingStage={viewingStage}
                        onSelectStage={setViewingStage}
                        currentScore={currentScore}
                        maxScore={maxScore}
                        stageLabels={STAGE_LABELS}
                        currentStage={currentStage}
                        activeStage={activeStage}
                    />

                    <TestResultsCard results={localTestResults || testResults} />

                    <CoachFeedback
                        displayedAttempt={displayedAttempt || null}
                        activeStage={activeStage}
                        stageLabels={STAGE_LABELS}
                    />

                    <AttemptHistory
                        attempts={attempts}
                        stageLabels={STAGE_LABELS}
                        calculateStageScore={calculateStageScore}
                        stageMaxScores={STAGE_MAX_SCORES}
                    />
                </div>
            </div>
        </DashboardLayout>
    );
}

