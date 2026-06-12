import { useCallback, useEffect, useState } from 'react';
import axios from 'axios';
import { route } from 'ziggy-js';
import type { DifficultyFilter, ProblemListItem } from '../../../types/problem';
import type { UseProblemExplorerResult } from '../types';

export function useProblemExplorer(): UseProblemExplorerResult {
    const [isOpen, setIsOpen] = useState(false);
    const [selectedDifficulty, setSelectedDifficulty] = useState<DifficultyFilter>('All');
    const [problems, setProblems] = useState<ProblemListItem[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const fetchProblems = useCallback(async () => {
        setLoading(true);
        setError(null);

        try {
            const params: Record<string, string> = {};

            if (selectedDifficulty !== 'All') {
                params.difficulty = selectedDifficulty;
            }

            const response = await axios.get(route('api.problems.fetch'), { params });
            setProblems(response.data.data || []);
        } catch (err) {
            setError('Failed to load problems. Please try again.');
            console.error('Error fetching problems:', err);
        } finally {
            setLoading(false);
        }
    }, [selectedDifficulty]);

    useEffect(() => {
        if (isOpen) {
            fetchProblems();
        }
    }, [isOpen, fetchProblems]);

    return {
        isOpen,
        open: () => setIsOpen(true),
        close: () => setIsOpen(false),
        selectedDifficulty,
        setSelectedDifficulty,
        problems,
        loading,
        error,
        retry: fetchProblems,
    };
}
