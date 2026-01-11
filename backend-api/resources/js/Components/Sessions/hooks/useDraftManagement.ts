import { useCallback, useEffect, useMemo, useState } from 'react';
import { route } from 'ziggy-js';
import type { Attempt, Problem, Session } from '../types';

interface UseDraftManagementOptions {
    session: Session;
    problem: Problem;
    displayedAttempt: Attempt | null;
    selectedLang: string;
    isCodeStage: boolean;
}

interface UseDraftManagementResult {
    code: string;
    setCode: (value: string) => void;
    getInitialCode: (lang: string) => string;
    saveDraftCheckpoint: () => void;
    handleLanguageChange: (newLang: string, persistLanguage: (lang: string) => void) => void;
    getDefaultCode: (signature: Problem['signatures'][0] | undefined) => string;
}

export function useDraftManagement({
    session,
    problem,
    displayedAttempt,
    selectedLang,
    isCodeStage,
}: UseDraftManagementOptions): UseDraftManagementResult {
    const getStorageKey = useCallback(
        (lang: string) => `algo-drill:code:${session.id}:${session.state}:${lang}`,
        [session.id, session.state],
    );

    const getLocalDraft = useCallback(
        (lang: string): string | null => {
            if (typeof window === 'undefined') return null;
            return localStorage.getItem(getStorageKey(lang));
        },
        [getStorageKey],
    );

    const saveLocalDraft = useCallback(
        (lang: string, codeValue: string) => {
            if (typeof window === 'undefined') return;
            localStorage.setItem(getStorageKey(lang), codeValue);
        },
        [getStorageKey],
    );

    const getBackendDraft = useCallback(
        (stage: string, lang: string): string | null => {
            return session.code_drafts?.[stage]?.[lang] || null;
        },
        [session.code_drafts],
    );

    const getDefaultCode = useCallback((signature: Problem['signatures'][0] | undefined): string => {
        if (!signature) return '';

        const params = signature.params.map((p: any) => p.name || 'param').join(', ');

        if (signature.lang === 'javascript') {
            return `function ${signature.function_name}(${params}) {\n    // Your code here\n    \n}`;
        } else if (signature.lang === 'python') {
            return `def ${signature.function_name}(${params}):\n    # Your code here\n    pass`;
        } else if (signature.lang === 'php') {
            return `function ${signature.function_name}(${params}) {\n    // Your code here\n    \n}`;
        }
        return '';
    }, []);

    const getInitialCode = useCallback(
        (lang: string): string => {
            const backendDraft = getBackendDraft(session.state, lang);
            if (backendDraft) return backendDraft;

            const localDraft = getLocalDraft(lang);
            if (localDraft) return localDraft;

            const attemptLang = displayedAttempt?.payload?.lang;
            if (displayedAttempt?.payload?.code && attemptLang === lang) {
                return displayedAttempt.payload.code;
            }

            return getDefaultCode(problem.signatures.find((s) => s.lang === lang));
        },
        [displayedAttempt, getBackendDraft, getDefaultCode, getLocalDraft, problem.signatures, session.state],
    );

    const [code, setCode] = useState<string>(() => getInitialCode(selectedLang));

    useEffect(() => {
        setCode(getInitialCode(selectedLang));
    }, [getInitialCode, selectedLang]);

    useEffect(() => {
        if (!isCodeStage) return;

        const timer = setTimeout(() => {
            saveLocalDraft(selectedLang, code);

            (window as any).axios
                .post(route('sessions.saveDraft', session.id), {
                    stage: session.state,
                    lang: selectedLang,
                    code: code,
                })
                .catch(() => {});
        }, 1000);

        return () => clearTimeout(timer);
    }, [code, isCodeStage, saveLocalDraft, selectedLang, session.id, session.state]);

    const saveDraftCheckpoint = useCallback(() => {
        if (!isCodeStage) return;
        saveLocalDraft(selectedLang, code);

        (window as any).axios
            .post(route('sessions.saveDraft', session.id), {
                stage: session.state,
                lang: selectedLang,
                code: code,
            })
            .catch(() => {});
    }, [code, isCodeStage, saveLocalDraft, selectedLang, session.id, session.state]);

    const handleLanguageChange = useCallback(
        (newLang: string, persistLanguage: (lang: string) => void) => {
            saveDraftCheckpoint();
            persistLanguage(newLang);
            setCode(getInitialCode(newLang));
        },
        [getInitialCode, saveDraftCheckpoint],
    );

    return {
        code,
        setCode,
        getInitialCode,
        saveDraftCheckpoint,
        handleLanguageChange,
        getDefaultCode,
    };
}

