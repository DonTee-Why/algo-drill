import type { DifficultyFilter } from '../../types/problem';
import type { ProgressSnapshot } from './types';

export const DIFFICULTY_FILTERS: readonly DifficultyFilter[] = ['All', 'Easy', 'Medium', 'Hard'];

export const DIFFICULTY_BADGE_COLORS: Record<string, string> = {
    Easy: 'success',
    Medium: 'warning',
    Hard: 'failure',
};

/** Placeholder until progress is loaded from the backend. */
export const PLACEHOLDER_PROGRESS: ProgressSnapshot = {
    completed: 5,
    total: 12,
    avgScore: 2.4,
    timeToBF: 183,
    hintsUsed: 3,
};
