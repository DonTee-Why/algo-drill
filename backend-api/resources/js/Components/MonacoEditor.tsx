import React, { useEffect, useRef, useState } from 'react';
import * as monaco from 'monaco-editor';

// Configure Monaco loader for Vite
if (typeof window !== 'undefined' && !(window as any).MonacoEnvironment) {
    (window as any).MonacoEnvironment = {
        getWorkerUrl: function (_moduleId: string, label: string) {
            const isDev = (import.meta as any).env?.MODE === 'development' || (import.meta as any).env?.DEV;
            const base = isDev 
                ? '/node_modules/monaco-editor/esm/vs' 
                : '/assets';
            
            if (label === 'json') {
                return `${base}/language/json/json.worker.js`;
            }
            if (label === 'css' || label === 'scss' || label === 'less') {
                return `${base}/language/css/css.worker.js`;
            }
            if (label === 'html' || label === 'handlebars' || label === 'razor') {
                return `${base}/language/html/html.worker.js`;
            }
            if (label === 'typescript' || label === 'javascript') {
                return `${base}/language/typescript/ts.worker.js`;
            }
            return `${base}/editor/editor.worker.js`;
        },
    };
}

interface MonacoEditorProps {
    value: string;
    onChange: (value: string) => void;
    language: string;
    height?: string;
    theme?: 'light' | 'dark';
    readOnly?: boolean;
}

export default function MonacoEditor({
    value,
    onChange,
    language,
    height = '400px',
    theme = 'light',
    readOnly = false,
}: MonacoEditorProps) {
    const containerRef = useRef<HTMLDivElement>(null);
    const editorRef = useRef<monaco.editor.IStandaloneCodeEditor | null>(null);
    const onChangeRef = useRef(onChange);
    const [isDark, setIsDark] = useState(false);

    // Keep onChange ref up to date
    useEffect(() => {
        onChangeRef.current = onChange;
    }, [onChange]);

    useEffect(() => {
        // Check for dark mode
        const checkDarkMode = () => {
            const isDarkMode = document.documentElement.classList.contains('dark');
            setIsDark(isDarkMode);
        };

        checkDarkMode();

        // Watch for dark mode changes
        const observer = new MutationObserver(checkDarkMode);
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
        });

        return () => observer.disconnect();
    }, []);

    useEffect(() => {
        if (!containerRef.current) return;

        // Create editor instance
        const editor = monaco.editor.create(containerRef.current, {
            value,
            language: getMonacoLanguage(language),
            theme: isDark || theme === 'dark' ? 'vs-dark' : 'vs',
            automaticLayout: true,
            minimap: { enabled: false },
            scrollBeyondLastLine: false,
            fontSize: 14,
            lineNumbers: 'on',
            readOnly,
            wordWrap: 'on',
            tabSize: 4,
            insertSpaces: true,
            formatOnPaste: true,
            formatOnType: true,
        });

        editorRef.current = editor;

        // Handle value changes from editor
        const disposable = editor.onDidChangeModelContent(() => {
            const newValue = editor.getValue();
            onChangeRef.current(newValue);
        });

        return () => {
            disposable.dispose();
            editor.dispose();
        };
    }, [isDark, theme]);

    // Update readOnly property without recreating editor
    useEffect(() => {
        if (editorRef.current) {
            editorRef.current.updateOptions({ readOnly });
        }
    }, [readOnly]);

    useEffect(() => {
        if (editorRef.current && editorRef.current.getValue() !== value) {
            editorRef.current.setValue(value);
        }
    }, [value]);

    useEffect(() => {
        if (editorRef.current) {
            const model = editorRef.current.getModel();
            if (model) {
                monaco.editor.setModelLanguage(model, getMonacoLanguage(language));
            }
        }
    }, [language]);

    useEffect(() => {
        if (editorRef.current) {
            const currentTheme = isDark || theme === 'dark' ? 'vs-dark' : 'vs';
            monaco.editor.setTheme(currentTheme);
        }
    }, [isDark, theme]);

    function getMonacoLanguage(lang: string): string {
        const langMap: Record<string, string> = {
            javascript: 'javascript',
            python: 'python',
            php: 'php',
        };
        return langMap[lang.toLowerCase()] || 'plaintext';
    }

    return (
        <div
            ref={containerRef}
            style={{ height, width: '100%' }}
            className="border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden"
        />
    );
}

