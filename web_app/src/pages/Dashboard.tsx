import React, { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import api from '../lib/api';
import { AreaChart, Area, XAxis, YAxis, ResponsiveContainer, Tooltip } from 'recharts';
import { 
  Calendar, CheckCircle, Clock, ChevronRight, Briefcase, 
  FolderOpen, Heart, Gift, Sparkles, Plus, X, User, Users
} from 'lucide-react';
import { useNavigate } from 'react-router-dom';

interface DashboardStats {
  leaves_left: number;
  allocated_leaves: number;
  today_status: string;
  clock_in_time: string | null;
  manager_name: string;
  announcements: Array<{
    id: string;
    title: string;
    content: string;
    created_at: string;
    creator?: { name: string };
  }>;
}

interface AdminDashboardData {
  stats: {
    total_employees: number;
    present_today: number;
    absent_today: number;
    pending_leaves: number;
    birthdays_this_month: number;
    monthly_payroll: number;
  };
  attendance_trends: Array<{ day: string; Present: number; Late: number }>;
  department_allocation: Array<{ name: string; count: number; percentage: number }>;
}

interface HrDashboardData {
  stats: {
    active_staff: number;
    new_joiners_month: number;
    birthdays_this_week: number;
    pending_leaves: number;
  };
  onboarding_queue: Array<{
    id: string;
    name: string;
    step: string;
    percent: number;
    designation?: string | null;
  }>;
  document_verification: Array<{
    id: string;
    name: string;
    doc: string;
    status: string;
  }>;
}

interface Appreciation {
  id: string;
  sender: string;
  receiver: string;
  message: string;
  theme: 'indigo' | 'rose' | 'emerald' | 'amber';
}

interface FeedItem {
  id: string;
  type: 'announcement' | 'birthday' | 'anniversary' | 'event' | 'wish';
  title: string;
  detail: string;
  date: string;
}

export const Dashboard: React.FC = () => {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [adminDashboard, setAdminDashboard] = useState<AdminDashboardData | null>(null);
  const [hrDashboard, setHrDashboard] = useState<HrDashboardData | null>(null);
  const [loading, setLoading] = useState(true);
  const roleNormalized = user?.role ? user.role.toLowerCase() : 'employee';

  // Engagement States
  const [appreciationModalOpen, setAppreciationModalOpen] = useState(false);
  const [birthdayModalOpen, setBirthdayModalOpen] = useState(false);
  const [activeBirthdayPerson, setActiveBirthdayPerson] = useState<{ name: string; role: string; dept: string; date: string } | null>(null);

  // Appreciations state
  const [appreciations, setAppreciations] = useState<Appreciation[]>(() => {
    const cached = localStorage.getItem('peer_appreciations');
    return cached ? JSON.parse(cached) : [
      { id: '1', sender: 'Sarah HR', receiver: 'John Employee', message: 'Outstanding work on delivering the react front end dashboard with premium glassmorphism aesthetics!', theme: 'indigo' },
      { id: '2', sender: 'Sarah Manager', receiver: 'Alex Developer', message: 'Huge thanks for the database schema fixes. The migration now runs flawlessly in all local containers.', theme: 'emerald' }
    ];
  });

  // Feed items state
  const [feedItems, setFeedItems] = useState<FeedItem[]>(() => {
    const cached = localStorage.getItem('company_feed');
    return cached ? JSON.parse(cached) : [
      { id: 'f1', type: 'announcement', title: 'System Migration Successful', detail: 'All databases have been successfully upgraded to support global tenant-isolation schemas.', date: 'Today' },
      { id: 'f2', type: 'birthday', title: 'Sarah HR\'s Birthday', detail: 'Sarah is celebrating her birthday today! Send her your best wishes.', date: 'Today' },
      { id: 'f3', type: 'anniversary', title: 'John Employee\'s Work Anniversary', detail: 'John is celebrating 2 years of building outstanding systems at HumaNode!', date: 'Yesterday' },
      { id: 'f4', type: 'event', title: 'Q2 Company Townhall', detail: 'Townhall is scheduled on Friday at 10:00 AM UTC. Please prepare your Q&A points.', date: 'In 3 Days' }
    ];
  });

  // Peer Appreciation Form State
  const [apprecReceiver, setApprecReceiver] = useState('');
  const [apprecMessage, setApprecMessage] = useState('');
  const [apprecTheme, setApprecTheme] = useState<'indigo' | 'rose' | 'emerald' | 'amber'>('indigo');

  // Birthday wish Form State
  const [, setWishTemplate] = useState('');
  const [wishReactions, setWishReactions] = useState<string[]>([]);
  const [customWishText, setCustomWishText] = useState('');

  useEffect(() => {
    localStorage.setItem('peer_appreciations', JSON.stringify(appreciations));
  }, [appreciations]);

  useEffect(() => {
    localStorage.setItem('company_feed', JSON.stringify(feedItems));
  }, [feedItems]);

  useEffect(() => {
    const fetchDashboardStats = async () => {
      try {
        if (roleNormalized === 'admin' || roleNormalized === 'super admin') {
          const res = await api.get('/dashboard/admin');
          setAdminDashboard(res.data.data);
          return;
        }

        if (roleNormalized === 'hr') {
          const res = await api.get('/dashboard/hr');
          setHrDashboard(res.data.data);
          return;
        }

        const res = await api.get('/ess/dashboard');
        setStats(res.data.data);
      } catch {
        await Promise.resolve();
        if (roleNormalized !== 'admin' && roleNormalized !== 'super admin' && roleNormalized !== 'hr') {
          setStats({
            leaves_left: 14,
            allocated_leaves: 24,
            today_status: 'Present',
            clock_in_time: '09:05',
            manager_name: user?.employee?.manager?.name || 'Sarah Manager',
            announcements: [
              {
                id: '1',
                title: 'Annual Hackathon 2026',
                content: 'Get ready for HumaNode annual Hackathon next week! Register your team of 4 on the HR portal before Friday.',
                created_at: '2026-06-16T10:00:00Z',
                creator: { name: 'Sarah HR' }
              },
              {
                id: '2',
                title: 'New Geofence Attendance Rules',
                content: 'Please ensure you are within 100 meters of the office coordinates when using the mobile or web clock-in checks.',
                created_at: '2026-06-15T08:00:00Z',
                creator: { name: 'System Admin' }
              }
            ]
          });
        }
      } finally {
        setLoading(false);
      }
    };
    fetchDashboardStats();
  }, [roleNormalized, user]);

  const triggerConfetti = async () => {
    const { default: confetti } = await import('canvas-confetti');
    confetti({
      particleCount: 120,
      spread: 80,
      origin: { y: 0.6 }
    });
  };

  const handleOpenBirthdayModal = (person: { name: string; role: string; dept: string; date: string }) => {
    setActiveBirthdayPerson(person);
    setWishTemplate('');
    setWishReactions([]);
    setCustomWishText('');
    setBirthdayModalOpen(true);
  };

  const handleWishTemplateSelect = (val: string) => {
    setWishTemplate(val);
    setCustomWishText(prev => {
      const trimmed = prev.trim();
      return trimmed ? `${trimmed} ${val}` : val;
    });
  };

  const handleToggleReaction = (emoji: string) => {
    setWishReactions(prev => 
      prev.includes(emoji) ? prev.filter(r => r !== emoji) : [...prev, emoji]
    );
  };

  const handleSendBirthdayWish = (e: React.FormEvent) => {
    e.preventDefault();
    if (!activeBirthdayPerson) return;

    const emojiString = wishReactions.join(' ');
    const wishDetail = `${user?.name || 'Someone'} wished ${activeBirthdayPerson.name}: "${customWishText || 'Happy Birthday!'}" ${emojiString}`;
    
    // Add to social feed
    const newFeedItem: FeedItem = {
      id: 'wish-' + Date.now(),
      type: 'wish',
      title: `Birthday wish for ${activeBirthdayPerson.name}`,
      detail: wishDetail,
      date: 'Just Now'
    };

    setFeedItems(prev => [newFeedItem, ...prev]);
    setBirthdayModalOpen(false);
    triggerConfetti();
  };

  const handleSendAppreciation = (e: React.FormEvent) => {
    e.preventDefault();
    if (!apprecReceiver || !apprecMessage) return;

    const newAppreciation: Appreciation = {
      id: 'apprec-' + Date.now(),
      sender: user?.name || 'Admin',
      receiver: apprecReceiver,
      message: apprecMessage,
      theme: apprecTheme
    };

    setAppreciations(prev => [newAppreciation, ...prev]);
    
    // Add to social feed
    const newFeedItem: FeedItem = {
      id: 'feed-apprec-' + Date.now(),
      type: 'anniversary',
      title: `${newAppreciation.sender} appreciated ${newAppreciation.receiver}!`,
      detail: `"${newAppreciation.message}"`,
      date: 'Just Now'
    };
    
    setFeedItems(prev => [newFeedItem, ...prev]);
    setAppreciationModalOpen(false);
    setApprecReceiver('');
    setApprecMessage('');
    triggerConfetti();
  };

  const fallbackAttendanceChartData = [
    { day: 'Mon', Present: 94, Late: 5 },
    { day: 'Tue', Present: 96, Late: 3 },
    { day: 'Wed', Present: 93, Late: 6 },
    { day: 'Thu', Present: 97, Late: 2 },
    { day: 'Fri', Present: 95, Late: 4 },
  ];

  if (loading) {
    return <div className="text-center py-12 text-slate-400 text-sm">Loading dashboard metrics...</div>;
  }

  const attendanceChartData = adminDashboard?.attendance_trends?.length
    ? adminDashboard.attendance_trends
    : fallbackAttendanceChartData;
  const departmentColors = ['bg-primary-500', 'bg-secondary-500', 'bg-warning', 'bg-indigo-500', 'bg-success', 'bg-danger'];
  const formatCurrency = (amount: number) => new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 0,
  }).format(amount);
  const currentMonthLabel = new Intl.DateTimeFormat('en-US', { month: 'long' }).format(new Date());

  // -------------------------------------------------------------
  // VIEW RENDER: ADMIN DASHBOARD
  // -------------------------------------------------------------
  if (roleNormalized === 'admin' || roleNormalized === 'super admin') {
    return (
      <div className="space-y-8">
        <div>
          <h1 className="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100">
            System Administration Overview
          </h1>
          <p className="text-xs text-slate-500 mt-1">
            Enterprise analytics, company-wide payroll projections, and structural parameters.
          </p>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6">
          <div className="glass-panel p-5 text-center">
            <Users className="mx-auto text-primary-500 mb-2" size={24} />
            <p className="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Total Employees</p>
            <h3 className="text-2xl font-black mt-1 text-slate-800 dark:text-slate-200">{adminDashboard?.stats.total_employees ?? 0}</h3>
          </div>
          <div className="glass-panel p-5 text-center">
            <CheckCircle className="mx-auto text-success mb-2" size={24} />
            <p className="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Present Today</p>
            <h3 className="text-2xl font-black mt-1 text-success">{adminDashboard?.stats.present_today ?? 0}</h3>
          </div>
          <div className="glass-panel p-5 text-center">
            <Clock className="mx-auto text-danger mb-2" size={24} />
            <p className="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Absent Today</p>
            <h3 className="text-2xl font-black mt-1 text-danger">{adminDashboard?.stats.absent_today ?? 0}</h3>
          </div>
          <div className="glass-panel p-5 text-center">
            <Calendar className="mx-auto text-warning mb-2" size={24} />
            <p className="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Leaves Pending</p>
            <h3 className="text-2xl font-black mt-1 text-warning">{adminDashboard?.stats.pending_leaves ?? 0}</h3>
          </div>
          <div className="glass-panel p-5 text-center">
            <Gift className="mx-auto text-secondary-500 mb-2" size={24} />
            <p className="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Birthdays ({currentMonthLabel})</p>
            <h3 className="text-2xl font-black mt-1 text-secondary-500">{adminDashboard?.stats.birthdays_this_month ?? 0}</h3>
          </div>
          <div className="glass-panel p-5 text-center">
            <Briefcase className="mx-auto text-indigo-500 mb-2" size={24} />
            <p className="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Monthly Payroll</p>
            <h3 className="text-xl font-black mt-1.5 text-slate-800 dark:text-slate-200">{formatCurrency(adminDashboard?.stats.monthly_payroll ?? 0)}</h3>
          </div>
        </div>

        {/* Charts & Split */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <div className="lg:col-span-2 glass-panel p-6">
            <h3 className="text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 border-b border-slate-200 dark:border-slate-800/80 pb-3 mb-6">
              Global Attendance trends
            </h3>
            <div className="h-64">
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={attendanceChartData}>
                  <XAxis dataKey="day" stroke="#64748b" fontSize={10} tickLine={false} />
                  <YAxis stroke="#64748b" fontSize={10} tickLine={false} />
                  <Tooltip contentStyle={{ background: '#0f172a', borderColor: '#334155', borderRadius: '12px' }} />
                  <Area type="monotone" dataKey="Present" stroke="#00e5ff" fill="url(#colorCyan)" strokeWidth={2} />
                </AreaChart>
              </ResponsiveContainer>
            </div>
          </div>

          <div className="glass-panel p-6 space-y-6">
            <h3 className="text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 border-b border-slate-200 dark:border-slate-800/80 pb-3">
              Department Allocation
            </h3>
            <div className="space-y-4">
              {(adminDashboard?.department_allocation ?? []).map((d, index) => (
                <div key={d.name} className="flex justify-between items-center text-xs">
                  <div className="flex items-center gap-2">
                    <span className={`w-3 h-3 rounded-full ${departmentColors[index % departmentColors.length]}`}></span>
                    <span className="text-slate-700 dark:text-slate-300 font-semibold">{d.name}</span>
                  </div>
                  <span className="font-bold text-slate-800 dark:text-slate-200">{d.count} ({d.percentage}%)</span>
                </div>
              ))}
              {(!adminDashboard?.department_allocation?.length) && (
                <p className="text-xs text-slate-500 italic">No department allocation data yet.</p>
              )}
            </div>
          </div>
        </div>
      </div>
    );
  }

  // -------------------------------------------------------------
  // VIEW RENDER: HR DASHBOARD
  // -------------------------------------------------------------
  if (roleNormalized === 'hr') {
    return (
      <div className="space-y-8">
        <div>
          <h1 className="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100">
            HR Operational Center
          </h1>
          <p className="text-xs text-slate-500 mt-1">
            Talent acquisition trackers, document checking queues, and onboarding parameters.
          </p>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div className="glass-panel p-6 flex items-center gap-4">
            <Users className="text-primary-500" size={28} />
            <div>
              <p className="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Active Staff</p>
              <h3 className="text-xl font-bold mt-0.5 text-slate-800 dark:text-slate-100">{hrDashboard?.stats.active_staff ?? 0} Employees</h3>
            </div>
          </div>
          <div className="glass-panel p-6 flex items-center gap-4">
            <User className="text-success" size={28} />
            <div>
              <p className="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">New Joiners (Month)</p>
              <h3 className="text-xl font-bold mt-0.5 text-slate-800 dark:text-slate-100">{hrDashboard?.stats.new_joiners_month ?? 0} Employees</h3>
            </div>
          </div>
          <div className="glass-panel p-6 flex items-center gap-4">
            <Gift className="text-secondary-500" size={28} />
            <div>
              <p className="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Birthdays This Week</p>
              <h3 className="text-xl font-bold mt-0.5 text-slate-800 dark:text-slate-100">{hrDashboard?.stats.birthdays_this_week ?? 0} Upcoming</h3>
            </div>
          </div>
          <div className="glass-panel p-6 flex items-center gap-4">
            <Calendar className="text-warning" size={28} />
            <div>
              <p className="text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Pending Leaves</p>
              <h3 className="text-xl font-bold mt-0.5 text-slate-800 dark:text-slate-100">{hrDashboard?.stats.pending_leaves ?? 0} Requests</h3>
            </div>
          </div>
        </div>

        {/* Action Blocks */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div className="glass-panel p-6 space-y-4">
            <h3 className="text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 border-b border-slate-200 dark:border-slate-800/80 pb-3">
              Onboarding Checklist Queue
            </h3>
            <div className="space-y-3">
              {(hrDashboard?.onboarding_queue ?? []).map(h => (
                <div key={h.id} className="p-3 bg-slate-100/40 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800/60 rounded-xl text-xs flex justify-between items-center">
                  <div>
                    <p className="font-bold text-slate-800 dark:text-slate-200">{h.name}</p>
                    <p className="text-[10px] text-slate-400 mt-0.5">{h.step}</p>
                  </div>
                  <span className="px-2 py-0.5 bg-primary-500/10 text-primary-500 rounded-lg text-[10px] font-bold">{h.percent}% Completed</span>
                </div>
              ))}
              {(!hrDashboard?.onboarding_queue?.length) && (
                <p className="text-xs text-slate-500 italic">No active onboarding tasks.</p>
              )}
            </div>
          </div>

          <div className="glass-panel p-6 space-y-4">
            <h3 className="text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 border-b border-slate-200 dark:border-slate-800/80 pb-3">
              Required Document Verification
            </h3>
            <div className="space-y-3">
              {(hrDashboard?.document_verification ?? []).map(d => (
                <div key={d.id} className="p-3 bg-slate-100/40 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800/60 rounded-xl text-xs flex justify-between items-center">
                  <div>
                    <p className="font-bold text-slate-800 dark:text-slate-200">{d.name}</p>
                    <p className="text-[10px] text-slate-400 mt-0.5">{d.doc}</p>
                  </div>
                  <span className={`px-2 py-0.5 rounded-lg text-[9px] font-bold ${
                    d.status === 'Verified' ? 'bg-success/15 text-success' : 'bg-warning/15 text-warning'
                  }`}>{d.status}</span>
                </div>
              ))}
              {(!hrDashboard?.document_verification?.length) && (
                <p className="text-xs text-slate-500 italic">No documents waiting for review.</p>
              )}
            </div>
          </div>
        </div>
      </div>
    );
  }

  // -------------------------------------------------------------
  // VIEW RENDER: MANAGER DASHBOARD
  // -------------------------------------------------------------
  if (roleNormalized === 'manager') {
    return (
      <div className="space-y-8">
        <div>
          <h1 className="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100">
            Manager Control Dashboard
          </h1>
          <p className="text-xs text-slate-500 mt-1">
            Overview of direct reports, shifts, calendar schedules, and approvals.
          </p>
        </div>

        {/* Quick action wrapper to route directly to existing manager view */}
        <div className="glass-panel p-6 flex justify-between items-center">
          <div>
            <h3 className="font-bold text-sm">Team Roster, Approvals & Timesheets</h3>
            <p className="text-xs text-slate-400 mt-0.5">Manage details for John Employee and other team members.</p>
          </div>
          <button onClick={() => navigate('/manager/dashboard')} className="px-4 py-2.5 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl text-xs uppercase tracking-wide btn-glow cursor-pointer">
            Open Team Portal
          </button>
        </div>
      </div>
    );
  }

  // -------------------------------------------------------------
  // VIEW RENDER: EMPLOYEE DASHBOARD
  // -------------------------------------------------------------
  return (
    <div className="space-y-8 max-w-7xl mx-auto">
      {/* Welcome banner */}
      <div>
        <h1 className="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100 animate-fade-in">
          Welcome, <span className="bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent">{user?.name}!</span>
        </h1>
        <p className="text-xs text-slate-500 mt-1">
          Here is your dashboard overview for today, <span className="font-semibold text-slate-700 dark:text-slate-300">{new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</span>.
        </p>
      </div>

      {/* Stats Cards Row */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {/* Leaves Card */}
        <div className="glass-panel p-6 flex items-center gap-5 hover:scale-[1.01] transition-transform duration-300">
          <div className="w-14 h-14 rounded-2xl bg-primary-500/10 border border-primary-500/20 flex items-center justify-center text-primary-500">
            <Calendar size={24} />
          </div>
          <div>
            <p className="text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase">Leaves Remaining</p>
            <p className="text-xl font-bold text-slate-800 dark:text-slate-100 mt-1 animate-pulse">
              <span className="text-primary-500">{stats?.leaves_left}</span> / {stats?.allocated_leaves} Days
            </p>
          </div>
        </div>

        {/* Attendance Card */}
        <div className="glass-panel p-6 flex items-center gap-5 hover:scale-[1.01] transition-transform duration-300">
          <div className={`w-14 h-14 rounded-2xl flex items-center justify-center text-2xl ${
            stats?.today_status === 'Present' || stats?.today_status === 'Late' 
              ? 'bg-success/10 text-success border border-success/20' 
              : 'bg-danger/10 text-danger border border-danger/20'
          }`}>
            <CheckCircle size={24} />
          </div>
          <div>
            <p className="text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase">Today's Attendance</p>
            <p className={`text-xl font-bold mt-1 ${
              stats?.today_status === 'Present' || stats?.today_status === 'Late' ? 'text-success' : 'text-danger'
            }`}>
              {stats?.today_status} 
              {stats?.clock_in_time && (
                <span className="text-xs font-normal text-slate-400 ml-1">({stats.clock_in_time})</span>
              )}
            </p>
          </div>
        </div>

        {/* Manager Card */}
        <div className="glass-panel p-6 flex items-center gap-5 hover:scale-[1.01] transition-transform duration-300">
          <div className="w-14 h-14 rounded-2xl bg-warning/10 border border-warning/20 flex items-center justify-center text-warning">
            <Briefcase size={24} />
          </div>
          <div>
            <p className="text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase">Direct Manager</p>
            <p className="text-base font-bold text-slate-800 dark:text-slate-100 mt-1 truncate max-w-[170px]" title={stats?.manager_name}>
              {stats?.manager_name}
            </p>
          </div>
        </div>
      </div>

      {/* Main split */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {/* Left and Middle Columns (col-span-8) */}
        <div className="lg:col-span-8 space-y-8">
          
          {/* Company Feed / Engagement Wall */}
          <div className="glass-panel p-6 space-y-6">
            <h3 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800/40 pb-4 flex items-center gap-2">
              <Sparkles size={16} className="text-secondary-500 animate-spin" /> Company Engagement Feed
            </h3>

            <div className="space-y-4 max-h-[450px] overflow-y-auto pr-1">
              {feedItems.map((item) => (
                <div key={item.id} className="p-4 bg-slate-100/40 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800/60 rounded-2xl text-xs space-y-2">
                  <div className="flex justify-between items-center">
                    <span className={`px-2.5 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider ${
                      item.type === 'announcement' ? 'bg-indigo-500/10 text-indigo-500' :
                      item.type === 'birthday' ? 'bg-secondary-500/10 text-secondary-500' :
                      item.type === 'anniversary' ? 'bg-success/10 text-success' : 
                      item.type === 'wish' ? 'bg-rose-500/10 text-rose-500' : 'bg-warning/10 text-warning'
                    }`}>
                      {item.type}
                    </span>
                    <span className="text-[10px] text-slate-400 font-semibold">{item.date}</span>
                  </div>
                  <div>
                    <h4 className="font-bold text-sm text-slate-800 dark:text-slate-100">{item.title}</h4>
                    <p className="text-slate-600 dark:text-slate-400 leading-relaxed mt-1 whitespace-pre-line">{item.detail}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Peer Appreciation Wall */}
          <div className="glass-panel p-6 space-y-6">
            <div className="flex justify-between items-center border-b border-slate-200 dark:border-slate-800/40 pb-4">
              <h3 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest flex items-center gap-2">
                <Heart size={16} className="text-rose-500 animate-pulse" /> Peer Appreciation Wall
              </h3>
              <button 
                onClick={() => setAppreciationModalOpen(true)}
                className="px-2.5 py-1.5 bg-rose-500 hover:bg-rose-400 text-white font-bold rounded-lg text-[10px] flex items-center gap-1.5 cursor-pointer shadow-lg shadow-rose-500/15"
              >
                <Plus size={12} /> Send Thanks
              </button>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {appreciations.map((apprec) => {
                const colors = 
                  apprec.theme === 'rose' ? 'from-rose-500/10 to-danger/10 border-rose-500/20 text-rose-400' :
                  apprec.theme === 'emerald' ? 'from-success/10 to-primary-500/10 border-success/20 text-success' :
                  apprec.theme === 'amber' ? 'from-warning/10 to-secondary-500/10 border-warning/20 text-warning' :
                  'from-primary-500/10 to-indigo-500/10 border-primary-500/20 text-primary-400';
                
                return (
                  <div key={apprec.id} className={`p-5 bg-gradient-to-tr ${colors} border rounded-2xl flex flex-col justify-between space-y-4 hover:scale-[1.02] transition-all duration-300`}>
                    <div>
                      <p className="text-xs italic leading-relaxed text-slate-800 dark:text-slate-200">
                        "{apprec.message}"
                      </p>
                    </div>
                    <div className="flex justify-between items-center text-[10px] border-t border-slate-200/40 dark:border-slate-800/40 pt-3">
                      <span className="font-bold text-slate-500">From: <strong className="text-slate-700 dark:text-slate-300">{apprec.sender}</strong></span>
                      <span className="font-bold text-slate-500">To: <strong className="text-slate-700 dark:text-slate-300">{apprec.receiver}</strong></span>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>

        </div>

        {/* Right Column: Actions, Holidays, Birthdays (col-span-4) */}
        <div className="lg:col-span-4 space-y-8">
          
          {/* Quick Actions */}
          <div className="glass-panel p-6">
            <h3 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest mb-4 border-b border-slate-200 dark:border-slate-800/80 pb-2">My Operations</h3>
            <div className="space-y-3">
              <button onClick={() => navigate('/attendance')} className="w-full flex items-center justify-between p-3.5 bg-slate-100/50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800/80 rounded-xl hover:border-primary-500 hover:scale-[1.01] transition-all duration-300 group cursor-pointer">
                <div className="flex items-center gap-3">
                  <span className="w-8 h-8 rounded-lg bg-primary-500/10 text-primary-500 flex items-center justify-center group-hover:bg-primary-500 group-hover:text-slate-950 transition-colors"><Clock size={16} /></span>
                  <span className="text-xs font-bold text-slate-700 dark:text-slate-300">Clock In / Out</span>
                </div>
                <ChevronRight size={14} className="text-slate-400 group-hover:translate-x-1 transition-transform" />
              </button>
              <button onClick={() => navigate('/leaves')} className="w-full flex items-center justify-between p-3.5 bg-slate-100/50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800/80 rounded-xl hover:border-primary-500 hover:scale-[1.01] transition-all duration-300 group group-hover:border-primary-500 group cursor-pointer">
                <div className="flex items-center gap-3">
                  <span className="w-8 h-8 rounded-lg bg-primary-500/10 text-primary-500 flex items-center justify-center group-hover:bg-primary-500 group-hover:text-slate-950 transition-colors"><Calendar size={16} /></span>
                  <span className="text-xs font-bold text-slate-700 dark:text-slate-300">Apply for Leave</span>
                </div>
                <ChevronRight size={14} className="text-slate-400 group-hover:translate-x-1 transition-transform" />
              </button>
              <button onClick={() => navigate('/documents')} className="w-full flex items-center justify-between p-3.5 bg-slate-100/50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800/80 rounded-xl hover:border-primary-500 hover:scale-[1.01] transition-all duration-300 group cursor-pointer">
                <div className="flex items-center gap-3">
                  <span className="w-8 h-8 rounded-lg bg-primary-500/10 text-primary-500 flex items-center justify-center group-hover:bg-primary-500 group-hover:text-slate-950 transition-colors"><FolderOpen size={16} /></span>
                  <span className="text-xs font-bold text-slate-700 dark:text-slate-300">My Document Locker</span>
                </div>
                <ChevronRight size={14} className="text-slate-400 group-hover:translate-x-1 transition-transform" />
              </button>
            </div>
          </div>

          {/* Upcoming Birthdays with wishes modal trigger */}
          <div className="glass-panel p-6">
            <h3 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest mb-4 border-b border-slate-200 dark:border-slate-800/80 pb-2">Upcoming Birthdays</h3>
            <div className="space-y-4">
              {[
                { name: 'Sarah HR', role: 'HR Director', dept: 'Human Resources', date: 'June 15' },
                { name: 'Alex Developer', role: 'Lead Architect', dept: 'Software Engineering', date: 'June 22' }
              ].map((person) => (
                <div key={person.name} className="flex items-center justify-between">
                  <div className="flex items-center gap-3">
                    <span className="text-xl">🎂</span>
                    <div>
                      <p className="text-xs font-bold text-slate-800 dark:text-slate-200">{person.name}</p>
                      <p className="text-[10px] text-slate-400 mt-0.5">{person.role} • {person.date}</p>
                    </div>
                  </div>
                  <button 
                    onClick={() => handleOpenBirthdayModal(person)} 
                    className="text-[10px] px-2.5 py-1 bg-secondary-500/15 hover:bg-secondary-500/35 text-secondary-500 rounded-lg hover:scale-[1.02] active:scale-95 transition-all font-semibold cursor-pointer"
                  >
                    Wish 🍦
                  </button>
                </div>
              ))}
            </div>
          </div>

          {/* Holiday Calendar */}
          <div className="glass-panel p-6">
            <h3 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest mb-4 border-b border-slate-200 dark:border-slate-800/80 pb-2">Holiday Calendar</h3>
            <div className="space-y-3.5">
              {[
                { name: 'Independence Day', date: 'July 4', type: 'Federal' },
                { name: 'Labor Day', date: 'September 7', type: 'Corporate' },
                { name: 'Thanksgiving Day', date: 'November 26', type: 'Federal' }
              ].map((holiday) => (
                <div key={holiday.name} className="flex justify-between items-center text-xs">
                  <div>
                    <p className="font-bold text-slate-800 dark:text-slate-200">{holiday.name}</p>
                    <p className="text-[10px] text-slate-400 mt-0.5">{holiday.type}</p>
                  </div>
                  <span className="font-black text-primary-500">{holiday.date}</span>
                </div>
              ))}
            </div>
          </div>

        </div>

      </div>

      {/* Birthday Wishes Modal */}
      {birthdayModalOpen && activeBirthdayPerson && (
        <div className="fixed inset-0 z-50 overflow-y-auto">
          <div className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" onClick={() => setBirthdayModalOpen(false)}></div>
          <div className="flex min-h-full items-center justify-center p-4">
            <div className="relative transform overflow-hidden rounded-2xl bg-slate-900 border border-slate-800 p-6 shadow-2xl transition-all w-full max-w-md">
              <div className="flex justify-between items-center pb-3 border-b border-slate-800 mb-4">
                <h3 className="font-bold text-slate-100 text-sm flex items-center gap-1.5"><Gift size={16} className="text-rose-400" /> Send Birthday Wish</h3>
                <button onClick={() => setBirthdayModalOpen(false)} className="text-slate-400 hover:text-slate-200 cursor-pointer">
                  <X size={18} />
                </button>
              </div>

              {/* Profile Card Summary */}
              <div className="flex items-center gap-3.5 p-3.5 bg-slate-950/40 border border-slate-800 rounded-xl mb-4">
                <div className="w-12 h-12 rounded-xl bg-gradient-to-tr from-secondary-500 to-rose-500 flex items-center justify-center font-bold text-slate-950">
                  {activeBirthdayPerson.name[0]}
                </div>
                <div>
                  <h4 className="font-bold text-xs text-slate-200">{activeBirthdayPerson.name}</h4>
                  <p className="text-[10px] text-slate-400 mt-0.5">{activeBirthdayPerson.role} • {activeBirthdayPerson.dept}</p>
                  <p className="text-[9px] text-primary-500 mt-0.5">Birthday: {activeBirthdayPerson.date}</p>
                </div>
              </div>

              <form onSubmit={handleSendBirthdayWish} className="space-y-4">
                {/* Wish Template Selection Tags */}
                <div className="space-y-1">
                  <label className="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Wish Options</label>
                  <div className="flex flex-wrap gap-1.5 mt-1">
                    {[
                      '🎉 Happy Birthday!',
                      '🎂 Many Happy Returns!',
                      '🎈 Have a Great Year Ahead!',
                      '🌟 Best Wishes!'
                    ].map(tag => (
                      <button 
                        type="button" 
                        key={tag} 
                        onClick={() => handleWishTemplateSelect(tag)}
                        className="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg text-[10px] font-semibold transition-all border border-slate-700 cursor-pointer"
                      >
                        {tag}
                      </button>
                    ))}
                  </div>
                </div>

                {/* Quick Emoji Reactions */}
                <div className="space-y-1">
                  <label className="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Quick Reactions</label>
                  <div className="flex gap-2.5 mt-1.5">
                    {['❤️', '🎉', '🎂', '👏', '🌟'].map(emoji => {
                      const selected = wishReactions.includes(emoji);
                      return (
                        <button 
                          type="button" 
                          key={emoji} 
                          onClick={() => handleToggleReaction(emoji)}
                          className={`w-9 h-9 rounded-xl flex items-center justify-center text-sm border transition-all ${
                            selected 
                              ? 'bg-secondary-500/20 border-secondary-500 scale-105' 
                              : 'bg-slate-950/40 border-slate-800 hover:border-slate-700'
                          } cursor-pointer`}
                        >
                          {emoji}
                        </button>
                      );
                    })}
                  </div>
                </div>

                {/* Custom message text area */}
                <div className="space-y-1">
                  <label className="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Custom Message</label>
                  <textarea 
                    value={customWishText}
                    onChange={(e) => setCustomWishText(e.target.value)}
                    className="glass-input text-xs resize-none"
                    rows={3}
                    placeholder="Type your special birthday message..."
                    required
                  ></textarea>
                </div>

                <div className="flex gap-3 pt-3 border-t border-slate-800">
                  <button type="button" onClick={() => setBirthdayModalOpen(false)} className="flex-1 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-xl text-xs cursor-pointer">
                    Cancel
                  </button>
                  <button type="submit" className="flex-1 py-2 bg-secondary-500 hover:bg-secondary-400 text-slate-950 font-bold rounded-xl text-xs btn-glow cursor-pointer">
                    Send Wishes 🍦
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      )}

      {/* Peer Appreciation Modal */}
      {appreciationModalOpen && (
        <div className="fixed inset-0 z-50 overflow-y-auto">
          <div className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" onClick={() => setAppreciationModalOpen(false)}></div>
          <div className="flex min-h-full items-center justify-center p-4">
            <div className="relative transform overflow-hidden rounded-2xl bg-slate-900 border border-slate-800 p-6 shadow-2xl transition-all w-full max-w-md">
              <div className="flex justify-between items-center pb-3 border-b border-slate-800 mb-4">
                <h3 className="font-bold text-slate-100 text-sm flex items-center gap-1.5"><Heart size={16} className="text-rose-500" /> Write Peer Appreciation Card</h3>
                <button onClick={() => setAppreciationModalOpen(false)} className="text-slate-400 hover:text-slate-200 cursor-pointer">
                  <X size={18} />
                </button>
              </div>

              <form onSubmit={handleSendAppreciation} className="space-y-4">
                <div className="space-y-1">
                  <label className="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Appreciate Colleague</label>
                  <select value={apprecReceiver} onChange={(e) => setApprecReceiver(e.target.value)} className="glass-input text-xs" required>
                    <option value="" disabled>Select Colleague</option>
                    <option value="Sarah HR">Sarah HR</option>
                    <option value="Sarah Manager">Sarah Manager</option>
                    <option value="John Employee">John Employee</option>
                    <option value="Alex Developer">Alex Developer</option>
                  </select>
                </div>

                <div className="space-y-1">
                  <label className="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Card Style Theme</label>
                  <div className="grid grid-cols-4 gap-2 mt-1">
                    {[
                      { theme: 'indigo', label: 'Indigo' },
                      { theme: 'rose', label: 'Rose' },
                      { theme: 'emerald', label: 'Emerald' },
                      { theme: 'amber', label: 'Amber' }
                    ].map(item => (
                      <button
                        type="button"
                        key={item.theme}
                        onClick={() => setApprecTheme(item.theme as any)}
                        className={`py-1.5 rounded-lg text-[9px] font-bold border transition-all ${
                          apprecTheme === item.theme 
                            ? 'bg-primary-500/10 border-primary-500 text-primary-500' 
                            : 'bg-slate-950/40 border-slate-800 text-slate-400 hover:border-slate-700'
                        } cursor-pointer`}
                      >
                        {item.label}
                      </button>
                    ))}
                  </div>
                </div>

                <div className="space-y-1">
                  <label className="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Thank You Message</label>
                  <textarea
                    value={apprecMessage}
                    onChange={(e) => setApprecMessage(e.target.value)}
                    className="glass-input text-xs resize-none"
                    rows={4}
                    placeholder="Write your thank you card note to your peer..."
                    required
                  ></textarea>
                </div>

                <div className="flex gap-4 pt-3 border-t border-slate-800">
                  <button type="button" onClick={() => setAppreciationModalOpen(false)} className="flex-1 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-xl text-xs cursor-pointer">
                    Cancel
                  </button>
                  <button type="submit" className="flex-1 py-2 bg-rose-500 hover:bg-rose-400 text-white font-bold rounded-xl text-xs btn-glow cursor-pointer">
                    Send Card ❤️
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      )}

    </div>
  );
};

export default Dashboard;
