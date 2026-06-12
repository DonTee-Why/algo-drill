import React from 'react';
import { Button } from 'flowbite-react';
import type { WelcomeHeaderProps } from './types';

export default function WelcomeHeader({ user, lastActiveSession, onStartNew }: WelcomeHeaderProps) {
    return (
        <div className="flex items-start justify-between mb-6">
            <div>
                <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-3">
                    Welcome back, {user?.name || 'User'}!
                </h1>
                <p className="text-gray-600 dark:text-gray-400">
                    {lastActiveSession
                        ? `You've got an active session in progress: ${lastActiveSession.problem.title}. You can resume or start something new.`
                        : 'Pick a problem to start your next guided practice session.'}
                </p>
            </div>
            <Button
                color="blue"
                onClick={onStartNew}
                className="cursor-pointer hover:bg-blue-600 hover:text-white"
            >
                Start new problem
            </Button>
        </div>
    );
}
