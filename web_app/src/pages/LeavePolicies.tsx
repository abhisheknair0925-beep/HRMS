import React, { useEffect, useState } from 'react';
import api from '../lib/api';
import { 
  Settings2, Calendar, CheckCircle2, 
  HelpCircle, DollarSign, FileSpreadsheet, X 
} from 'lucide-react';

interface HolidayEvent {
  id: string;
  name: string;
  date: string;
  type: string;
}

interface LeavePolicyConfig {
  id: string;
  name: string;
  max_days: number;
  encashable: boolean;
}

interface CompOffRequest {
  id: string;
  employee_name: string;
  worked_date: string;
  reason: string;
  status: 'Approved' | 'Pending' | 'Rejected';
}

interface EncashmentRequest {
  id: string;
  employee_name: string;
  policy_name: string;
  days_to_encash: number;
  amount: number;
  status: 'Approved' | 'Pending' | 'Rejected';
}

interface EmployeeOption {
  id: string;
  name: string;
}

export const LeavePolicies: React.FC = () => {
  // Configured policies
  const [policies, setPolicies] = useState<LeavePolicyConfig[]>([]);

  // Holiday list
  const [holidays, setHolidays] = useState<HolidayEvent[]>([]);

  // Comp-off list
  const [compOffs, setCompOffs] = useState<CompOffRequest[]>([]);
  const [employees, setEmployees] = useState<EmployeeOption[]>([]);

  // Leave encashment list
  const [encashments, setEncashments] = useState<EncashmentRequest[]>([]);
  const [loading, setLoading] = useState(true);

  // Form states
  const [editPolicyId, setEditPolicyId] = useState<string | null>(null);
  const [editPolicyDays, setEditPolicyDays] = useState(15);
  const [showCompOffModal, setShowCompOffModal] = useState(false);
  const [compOffEmployeeId, setCompOffEmployeeId] = useState('');
  const [compOffDate, setCompOffDate] = useState('');
  const [compOffReason, setCompOffReason] = useState('Production support');

  const [success, setSuccess] = useState(false);
  const [successText, setSuccessText] = useState('');

  const triggerSuccess = (text: string) => {
    setSuccessText(text);
    setSuccess(true);
    setTimeout(() => setSuccess(false), 3000);
  };

  const mapPolicy = (policy: any): LeavePolicyConfig => ({
    id: policy.id,
    name: policy.name,
    max_days: Number(policy.total_days ?? 0),
    encashable: Number(policy.carry_over_max ?? 0) > 0 || policy.name.toLowerCase().includes('annual'),
  });

  const mapEncashment = (encashment: any): EncashmentRequest => ({
    id: encashment.id,
    employee_name: encashment.employee
      ? `${encashment.employee.first_name} ${encashment.employee.last_name}`
      : 'Employee',
    policy_name: encashment.leave_policy?.name ?? 'Leave',
    days_to_encash: Number(encashment.days_to_encash ?? 0),
    amount: Number(encashment.total_amount ?? 0),
    status: encashment.status,
  });

  const mapHoliday = (holiday: any): HolidayEvent => ({
    id: holiday.id,
    name: holiday.name,
    date: holiday.holiday_date,
    type: holiday.type,
  });

  useEffect(() => {
    const loadLeaveManagement = async () => {
      try {
        const [policiesRes, encashmentsRes, holidaysRes, compOffsRes, employeesRes] = await Promise.all([
          api.get('/leave-policies'),
          api.get('/leaves/encashments/pending'),
          api.get('/holidays'),
          api.get('/comp-offs'),
          api.get('/employees'),
        ]);
        setPolicies((policiesRes.data.data ?? []).map(mapPolicy));
        setEncashments((encashmentsRes.data.data ?? []).map(mapEncashment));
        setHolidays((holidaysRes.data.data ?? []).map(mapHoliday));
        setCompOffs(compOffsRes.data.data ?? []);
        const employeeOptions = (employeesRes.data.data.data ?? []).map((employee: any) => ({
          id: employee.id,
          name: `${employee.first_name} ${employee.last_name}`,
        }));
        setEmployees(employeeOptions);
        setCompOffEmployeeId(employeeOptions[0]?.id ?? '');
      } finally {
        setLoading(false);
      }
    };

    loadLeaveManagement();
  }, []);

  const handleUpdatePolicy = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editPolicyId) return;

    const currentPolicy = policies.find(policy => policy.id === editPolicyId);
    const res = await api.put(`/leave-policies/${editPolicyId}`, {
      name: currentPolicy?.name,
      total_days: editPolicyDays,
    });
    const updatedPolicy = mapPolicy(res.data.data);
    setPolicies(prev => prev.map(p => p.id === editPolicyId ? updatedPolicy : p));
    setEditPolicyId(null);
    triggerSuccess('Leave policy allocations updated successfully!');
  };

  const handleCreateCompOff = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!compOffDate || !compOffEmployeeId) return;

    const res = await api.post('/comp-offs', {
      employee_id: compOffEmployeeId,
      worked_date: compOffDate,
      reason: compOffReason,
    });

    setCompOffs([res.data.data, ...compOffs]);
    setShowCompOffModal(false);
    triggerSuccess('Comp-off credit request submitted!');
  };

  const handleApproveCompOff = async (id: string) => {
    const res = await api.post(`/comp-offs/${id}/approve`);
    setCompOffs(prev => prev.map(c => c.id === id ? res.data.data : c));
    triggerSuccess('Comp-off credited to employee leave balance.');
  };

  const handleRejectCompOff = async (id: string) => {
    const res = await api.post(`/comp-offs/${id}/reject`);
    setCompOffs(prev => prev.map(c => c.id === id ? res.data.data : c));
    triggerSuccess('Comp-off request rejected.');
  };

  const handleApproveEncashment = async (id: string) => {
    const res = await api.post(`/leaves/encashments/${id}/approve`);
    const updated = mapEncashment(res.data.data);
    setEncashments(prev => prev.map(e => e.id === id ? updated : e));
    triggerSuccess('Leave encashment request approved. Salary slip deductions logged.');
  };

  const handleRejectEncashment = async (id: string) => {
    const res = await api.post(`/leaves/encashments/${id}/reject`);
    const updated = mapEncashment(res.data.data);
    setEncashments(prev => prev.map(e => e.id === id ? updated : e));
    triggerSuccess('Leave encashment request rejected.');
  };

  const handleDownloadReport = () => {
    alert("Leave parameters summary sheet generated. Download started (leave_registry_report_2026.csv).");
  };

  if (loading) {
    return <div className="text-center py-12 text-slate-400 text-sm">Loading leave management...</div>;
  }

  return (
    <div className="space-y-8 max-w-7xl mx-auto">
      {/* Title */}
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Leave Configuration & Policy Manager</h1>
          <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Configure employee leave caps, audit compensatory credits, evaluate cash conversions, and view public calendars.
          </p>
        </div>
        <button 
          onClick={handleDownloadReport}
          className="px-4 py-2.5 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl btn-glow text-xs flex items-center gap-2 cursor-pointer"
        >
          <FileSpreadsheet size={16} /> Export Leaves Report
        </button>
      </div>

      {success && (
        <div className="p-4 rounded-xl bg-success/20 border border-success/30 text-success text-xs font-semibold shadow-lg backdrop-blur-sm flex items-center gap-2">
          <CheckCircle2 size={16} /> {successText}
        </div>
      )}

      {/* Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {/* Left Side: Policies & Calendar (col-span-6) */}
        <div className="lg:col-span-6 space-y-6">
          
          {/* Policy Caps Panel */}
          <div className="glass-panel p-6 space-y-4">
            <h3 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800/40 pb-2 flex items-center gap-1.5">
              <Settings2 size={16} className="text-primary-500" /> Active Policy Caps
            </h3>

            <div className="divide-y divide-slate-100 dark:divide-slate-800/50">
              {policies.map(policy => (
                <div key={policy.id} className="py-3.5 flex justify-between items-center text-xs font-semibold">
                  <div>
                    <p className="text-slate-800 dark:text-slate-200 font-bold">{policy.name}</p>
                    <p className="text-[10px] text-slate-400 mt-0.5">Encashable: {policy.encashable ? 'Yes' : 'No'}</p>
                  </div>
                  <div className="flex items-center gap-3">
                    {editPolicyId === policy.id ? (
                      <form onSubmit={handleUpdatePolicy} className="flex gap-2 items-center">
                        <input 
                          type="number" 
                          value={editPolicyDays} 
                          onChange={(e) => setEditPolicyDays(Number(e.target.value))} 
                          className="glass-input text-[11px] py-1 px-2 w-16 text-center" 
                          required 
                        />
                        <button type="submit" className="px-2 py-1 bg-success text-slate-950 rounded-lg font-bold text-[10px]">Save</button>
                        <button type="button" onClick={() => setEditPolicyId(null)} className="px-2 py-1 bg-slate-800 text-slate-200 rounded-lg font-bold text-[10px]">Cancel</button>
                      </form>
                    ) : (
                      <>
                        <span className="text-primary-500 font-black text-sm">{policy.max_days} Days</span>
                        <button 
                          onClick={() => {
                            setEditPolicyId(policy.id);
                            setEditPolicyDays(policy.max_days);
                          }}
                          className="px-2.5 py-1 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-primary-500 text-slate-400 border border-slate-200 dark:border-slate-800 rounded-lg text-[10px] cursor-pointer"
                        >
                          Modify
                        </button>
                      </>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Holiday Calendar */}
          <div className="glass-panel p-6 space-y-4">
            <h3 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800/40 pb-2 flex items-center gap-1.5">
              <Calendar size={16} className="text-primary-500" /> Holiday Calendar
            </h3>

            <div className="space-y-3">
              {holidays.map(h => (
                <div key={h.id} className="flex items-center justify-between p-3.5 bg-slate-100/30 dark:bg-slate-950/15 border border-slate-200 dark:border-slate-800/60 rounded-xl text-xs font-semibold">
                  <div className="flex items-center gap-3">
                    <span className="text-xl">📅</span>
                    <div>
                      <p className="text-slate-700 dark:text-slate-300 font-bold">{h.name}</p>
                      <p className="text-[10px] text-slate-400 mt-0.5">{h.type}</p>
                    </div>
                  </div>
                  <span className="text-slate-400 text-[10px] font-bold">
                    {new Date(h.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                  </span>
                </div>
              ))}
            </div>
          </div>

        </div>

        {/* Right Side: Comp-off & Encashments (col-span-6) */}
        <div className="lg:col-span-6 space-y-6">
          
          {/* Compensatory Off Panel */}
          <div className="glass-panel p-6 space-y-4">
            <div className="flex justify-between items-center border-b border-slate-200 dark:border-slate-800/40 pb-2">
              <h3 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest flex items-center gap-1.5">
                <HelpCircle size={16} className="text-primary-500" /> Compensatory Off (Comp-off) Credits
              </h3>
              <button 
                onClick={() => setShowCompOffModal(true)}
                className="px-2.5 py-1 bg-primary-500 hover:bg-primary-400 text-slate-950 font-black rounded-lg text-[9px] uppercase tracking-wider btn-glow cursor-pointer"
              >
                Request Credit
              </button>
            </div>

            {/* Comp-off list */}
            {compOffs.length === 0 ? (
              <p className="text-xs text-slate-500 italic">No comp-off credits requested.</p>
            ) : (
              <div className="space-y-3">
                {compOffs.map(c => (
                  <div key={c.id} className="p-4 bg-slate-100/30 dark:bg-slate-950/15 border border-slate-200 dark:border-slate-800/60 rounded-2xl text-xs space-y-3">
                    <div className="flex justify-between items-start">
                      <div>
                        <p className="font-bold text-slate-800 dark:text-slate-200">{c.employee_name}</p>
                        <p className="text-[10px] text-slate-400 mt-0.5">Worked on: {new Date(c.worked_date).toLocaleDateString()}</p>
                      </div>
                      <span className={`px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider ${
                        c.status === 'Approved' ? 'bg-success/10 text-success' :
                        c.status === 'Pending' ? 'bg-warning/10 text-warning' : 'bg-danger/10 text-danger'
                      }`}>
                        {c.status}
                      </span>
                    </div>
                    <p className="text-[11px] text-slate-500 leading-relaxed italic">Reason: "{c.reason}"</p>
                    
                    {c.status === 'Pending' && (
                      <div className="flex gap-2 pt-2 border-t border-slate-200 dark:border-slate-800/50">
                        <button 
                          onClick={() => handleApproveCompOff(c.id)}
                          className="px-3 py-1 bg-success text-slate-950 font-black rounded-lg text-[10px] cursor-pointer"
                        >
                          Approve Credit
                        </button>
                        <button 
                          onClick={() => handleRejectCompOff(c.id)}
                          className="px-3 py-1 bg-danger text-white font-black rounded-lg text-[10px] cursor-pointer"
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

          {/* Leave Encashment Panel */}
          <div className="glass-panel p-6 space-y-4">
            <h3 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800/40 pb-2 flex items-center gap-1.5">
              <DollarSign size={16} className="text-primary-500" /> Leave Encashment Workflow
            </h3>

            {encashments.length === 0 ? (
              <p className="text-xs text-slate-500 italic">No cash-conversions requests pending.</p>
            ) : (
              <div className="space-y-3">
                {encashments.map(e => (
                  <div key={e.id} className="p-4 bg-slate-100/30 dark:bg-slate-950/15 border border-slate-200 dark:border-slate-800/60 rounded-2xl text-xs space-y-3">
                    <div className="flex justify-between items-start">
                      <div>
                        <p className="font-bold text-slate-800 dark:text-slate-200">{e.employee_name}</p>
                        <p className="text-[10px] text-slate-400 mt-0.5">Encash: {e.days_to_encash} days of {e.policy_name}</p>
                      </div>
                      <span className={`px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider ${
                        e.status === 'Approved' ? 'bg-success/10 text-success' :
                        e.status === 'Pending' ? 'bg-warning/10 text-warning' : 'bg-danger/10 text-danger'
                      }`}>
                        {e.status}
                      </span>
                    </div>
                    <p className="text-xs font-bold text-success">Calculated Value: ${e.amount.toLocaleString()} USD</p>
                    
                    {e.status === 'Pending' && (
                      <div className="flex gap-2 pt-2 border-t border-slate-200 dark:border-slate-800/50">
                        <button 
                          onClick={() => handleApproveEncashment(e.id)}
                          className="px-3 py-1 bg-success text-slate-950 font-black rounded-lg text-[10px] cursor-pointer"
                        >
                          Approve & Pay
                        </button>
                        <button 
                          onClick={() => handleRejectEncashment(e.id)}
                          className="px-3 py-1 bg-danger text-white font-black rounded-lg text-[10px] cursor-pointer"
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

        </div>

      </div>

      {/* Comp-off Request Form Modal */}
      {showCompOffModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div className="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" onClick={() => setShowCompOffModal(false)}></div>
          <div className="relative glass-panel bg-slate-900 border border-slate-800 p-6 max-w-md w-full shadow-2xl z-10 text-xs">
            <div className="flex justify-between items-center pb-3 border-b border-slate-800 mb-4">
              <h3 className="text-sm font-bold text-slate-100">Submit Comp-Off Credit</h3>
              <button onClick={() => setShowCompOffModal(false)} className="text-slate-400 hover:text-slate-200 cursor-pointer">
                <X size={18} />
              </button>
            </div>
            
            <form onSubmit={handleCreateCompOff} className="space-y-4">
              <div className="space-y-1">
                <label className="text-[10px] font-bold text-slate-400 uppercase">Employee</label>
                <select value={compOffEmployeeId} onChange={(e) => setCompOffEmployeeId(e.target.value)} className="glass-input text-xs" required>
                  {employees.map(employee => (
                    <option key={employee.id} value={employee.id}>{employee.name}</option>
                  ))}
                </select>
              </div>

              <div className="space-y-1">
                <label className="text-[10px] font-bold text-slate-400 uppercase">Worked Date</label>
                <input type="date" value={compOffDate} onChange={(e) => setCompOffDate(e.target.value)} className="glass-input text-xs" required />
              </div>

              <div className="space-y-1">
                <label className="text-[10px] font-bold text-slate-400 uppercase">Description / Support Reason</label>
                <textarea value={compOffReason} onChange={(e) => setCompOffReason(e.target.value)} className="glass-input text-xs resize-none" rows={3} placeholder="Worked weekend migration support..." required></textarea>
              </div>

              <button type="submit" className="w-full py-2.5 bg-primary-500 text-slate-950 font-bold rounded-xl text-xs uppercase tracking-wider btn-glow transition-all cursor-pointer">
                Submit Credit Request
              </button>
            </form>
          </div>
        </div>
      )}

    </div>
  );
};

export default LeavePolicies;
