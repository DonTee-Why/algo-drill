import React from "react";
import { BarChart3, Users, Brain, PlayCircle, Sparkles, Settings, Activity, ChevronRight } from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Separator } from "@/components/ui/separator";
import { ResponsiveContainer, BarChart, Bar, XAxis, Tooltip } from "recharts";

const sessionTrend = [
  { label: "Mon", sessions: 38 },
  { label: "Tue", sessions: 52 },
  { label: "Wed", sessions: 61 },
  { label: "Thu", sessions: 47 },
  { label: "Fri", sessions: 75 },
  { label: "Sat", sessions: 29 },
  { label: "Sun", sessions: 16 },
];

const recentSessions = [
  {
    id: "SESS-9F2A",
    user: "tim***@gmail.com",
    problem: "Two Sum Variants",
    difficulty: "Easy",
    state: "TEST",
    completion: "5/6 stages",
    hints: 2,
    createdAt: "4 min ago",
  },
  {
    id: "SESS-4B1C",
    user: "ada***@proton.me",
    problem: "Merge K Sorted Lists",
    difficulty: "Hard",
    state: "DONE",
    completion: "6/6 stages",
    hints: 1,
    createdAt: "18 min ago",
  },
  {
    id: "SESS-7D9E",
    user: "dev***@yahoo.com",
    problem: "LRU Cache",
    difficulty: "Medium",
    state: "CODE",
    completion: "3/6 stages",
    hints: 0,
    createdAt: "33 min ago",
  },
  {
    id: "SESS-2A7K",
    user: "sam***@outlook.com",
    problem: "Binary Tree Right View",
    difficulty: "Medium",
    state: "BRUTE_FORCE",
    completion: "2/6 stages",
    hints: 1,
    createdAt: "49 min ago",
  },
];

const topProblems = [
  {
    title: "Two Sum Variants",
    tagline: "Hash maps, arrays",
    difficulty: "Easy",
    attempts: 184,
    completionRate: 86,
  },
  {
    title: "Valid Anagram (Streaming)",
    tagline: "Frequency maps, streaming input",
    difficulty: "Medium",
    attempts: 121,
    completionRate: 71,
  },
  {
    title: "Merge K Sorted Lists",
    tagline: "Heaps, linked lists",
    difficulty: "Hard",
    attempts: 94,
    completionRate: 44,
  },
  {
    title: "Sliding Window Maximum",
    tagline: "Deque, windows",
    difficulty: "Medium",
    attempts: 109,
    completionRate: 63,
  },
];

const liveCoach = [
  {
    id: "COACH-REQ-1827",
    stage: "CLARIFY",
    user: "tim***@gmail.com",
    problem: "Two Sum Variants",
    waitingMs: 3200,
  },
  {
    id: "COACH-REQ-1825",
    stage: "PSEUDOCODE",
    user: "ada***@proton.me",
    problem: "LRU Cache",
    waitingMs: 1480,
  },
];

const healthMetrics = {
  coachLatencyP95: 1.7,
  leakIncidents: 0,
  testRunnerErrorRate: 0.6,
  revealUsageToday: 38,
};

const difficultyBadgeVariant: Record<string, string> = {
  Easy: "bg-emerald-500/10 text-emerald-500 border-emerald-500/30",
  Medium: "bg-amber-500/10 text-amber-500 border-amber-500/30",
  Hard: "bg-rose-500/10 text-rose-500 border-rose-500/30",
};

const stagePillVariant: Record<string, string> = {
  CLARIFY: "bg-sky-500/10 text-sky-400",
  BRUTE_FORCE: "bg-violet-500/10 text-violet-400",
  PSEUDOCODE: "bg-amber-500/10 text-amber-400",
  CODE: "bg-emerald-500/10 text-emerald-400",
  TEST: "bg-indigo-500/10 text-indigo-400",
  OPTIMIZE: "bg-fuchsia-500/10 text-fuchsia-400",
  DONE: "bg-emerald-500/10 text-emerald-400",
};

const formatMs = (ms: number) => `${(ms / 1000).toFixed(1)}s`;

const AdminDashboard: React.FC = () => {
  return (
    <div className="h-screen w-full bg-slate-950 text-slate-50 flex overflow-hidden">
      {/* Sidebar */}
      <aside className="hidden lg:flex lg:w-64 xl:w-72 flex-col border-r border-slate-800 bg-slate-950/80 backdrop-blur">
        <div className="flex items-center gap-3 px-5 pt-5 pb-4">
          <div className="h-9 w-9 rounded-xl bg-sky-500/10 flex items-center justify-center">
            <Brain className="h-5 w-5 text-sky-400" />
          </div>
          <div className="flex flex-col">
            <span className="text-sm font-semibold tracking-tight">AlgoDrill</span>
            <span className="text-xs text-slate-400">Socratic Interview Coach</span>
          </div>
        </div>
        <Separator className="bg-slate-800" />

        <nav className="flex-1 px-2 py-3 space-y-1 text-sm">
          <SidebarItem icon={BarChart3} label="Overview" active />
          <SidebarItem icon={Activity} label="Sessions" />
          <SidebarItem icon={Users} label="Users" />
          <SidebarItem icon={PlayCircle} label="Problems" />
          <SidebarItem icon={Sparkles} label="AI Coach" />
          <SidebarItem icon={Settings} label="Settings" />
        </nav>

        <div className="px-4 pb-4">
          <Card className="border-slate-800 bg-slate-900/70">
            <CardHeader className="pb-2">
              <CardTitle className="text-xs font-semibold text-slate-300 flex items-center justify-between">
                Today’s snapshot
                <Badge className="bg-sky-500/10 text-sky-400 border-0 text-[10px]">Live</Badge>
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-2 text-[11px] text-slate-400">
              <div className="flex items-center justify-between">
                <span>Active sessions</span>
                <span className="text-slate-100 font-medium">27</span>
              </div>
              <div className="flex items-center justify-between">
                <span>Coach latency p95</span>
                <span className="text-slate-100 font-medium">{healthMetrics.coachLatencyP95}s</span>
              </div>
              <div className="flex items-center justify-between">
                <span>Reveal actions</span>
                <span className="text-slate-100 font-medium">{healthMetrics.revealUsageToday}</span>
              </div>
            </CardContent>
          </Card>
        </div>
      </aside>

      {/* Main */}
      <div className="flex-1 flex flex-col min-w-0">
        {/* Top bar */}
        <header className="border-b border-slate-800 bg-slate-950/90 backdrop-blur px-4 lg:px-6 py-3 flex items-center justify-between gap-3">
          <div className="space-y-0.5">
            <div className="flex items-center gap-2">
              <span className="text-xs font-medium uppercase tracking-[0.16em] text-slate-500">Admin</span>
              <span className="h-1 w-1 rounded-full bg-slate-600" />
              <span className="text-xs font-medium tracking-[0.16em] text-slate-400">Dashboard</span>
            </div>
            <div className="flex items-center gap-2 flex-wrap">
              <h1 className="text-base md:text-lg font-semibold tracking-tight">System health & learning metrics</h1>
              <Badge className="bg-emerald-500/10 text-emerald-400 border-0 text-[10px] flex items-center gap-1">
                <span className="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse" />
                Live data (mock)
              </Badge>
            </div>
          </div>

          <div className="flex items-center gap-2 md:gap-3">
            <Select defaultValue="today">
              <SelectTrigger className="h-8 bg-slate-900 border-slate-700 text-xs min-w-[120px]">
                <SelectValue placeholder="Range" />
              </SelectTrigger>
              <SelectContent className="bg-slate-900 border-slate-700 text-xs">
                <SelectItem value="today">Today</SelectItem>
                <SelectItem value="7d">Last 7 days</SelectItem>
                <SelectItem value="30d">Last 30 days</SelectItem>
              </SelectContent>
            </Select>
            <Button size="sm" variant="outline" className="h-8 border-slate-700 bg-slate-900/70 text-xs">
              Export
            </Button>
            <Button size="sm" className="h-8 text-xs bg-sky-500 hover:bg-sky-500/90">
              New problem
            </Button>
          </div>
        </header>

        {/* Content */}
        <div className="flex-1 flex flex-col lg:flex-row min-h-0">
          {/* Left: main content */}
          <main className="flex-1 px-4 lg:px-6 py-4 space-y-4 min-w-0">
            {/* Stat cards */}
            <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
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

            <div className="grid grid-cols-1 xl:grid-cols-3 gap-4 min-h-[260px]">
              {/* Sessions chart */}
              <Card className="border-slate-800 bg-slate-900/70 col-span-1 xl:col-span-2 flex flex-col">
                <CardHeader className="flex flex-row items-center justify-between gap-2 pb-2">
                  <div>
                    <CardTitle className="text-sm font-semibold">Sessions over time</CardTitle>
                    <p className="text-xs text-slate-400 mt-1">
                      Track daily sessions and spot drop-offs before they hurt practice streaks.
                    </p>
                  </div>
                  <Badge className="bg-slate-900 text-[10px] border-slate-700">Last 7 days</Badge>
                </CardHeader>
                <CardContent className="flex-1 flex items-center justify-center pb-3">
                  <div className="w-full h-40 md:h-48">
                    <ResponsiveContainer width="100%" height="100%">
                      <BarChart data={sessionTrend} barSize={18}>
                        <XAxis dataKey="label" tickLine={false} axisLine={false} tick={{ fill: "#64748b", fontSize: 11 }} />
                        <Tooltip
                          cursor={{ fill: "rgba(148, 163, 184, 0.10)" }}
                          contentStyle={{
                            backgroundColor: "#020617",
                            borderRadius: 8,
                            border: "1px solid rgba(51,65,85,0.7)",
                            padding: 10,
                          }}
                          labelStyle={{ color: "#cbd5f5" }}
                        />
                        <Bar dataKey="sessions" radius={[4, 4, 0, 0]} />
                      </BarChart>
                    </ResponsiveContainer>
                  </div>
                </CardContent>
              </Card>

              {/* Coach health */}
              <Card className="border-slate-800 bg-slate-900/70 flex flex-col">
                <CardHeader className="pb-2">
                  <CardTitle className="text-sm font-semibold flex items-center gap-2">
                    <Sparkles className="h-4 w-4 text-sky-400" />
                    AI coach health
                  </CardTitle>
                </CardHeader>
                <CardContent className="space-y-3 text-xs text-slate-300">
                  <div className="flex items-center justify-between">
                    <span>Latency p95</span>
                    <span className="font-medium">{healthMetrics.coachLatencyP95}s</span>
                  </div>
                  <div className="flex items-center justify-between">
                    <span>Leak incidents</span>
                    <span className="font-medium text-emerald-400">{healthMetrics.leakIncidents}</span>
                  </div>
                  <div className="flex items-center justify-between">
                    <span>Test runner error rate</span>
                    <span className="font-medium">{healthMetrics.testRunnerErrorRate}%</span>
                  </div>
                  <Separator className="bg-slate-800" />
                  <p className="text-[11px] text-slate-400">
                    Guardrails are currently passing all probe tests. Any leaked canonical solution will be surfaced here.
                  </p>
                </CardContent>
              </Card>
            </div>

            {/* Tabs: sessions + problems */}
            <Tabs defaultValue="sessions" className="space-y-3">
              <div className="flex items-center justify-between gap-3 flex-wrap">
                <TabsList className="bg-slate-900 border border-slate-800">
                  <TabsTrigger value="sessions" className="data-[state=active]:bg-slate-800 text-xs">
                    Live sessions
                  </TabsTrigger>
                  <TabsTrigger value="problems" className="data-[state=active]:bg-slate-800 text-xs">
                    Top problems
                  </TabsTrigger>
                </TabsList>
                <div className="flex items-center gap-2 text-[11px] text-slate-400">
                  <span className="h-2 w-2 rounded-full bg-emerald-400 animate-pulse" />
                  Auto-refresh every 15s
                </div>
              </div>

              <TabsContent value="sessions" className="mt-0">
                <Card className="border-slate-800 bg-slate-900/70">
                  <CardContent className="pt-3 px-0">
                    <ScrollArea className="w-full max-h-72">
                      <table className="w-full text-xs">
                        <thead className="sticky top-0 z-10 bg-slate-900/95 backdrop-blur border-b border-slate-800">
                          <tr className="text-[11px] text-slate-400">
                            <th className="font-medium text-left px-4 py-2">Session</th>
                            <th className="font-medium text-left px-2 py-2">User</th>
                            <th className="font-medium text-left px-2 py-2">Problem</th>
                            <th className="font-medium text-left px-2 py-2">Stage</th>
                            <th className="font-medium text-left px-2 py-2">Progress</th>
                            <th className="font-medium text-right px-4 py-2">Hints</th>
                            <th className="w-8" />
                          </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-800/80">
                          {recentSessions.map((session) => (
                            <tr key={session.id} className="hover:bg-slate-900/80">
                              <td className="px-4 py-2 align-middle">
                                <div className="flex flex-col">
                                  <span className="font-medium text-slate-100 text-[11px]">
                                    {session.id}
                                  </span>
                                  <span className="text-[10px] text-slate-500">{session.createdAt}</span>
                                </div>
                              </td>
                              <td className="px-2 py-2 align-middle text-[11px] text-slate-200">
                                {session.user}
                              </td>
                              <td className="px-2 py-2 align-middle">
                                <div className="flex flex-col">
                                  <span className="text-[11px] text-slate-100">{session.problem}</span>
                                  <span
                                    className={`mt-1 inline-flex w-min whitespace-nowrap rounded-full border px-2 py-0.5 text-[9px] font-medium ${difficultyBadgeVariant[session.difficulty]}`}
                                  >
                                    {session.difficulty}
                                  </span>
                                </div>
                              </td>
                              <td className="px-2 py-2 align-middle">
                                <span
                                  className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium ${
                                    stagePillVariant[session.state as keyof typeof stagePillVariant]
                                  }`}
                                >
                                  {session.state}
                                </span>
                              </td>
                              <td className="px-2 py-2 align-middle text-[11px] text-slate-200">
                                {session.completion}
                              </td>
                              <td className="px-4 py-2 align-middle text-right text-[11px] text-slate-200">
                                {session.hints}
                              </td>
                              <td className="pr-2 pl-1 py-2 align-middle text-right">
                                <Button
                                  size="icon"
                                  variant="ghost"
                                  className="h-7 w-7 text-slate-400 hover:text-slate-100 hover:bg-slate-800/80"
                                >
                                  <ChevronRight className="h-3 w-3" />
                                </Button>
                              </td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </ScrollArea>
                  </CardContent>
                </Card>
              </TabsContent>

              <TabsContent value="problems" className="mt-0">
                <Card className="border-slate-800 bg-slate-900/70">
                  <CardContent className="pt-3 px-0">
                    <ScrollArea className="w-full max-h-72">
                      <table className="w-full text-xs">
                        <thead className="sticky top-0 z-10 bg-slate-900/95 backdrop-blur border-b border-slate-800">
                          <tr className="text-[11px] text-slate-400">
                            <th className="font-medium text-left px-4 py-2">Problem</th>
                            <th className="font-medium text-left px-2 py-2">Difficulty</th>
                            <th className="font-medium text-left px-2 py-2">Attempts</th>
                            <th className="font-medium text-left px-2 py-2">Completion</th>
                          </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-800/80">
                          {topProblems.map((problem) => (
                            <tr key={problem.title} className="hover:bg-slate-900/80">
                              <td className="px-4 py-2 align-middle">
                                <div className="flex flex-col">
                                  <span className="text-[11px] text-slate-100 font-medium">
                                    {problem.title}
                                  </span>
                                  <span className="text-[10px] text-slate-500">{problem.tagline}</span>
                                </div>
                              </td>
                              <td className="px-2 py-2 align-middle">
                                <span
                                  className={`inline-flex rounded-full border px-2 py-0.5 text-[9px] font-medium ${
                                    difficultyBadgeVariant[problem.difficulty]
                                  }`}
                                >
                                  {problem.difficulty}
                                </span>
                              </td>
                              <td className="px-2 py-2 align-middle text-[11px] text-slate-200">
                                {problem.attempts}
                              </td>
                              <td className="px-2 py-2 align-middle">
                                <div className="flex items-center gap-2">
                                  <div className="flex-1 h-1.5 rounded-full bg-slate-800 overflow-hidden">
                                    <div
                                      className="h-full bg-emerald-500"
                                      style={{ width: `${problem.completionRate}%` }}
                                    />
                                  </div>
                                  <span className="text-[11px] text-slate-200 w-10">
                                    {problem.completionRate}%
                                  </span>
                                </div>
                              </td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </ScrollArea>
                  </CardContent>
                </Card>
              </TabsContent>
            </Tabs>
          </main>

          {/* Right: live coach + queue */}
          <aside className="w-full lg:w-80 xl:w-96 border-t lg:border-t-0 lg:border-l border-slate-800 bg-slate-950/80 backdrop-blur flex flex-col">
            <div className="px-4 py-3 border-b border-slate-800 flex items-center justify-between">
              <div>
                <p className="text-xs font-semibold text-slate-100 flex items-center gap-2">
                  Live coach queue
                  <span className="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse" />
                </p>
                <p className="text-[11px] text-slate-400 mt-0.5">Requests hitting FastAPI sidecar in real time.</p>
              </div>
              <Badge className="bg-slate-900 text-[10px] border-slate-700">{liveCoach.length} in queue</Badge>
            </div>

            <ScrollArea className="flex-1 w-full">
              <div className="p-3 space-y-2">
                {liveCoach.map((item) => (
                  <Card key={item.id} className="border-slate-800 bg-slate-900/80">
                    <CardContent className="py-3 px-3 space-y-2">
                      <div className="flex items-center justify-between gap-2">
                        <div className="flex flex-col">
                          <span className="text-[11px] font-medium text-slate-100">{item.problem}</span>
                          <span className="text-[10px] text-slate-500">{item.user}</span>
                        </div>
                        <span
                          className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium ${
                            stagePillVariant[item.stage as keyof typeof stagePillVariant]
                          }`}
                        >
                          {item.stage}
                        </span>
                      </div>
                      <div className="flex items-center justify-between text-[11px] text-slate-400">
                        <span>Waiting</span>
                        <span className="text-slate-100 font-medium">{formatMs(item.waitingMs)}</span>
                      </div>
                    </CardContent>
                  </Card>
                ))}

                <Card className="border-dashed border-slate-800 bg-slate-950/60 mt-2">
                  <CardContent className="py-3 px-3 text-[11px] text-slate-400 space-y-1">
                    <p className="font-medium text-slate-200 flex items-center gap-2">
                      <Sparkles className="h-3.5 w-3.5 text-sky-400" />
                      Independence score experiments
                    </p>
                    <p>
                      Use this panel to watch how often users ask for hints vs. progress on their own across stages.
                    </p>
                  </CardContent>
                </Card>
              </div>
            </ScrollArea>
          </aside>
        </div>
      </div>
    </div>
  );
};

interface SidebarItemProps {
  icon: React.ComponentType<React.SVGProps<SVGSVGElement>>;
  label: string;
  active?: boolean;
}

const SidebarItem: React.FC<SidebarItemProps> = ({ icon: Icon, label, active }) => {
  return (
    <button
      className={`w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition-colors border border-transparent ${
        active
          ? "bg-slate-800 text-slate-50 border-slate-700"
          : "text-slate-400 hover:bg-slate-900/80 hover:text-slate-100"
      }`}
      type="button"
    >
      <Icon className="h-4 w-4" />
      <span>{label}</span>
    </button>
  );
};

interface StatCardProps {
  label: string;
  value: string;
  delta: string;
  deltaTone?: "positive" | "negative" | "neutral";
  hint?: string;
}

const StatCard: React.FC<StatCardProps> = ({ label, value, delta, deltaTone = "neutral", hint }) => {
  const deltaColor =
    deltaTone === "positive"
      ? "text-emerald-400"
      : deltaTone === "negative"
      ? "text-rose-400"
      : "text-slate-400";

  return (
    <Card className="border-slate-800 bg-slate-900/70">
      <CardHeader className="pb-1">
        <CardTitle className="text-[11px] font-medium text-slate-400 flex items-center justify-between gap-2">
          <span>{label}</span>
        </CardTitle>
      </CardHeader>
      <CardContent className="pt-1 pb-3 space-y-1">
        <div className="flex items-baseline justify-between gap-2">
          <span className="text-xl font-semibold tracking-tight text-slate-50">{value}</span>
          <span className={`text-[11px] font-medium ${deltaColor}`}>{delta}</span>
        </div>
        {hint && <p className="text-[10px] text-slate-500 mt-1 leading-snug">{hint}</p>}
      </CardContent>
    </Card>
  );
};

export default AdminDashboard;
