import React, { useEffect, useState } from 'react';
import api from '../lib/api';
import { 
  Users, Calendar, Clock, UserCheck, ShieldAlert
} from 'lucide-react';

interface TeamMember {
  id: string;
  name: string;
  role: string;
  shift_id: string | null;
  shift: string;
  todayStatus: 'Present' | 'Late' | 'Absent' | 'Not Checked-In';
  clockInTime: string | null;
  timesheet: Record<string, number>; // hours worked for Mon-Fri
}

interface TeamLeaveRequest {
  id: string;
  employee_name: string;
  policy_name: string;
  start_date: string;
  end_date: string;
  days: number;
  reason: string;
  status: 'Approved' | 'Pending' | 'Rejected';
}

interface ShiftOption {
  id: string;
  name: string;
  start_time: string;
  end_time: string;
}

interface ManagerDashboardPayload {
  team: TeamMember[];
  leave_requests: TeamLeaveRequest[];
  shifts: ShiftOption[];
  summary: {
    checked_in_count: number;
    total_team_count: number;
    avg_weekly_hours: number;
    pending_leave_requests: number;
  };
}

export const ManagerDashboard: React.FC = () => {
  const [team, setTeam] = useState<TeamMember[]>([]);
  const [leaves, setLeaves] = useState<TeamLeaveRequest[]>([]);
  const [shifts, setShifts] = useState<ShiftOption[]>([]);
  const [summary, setSummary] = useState<ManagerDashboardPayload['summary']>({
    checked_in_count: 0,
    total_team_count: 0,
    avg_weekly_hours: 0,
    pending_leave_requests: 0,
  });
  const [loading, setLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState<string | null>(null);

  // Form states for Shift re-assignment
  const [selectedMemberId, setSelectedMemberId] = useState('');
  const [targetShiftId, setTargetShiftId] = useState('');
  const [success, setSuccess] = useState(false);

  useEffect(() => {
    const fetchManagerDashboard = async () => {
      try {
        const res = await api.get('/manager/dashboard');
        const data: ManagerDashboardPayload = res.data.data;
        setTeam(data.team);
        setLeaves(data.leave_requests);
        setShifts(data.shifts);
        setSummary(data.summary);
        setSelectedMemberId(data.team[0]?.id ?? '');
        setTargetShiftId(data.team[0]?.shift_id ?? data.shifts[0]?.id ?? '');
      } finally {
        setLoading(false);
      }
    };

    fetchManagerDashboard();
  }, []);

  const checkedInCount = summary.checked_in_count;
  const totalTeamCount = summary.total_team_count;

  const handleShiftChange = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedMemberId || !targetShiftId) return;

    setActionLoading('shift');
    try {
      const res = await api.put(`/manager/direct-reports/${selectedMemberId}/shift`, {
        shift_id: targetShiftId,
      });
      const updatedMember: TeamMember = res.data.data;
      setTeam(prev => prev.map(member => member.id === selectedMemberId ? updatedMember : member));
      setSuccess(true);
      setTimeout(() => setSuccess(false), 3000);
    } finally {
      setActionLoading(null);
    }
  };

  const handleApproveLeave = async (id: string) => {
    setActionLoading(id);
    try {
      const res = await api.post(`/manager/leaves/${id}/approve`);
      const updatedLeave: TeamLeaveRequest = res.data.data;
      setLeaves(prev => prev.map(l => l.id === id ? updatedLeave : l));
      setSummary(prev => ({
        ...prev,
        pending_leave_requests: Math.max(prev.pending_leave_requests - 1, 0),
      }));
    } finally {
      setActionLoading(null);
    }
  };

  const handleRejectLeave = async (id: string) => {
    setActionLoading(id);
    try {
      const res = await api.post(`/manager/leaves/${id}/reject`, {
        rejection_reason: 'Rejected from Manager Self Service portal.',
      });
      const updatedLeave: TeamLeaveRequest = res.data.data;
      setLeaves(prev => prev.map(l => l.id === id ? updatedLeave : l));
      setSummary(prev => ({
        ...prev,
        pending_leave_requests: Math.max(prev.pending_leave_requests - 1, 0),
      }));
    } finally {
      setActionLoading(null);
    }
  };

  if (loading) {
    return <div className="text-center py-12 text-slate-400 text-sm">Loading manager portal...</div>;
  }

  return (
    <div className="space-y-8 max-w-7xl mx-auto">
      {/* Title */}
      <div>
        <h1 className="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Manager Self Service Portal</h1>
        <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
          Monitor direct reports attendance status, configure shift schedules, review timesheets, and approve leave requests.
        </p>
      </div>

      {/* Team Presence Gauge card */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {/* Presence Card */}
        <div className="glass-panel p-6 flex items-center gap-5 hover:scale-[1.01] transition-transform">
          <div className="w-14 h-14 rounded-2xl bg-primary-500/10 border border-primary-500/20 flex items-center justify-center text-primary-500">
            <Users size={24} />
          </div>
          <div>
            <p className="text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase">Team Check-In Rate</p>
            <p className="text-xl font-bold text-slate-800 dark:text-slate-100 mt-1">
              <span className="text-primary-500">{checkedInCount}</span> / {totalTeamCount} Present
            </p>
          </div>
        </div>

        {/* Total Hours Card */}
        <div className="glass-panel p-6 flex items-center gap-5 hover:scale-[1.01] transition-transform">
          <div className="w-14 h-14 rounded-2xl bg-success/10 border border-success/20 flex items-center justify-center text-success">
            <Clock size={24} />
          </div>
          <div>
            <p className="text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase">Avg Hours Worked (Weekly)</p>
            <p className="text-xl font-bold text-success mt-1">
              {summary.avg_weekly_hours.toFixed(1)} Hrs / Member
            </p>
          </div>
        </div>

        {/* Pending Approvals Card */}
        <div className="glass-panel p-6 flex items-center gap-5 hover:scale-[1.01] transition-transform">
          <div className="w-14 h-14 rounded-2xl bg-warning/10 border border-warning/20 flex items-center justify-center text-warning">
            <Calendar size={24} />
          </div>
          <div>
            <p className="text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase">Pending Approvals</p>
            <p className="text-xl font-bold text-warning mt-1">
              {summary.pending_leave_requests} Leave Requests
            </p>
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {/* Left: Direct Reports & Shift planner (col-span-6) */}
        <div className="lg:col-span-6 space-y-6">
          
          {/* Active Presence Board */}
          <div className="glass-panel p-6 space-y-4">
            <h3 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800/40 pb-2 flex items-center gap-1.5">
              <UserCheck size={16} className="text-primary-500" /> Direct Reports Presence
            </h3>

            <div className="space-y-3">
              {team.length === 0 ? (
                <p className="text-xs text-slate-500 italic">No direct reports assigned.</p>
              ) : team.map(member => (
                <div key={member.id} className="flex justify-between items-center p-3.5 bg-slate-100/30 dark:bg-slate-950/15 border border-slate-200 dark:border-slate-800/60 rounded-xl text-xs font-semibold">
                  <div>
                    <p className="text-slate-800 dark:text-slate-200 font-bold">{member.name}</p>
                    <p className="text-[9px] text-slate-400 mt-0.5">{member.role} • <span className="text-slate-500 font-semibold">{member.shift}</span></p>
                  </div>
                  <div className="text-right">
                    <span className={`px-2 py-0.5 rounded-full text-[8.5px] font-black uppercase tracking-wider ${
                      member.todayStatus === 'Present' ? 'bg-success/10 text-success' :
                      member.todayStatus === 'Late' ? 'bg-warning/10 text-warning' :
                      member.todayStatus === 'Absent' ? 'bg-danger/10 text-danger' : 'bg-slate-800 text-slate-400'
                    }`}>
                      {member.todayStatus}
                    </span>
                    {member.clockInTime && (
                      <p className="text-[9px] text-slate-400 mt-1">Clock In: {member.clockInTime}</p>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Shift Planner Form */}
          <div className="glass-panel p-6 space-y-4">
            <h3 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800/40 pb-2 flex items-center gap-1.5">
              <Calendar size={16} className="text-primary-500" /> Shift & Roster Planner
            </h3>

            {success && (
              <div className="p-3 bg-success/20 border border-success/30 text-success rounded-xl text-xs font-semibold shadow-lg backdrop-blur-sm">
                ✅ Shift roster updated successfully!
              </div>
            )}

            <form onSubmit={handleShiftChange} className="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
              <div>
                <label className="text-[9px] font-bold text-slate-400 uppercase">Employee</label>
                <select
                  value={selectedMemberId}
                  onChange={(e) => {
                    const member = team.find(item => item.id === e.target.value);
                    setSelectedMemberId(e.target.value);
                    setTargetShiftId(member?.shift_id ?? shifts[0]?.id ?? '');
                  }}
                  className="glass-input text-xs"
                  disabled={team.length === 0}
                >
                  {team.map(m => (
                    <option key={m.id} value={m.id}>{m.name}</option>
                  ))}
                </select>
              </div>
              <div>
                <label className="text-[9px] font-bold text-slate-400 uppercase">Assign Shift</label>
                <select
                  value={targetShiftId}
                  onChange={(e) => setTargetShiftId(e.target.value)}
                  className="glass-input text-xs"
                  disabled={shifts.length === 0}
                >
                  {shifts.map(shift => (
                    <option key={shift.id} value={shift.id}>
                      {shift.name} ({shift.start_time.slice(0, 5)}-{shift.end_time.slice(0, 5)})
                    </option>
                  ))}
                </select>
              </div>
              <button
                type="submit"
                disabled={!selectedMemberId || !targetShiftId || actionLoading === 'shift'}
                className="w-full py-2.5 bg-primary-500 hover:bg-primary-400 disabled:opacity-50 disabled:cursor-not-allowed text-slate-950 font-bold rounded-xl text-xs uppercase tracking-wider btn-glow transition-all cursor-pointer"
              >
                {actionLoading === 'shift' ? 'Updating...' : 'Update Roster'}
              </button>
            </form>
          </div>

        </div>

        {/* Right: Timesheets and Leave approvals (col-span-6) */}
        <div className="lg:col-span-6 space-y-6">
          
          {/* Team Leaves Queue */}
          <div className="glass-panel p-6 space-y-4">
            <h3 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800/40 pb-2 flex items-center gap-1.5">
              <ShieldAlert size={16} className="text-primary-500" /> Pending Team Leave Requests
            </h3>

            {leaves.length === 0 ? (
              <p className="text-xs text-slate-500 italic">No leave applications pending.</p>
            ) : (
              <div className="space-y-3.5">
                {leaves.map(request => (
                  <div key={request.id} className="p-4 bg-slate-100/30 dark:bg-slate-950/15 border border-slate-200 dark:border-slate-800/60 rounded-2xl text-xs space-y-3">
                    <div className="flex justify-between items-start">
                      <div>
                        <p className="font-bold text-slate-800 dark:text-slate-200">{request.employee_name}</p>
                        <p className="text-[10px] text-slate-400 mt-0.5">{request.policy_name} • <span className="font-bold text-primary-500">{request.days} Days</span></p>
                        <p className="text-[9px] text-slate-500 mt-1">Dates: {new Date(request.start_date).toLocaleDateString()} to {new Date(request.end_date).toLocaleDateString()}</p>
                      </div>
                      <span className={`px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider ${
                        request.status === 'Approved' ? 'bg-success/10 text-success' :
                        request.status === 'Pending' ? 'bg-warning/10 text-warning' : 'bg-danger/10 text-danger'
                      }`}>
                        {request.status}
                      </span>
                    </div>
                    <p className="text-[11px] text-slate-500 italic leading-relaxed">Reason: "{request.reason}"</p>
                    
                    {request.status === 'Pending' && (
                      <div className="flex gap-2 pt-2 border-t border-slate-200 dark:border-slate-800/50">
                        <button 
                          onClick={() => handleApproveLeave(request.id)}
                          disabled={actionLoading === request.id}
                          className="px-3 py-1 bg-success disabled:opacity-50 disabled:cursor-not-allowed text-slate-950 font-black rounded-lg text-xs cursor-pointer"
                        >
                          Approve
                        </button>
                        <button 
                          onClick={() => handleRejectLeave(request.id)}
                          disabled={actionLoading === request.id}
                          className="px-3 py-1 bg-danger disabled:opacity-50 disabled:cursor-not-allowed text-white font-black rounded-lg text-xs cursor-pointer"
                        >
                          Reject
                        </button>
                      </div>
                    )}
                  </div>
                ))}
              </div>
            )}
          </div>

          {/* Weekly Timesheet Hours Grid */}
          <div className="glass-panel p-6 space-y-4">
            <h3 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800/40 pb-4 flex items-center gap-1.5">
              <Clock size={16} className="text-primary-500" /> Weekly Timesheets (Hours Log)
            </h3>

            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs border-collapse">
                <thead>
                  <tr className="border-b border-slate-200 dark:border-slate-800/80 text-slate-400 uppercase tracking-widest font-bold">
                    <th className="pb-3 pr-2">Member</th>
                    <th className="pb-3 px-2 text-center">Mon</th>
                    <th className="pb-3 px-2 text-center">Tue</th>
                    <th className="pb-3 px-2 text-center">Wed</th>
                    <th className="pb-3 px-2 text-center">Thu</th>
                    <th className="pb-3 px-2 text-center">Fri</th>
                    <th className="pb-3 pl-2 text-right">Total</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 dark:divide-slate-800/40 font-semibold">
                  {team.map(member => {
                    const totalHours = Object.values(member.timesheet).reduce((a, b) => a + b, 0);
                    return (
                      <tr key={member.id} className="hover:bg-slate-100/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td className="py-3 pr-2 font-bold text-slate-700 dark:text-slate-300">{member.name.split(' ')[0]}</td>
                        <td className="py-3 px-2 text-center text-slate-600 dark:text-slate-400">{member.timesheet.Mon}h</td>
                        <td className="py-3 px-2 text-center text-slate-600 dark:text-slate-400">{member.timesheet.Tue}h</td>
                        <td className="py-3 px-2 text-center text-slate-600 dark:text-slate-400">{member.timesheet.Wed}h</td>
                        <td className="py-3 px-2 text-center text-slate-600 dark:text-slate-400">{member.timesheet.Thu}h</td>
                        <td className="py-3 px-2 text-center text-slate-600 dark:text-slate-400">{member.timesheet.Fri}h</td>
                        <td className="py-3 pl-2 text-right font-black text-primary-500">{totalHours.toFixed(1)}h</td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </div>

        </div>

      </div>
    </div>
  );
};

export default ManagerDashboard;
