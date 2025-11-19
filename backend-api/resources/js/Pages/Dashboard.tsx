import { Head } from '@inertiajs/react';
import React, { useState } from 'react';
import DashboardLayout from './Layouts/DashboardLayout';
import { Card, Progress, Button, Modal, ModalHeader, ModalBody, ModalFooter, Badge } from 'flowbite-react';

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
    id: number;
    title: string;
    difficulty: string;
}

export default function Dashboard({ auth }: Props) {
    const [showProblemModal, setShowProblemModal] = useState(false);

    // Dummy data
    const progress: ProgressData = { completed: 5, total: 12, avgScore: 2.4, timeToBF: 183, hintsUsed: 3 };
    const activeSessions: Session[] = [
        { id: 1, problem: 'Two Sum', stage: 'PSEUDOCODE' },
        { id: 2, problem: 'Valid Parentheses', stage: 'BRUTE_FORCE' },
    ];
    const problems: Problem[] = [
        { id: 3, title: 'Reverse Linked List', difficulty: 'Medium' },
        { id: 4, title: 'Longest Substring Without Repeating', difficulty: 'Hard' },
        { id: 5, title: 'Move Zeroes', difficulty: 'Easy' },
    ];

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
                    <Button color="blue" size="lg" onClick={() => setShowProblemModal(true)} className="cursor-pointer hover:bg-blue-700 hover:text-white">
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
                                <p className="text-sm text-gray-500">Jump back into your last active problem.</p>
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
                                        {`Resume at ${activeSessions[0].stage}`}
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
                            <div className="text-sm text-gray-500">
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
                                <p className="text-sm text-gray-500">
                                    Pick a problem and go through Clarify → Brute Force → Pseudocode → Code → Test → Optimize.
                                </p>
                            </div>
                            <Button color="blue" onClick={() => setShowProblemModal(true)} className="cursor-pointer hover:bg-blue-600 hover:text-white whitespace-nowrap">
                                Browse problems
                            </Button>
                        </div>
                    </Card>
                </div>

                {/* Problem Selection Modal */}
                <Modal show={showProblemModal} onClose={() => setShowProblemModal(false)} size="lg">
                    <ModalHeader>Select a Problem</ModalHeader>
                    <ModalBody>
                        <div className="space-y-3">
                            {problems.map((problem) => (
                                <div
                                    key={problem.id}
                                    className="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <div>
                                        <p className="font-medium text-gray-900 dark:text-white">{problem.title}</p>
                                        <Badge color={difficultyColors[problem.difficulty] as 'success' | 'warning' | 'failure'}>
                                            {problem.difficulty}
                                        </Badge>
                                    </div>
                                    <Button size="sm" color="blue">
                                        Start
                                    </Button>
                                </div>
                            ))}
                        </div>
                    </ModalBody>
                    <ModalFooter>
                        <Button color="gray" onClick={() => setShowProblemModal(false)}>
                            Cancel
                        </Button>
                    </ModalFooter>
                </Modal>
            </div>
        </DashboardLayout>
    );
}
