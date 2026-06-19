import React, { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import api from '../lib/api';
import { AreaChart, Area, XAxis, YAxis, ResponsiveContainer, Tooltip } from 'recharts';
import { Calendar, CheckCircle, Clock, Megaphone, ChevronRight, Briefcase, FolderOpen } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import Employees from './Employees';

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

export const Dashboard: React.FC = () => {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchDashboardStats = async () => {
      try {
        const res = await api.get('/dashboard/stats');
        setStats(res.data.data);
      } catch {
        // Fallback static mock data for initial load/testing
        setStats({
          leaves_left: 14,
          allocated_leaves: 24,
          today_status: 'Present',
          clock_in_time: '09:05',
          manager_name: user?.employee?.manager?.name || 'Sarah HR',
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
      } finally {
        setLoading(false);
      }
    };
    fetchDashboardStats();
  }, [user]);

  const triggerConfetti = async () => {
    const { default: confetti } = await import('canvas-confetti');
    confetti({
      particleCount: 100,
      spread: 70,
      origin: { y: 0.6 }
    });
  };

  const chartData = [
    { day: 'Mon', Present: 94, Late: 5 },
    { day: 'Tue', Present: 96, Late: 3 },
    { day: 'Wed', Present: 93, Late: 6 },
    { day: 'Thu', Present: 97, Late: 2 },
    { day: 'Fri', Present: 95, Late: 4 },
  ];

  if (loading) {
    return <div className="text-center py-12 text-slate-400 text-sm">Loading dashboard metrics...</div>;
  }

  if (user?.role === 'Admin' || user?.role === 'HR') {
    return <Employees />;
  }

  return (
    <div className="space-y-8">
      {/* Welcome banner */}
      <div>
        <h1 className="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100">
          Welcome, <span className="bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent">{user?.name}!</span>
        </h1>
        <p className="text-xs text-slate-500 mt-1">
          Here is your dashboard overview for today, <span className="font-semibold text-slate-700 dark:text-slate-300">{new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</span>.
        </p>
      </div>

      {/* Stats Cards Row */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        {/* Leaves Card */}
        <div className="glass-panel p-6 flex items-center gap-5 hover:scale-[1.01] transition-transform">
          <div className="w-14 h-14 rounded-2xl bg-primary-500/10 border border-primary-500/20 flex items-center justify-center text-primary-500">
            <Calendar size={24} />
          </div>
          <div>
            <p className="text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase">Leaves Left</p>
            <p className="text-xl font-bold text-slate-800 dark:text-slate-100 mt-1">
              <span className="text-primary-500">{stats?.leaves_left}</span> / {stats?.allocated_leaves} Days
            </p>
          </div>
        </div>

        {/* Attendance Card */}
        <div className="glass-panel p-6 flex items-center gap-5 hover:scale-[1.01] transition-transform">
          <div className={`w-14 h-14 rounded-2xl flex items-center justify-center text-2xl ${
            stats?.today_status === 'Present' ? 'bg-success/10 text-success border border-success/20' : 'bg-danger/10 text-danger border border-danger/20'
          }`}>
            <CheckCircle size={24} />
          </div>
          <div>
            <p className="text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase">Today's Status</p>
            <p className={`text-xl font-bold mt-1 ${stats?.today_status === 'Present' ? 'text-success' : 'text-danger'}`}>
              {stats?.today_status} 
              {stats?.clock_in_time && (
                <span className="text-xs font-normal text-slate-400 ml-1">({stats.clock_in_time})</span>
              )}
            </p>
          </div>
        </div>

        {/* Manager Card */}
        <div className="glass-panel p-6 flex items-center gap-5 hover:scale-[1.01] transition-transform">
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
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {/* Left Column: Announcements and Charts */}
        <div className="lg:col-span-2 space-y-8">
          
          {/* Chart Card */}
          <div className="glass-panel p-6">
            <h3 className="text-base font-bold text-slate-900 dark:text-slate-100 mb-6 border-b border-slate-200 dark:border-slate-800/80 pb-4">
              Attendance Trends
            </h3>
            <div className="h-64 w-full">
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={chartData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                  <XAxis dataKey="day" stroke="#64748b" fontSize={11} tickLine={false} />
                  <YAxis stroke="#64748b" fontSize={11} tickLine={false} />
                  <Tooltip contentStyle={{ background: '#0f172a', borderColor: '#334155', borderRadius: '12px' }} />
                  <Area type="monotone" dataKey="Present" stroke="#00e5ff" fill="url(#colorCyan)" strokeWidth={2} />
                  <defs>
                    <linearGradient id="colorCyan" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor="#00e5ff" stopOpacity={0.2}/>
                      <stop offset="95%" stopColor="#00e5ff" stopOpacity={0}/>
                    </linearGradient>
                  </defs>
                </AreaChart>
              </ResponsiveContainer>
            </div>
          </div>

          {/* Announcements Card */}
          <div className="glass-panel p-6">
            <h3 className="text-base font-bold text-slate-900 dark:text-slate-100 mb-6 border-b border-slate-200 dark:border-slate-800/80 pb-4 flex items-center gap-2">
              <Megaphone size={18} /> Company Announcements
            </h3>
            <div className="space-y-4">
              {stats?.announcements.map((ann) => (
                <div key={ann.id} className="p-5 bg-slate-100/50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800/60 rounded-2xl">
                  <div className="flex items-center justify-between gap-4">
                    <h4 className="font-bold text-sm text-slate-900 dark:text-slate-100">{ann.title}</h4>
                    <span className="px-2 py-0.5 bg-gradient-to-r from-primary-500 to-secondary-500 text-[9px] font-black text-slate-950 rounded-full">NEW</span>
                  </div>
                  <p className="text-[10px] text-slate-400 mt-1 font-medium">Published by {ann.creator?.name || 'HR Admin'}</p>
                  <p className="text-xs text-slate-600 dark:text-slate-300 mt-3 leading-relaxed whitespace-pre-line">{ann.content}</p>
                </div>
              ))}
            </div>
          </div>

        </div>

        {/* Right Column: Actions and Birthday trigger */}
        <div className="space-y-8">
          
          {/* Actions Card */}
          <div className="glass-panel p-6">
            <h3 className="text-base font-bold text-slate-900 dark:text-slate-100 mb-4 border-b border-slate-200 dark:border-slate-800/80 pb-2">Quick Actions</h3>
            <div className="space-y-3">
              <button onClick={() => navigate('/attendance')} className="w-full flex items-center justify-between p-3.5 bg-slate-100/50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800/80 rounded-xl hover:border-primary-500 hover:scale-[1.01] transition-all duration-300 group cursor-pointer">
                <div className="flex items-center gap-3">
                  <span className="w-8 h-8 rounded-lg bg-primary-500/10 text-primary-500 flex items-center justify-center group-hover:bg-primary-500 group-hover:text-slate-950 transition-colors"><Clock size={16} /></span>
                  <span className="text-xs font-bold text-slate-700 dark:text-slate-300">Clock In / Out</span>
                </div>
                <ChevronRight size={14} className="text-slate-400 group-hover:translate-x-1 transition-transform" />
              </button>
              <button onClick={() => navigate('/leaves')} className="w-full flex items-center justify-between p-3.5 bg-slate-100/50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800/80 rounded-xl hover:border-primary-500 hover:scale-[1.01] transition-all duration-300 group cursor-pointer">
                <div className="flex items-center gap-3">
                  <span className="w-8 h-8 rounded-lg bg-primary-500/10 text-primary-500 flex items-center justify-center group-hover:bg-primary-500 group-hover:text-slate-950 transition-colors"><Calendar size={16} /></span>
                  <span className="text-xs font-bold text-slate-700 dark:text-slate-300">Apply for Leave</span>
                </div>
                <ChevronRight size={14} className="text-slate-400 group-hover:translate-x-1 transition-transform" />
              </button>
              <button onClick={() => navigate('/documents')} className="w-full flex items-center justify-between p-3.5 bg-slate-100/50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800/80 rounded-xl hover:border-primary-500 hover:scale-[1.01] transition-all duration-300 group cursor-pointer">
                <div className="flex items-center gap-3">
                  <span className="w-8 h-8 rounded-lg bg-primary-500/10 text-primary-500 flex items-center justify-center group-hover:bg-primary-500 group-hover:text-slate-950 transition-colors"><FolderOpen size={16} /></span>
                  <span className="text-xs font-bold text-slate-700 dark:text-slate-300">My Payslips</span>
                </div>
                <ChevronRight size={14} className="text-slate-400 group-hover:translate-x-1 transition-transform" />
              </button>
            </div>
          </div>

          {/* Birthday Celebrations */}
          <div className="glass-panel p-6">
            <h3 className="text-base font-bold text-slate-900 dark:text-slate-100 mb-4 border-b border-slate-200 dark:border-slate-800/80 pb-2">Upcoming Birthdays</h3>
            <div className="space-y-4">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <span className="text-2xl">🎈</span>
                  <div>
                    <p className="text-xs font-bold text-slate-800 dark:text-slate-200">Sarah HR</p>
                    <p className="text-[10px] text-slate-400 mt-0.5">HR Director • June 15</p>
                  </div>
                </div>
                <button onClick={triggerConfetti} className="text-[10px] px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-lg hover:bg-slate-200 font-semibold cursor-pointer">Send 🍦</button>
              </div>
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <span className="text-2xl">🎂</span>
                  <div>
                    <p className="text-xs font-bold text-slate-800 dark:text-slate-200">Alex Dev</p>
                    <p className="text-[10px] text-slate-400 mt-0.5">Lead Architect • June 22</p>
                  </div>
                </div>
                <button onClick={triggerConfetti} className="text-[10px] px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-lg hover:bg-slate-200 font-semibold cursor-pointer">Send 🍦</button>
              </div>
            </div>
          </div>

        </div>

      </div>

    </div>
  );
};
export default Dashboard;
