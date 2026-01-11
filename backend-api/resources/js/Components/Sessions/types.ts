export interface AuthUser {
    id: number;
    name: string;
    email: string;
}

export interface Session {
    id: string;
    state: string;
    selected_lang: string;
    created_at: string;
    code_drafts?: Record<string, Record<string, string>> | null;
}

export interface ProblemSignature {
    lang: string;
    function_name: string;
    params: Array<{ name: string; type?: string }>;
    returns: Array<{ type?: string }>;
}

export interface Problem {
    id: string;
    title: string;
    difficulty: string;
    tags: string[];
    constraints: string[];
    description_md: string;
    signatures: ProblemSignature[];
}

export interface StageProgressItem {
    stage: string;
    label: string;
    isCurrent: boolean;
    isLocked: boolean;
    isCompleted: boolean;
    totalAttempts: number;
    completedAttempts: number;
}

export interface TestCase {
    id?: number;
    status: string;
    timeMs?: number;
    got?: any;
    expected?: any;
    input?: any;
    error?: string;
}

export interface TestResults {
    summary: {
        passed: number;
        failed: number;
        total: number;
    };
    cases: TestCase[];
    error?: string;
}

export interface Attempt {
    id?: string;
    stage: string;
    coach_msg: string | null;
    rubric_scores: any;
    passed?: boolean;
    payload?: any;
    created_at: string;
}

export interface ShowProps {
    auth?: {
        user?: AuthUser | null;
    };
    session: Session;
    problem: Problem;
    stageProgress: StageProgressItem[];
    testResults: TestResults | null;
    latestAttempt: Attempt | null;
    stageAttempts: Record<string, Attempt>;
    attempts: Attempt[];
}

