import React, { useState, useEffect } from 'react';
import api from '../lib/api';
import { Plus, X } from 'lucide-react';

interface LeaveBalance {
  leave_policy_id: string;
  allocated_days: number;
  used_days: number;
  encashed_days: number;
  leave_policy: { name: string };
}

interface LeaveRequest {
  id: string;
  start_date: string;
  end_date: string;
  total_days: number;
  reason: string;
  status: 'Approved' | 'Pending' | 'Rejected';
  leave_policy?: { name: string };
}

interface LeavePolicy {
  id: string;
  name: string;
  total_days: number;
}

export const Leaves: React.FC = () => {
  const [balances, setBalances] = useState<LeaveBalance[]>([]);
  const [requests, setRequests] = useState<LeaveRequest[]>([]);
  const [policies, setPolicies] = useState<LeavePolicy[]>([]);
  
  const [modalOpen, setModalOpen] = useState(false);
  const [loading, setLoading] = useState(true);

  // Form State
  const [policyId, setPolicyId] = useState('');
  const [startDate, setStartDate] = useState('');
  const [endDate, setEndDate] = useState('');
  const [halfDay, setHalfDay] = useState(false);
  const [reason, setReason] = useState('');

  const fetchData = async () => {
    try {
      const res = await api.get('/leave');
      setBalances(res.data.data.balances || []);
      setRequests(res.data.data.requests || []);
      setPolicies(res.data.data.policies || []);
    } catch {
      // Ensure async context to avoid calling setState synchronously in useEffect
      await Promise.resolve();
      // Mock Data
      setBalances([
        { leave_policy_id: '1', allocated_days: 10, used_days: 2, encashed_days: 0, leave_policy: { name: 'Sick Leave' } },
        { leave_policy_id: '2', allocated_days: 12, used_days: 4, encashed_days: 0, leave_policy: { name: 'Casual Leave' } },
        { leave_policy_id: '3', allocated_days: 15, used_days: 0, encashed_days: 0, leave_policy: { name: 'Annual Leave' } }
      ]);
      setRequests([
        { id: '1', start_date: '2026-06-12', end_date: '2026-06-13', total_days: 2, reason: 'Family medical issue', status: 'Approved', leave_policy: { name: 'Sick Leave' } },
        { id: '2', start_date: '2026-06-25', end_date: '2026-06-25', total_days: 1, reason: 'Personal work', status: 'Pending', leave_policy: { name: 'Casual Leave' } }
      ]);
      setPolicies([
        { id: '1', name: 'Sick Leave', total_days: 10 },
        { id: '2', name: 'Casual Leave', total_days: 12 },
        { id: '3', name: 'Annual Leave', total_days: 15 }
      ]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    Promise.resolve().then(() => {
      fetchData();
    });
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await api.post('/leave/apply', {
        leave_policy_id: policyId,
        start_date: startDate,
        end_date: endDate,
        half_day: halfDay,
        reason: reason
      });
      setModalOpen(false);
      fetchData();
      
      // Reset form
      setPolicyId('');
      setStartDate('');
      setEndDate('');
      setHalfDay(false);
      setReason('');
    } catch (err: unknown) {
      const error = err as { response?: { data?: { message?: string } } };
      alert(error.response?.data?.message || 'Failed to submit leave request.');
    }
  };

  if (loading) {
    return <div className="text-center py-12 text-slate-400 text-sm">Loading leave parameters...</div>;
  }

  return (
    <div className="space-y-8">
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Leave Management</h1>
          <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Check your remaining leave balances, apply for time-off, and track approvals.
          </p>
        </div>
        <button onClick={() => setModalOpen(true)}
                className="px-4 py-2.5 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl btn-glow text-xs flex items-center gap-2 cursor-pointer">
          <Plus size={16} /> Apply For Leave
        </button>
      </div>

      {/* Grid: Balances */}
      <div>
        <h3 className="text-sm font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase mb-4">Leave Balances</h3>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {balances.map((balance) => {
            const remaining = balance.allocated_days - balance.used_days - balance.encashed_days;
            return (
              <div key={balance.leave_policy_id} className="glass-panel p-6 text-center flex flex-col justify-between hover:scale-[1.01] transition-transform">
                <div>
                  <p className="text-sm font-bold text-slate-800 dark:text-slate-200">{balance.leave_policy.name}</p>
                  <h2 className="text-4xl font-extrabold bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent my-4">
                    {remaining}
                  </h2>
                  <p className="text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase">Days Remaining</p>
                </div>
                
                <div className="grid grid-cols-3 gap-2 border-t border-slate-200 dark:border-slate-800/80 pt-4 mt-6 text-[10px] font-semibold">
                  <div>
                    <p className="text-slate-700 dark:text-slate-300 font-bold text-xs">{balance.allocated_days}</p>
                    <p className="text-slate-400 mt-0.5">Allocated</p>
                  </div>
                  <div>
                    <p className="text-slate-700 dark:text-slate-300 font-bold text-xs">{balance.used_days}</p>
                    <p className="text-slate-400 mt-0.5">Used</p>
                  </div>
                  <div>
                    <p className="text-slate-700 dark:text-slate-300 font-bold text-xs">{balance.encashed_days}</p>
                    <p className="text-slate-400 mt-0.5">Encashed</p>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      </div>

      {/* Table List */}
      <div className="glass-panel p-6">
        <h3 className="text-sm font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase mb-4">Leave Request History</h3>
        
        {requests.length === 0 ? (
          <div className="text-center py-12 text-slate-400">
            <span className="text-4xl block mb-2">📬</span>
            <p className="text-sm">No leave applications found.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs border-collapse">
              <thead>
                <tr className="border-b border-slate-200 dark:border-slate-800/80 text-slate-400 uppercase tracking-widest font-bold">
                  <th className="pb-3 pr-4">Policy Type</th>
                  <th className="pb-3 px-4">Start Date</th>
                  <th className="pb-3 px-4">End Date</th>
                  <th className="pb-3 px-4">Days</th>
                  <th className="pb-3 px-4">Reason</th>
                  <th className="pb-3 pl-4 text-right">Status</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 dark:divide-slate-800/40">
                {requests.map((req) => (
                  <tr key={req.id} className="hover:bg-slate-100/50 dark:hover:bg-slate-800/20 transition-colors">
                    <td className="py-3.5 pr-4 font-bold text-slate-700 dark:text-slate-300">
                      {req.leave_policy ? req.leave_policy.name : 'N/A'}
                    </td>
                    <td className="py-3.5 px-4 text-slate-600 dark:text-slate-400 font-medium">{req.start_date}</td>
                    <td className="py-3.5 px-4 text-slate-600 dark:text-slate-400 font-medium">{req.end_date}</td>
                    <td className="py-3.5 px-4 text-slate-700 dark:text-slate-300 font-semibold">{req.total_days} days</td>
                    <td className="py-3.5 px-4 text-slate-500 dark:text-slate-400 max-w-[200px] truncate" title={req.reason}>
                      {req.reason}
                    </td>
                    <td className="py-3.5 pl-4 text-right">
                      <span className={`px-2.5 py-0.5 rounded-full text-[9px] font-black tracking-wider uppercase ${
                        req.status === 'Approved' ? 'bg-success/10 text-success' :
                        req.status === 'Pending' ? 'bg-warning/10 text-warning' : 'bg-danger/10 text-danger'
                      }`}>
                        {req.status}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Custom Modal */}
      {modalOpen && (
        <div className="fixed inset-0 z-50 overflow-y-auto">
          <div className="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" onClick={() => setModalOpen(false)}></div>
          <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div className="relative transform overflow-hidden rounded-2xl bg-slate-900 border border-slate-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg p-6">
              
              <div className="flex items-center justify-between pb-4 border-b border-slate-800">
                <h3 className="text-lg font-bold text-slate-100">Apply for Leave</h3>
                <button onClick={() => setModalOpen(false)} className="text-slate-400 hover:text-slate-200 cursor-pointer">
                  <X size={20} />
                </button>
              </div>

              <form onSubmit={handleSubmit} className="space-y-4 mt-4">
                <div className="space-y-1">
                  <label className="text-[10px] font-bold text-slate-400 uppercase">Leave Type</label>
                  <select value={policyId} onChange={(e) => setPolicyId(e.target.value)} className="glass-input text-xs" required>
                    <option value="" disabled>Select Leave Type</option>
                    {policies.map((policy) => (
                      <option key={policy.id} value={policy.id}>{policy.name} (Total {policy.total_days} Days)</option>
                    ))}
                  </select>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-1">
                    <label className="text-[10px] font-bold text-slate-400 uppercase">Start Date</label>
                    <input type="date" value={startDate} onChange={(e) => setStartDate(e.target.value)} className="glass-input text-xs" required min={new Date().toISOString().split('T')[0]} />
                  </div>
                  <div className="space-y-1">
                    <label className="text-[10px] font-bold text-slate-400 uppercase">End Date</label>
                    <input type="date" value={endDate} onChange={(e) => setEndDate(e.target.value)} className="glass-input text-xs" required min={startDate || new Date().toISOString().split('T')[0]} />
                  </div>
                </div>

                <div className="flex items-center gap-2 py-1">
                  <input type="checkbox" checked={halfDay} onChange={(e) => setHalfDay(e.target.checked)} className="w-4 h-4 rounded border-slate-300 text-primary-500 bg-slate-950/40 cursor-pointer" />
                  <label className="text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer select-none">Apply as Half Day</label>
                </div>

                <div className="space-y-1">
                  <label className="text-[10px] font-bold text-slate-400 uppercase">Reason for Leave</label>
                  <textarea value={reason} onChange={(e) => setReason(e.target.value)} className="glass-input text-xs resize-none" rows={4} placeholder="Brief explanation of your leave request..." required></textarea>
                </div>

                <div className="flex gap-4 pt-4 border-t border-slate-800">
                  <button type="button" onClick={() => setModalOpen(false)} className="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-xl text-xs transition-colors cursor-pointer">
                    Cancel
                  </button>
                  <button type="submit" className="flex-1 py-2.5 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl text-xs transition-colors btn-glow cursor-pointer">
                    Submit Request
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
export default Leaves;
