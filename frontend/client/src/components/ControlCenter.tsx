/**
 * Architectural Nightshift reminder: build information like a control-room floorplan—
 * precision dividers, graphite fields, one Blueprint Cobalt signal, and no generic card wall.
 */
import { Button } from "@/components/ui/button";
import { Tooltip, TooltipContent, TooltipTrigger } from "@/components/ui/tooltip";
import {
  Activity,
  ArrowRight,
  Bell,
  Braces,
  Check,
  ChevronDown,
  CircleDot,
  Clipboard,
  Code2,
  Command,
  Database,
  FileText,
  Gauge,
  GitBranch,
  HeartHandshake,
  HelpCircle,
  LayoutDashboard,
  LockKeyhole,
  Menu,
  MoreHorizontal,
  Radar,
  Search,
  Settings2,
  ShieldCheck,
  SlidersHorizontal,
  TerminalSquare,
  UserRound,
  UsersRound,
  X,
  Zap,
} from "lucide-react";
import { AnimatePresence, motion } from "framer-motion";
import { useMemo, useState } from "react";
import { toast } from "sonner";

type NavItem = {
  label: string;
  icon: typeof LayoutDashboard;
  hint?: string;
};

const navGroups: { label: string; items: NavItem[] }[] = [
  {
    label: "Control room",
    items: [
      { label: "Overview", icon: LayoutDashboard },
      { label: "Users", icon: UsersRound, hint: "API-backed" },
      { label: "Relationships", icon: HeartHandshake, hint: "Mutual" },
      { label: "Reputation", icon: Gauge, hint: "Calculated" },
    ],
  },
  {
    label: "Engineering",
    items: [
      { label: "API contract", icon: Braces },
      { label: "Access tokens", icon: LockKeyhole },
      { label: "Rate policy", icon: Radar },
    ],
  },
];

const endpointRows = [
  { method: "POST", route: "/api/auth/token", domain: "Authentication" },
  { method: "GET", route: "/api/users", domain: "Users" },
  { method: "POST", route: "/api/users/{id}/relationships", domain: "Relationships" },
  { method: "GET", route: "/api/metrics/reputation", domain: "Metrics" },
];

const readiness = [
  {
    icon: LockKeyhole,
    title: "Bearer token lifecycle",
    text: "Expiry, revocation, and hashed storage are specified in the API contract.",
    tag: "Auth",
  },
  {
    icon: Activity,
    title: "Per-token rate policy",
    text: "Read and write gates are prepared for Redis-backed enforcement.",
    tag: "Policy",
  },
  {
    icon: GitBranch,
    title: "Relationship integrity",
    text: "Mutual rows, transactions, and conflict handling protect against duplicate links.",
    tag: "Data",
  },
];

const methodTone: Record<string, string> = {
  GET: "text-[#b8c5ff] bg-[#4D67FF]/12 border-[#4D67FF]/25",
  POST: "text-[#A4F8C2] bg-[#54D98C]/10 border-[#54D98C]/20",
  PUT: "text-[#FFCF8B] bg-[#FFAB4D]/10 border-[#FFAB4D]/20",
  DELETE: "text-[#FFABAB] bg-[#FF6B6B]/10 border-[#FF6B6B]/20",
};

function LogoMark({ compact = false }: { compact?: boolean }) {
  return (
    <div className={`flex items-center ${compact ? "justify-center" : "gap-3"}`}>
      <div className="relative grid size-10 place-items-center overflow-hidden border border-[#697fff]/45 bg-[#4D67FF]/12 shadow-[0_0_24px_rgba(77,103,255,.16)]">
        <img
          src="/manus-storage/broomees-mark_38c6c87d.png"
          alt="Broomees brand mark"
          className="size-7 object-contain"
        />
      </div>
      {!compact && (
        <div className="leading-none">
          <div className="font-display text-[15px] font-bold tracking-[-0.04em] text-white">broomees</div>
          <div className="mt-1 font-mono text-[9px] uppercase tracking-[0.2em] text-[#77819a]">Control center</div>
        </div>
      )}
    </div>
  );
}

function NavButton({
  item,
  active,
  onClick,
}: {
  item: NavItem;
  active: boolean;
  onClick: () => void;
}) {
  const Icon = item.icon;
  return (
    <button
      onClick={onClick}
      className={`group relative flex w-full items-center gap-3 px-3 py-2.5 text-left text-[13px] transition duration-200 ease-out active:scale-[0.98] ${
        active
          ? "bg-[#4D67FF]/11 text-white"
          : "text-[#8f99af] hover:bg-white/[0.035] hover:text-[#e5e9f4]"
      }`}
    >
      {active && <span className="absolute inset-y-0 left-0 w-[2px] bg-[#5E77FF] shadow-[0_0_12px_#4D67FF]" />}
      <Icon className={`size-4 ${active ? "text-[#85a0ff]" : "text-[#68728a] group-hover:text-[#aeb9d2]"}`} strokeWidth={1.7} />
      <span className="font-medium">{item.label}</span>
      {item.hint && <span className="ml-auto font-mono text-[9px] uppercase tracking-[0.1em] text-[#606a82]">{item.hint}</span>}
    </button>
  );
}

function MetricCard({ label, icon: Icon, description }: { label: string; icon: typeof UsersRound; description: string }) {
  return (
    <div className="relative min-h-[174px] overflow-hidden border border-white/[0.07] bg-[#11141d]/88 p-5 shadow-[0_16px_50px_rgba(0,0,0,.14)] transition duration-200 hover:-translate-y-0.5 hover:border-[#4D67FF]/50">
      <div className="blueprint-corner absolute right-0 top-0 size-9 border-l-2 border-b-2 border-[#4D67FF]/50" />
      <div className="absolute bottom-0 left-0 size-7 border-r border-t border-[#4D67FF]/25" />
      <div className="flex items-start justify-between">
        <span className="font-mono text-[10px] uppercase tracking-[0.16em] text-[#7e88a1]">{label}</span>
        <Icon className="size-4 text-[#6d7eaa]" strokeWidth={1.5} />
      </div>
      <div className="mt-6 flex items-end gap-2">
        <span className="font-display text-4xl font-bold tracking-[-0.07em] text-white">—</span>
        <span className="mb-1.5 border border-[#4D67FF]/35 bg-[#4D67FF]/12 px-1.5 py-0.5 font-mono text-[9px] uppercase tracking-[0.08em] text-[#aebcff]">Awaiting authority</span>
      </div>
      <p className="mt-2 max-w-[220px] text-xs leading-5 text-[#778199]">{description}</p>
      <div className="mt-3 flex items-center justify-between border-t border-dashed border-white/[0.1] pt-2 font-mono text-[8px] uppercase tracking-[.11em] text-[#63708a]"><span>Source / metrics</span><span>Unlinked</span></div>
    </div>
  );
}

function EndpointRow({ method, route, domain }: (typeof endpointRows)[number]) {
  return (
    <div className="grid grid-cols-[58px_minmax(0,1fr)_auto] items-center gap-3 border-t border-dashed border-white/[0.09] px-5 py-3.5 transition duration-200 hover:bg-white/[0.025] sm:grid-cols-[64px_minmax(0,1fr)_92px_auto]">
      <span className={`w-fit border px-1.5 py-1 font-mono text-[9px] font-medium tracking-[0.06em] ${methodTone[method]}`}>{method}</span>
      <span className="truncate font-mono text-[11px] text-[#dce1ef]">{route}</span>
      <span className="hidden text-right text-xs text-[#727c94] sm:block">{domain}</span>
      <span className="flex items-center gap-1.5 font-mono text-[9px] uppercase tracking-[0.1em] text-[#9da7be]"><span className="size-1.5 rounded-full bg-[#626d86]" />Contract</span>
    </div>
  );
}

export default function ControlCenter() {
  const [activeNav, setActiveNav] = useState("Overview");
  const [mobileOpen, setMobileOpen] = useState(false);
  const [hasCopied, setHasCopied] = useState(false);
  const [focusMode, setFocusMode] = useState(false);

  const activeTitle = useMemo(() => activeNav === "Overview" ? "System overview" : activeNav, [activeNav]);

  const selectNav = (label: string) => {
    setActiveNav(label);
    setMobileOpen(false);
    if (label !== "Overview") {
      toast.info(`${label} workspace`, {
        description: "This static control-center view is ready to bind to the corresponding Laravel API resource.",
      });
    }
  };

  const copyContract = async () => {
    try {
      await navigator.clipboard.writeText("GET /api/metrics/reputation");
      setHasCopied(true);
      toast.success("Endpoint copied", { description: "GET /api/metrics/reputation is ready for your API client." });
      window.setTimeout(() => setHasCopied(false), 1600);
    } catch {
      toast.message("Copy unavailable", { description: "Use GET /api/metrics/reputation in your API client." });
    }
  };

  const connectApi = () => {
    toast.message("API connection point identified", {
      description: "Bind this interface to the Laravel endpoint layer when the backend is running.",
      action: { label: "View contract", onClick: () => selectNav("API contract") },
    });
  };

  return (
    <div className={`min-h-screen overflow-x-hidden bg-[#0b0d13] text-[#e8ebf5] ${focusMode ? "focus-mode" : ""}`}>
      <div className="pointer-events-none fixed inset-0 opacity-[0.22] [background-image:radial-gradient(circle_at_1px_1px,rgba(198,210,255,.22)_1px,transparent_0)] [background-size:24px_24px]" />
      <div className="pointer-events-none fixed inset-0 bg-[linear-gradient(90deg,rgba(77,103,255,.04)_1px,transparent_1px),linear-gradient(0deg,rgba(77,103,255,.025)_1px,transparent_1px)] bg-[size:72px_72px] opacity-30" />

      <aside className="fixed inset-y-0 left-0 z-40 hidden w-[254px] border-r border-white/[0.07] bg-[#0d1018]/95 px-3 pb-5 pt-6 backdrop-blur-xl lg:flex lg:flex-col">
        <LogoMark />
        <div className="mt-10 space-y-7">
          {navGroups.map((group) => (
            <div key={group.label}>
              <p className="px-3 pb-2 font-mono text-[9px] uppercase tracking-[0.18em] text-[#576179]">{group.label}</p>
              <div className="space-y-0.5">
                {group.items.map((item) => <NavButton key={item.label} item={item} active={activeNav === item.label} onClick={() => selectNav(item.label)} />)}
              </div>
            </div>
          ))}
        </div>
        <div className="mt-auto border border-white/[0.07] bg-white/[0.018] p-3.5">
          <div className="flex items-center gap-2">
            <span className="relative flex size-2"><span className="absolute inline-flex size-2 animate-ping rounded-full bg-[#88ffae] opacity-40" /><span className="relative inline-flex size-2 rounded-full bg-[#73e39a]" /></span>
            <span className="font-mono text-[9px] uppercase tracking-[0.14em] text-[#a6b0c7]">Control surface online</span>
          </div>
          <p className="mt-2 text-[11px] leading-4 text-[#6d7890]">Frontend workspace loaded. Connect a backend source to populate operational values.</p>
        </div>
        <button onClick={() => toast.info("Settings panel", { description: "Configuration controls can be layered here once your deployment settings are ready." })} className="mt-3 flex items-center gap-2 px-3 py-2 text-xs text-[#7a8499] transition hover:text-white active:scale-[0.98]">
          <Settings2 className="size-3.5" /> Workspace settings
        </button>
      </aside>

      <AnimatePresence>
        {mobileOpen && (
          <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }} className="fixed inset-0 z-50 bg-[#07090e]/85 backdrop-blur-sm lg:hidden">
            <motion.div initial={{ x: -280 }} animate={{ x: 0 }} exit={{ x: -280 }} transition={{ type: "spring", stiffness: 330, damping: 32 }} className="h-full w-[284px] border-r border-white/[0.08] bg-[#0d1018] px-3 pb-5 pt-5">
              <div className="flex items-center justify-between px-1">
                <LogoMark />
                <button aria-label="Close navigation" onClick={() => setMobileOpen(false)} className="p-2 text-[#9ea9c1]"><X className="size-4" /></button>
              </div>
              <div className="mt-8 space-y-7">
                {navGroups.map((group) => (
                  <div key={group.label}>
                    <p className="px-3 pb-2 font-mono text-[9px] uppercase tracking-[0.18em] text-[#576179]">{group.label}</p>
                    <div className="space-y-0.5">{group.items.map((item) => <NavButton key={item.label} item={item} active={activeNav === item.label} onClick={() => selectNav(item.label)} />)}</div>
                  </div>
                ))}
              </div>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>

      <main className="relative lg:ml-[254px]">
        <header className="relative z-30 flex min-h-[72px] items-center justify-between border-b border-white/[0.07] bg-[#0d1018]/78 px-5 backdrop-blur-xl sm:px-8">
          <div className="flex min-w-0 items-center gap-3">
            <button aria-label="Open navigation" onClick={() => setMobileOpen(true)} className="grid size-9 place-items-center border border-white/[0.1] text-[#acb5c8] lg:hidden"><Menu className="size-4" /></button>
            <div className="hidden lg:block"><LogoMark /></div>
            <div className="hidden items-center gap-2 text-xs sm:flex">
              <span className="text-[#495269]">/</span><span className="font-medium text-[#e5e9f5]">{activeTitle}</span>
            </div>
            <div className="flex items-center gap-2 sm:hidden"><LogoMark compact /><span className="font-display text-sm font-bold tracking-[-.04em]">{activeTitle}</span></div>
          </div>
          <div className="flex items-center gap-2 sm:gap-3">
            <Tooltip>
              <TooltipTrigger asChild>
                <button onClick={() => setFocusMode((value) => !value)} aria-label="Toggle focus mode" className={`hidden size-9 place-items-center border text-[#98a3bb] transition hover:text-white md:grid ${focusMode ? "border-[#4D67FF]/55 bg-[#4D67FF]/15 text-[#a2b3ff]" : "border-white/[0.09]"}`}><Command className="size-3.5" /></button>
              </TooltipTrigger>
              <TooltipContent>Toggle focused reading mode</TooltipContent>
            </Tooltip>
            <button aria-label="Notifications" onClick={() => toast.message("No deployment alerts", { description: "When your API is connected, operational signals will surface here." })} className="relative grid size-9 place-items-center border border-white/[0.09] text-[#98a3bb] transition hover:border-[#4D67FF]/45 hover:text-white"><Bell className="size-3.5" /><span className="absolute right-2 top-2 size-1.5 rounded-full bg-[#86f5a8]" /></button>
            <Button onClick={connectApi} className="h-9 rounded-none bg-[#4D67FF] px-3 text-[11px] font-semibold text-white shadow-[0_10px_25px_rgba(77,103,255,.22)] hover:bg-[#6179ff] sm:px-4">Connect API <ArrowRight className="ml-1.5 size-3.5" /></Button>
          </div>
        </header>

        <div className="mx-auto max-w-[1560px] px-5 pb-12 pt-7 sm:px-8 xl:px-10">
          <motion.section initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.38, ease: [0.23, 1, 0.32, 1] }} className="relative overflow-hidden border border-white/[0.09] bg-[#10141e] shadow-[0_26px_80px_rgba(0,0,0,.28)]">
            <img src="/manus-storage/broomees-hero-console_57ed24b2.jpg" alt="Abstract developer-control environment" className="absolute inset-0 size-full object-cover opacity-[0.45] mix-blend-screen" />
            <div className="absolute inset-0 bg-[linear-gradient(90deg,rgba(11,13,19,.97)_0%,rgba(11,13,19,.77)_46%,rgba(11,13,19,.35)_100%)]" />
            <div className="absolute inset-0 [background:linear-gradient(90deg,transparent_0,transparent_calc(100%-1px),rgba(102,124,255,.14)_calc(100%-1px)),linear-gradient(0deg,transparent_0,transparent_calc(100%-1px),rgba(102,124,255,.08)_calc(100%-1px))] [background-size:50px_50px] opacity-30" />
            <div className="relative grid min-h-[330px] gap-8 p-6 sm:p-9 lg:grid-cols-[minmax(0,1.25fr)_minmax(310px,.75fr)] lg:p-11">
              <div className="flex flex-col justify-between">
                <div>
                  <div className="flex items-center gap-2 font-mono text-[10px] uppercase tracking-[0.17em] text-[#aebcff]"><span className="size-1.5 bg-[#627dff] shadow-[0_0_12px_#4D67FF]" />System posture</div>
                  <h1 className="mt-5 max-w-2xl font-display text-[clamp(2.2rem,5vw,4.5rem)] font-bold leading-[0.94] tracking-[-0.075em] text-[#f2f4fb]">Reputation,<br /><span className="text-[#8ea2ff]">accounted for.</span></h1>
                  <p className="mt-5 max-w-xl text-sm leading-6 text-[#aab3c8]">A deliberate control surface for the Broomees User Relationship, Reputation &amp; Access-Control API. Connect your Laravel runtime to replace architectural placeholders with authoritative data.</p>
                </div>
                <div className="mt-8 flex flex-wrap items-center gap-3">
                  <Button onClick={connectApi} className="h-10 rounded-none bg-[#eff2ff] px-4 text-xs font-semibold text-[#11152a] hover:bg-white">Attach data source <ArrowRight className="ml-2 size-3.5" /></Button>
                  <button onClick={() => selectNav("API contract")} className="flex h-10 items-center gap-2 border border-white/[0.16] bg-black/10 px-3.5 text-xs font-medium text-[#dfe5f7] transition hover:border-[#7d90ff]/50 hover:bg-[#4D67FF]/10 active:scale-[0.98]"><Code2 className="size-3.5 text-[#91a4ff]" />Inspect API contract</button>
                </div>
              </div>
              <div className="self-end border border-white/[0.12] border-l-2 border-l-[#4D67FF]/70 bg-[#0d1119]/72 p-4 backdrop-blur-md lg:ml-auto lg:w-full lg:max-w-[390px]">
                <div className="flex items-start justify-between gap-3">
                  <div><p className="font-mono text-[9px] uppercase tracking-[0.16em] text-[#8490ad]">Runtime signal</p><p className="mt-2 font-display text-xl font-bold tracking-[-0.045em] text-[#f3f5fb]">Awaiting API source</p></div>
                  <span className="border border-[#FFCE85]/25 bg-[#FFB54A]/10 px-2 py-1 font-mono text-[9px] uppercase tracking-[.12em] text-[#ffd08f]">Unlinked</span>
                </div>
                <div className="mt-5 border-t border-dashed border-white/[0.12] pt-4">
                  <div className="flex items-center justify-between text-xs"><span className="text-[#8994aa]">Data authority</span><span className="font-mono text-[10px] text-[#c9d1e8]">Laravel + PostgreSQL</span></div>
                  <div className="mt-3 flex items-center justify-between text-xs"><span className="text-[#8994aa]">Rate limiter</span><span className="font-mono text-[10px] text-[#c9d1e8]">Redis / token-scoped</span></div>
                  <div className="mt-3 flex items-center justify-between text-xs"><span className="text-[#8994aa]">Reputation model</span><span className="font-mono text-[10px] text-[#c9d1e8]">Calculated, not guessed</span></div>
                </div>
                <div className="mt-4 flex items-center justify-between border-t border-[#4D67FF]/25 pt-3 font-mono text-[9px] uppercase tracking-[.1em]"><span className="flex items-center gap-1.5 text-[#aab8d7]"><span className="size-1.5 rounded-full bg-[#6c85ff] shadow-[0_0_8px_#4D67FF]" />Binding required</span><span className="text-[#71809d]">Checked / no source</span></div>
              </div>
            </div>
          </motion.section>

          <section className="relative grid border-x border-b border-white/[0.07] bg-[#0d1017]/80 sm:grid-cols-3">
            {[
              ["Contract scope", "11 required endpoints", Braces],
              ["Access model", "Expirable bearer tokens", ShieldCheck],
              ["Integrity model", "Transactions + unique constraints", GitBranch],
            ].map(([label, value, Icon], index) => {
              const SignalIcon = Icon as typeof Braces;
              return <div key={label as string} className={`flex items-center gap-3 px-5 py-4 ${index !== 2 ? "border-b border-white/[0.07] sm:border-b-0 sm:border-r" : ""}`}><span className="grid size-8 place-items-center border border-[#4D67FF]/20 bg-[#4D67FF]/8 text-[#91a3ff]"><SignalIcon className="size-3.5" /></span><div><p className="font-mono text-[9px] uppercase tracking-[.14em] text-[#737e98]">{label as string}</p><p className="mt-1 text-xs font-medium text-[#c8d0e3]">{value as string}</p></div></div>;
            })}
          </section>

          <section className="mt-8 grid gap-8 xl:grid-cols-[minmax(0,1.35fr)_360px]">
            <div>
              <div className="mb-4 flex items-end justify-between gap-4">
                <div><p className="section-kicker">Live data register</p><h2 className="mt-2 font-display text-2xl font-bold tracking-[-.055em] text-white">Metrics wait for authority.</h2></div>
                <span className="hidden font-mono text-[10px] uppercase tracking-[.12em] text-[#657089] sm:block">Source: not configured</span>
              </div>
              <div className="grid gap-3 sm:grid-cols-2 2xl:grid-cols-4">
                <MetricCard label="Total users" icon={UsersRound} description="Population count from the user resource." />
                <MetricCard label="Average reputation" icon={Gauge} description="Authoritative average of stored reputation scores." />
                <MetricCard label="Mutual links" icon={HeartHandshake} description="Two-way relationship pairs protected by transaction." />
                <MetricCard label="Blocked relationships" icon={ShieldCheck} description="Penalty input for the reputation algorithm." />
              </div>
            </div>
            <div className="relative overflow-hidden border border-white/[0.08] bg-[#11151f]">
              <img src="/manus-storage/broomees-relationship-detail_6ff04ee8.jpg" alt="Abstract mutual relationship visualization" className="absolute inset-0 size-full object-cover opacity-[0.32]" />
              <div className="absolute inset-0 bg-[linear-gradient(180deg,rgba(16,19,29,.55),rgba(13,16,24,.96))]" />
              <div className="relative flex h-full min-h-[260px] flex-col justify-between p-5">
                <div className="flex items-center justify-between"><span className="section-kicker">Reputation formula</span><HeartHandshake className="size-4 text-[#8ba0ff]" /></div>
                <div><p className="max-w-[280px] font-display text-[21px] font-bold leading-[1.02] tracking-[-.055em] text-white">A score that traces back to real relationships.</p><div className="mt-5 border-l border-[#4D67FF]/60 bg-black/25 px-3 py-2.5 font-mono text-[10px] leading-5 text-[#bdc8ef]">friends + (shared hobbies × 0.5)<br />+ min(age / 30, 3) − blocks</div></div>
                <div className="mt-5 border-t border-[#4D67FF]/25 pt-3"><div className="flex items-center justify-between font-mono text-[9px] uppercase tracking-[.11em] text-[#7886a8]"><span>Calculated after write</span><span>Authority / relational data</span></div><button onClick={() => toast.info("Formula documented", { description: "The dashboard is designed around the exact reputation calculation in your assignment." })} className="mt-3 flex items-center gap-2 text-left text-xs font-medium text-[#aab8ff] transition hover:text-white">Review calculation notes <ArrowRight className="size-3.5" /></button></div>
              </div>
            </div>
          </section>

          <section className="mt-8 grid gap-8 2xl:grid-cols-[minmax(0,1.12fr)_minmax(390px,.88fr)]">
            <div className="border border-white/[0.08] bg-[#10141d]/92 shadow-[0_16px_54px_rgba(0,0,0,.12)]">
              <div className="flex items-start justify-between gap-4 p-5 pb-4"><div><p className="section-kicker">Contract console</p><h2 className="mt-2 font-display text-xl font-bold tracking-[-.055em] text-white">The required surface area.</h2><p className="mt-1 text-xs text-[#77839d]">Endpoints listed in the engineering brief, ready to map to live requests.</p></div><button onClick={() => selectNav("API contract")} className="grid size-8 place-items-center border border-white/[0.08] text-[#92a0ba] transition hover:border-[#4D67FF]/50 hover:text-white"><MoreHorizontal className="size-4" /></button></div>
              <div className="border-b border-white/[0.08] px-5 pb-3"><div className="grid grid-cols-[58px_minmax(0,1fr)_auto] gap-3 font-mono text-[9px] uppercase tracking-[.13em] text-[#59647d] sm:grid-cols-[64px_minmax(0,1fr)_92px_auto]"><span>Verb</span><span>Route</span><span className="hidden text-right sm:block">Domain</span><span>State</span></div></div>
              <div>{endpointRows.map((row) => <EndpointRow key={row.route} {...row} />)}</div>
              <div className="flex flex-col gap-3 border-t border-white/[0.08] bg-white/[0.015] p-4 sm:flex-row sm:items-center sm:justify-between"><p className="text-xs leading-5 text-[#7c879f]">Swagger remains independent of this dashboard at <span className="font-mono text-[#b6c2ed]">/api/documentation</span>.</p><button onClick={copyContract} className="flex shrink-0 items-center gap-2 self-start border border-[#4D67FF]/25 bg-[#4D67FF]/8 px-3 py-2 font-mono text-[10px] uppercase tracking-[.08em] text-[#a8b7ff] transition hover:bg-[#4D67FF]/16 sm:self-auto">{hasCopied ? <Check className="size-3.5" /> : <Clipboard className="size-3.5" />}{hasCopied ? "Copied" : "Copy metrics route"}</button></div>
            </div>

            <div className="relative overflow-hidden border border-white/[0.08] bg-[#11151f]">
              <img src="/manus-storage/broomees-access-detail_b3efd446.jpg" alt="Abstract API access control tile" className="absolute inset-0 size-full object-cover opacity-[0.3]" />
              <div className="absolute inset-0 bg-[linear-gradient(180deg,rgba(15,18,27,.84),rgba(11,14,21,.98))]" />
              <div className="relative p-5"><p className="section-kicker">Engineering readiness</p><h2 className="mt-2 font-display text-xl font-bold tracking-[-.055em] text-white">Build out, not guesswork.</h2><p className="mt-1 max-w-md text-xs leading-5 text-[#8994aa]">The control surface exposes the core safeguards expected in the backend architecture without simulating data that does not yet exist.</p></div>
              <div className="relative border-t border-white/[0.08]">
                {readiness.map((item, index) => { const Icon = item.icon; return <div key={item.title} className={`grid grid-cols-[32px_minmax(0,1fr)_auto] gap-3 p-5 ${index < readiness.length - 1 ? "border-b border-dashed border-white/[0.1]" : ""}`}><span className="grid size-8 place-items-center border border-[#4D67FF]/20 bg-[#4D67FF]/8 text-[#91a4ff]"><Icon className="size-3.5" /></span><div><p className="text-sm font-medium text-[#dfe5f5]">{item.title}</p><p className="mt-1 text-xs leading-5 text-[#78839b]">{item.text}</p></div><span className="h-fit border border-white/[0.1] bg-black/15 px-1.5 py-1 font-mono text-[8px] uppercase tracking-[.12em] text-[#8d98b2]">{item.tag}</span></div>; })}
              </div>
            </div>
          </section>

          <section className="mt-8 grid gap-4 border-y border-white/[0.07] py-5 md:grid-cols-[1.3fr_1fr_1fr]">
            <div className="flex items-start gap-3"><div className="mt-0.5 grid size-8 shrink-0 place-items-center border border-[#4D67FF]/20 bg-[#4D67FF]/8"><Database className="size-3.5 text-[#9aabff]" /></div><div><p className="font-mono text-[9px] uppercase tracking-[.14em] text-[#73809b]">Suggested runtime</p><p className="mt-1 text-sm font-medium text-[#dbe2f6]">Laravel 12 · PostgreSQL · Redis</p></div></div>
            <div className="flex items-start gap-3 border-t border-white/[0.07] pt-4 md:border-l md:border-t-0 md:pl-5 md:pt-0"><div className="mt-0.5 grid size-8 shrink-0 place-items-center border border-[#4D67FF]/20 bg-[#4D67FF]/8"><TerminalSquare className="size-3.5 text-[#9aabff]" /></div><div><p className="font-mono text-[9px] uppercase tracking-[.14em] text-[#73809b]">Health endpoint</p><p className="mt-1 font-mono text-sm font-medium text-[#dbe2f6]">GET /health</p></div></div>
            <div className="flex items-start gap-3 border-t border-white/[0.07] pt-4 md:border-l md:border-t-0 md:pl-5 md:pt-0"><div className="mt-0.5 grid size-8 shrink-0 place-items-center border border-[#4D67FF]/20 bg-[#4D67FF]/8"><FileText className="size-3.5 text-[#9aabff]" /></div><div><p className="font-mono text-[9px] uppercase tracking-[.14em] text-[#73809b]">Build record</p><p className="mt-1 text-sm font-medium text-[#dbe2f6]">Frontend control surface</p></div></div>
          </section>

          <footer className="flex flex-col gap-3 pt-6 text-[11px] text-[#68738a] sm:flex-row sm:items-center sm:justify-between"><p>Broomees Control Center · static frontend workspace</p><div className="flex items-center gap-4"><button onClick={() => toast.info("Runbook", { description: "Add operational procedures alongside your Laravel deployment documentation." })} className="transition hover:text-[#c6d0f1]">Runbook</button><button onClick={() => toast.info("Security notes", { description: "Keep raw tokens, passwords, and internal traces out of the browser and logs." })} className="transition hover:text-[#c6d0f1]">Security notes</button><span className="font-mono text-[9px] uppercase tracking-[.13em] text-[#566078]">v0.1 / UI scaffold</span></div></footer>
        </div>
      </main>
    </div>
  );
}
