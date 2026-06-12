import React from 'react';
import { Button, Modal, ModalHeader, ModalBody, ModalFooter, Badge, Spinner } from 'flowbite-react';
import { DIFFICULTY_BADGE_COLORS, DIFFICULTY_FILTERS } from './constants';
import type { ProblemExplorerModalProps } from './types';

export default function ProblemExplorerModal({
    isOpen,
    onClose,
    selectedDifficulty,
    onDifficultyChange,
    problems,
    loading,
    error,
    onRetry,
}: ProblemExplorerModalProps) {
    return (
        <Modal show={isOpen} onClose={onClose} size="2xl">
            <ModalHeader className="border-b-0">
                <div>
                    <h3 className="text-2xl font-bold text-gray-900 dark:text-white">Problem Explorer</h3>
                    <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Choose a problem to begin a new guided session.
                    </p>
                </div>
            </ModalHeader>
            <ModalBody>
                <div className="space-y-6">
                    <div className="flex gap-2">
                        {DIFFICULTY_FILTERS.map((difficulty) => (
                            <Button
                                key={difficulty}
                                size="sm"
                                color={selectedDifficulty === difficulty ? 'blue' : 'gray'}
                                onClick={() => onDifficultyChange(difficulty)}
                                className="cursor-pointer"
                                disabled={loading}
                            >
                                {difficulty}
                            </Button>
                        ))}
                    </div>

                    {loading && (
                        <div className="flex items-center justify-center py-8">
                            <Spinner size="xl" />
                        </div>
                    )}

                    {error && !loading && (
                        <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 text-center">
                            <p className="text-red-600 dark:text-red-400">{error}</p>
                            <Button size="sm" color="failure" onClick={onRetry} className="mt-2 cursor-pointer">
                                Retry
                            </Button>
                        </div>
                    )}

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
                                                <Badge
                                                    color={DIFFICULTY_BADGE_COLORS[problem.difficulty] as 'success' | 'warning' | 'failure'}
                                                    size="sm"
                                                >
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
                                        <Button
                                            size="sm"
                                            color="blue"
                                            className="ml-4 whitespace-nowrap cursor-pointer hover:bg-blue-600"
                                        >
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
                    <Button
                        color="gray"
                        className="cursor-pointer border-gray-800 hover:bg-gray-900 hover:text-white"
                        onClick={onClose}
                    >
                        Close
                    </Button>
                </div>
            </ModalFooter>
        </Modal>
    );
}
