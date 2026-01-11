import { useEffect, useState, useCallback } from 'react';
import { useForm, router } from '@inertiajs/react';
import { route } from 'ziggy-js';
import type { Session, TestResults } from '../types';

interface UseSessionFormOptions {
    session: Session;
    code: string;
    selectedLang: string;
    textInput: string;
    inputsOutputs: string;
    constraints: string;
    examples: string;
    isCodeStage: boolean;
    isTextStage: boolean;
    isHybridStage: boolean;
    initialComplexityAnalysis?: string;
    initialOptimizationTechnique?: string;
    initialTradeoffs?: string;
    testResults: TestResults | null;
    onSubmitSuccess: () => void;
}

interface UseSessionFormResult {
    data: any;
    setData: (field: string, value: any) => void;
    errors: Record<string, string>;
    processing: boolean;
    localTestResults: TestResults | null;
    setLocalTestResults: (value: TestResults | null) => void;
    isRunningTests: boolean;
    handleRunTests: () => Promise<void>;
    handleSubmit: () => void;
}

export function useSessionForm({
    session,
    code,
    selectedLang,
    textInput,
    inputsOutputs,
    constraints,
    examples,
    isCodeStage,
    isTextStage,
    isHybridStage,
    initialComplexityAnalysis,
    initialOptimizationTechnique,
    initialTradeoffs,
    testResults,
    onSubmitSuccess,
}: UseSessionFormOptions): UseSessionFormResult {
    const [localTestResults, setLocalTestResults] = useState<TestResults | null>(testResults);
    const [isRunningTests, setIsRunningTests] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        stage: session.state,
        payload: {
            code: code,
            lang: selectedLang,
            text: textInput,
            inputs_outputs: inputsOutputs,
            constraints: constraints,
            examples: examples,
            complexityAnalysis: initialComplexityAnalysis || '',
            optimizationTechnique: initialOptimizationTechnique || '',
            tradeoffs: initialTradeoffs || '',
        },
    });

    useEffect(() => {
        if (testResults) {
            setLocalTestResults(testResults);
        }
    }, [testResults]);

    useEffect(() => {
        setData('payload.code', code);
        setData('payload.lang', selectedLang);
        setData('payload.text', textInput);
        setData('payload.inputs_outputs', inputsOutputs);
        setData('payload.constraints', constraints);
        setData('payload.examples', examples);
        setData('stage', session.state);
    }, [code, selectedLang, textInput, inputsOutputs, constraints, examples, session.state, setData]);

    const buildPayload = useCallback((): Record<string, any> => {
        const payload: Record<string, any> = {};

        if (isTextStage) {
            if (session.state === 'CLARIFY') {
                payload.inputs_outputs = inputsOutputs;
                payload.constraints = constraints;
                payload.examples = examples;
            } else {
                payload.text = textInput;
            }
        }

        if (isCodeStage || isHybridStage) {
            payload.code = code;
            payload.lang = selectedLang;
        }

        if (isHybridStage) {
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
    }, [code, constraints, data.payload.complexityAnalysis, data.payload.optimizationTechnique, data.payload.tradeoffs, examples, inputsOutputs, isCodeStage, isHybridStage, isTextStage, selectedLang, session.state, textInput]);

    const handleRunTests = useCallback(async () => {
        if (!isCodeStage || !code.trim()) {
            return;
        }

        setIsRunningTests(true);

        try {
            const response = await (window as any).axios.post(route('sessions.runTests', session.id), {
                code: code,
                lang: selectedLang,
            });

            const responseData = response.data;

            if (responseData.success && responseData.data) {
                setLocalTestResults({
                    summary: {
                        passed: responseData.data.tests.summary.passed || 0,
                        failed: responseData.data.tests.summary.failed || 0,
                        total: responseData.data.tests.summary.total || 0,
                    },
                    cases: responseData.data.tests.cases || [],
                });
            } else {
                setLocalTestResults({
                    summary: {
                        passed: 0,
                        failed: 0,
                        total: 0,
                    },
                    cases: [],
                });
            }
        } catch (error: any) {
            let errorMessage = 'Failed to run tests. Please try again.';

            if (error.response?.status === 429) {
                errorMessage = 'Too many requests. Please wait a moment before trying again.';
            } else if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            }

            setLocalTestResults({
                summary: {
                    passed: 0,
                    failed: 0,
                    total: 0,
                },
                cases: [],
                error: errorMessage,
            });
        } finally {
            setIsRunningTests(false);
        }
    }, [code, isCodeStage, selectedLang, session.id]);

    const handleSubmit = useCallback(() => {
        const payload = buildPayload();

        router.post(
            route('sessions.submit', session.id),
            {
                stage: session.state,
                payload: payload,
            },
            {
                preserveScroll: true,
                onSuccess: onSubmitSuccess,
            },
        );
    }, [buildPayload, onSubmitSuccess, session.id, session.state]);

    return {
        data,
        setData,
        errors,
        processing,
        localTestResults,
        setLocalTestResults,
        isRunningTests,
        handleRunTests,
        handleSubmit,
    };
}

