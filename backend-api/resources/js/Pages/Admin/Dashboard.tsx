import { Head } from '@inertiajs/react';
import React, { useState, useEffect } from 'react';
import AdminLayout from '../Layouts/AdminLayout';
import { Sparkles, ChevronRight } from 'lucide-react';
import { Card, Badge, Button, Tabs, TabItem, Dropdown, DropdownItem } from 'flowbite-react';
import { ResponsiveContainer, BarChart, Bar, XAxis, Tooltip, YAxis } from 'recharts';

interface Props {
    auth?: {
        user?: {
            id: number;
            name: string;
            email: string;
            is_admin?: boolean;
        } | null;
    };
}

// Mock data
const sessionTrend = [
    { label: 'Mon', sessions: 38 },
    { label: 'Tue', sessions: 52 },
    { label: 'Wed', sessions: 61 },
    { label: 'Thu', sessions: 47 },
    { label: 'Fri', sessions: 75 },
    { label: 'Sat', sessions: 29 },
    { label: 'Sun', sessions: 16 },
];

const recentSessions = [
    {
        id: 'SESS-9F2A',
        user: 'tim***@gmail.com',
        problem: 'Two Sum Variants',
        difficulty: 'Easy',
        state: 'TEST',
        completion: '5/6 stages',
        hints: 2,
        createdAt: '4 min ago',
    },
    {
        id: 'SESS-4B1C',
        user: 'ada***@proton.me',
        problem: 'Merge K Sorted Lists',
        difficulty: 'Hard',
        state: 'DONE',
        completion: '6/6 stages',
        hints: 1,
        createdAt: '18 min ago',
    },
    {
        id: 'SESS-7D9E',
        user: 'dev***@yahoo.com',
        problem: 'LRU Cache',
        difficulty: 'Medium',
        state: 'CODE',
        completion: '3/6 stages',
        hints: 0,
        createdAt: '33 min ago',
    },
    {
        id: 'SESS-2A7K',
        user: 'sam***@outlook.com',
        problem: 'Binary Tree Right View',
        difficulty: 'Medium',
        state: 'BRUTE_FORCE',
        completion: '2/6 stages',
        hints: 1,
        createdAt: '49 min ago',
    },
];

const topProblems = [
    {
        title: 'Two Sum Variants',
        tagline: 'Hash maps, arrays',
        difficulty: 'Easy',
        attempts: 184,
        completionRate: 86,
    },
    {
        title: 'Valid Anagram (Streaming)',
        tagline: 'Frequency maps, streaming input',
        difficulty: 'Medium',
        attempts: 121,
        completionRate: 71,
    },
    {
        title: 'Merge K Sorted Lists',
        tagline: 'Heaps, linked lists',
        difficulty: 'Hard',
        attempts: 94,
        completionRate: 44,
    },
    {
        title: 'Sliding Window Maximum',
        tagline: 'Deque, windows',
        difficulty: 'Medium',
        attempts: 109,
        completionRate: 63,
    },
];

const liveCoach = [
    {
        id: 'COACH-REQ-1827',
        stage: 'CLARIFY',
        user: 'tim***@gmail.com',
        problem: 'Two Sum Variants',
        waitingMs: 3200,
    },
    {
        id: 'COACH-REQ-1825',
        stage: 'PSEUDOCODE',
        user: 'ada***@proton.me',
        problem: 'LRU Cache',
        waitingMs: 1480,
    },
];

const healthMetrics = {
    coachLatencyP95: 1.7,
    leakIncidents: 0,
    testRunnerErrorRate: 0.6,
    revealUsageToday: 38,
};

const difficultyBadgeColor: Record<string, string> = {
    Easy: 'success',
    Medium: 'warning',
    Hard: 'failure',
};

const stagePillColor: Record<string, string> = {
    CLARIFY: 'info',
    BRUTE_FORCE: 'purple',
    PSEUDOCODE: 'warning',
    CODE: 'success',
    TEST: 'indigo',
    OPTIMIZE: 'pink',
    DONE: 'success',
};

const formatMs = (ms: number) => `${(ms / 1000).toFixed(1)}s`;

interface StatCardProps {
    label: string;
    value: string;
    delta: string;
    deltaTone?: 'positive' | 'negative' | 'neutral';
    hint?: string;
}

const StatCard: React.FC<StatCardProps> = ({ label, value, delta, deltaTone = 'neutral', hint }) => {
    const deltaColor =
        deltaTone === 'positive'
            ? 'text-green-600 dark:text-green-400'
            : deltaTone === 'negative'
            ? 'text-red-600 dark:text-red-400'
            : 'text-gray-600 dark:text-gray-400';

    return (
        <Card className="shadow-sm">
            <div className="space-y-3">
                <h3 className="text-sm font-medium text-gray-600 dark:text-gray-400">{label}</h3>
                <div className="flex items-baseline justify-between gap-2">
                    <span className="text-2xl font-bold text-gray-900 dark:text-white">{value}</span>
                    <span className={`text-sm font-medium ${deltaColor}`}>{delta}</span>
                </div>
                {hint && <p className="text-xs text-gray-500 dark:text-gray-400">{hint}</p>}
            </div>
        </Card>
    );
};

export default function Dashboard({ auth }: Props) {
    const [themePreference, setThemePreference] = useState<'light' | 'dark' | 'system'>(() => {
        // Check localStorage, default to 'system'
        if (typeof window !== 'undefined') {
            const stored = localStorage.getItem('theme-preference');
            if (stored === 'light' || stored === 'dark' || stored === 'system') {
                return stored;
            }
        }
        return 'system';
    });

    // Apply theme changes to document
    useEffect(() => {
        const root = document.documentElement;
        
        const applyTheme = (isDark: boolean) => {
            if (isDark) {
                root.classList.add('dark');
            } else {
                root.classList.remove('dark');
            }
        };

        if (themePreference === 'system') {
            // Use system preference
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            applyTheme(mediaQuery.matches);
            
            // Listen for system preference changes
            const handleChange = (e: MediaQueryListEvent) => applyTheme(e.matches);
            mediaQuery.addEventListener('change', handleChange);
            
            return () => mediaQuery.removeEventListener('change', handleChange);
        } else {
            // Use user preference
            applyTheme(themePreference === 'dark');
        }
        
        localStorage.setItem('theme-preference', themePreference);
    }, [themePreference]);

    const handleThemeChange = (newTheme: 'light' | 'dark' | 'system') => {
        setThemePreference(newTheme);
    };

    return (
        <AdminLayout auth={auth}>
            <Head title="Admin - Overview" />
            
            {/* Top bar */}
            <header className="border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 px-6 py-4">
                <div className="flex items-center justify-between gap-4 flex-wrap">
                    <div>
                        <div className="flex items-center gap-2 text-sm mb-1">
                            <span className="text-gray-500 dark:text-gray-400">Admin</span>
                            <span className="text-gray-400 dark:text-gray-600">›</span>
                            <span className="text-gray-700 dark:text-gray-300 font-medium">Overview</span>
                        </div>
                        <div className="flex items-center gap-3">
                            <h1 className="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">System Health & Learning Metrics</h1>
                            <Badge color="success" className="text-xs">
                                <span className="flex items-center gap-1.5">
                                    <span className="h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse" />
                                    Live (mock)
                                </span>
                            </Badge>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        <select className="h-9 bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-sm rounded-lg text-gray-900 dark:text-white px-3">
                            <option value="today">Today</option>
                            <option value="7d">Last 7 days</option>
                            <option value="30d">Last 30 days</option>
                        </select>
                        
                        {/* Theme Selector */}
                        <Dropdown
                            label={
                                <button
                                    type="button"
                                    className="h-9 px-3 text-gray-500 rounded-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400 cursor-pointer"
                                    aria-label="Theme selector"
                                >
                                    {themePreference === 'system' ? (
                                        <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clipRule="evenodd" />
                                        </svg>
                                    ) : themePreference === 'dark' ? (
                                        <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                                        </svg>
                                    ) : (
                                        <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fillRule="evenodd" clipRule="evenodd" />
                                        </svg>
                                    )}
                                </button>
                            }
                            arrowIcon={false}
                            inline
                        >
                            <DropdownItem onClick={() => handleThemeChange('light')}>
                                <div className="flex items-center gap-2">
                                    <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fillRule="evenodd" clipRule="evenodd" />
                                    </svg>
                                    <span>Light</span>
                                    {themePreference === 'light' && <span className="ml-auto">✓</span>}
                                </div>
                            </DropdownItem>
                            <DropdownItem onClick={() => handleThemeChange('dark')}>
                                <div className="flex items-center gap-2">
                                    <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                                    </svg>
                                    <span>Dark</span>
                                    {themePreference === 'dark' && <span className="ml-auto">✓</span>}
                                </div>
                            </DropdownItem>
                            <DropdownItem onClick={() => handleThemeChange('system')}>
                                <div className="flex items-center gap-2">
                                    <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fillRule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clipRule="evenodd" />
                                    </svg>
                                    <span>System</span>
                                    {themePreference === 'system' && <span className="ml-auto">✓</span>}
                                </div>
                            </DropdownItem>
                        </Dropdown>

                        <Button size="sm" color="gray">
                            Export
                        </Button>
                        <Button size="sm" color="blue">
                            New problem
                        </Button>
                    </div>
                </div>
            </header>

            {/* Content */}
            <div className="flex-1 flex flex-col lg:flex-row min-h-0 overflow-auto">
                {/* Left: main content */}
                <main className="flex-1 px-6 py-6 space-y-6 min-w-0 overflow-auto">
                    {/* Stat cards */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                        <StatCard
                            label="Active users now"
                            value="127"
                            delta="+14.2%"
                            deltaTone="positive"
                            hint="Unique users with an in-progress session in the last 10 min."
                        />
                        <StatCard
                            label="Sessions started"
                            value="312"
                            delta="+8.9%"
                            deltaTone="positive"
                            hint="Sessions created in the selected time range."
                        />
                        <StatCard
                            label="Completion rate"
                            value="64%"
                            delta="+3.1%"
                            deltaTone="neutral"
                            hint="Sessions that reached DONE with all tests green."
                        />
                        <StatCard
                            label="Reveal usage"
                            value="38%"
                            delta="-5.4%"
                            deltaTone="positive"
                            hint="Share of DONE sessions where REVEAL was used."
                        />
                    </div>

                    <div className="grid grid-cols-1 xl:grid-cols-3 gap-6">
                        {/* Sessions chart */}
                        <Card className="shadow-sm col-span-1 xl:col-span-2">
                            <div className="space-y-4">
                                <div className="flex items-start justify-between gap-2">
                                    <div>
                                        <h3 className="text-base font-semibold text-gray-900 dark:text-white">Sessions over time</h3>
                                        <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            Track daily sessions and spot drop-offs before they hurt practice streaks.
                                        </p>
                                    </div>
                                    <Badge color="gray" className="text-xs">Last 7 days</Badge>
                                </div>
                                <div className="w-full h-48 md:h-56">
                                    <ResponsiveContainer width="100%" height="100%">
                                        <BarChart data={sessionTrend}>
                                            <XAxis 
                                                dataKey="label" 
                                                tickLine={false} 
                                                axisLine={false} 
                                                tick={{ fill: '#6b7280', fontSize: 12 }} 
                                            />
                                            <YAxis 
                                                tickLine={false} 
                                                axisLine={false} 
                                                tick={{ fill: '#6b7280', fontSize: 12 }} 
                                            />
                                            <Tooltip
                                                cursor={{ fill: 'rgba(59, 130, 246, 0.1)' }}
                                                contentStyle={{
                                                    backgroundColor: '#ffffff',
                                                    borderRadius: 8,
                                                    border: '1px solid #e5e7eb',
                                                    padding: 8,
                                                }}
                                            />
                                            <Bar dataKey="sessions" fill="#3b82f6" radius={[6, 6, 0, 0]} />
                                        </BarChart>
                                    </ResponsiveContainer>
                                </div>
                            </div>
                        </Card>

                        {/* Coach health */}
                        <Card className="shadow-sm">
                            <div className="space-y-4">
                                <h3 className="text-base font-semibold flex items-center gap-2 text-gray-900 dark:text-white">
                                    <Sparkles className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    AI coach health
                                </h3>
                                <div className="space-y-3 text-sm">
                                    <div className="flex items-center justify-between text-gray-600 dark:text-gray-400">
                                        <span>Latency p95</span>
                                        <span className="font-semibold text-gray-900 dark:text-white">{healthMetrics.coachLatencyP95}s</span>
                                    </div>
                                    <div className="flex items-center justify-between text-gray-600 dark:text-gray-400">
                                        <span>Leak incidents</span>
                                        <span className="font-semibold text-green-600 dark:text-green-400">{healthMetrics.leakIncidents}</span>
                                    </div>
                                    <div className="flex items-center justify-between text-gray-600 dark:text-gray-400">
                                        <span>Test runner error rate</span>
                                        <span className="font-semibold text-gray-900 dark:text-white">{healthMetrics.testRunnerErrorRate}%</span>
                                    </div>
                                    <hr className="border-gray-200 dark:border-gray-700" />
                                    <p className="text-xs text-gray-500 dark:text-gray-400">
                                        Guardrails are currently passing all probe tests. Any leaked canonical solution will be surfaced here.
                                    </p>
                                </div>
                            </div>
                        </Card>
                    </div>

                    {/* Tabs: sessions + problems */}
                    <div className="space-y-4">
                        <div className="flex items-center justify-between gap-3 flex-wrap">
                            <div className="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <span className="h-2 w-2 rounded-full bg-green-500 animate-pulse" />
                                Auto-refresh every 15s
                            </div>
                        </div>

                        <Tabs aria-label="Dashboard tabs">
                            <TabItem title="Live sessions">
                                <Card className="shadow-sm">
                                    <div className="overflow-x-auto">
                                        <table className="w-full text-sm">
                                            <thead className="text-xs text-gray-700 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-800">
                                                <tr>
                                                    <th className="px-4 py-3 text-left">Session</th>
                                                    <th className="px-4 py-3 text-left">User</th>
                                                    <th className="px-4 py-3 text-left">Problem</th>
                                                    <th className="px-4 py-3 text-left">Stage</th>
                                                    <th className="px-4 py-3 text-left">Progress</th>
                                                    <th className="px-4 py-3 text-right">Hints</th>
                                                    <th className="w-10" />
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                                                {recentSessions.map((session) => (
                                                    <tr key={session.id} className="hover:bg-gray-50 dark:hover:bg-gray-800">
                                                        <td className="px-4 py-3">
                                                            <div className="flex flex-col">
                                                                <span className="font-medium text-gray-900 dark:text-white text-xs">
                                                                    {session.id}
                                                                </span>
                                                                <span className="text-xs text-gray-500 dark:text-gray-400">{session.createdAt}</span>
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3 text-gray-700 dark:text-gray-300">
                                                            {session.user}
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <div className="flex flex-col gap-2">
                                                                <span className="text-gray-900 dark:text-white">{session.problem}</span>
                                                                <Badge 
                                                                    color={difficultyBadgeColor[session.difficulty] as any}
                                                                    className="w-fit text-xs"
                                                                >
                                                                    {session.difficulty}
                                                                </Badge>
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <Badge 
                                                                color={stagePillColor[session.state as keyof typeof stagePillColor] as any}
                                                                className="text-xs"
                                                            >
                                                                {session.state}
                                                            </Badge>
                                                        </td>
                                                        <td className="px-4 py-3 text-gray-700 dark:text-gray-300">
                                                            {session.completion}
                                                        </td>
                                                        <td className="px-4 py-3 text-right text-gray-700 dark:text-gray-300">
                                                            {session.hints}
                                                        </td>
                                                        <td className="px-2 py-3 text-right">
                                                            <Button
                                                                size="xs"
                                                                color="gray"
                                                            >
                                                                <ChevronRight className="h-4 w-4" />
                                                            </Button>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </Card>
                            </TabItem>
                            <TabItem title="Top problems">
                                <Card className="shadow-sm">
                                    <div className="overflow-x-auto">
                                        <table className="w-full text-sm">
                                            <thead className="text-xs text-gray-700 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-800">
                                                <tr>
                                                    <th className="px-4 py-3 text-left">Problem</th>
                                                    <th className="px-4 py-3 text-left">Difficulty</th>
                                                    <th className="px-4 py-3 text-left">Attempts</th>
                                                    <th className="px-4 py-3 text-left">Completion</th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                                                {topProblems.map((problem) => (
                                                    <tr key={problem.title} className="hover:bg-gray-50 dark:hover:bg-gray-800">
                                                        <td className="px-4 py-3">
                                                            <div className="flex flex-col">
                                                                <span className="font-medium text-gray-900 dark:text-white">
                                                                    {problem.title}
                                                                </span>
                                                                <span className="text-xs text-gray-500 dark:text-gray-400">{problem.tagline}</span>
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <Badge 
                                                                color={difficultyBadgeColor[problem.difficulty] as any}
                                                                className="text-xs"
                                                            >
                                                                {problem.difficulty}
                                                            </Badge>
                                                        </td>
                                                        <td className="px-4 py-3 text-gray-700 dark:text-gray-300">
                                                            {problem.attempts}
                                                        </td>
                                                        <td className="px-4 py-3">
                                                            <div className="flex items-center gap-3">
                                                                <div className="flex-1 h-2 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                                                    <div
                                                                        className="h-full bg-green-500"
                                                                        style={{ width: `${problem.completionRate}%` }}
                                                                    />
                                                                </div>
                                                                <span className="text-gray-700 dark:text-gray-300 w-12 text-right">
                                                                    {problem.completionRate}%
                                                                </span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                </Card>
                            </TabItem>
                        </Tabs>
                    </div>
                </main>

                {/* Right: live coach + queue */}
                <aside className="w-full lg:w-80 xl:w-96 border-t lg:border-t-0 lg:border-l border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                    <div className="px-4 py-4 border-b border-gray-200 dark:border-gray-800">
                        <div className="space-y-1">
                            <div className="flex items-center justify-between">
                                <h3 className="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    Live coach queue
                                    <span className="h-2 w-2 rounded-full bg-green-500 animate-pulse" />
                                </h3>
                                <Badge color="gray" className="text-xs">{liveCoach.length} in queue</Badge>
                            </div>
                            <p className="text-xs text-gray-600 dark:text-gray-400">Requests hitting FastAPI sidecar in real time.</p>
                        </div>
                    </div>

                    <div className="p-4 space-y-3 overflow-auto">
                        {liveCoach.map((item) => (
                            <Card key={item.id} className="shadow-sm">
                                <div className="space-y-3">
                                    <div className="flex items-start justify-between gap-2">
                                        <div className="flex flex-col min-w-0">
                                            <span className="text-sm font-medium text-gray-900 dark:text-white truncate">{item.problem}</span>
                                            <span className="text-xs text-gray-500 dark:text-gray-400">{item.user}</span>
                                        </div>
                                        <Badge 
                                            color={stagePillColor[item.stage as keyof typeof stagePillColor] as any}
                                            className="text-xs shrink-0"
                                        >
                                            {item.stage}
                                        </Badge>
                                    </div>
                                    <div className="flex items-center justify-between text-sm">
                                        <span className="text-gray-600 dark:text-gray-400">Waiting</span>
                                        <span className="font-semibold text-gray-900 dark:text-white">{formatMs(item.waitingMs)}</span>
                                    </div>
                                </div>
                            </Card>
                        ))}

                        <Card className="shadow-sm border-dashed">
                            <div className="space-y-2">
                                <h4 className="text-sm font-medium text-gray-900 dark:text-white flex items-center gap-2">
                                    <Sparkles className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                    Independence score experiments
                                </h4>
                                <p className="text-xs text-gray-600 dark:text-gray-400">
                                    Use this panel to watch how often users ask for hints vs. progress on their own across stages.
                                </p>
                            </div>
                        </Card>
                    </div>
                </aside>
            </div>
        </AdminLayout>
    );
}
