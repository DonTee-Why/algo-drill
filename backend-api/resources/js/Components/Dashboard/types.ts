import type { AuthProps, AuthUser } from '../../types/auth';
import type { DifficultyFilter, ProblemListItem } from '../../types/problem';

export interface ActiveSession {
    id: string;
    state: string;
    updated_at: string;
    problem: {
        id: string;
        title: string;
        slug: string;
        difficulty: string;
    };
}

export interface ProgressSnapshot {
    completed: number;
    total: number;
    avgScore: number;
    timeToBF: number;
    hintsUsed: number;
}

export interface DashboardProps extends AuthProps {
    activeSessions: ActiveSession[];
}

export interface WelcomeHeaderProps {
    user?: AuthUser | null;
    lastActiveSession: ActiveSession | null;
    onStartNew: () => void;
}

export interface ContinueSessionCardProps {
    activeSessions: ActiveSession[];
    onStartNew: () => void;
}

export interface ProgressCardProps {
    progress: ProgressSnapshot;
}

export interface StartProblemCardProps {
    onBrowse: () => void;
}

export interface ProblemExplorerModalProps {
    isOpen: boolean;
    onClose: () => void;
    selectedDifficulty: DifficultyFilter;
    onDifficultyChange: (difficulty: DifficultyFilter) => void;
    problems: ProblemListItem[];
    loading: boolean;
    error: string | null;
    onRetry: () => void;
}

export interface UseProblemExplorerResult {
    isOpen: boolean;
    open: () => void;
    close: () => void;
    selectedDifficulty: DifficultyFilter;
    setSelectedDifficulty: (difficulty: DifficultyFilter) => void;
    problems: ProblemListItem[];
    loading: boolean;
    error: string | null;
    retry: () => void;
}
