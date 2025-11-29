import { Head } from '@inertiajs/react';
import React, { useState, useEffect } from 'react';
import DashboardLayout from './Layouts/DashboardLayout';
import { Card, Progress, Button, Modal, ModalHeader, ModalBody, ModalFooter, Badge, Spinner } from 'flowbite-react';
import axios from 'axios';
import { route } from 'ziggy-js';

interface Props {
    auth?: {
        user?: {
            id: number;
            name: string;
            username?: string;
            email: string;
            preferred_languages?: string[];
        } | null;
    };
}

interface ProgressData {
    completed: number;
    total: number;
    avgScore: number;
    timeToBF: number;
    hintsUsed: number;
}

interface Session {
    id: number;
    problem: string;
    stage: string;
}

interface Problem {
    id: string;
    title: string;
    slug: string;
    difficulty: 'Easy' | 'Medium' | 'Hard';
    tags: string[];
    is_premium: boolean;
    created_at: string;
}

export default function Dashboard({ auth }: Props) {
    const [showProblemModal, setShowProblemModal] = useState(false);
    const [selectedDifficulty, setSelectedDifficulty] = useState<'All' | 'Easy' | 'Medium' | 'Hard'>('All');
    const [problems, setProblems] = useState<Problem[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    // Dummy data for progress and sessions
    const progress: ProgressData = { completed: 5, total: 12, avgScore: 2.4, timeToBF: 183, hintsUsed: 3 };
    const activeSessions: Session[] = [
        { id: 1, problem: 'Two Sum', stage: 'PSEUDOCODE' },
        { id: 2, problem: 'Valid Parentheses', stage: 'BRUTE_FORCE' },
    ];

    useEffect(() => {
        if (showProblemModal) {
            fetchProblems();
        }
    }, [showProblemModal, selectedDifficulty]);

    const fetchProblems = async () => {
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
    };

    const progressPercentage = (progress.completed / progress.total) * 100;
    const difficultyColors: Record<string, string> = {
        Easy: 'success',
        Medium: 'warning',
        Hard: 'failure',
    };

    return (
        <DashboardLayout auth={auth}>
            <Head title="Dashboard" />
            <div className="max-w-[1400px] mx-auto px-8 py-8">
                {/* Welcome Section */}
                <div className="flex items-start justify-between mb-6">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-3">
                            Welcome back, {auth?.user?.name || 'User'}!
                </h1>
                        <p className="text-gray-600 dark:text-gray-400">
                            You've got an active session in progress: {activeSessions[0]?.problem || 'Two Sum'}. You can resume or start something new.
                        </p>
                    </div>
                    <Button color="blue" onClick={() => setShowProblemModal(true)} className="cursor-pointer hover:bg-blue-600 hover:text-white">
                        Start new problem
                    </Button>
                </div>

                {/* Dashboard Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
                    {/* Continue where you left off Section */}
                    <Card className="rounded-xl shadow-md md:col-span-1">
                        <div className="space-y-6">
                            <div className="space-y-2">
                                <h5 className="text-xl font-bold text-gray-900 dark:text-white">Continue where you left off</h5>
                                <p className="text-sm text-gray-500 dark:text-gray-400">Jump back into your last active problem.</p>
                            </div>
                            <div className="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                                <p className="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-3">
                                    Last Active
                                </p>
                                <div className="flex items-center justify-between">
                                    <div>
                                        <p className="font-semibold text-gray-900 dark:text-white mb-1">{activeSessions[0].problem}</p>
                                        <p className="text-sm text-gray-600 dark:text-gray-400">Current stage: {activeSessions[0].stage}</p>
                                    </div>
                                    <Button size="md" color="blue" className="cursor-pointer hover:bg-blue-600 hover:text-white">
                                        {`Resume`}
                                    </Button>
                                </div>
                            </div>
                            
                            {activeSessions.length > 1 && (
                                <div>
                                    <p className="text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wide mb-3">
                                        Other Active Sessions
                                    </p>
                                    <div className="space-y-3">
                                        {activeSessions.slice(1).map((session) => (
                                            <div
                                                key={session.id}
                                                className="flex items-center justify-between border rounded-lg p-2 border-gray-200 dark:border-gray-700"
                                            >
                                                <div>
                                                    <p className="font-medium text-gray-900 dark:text-white">{session.problem}</p>
                                                    <p className="text-sm text-gray-600 dark:text-gray-400">Stage: {session.stage}</p>
                                                </div>
                                                <Button size="sm" color="light" className="text-blue-600 dark:text-blue-400 cursor-pointer">
                                                    Resume
                                                </Button>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    </Card>

                    {/* My Progress Section */}
                    <Card className="rounded-xl shadow-md md:col-span-1">
                        <div className="space-y-2">
                            <div className="flex items-center justify-between">
                                <h2 className="text-xl font-bold text-gray-900 dark:text-white">My Progress</h2>
                                <span className="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    Weekly snapshot
                                </span>
                            </div>
                            <div className="text-sm text-gray-500 dark:text-gray-400">
                                Track how consistently you&apos;re practicing.
                            </div>
                        </div>
                        <div className="space-y-5">
                            <div className="space-y-2">
                                <div className="flex justify-between">
                                    <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Problems completed
                                    </span>
                                    <span className="text-sm font-medium text-gray-900 dark:text-white">
                                        {`${progress.completed}/${progress.total}`}
                                    </span>
                                </div>
                                <Progress progress={progressPercentage} color="blue" size="md" />
                            </div>
                            <div className="grid grid-cols-3 gap-6">
                                <div className="text-center">
                                    <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">Avg stage score</p>
                                    <p className="text-xl font-bold text-gray-900 dark:text-white">
                                        {`${progress.avgScore.toFixed(1)}/3`}
                                    </p>
                                </div>
                                <div className="text-center">
                                    <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">Time to BF</p>
                                    <p className="text-xl font-bold text-gray-900 dark:text-white">
                                        {`${progress.timeToBF}s`}
                                    </p>
                                </div>
                                <div className="text-center">
                                    <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">Hints used</p>
                                    <p className="text-xl font-bold text-gray-900 dark:text-white">{progress.hintsUsed}</p>
                                </div>
                            </div>
                        </div>
                        <div className="mt-auto pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-between text-sm text-gray-600 dark:text-gray-400">
                            <span>Today: 1 problem, 3 stages cleared</span>
                            <span>2 hints used</span>
                        </div>
                    </Card>

                    {/* Start a new problem Section */}
                    <Card className="rounded-xl shadow-md col-span-1 md:col-span-2">
                        <div className="flex items-center justify-between gap-8">
                            <div className="space-y-2 flex-1">
                                <h5 className="text-xl font-bold text-gray-900 dark:text-white">Start a new problem</h5>
                                <p className="text-sm text-gray-500 dark:text-gray-400">
                                    Pick a problem and go through Clarify → Brute Force → Pseudocode → Code → Test → Optimize.
                                </p>
                            </div>
                            <Button color="blue" onClick={() => setShowProblemModal(true)} className="cursor-pointer hover:bg-blue-600 hover:text-white whitespace-nowrap">
                                Browse problems
                            </Button>
                        </div>
                    </Card>
                </div>

                {/* Problem Explorer Modal */}
                <Modal show={showProblemModal} onClose={() => setShowProblemModal(false)} size="2xl">
                    <ModalHeader className='border-b-0'>
                        <div>
                            <h3 className="text-2xl font-bold text-gray-900 dark:text-white">Problem Explorer</h3>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Choose a problem to begin a new guided session.
                            </p>
                        </div>
                    </ModalHeader>
                    <ModalBody>
                        <div className="space-y-6">
                            {/* Difficulty Filter Buttons */}
                            <div className="flex gap-2">
                                {(['All', 'Easy', 'Medium', 'Hard'] as const).map((difficulty) => (
                                    <Button
                                        key={difficulty}
                                        size="sm"
                                        color={selectedDifficulty === difficulty ? 'blue' : 'gray'}
                                        onClick={() => setSelectedDifficulty(difficulty)}
                                        className="cursor-pointer"
                                        disabled={loading}
                                    >
                                        {difficulty}
                                    </Button>
                                ))}
                            </div>

                            {/* Loading State */}
                            {loading && (
                                <div className="flex items-center justify-center py-8">
                                    <Spinner size="xl" />
                                </div>
                            )}

                            {/* Error State */}
                            {error && !loading && (
                                <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 text-center">
                                    <p className="text-red-600 dark:text-red-400">{error}</p>
                                    <Button
                                        size="sm"
                                        color="failure"
                                        onClick={fetchProblems}
                                        className="mt-2 cursor-pointer"
                                    >
                                        Retry
                                    </Button>
                                </div>
                            )}

                            {/* Problem List */}
                            {!loading && !error && (
                                <div className="space-y-4">
                                    {problems.length === 0 ? (
                                        <div className="text-center py-8 text-gray-500 dark:text-gray-400">
                                            No problems found. Try adjusting your filters.
                                        </div>
                                    ) : (
                                        problems.map((problem) => (
                                            <div
                                                key={problem.id}
                                                className="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700"
                                            >
                                                <div className="flex-1">
                                                    <div className="flex items-center gap-2 mb-1">
                                                        <h4 className="font-semibold text-gray-900 dark:text-white">
                                                            {problem.title}
                                                        </h4>
                                                        {problem.is_premium && (
                                                            <Badge color="warning" size="sm">
                                                                Premium
                                                            </Badge>
                                                        )}
                                                    </div>
                                                    <div className="flex items-center gap-2 mb-2">
                                                        <Badge color={difficultyColors[problem.difficulty] as any} size="sm">
                                                            {problem.difficulty}
                                                        </Badge>
                                                    </div>
                                                    <div className="flex gap-2 flex-wrap">
                                                        {problem.tags.slice(0, 3).map((tag) => (
                                                            <span
                                                                key={tag}
                                                                className="px-2 py-1 text-xs bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full"
                                                            >
                                                                {tag}
                                                            </span>
                                                        ))}
                                                        {problem.tags.length > 3 && (
                                                            <span className="px-2 py-1 text-xs text-gray-500 dark:text-gray-400">
                                                                +{problem.tags.length - 3} more
                                                            </span>
                                                        )}
                                                    </div>
                                                </div>
                                                <Button size="sm" color="blue" className="ml-4 whitespace-nowrap cursor-pointer hover:bg-blue-600">
                                                    Start
                                                </Button>
                                            </div>
                                        ))
                                    )}
                                </div>
                            )}
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <div className="flex justify-end w-full">
                            <Button color="gray" className='cursor-pointer border-gray-800 hover:bg-gray-900 hover:text-white' onClick={() => setShowProblemModal(false)}>
                                Close
                            </Button>
                        </div>
                    </ModalFooter>
                </Modal>
            </div>
        </DashboardLayout>
    );
}
