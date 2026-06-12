export type Difficulty = 'Easy' | 'Medium' | 'Hard';

export type DifficultyFilter = 'All' | Difficulty;

export interface ProblemListItem {
    id: string;
    title: string;
    slug: string;
    difficulty: Difficulty;
    tags: string[];
    is_premium: boolean;
    created_at: string;
}
