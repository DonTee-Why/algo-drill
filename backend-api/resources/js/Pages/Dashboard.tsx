import { Head } from '@inertiajs/react';
import React from 'react';
import ContinueSessionCard from '../Components/Dashboard/ContinueSessionCard';
import { PLACEHOLDER_PROGRESS } from '../Components/Dashboard/constants';
import { useProblemExplorer } from '../Components/Dashboard/hooks/useProblemExplorer';
import ProblemExplorerModal from '../Components/Dashboard/ProblemExplorerModal';
import ProgressCard from '../Components/Dashboard/ProgressCard';
import StartProblemCard from '../Components/Dashboard/StartProblemCard';
import type { DashboardProps } from '../Components/Dashboard/types';
import WelcomeHeader from '../Components/Dashboard/WelcomeHeader';
import DashboardLayout from './Layouts/DashboardLayout';

export default function Dashboard({ auth, activeSessions }: DashboardProps) {
    const explorer = useProblemExplorer();
    const lastActiveSession = activeSessions[0] ?? null;

    return (
        <DashboardLayout auth={auth}>
            <Head title="Dashboard" />
            <div className="max-w-[1400px] mx-auto px-8 py-8">
                <WelcomeHeader
                    user={auth?.user}
                    lastActiveSession={lastActiveSession}
                    onStartNew={explorer.open}
                />

                <div className="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
                    <ContinueSessionCard activeSessions={activeSessions} onStartNew={explorer.open} />
                    <ProgressCard progress={PLACEHOLDER_PROGRESS} />
                    <StartProblemCard onBrowse={explorer.open} />
                </div>

                <ProblemExplorerModal
                    isOpen={explorer.isOpen}
                    onClose={explorer.close}
                    selectedDifficulty={explorer.selectedDifficulty}
                    onDifficultyChange={explorer.setSelectedDifficulty}
                    problems={explorer.problems}
                    loading={explorer.loading}
                    error={explorer.error}
                    onRetry={explorer.retry}
                />
            </div>
        </DashboardLayout>
    );
}
