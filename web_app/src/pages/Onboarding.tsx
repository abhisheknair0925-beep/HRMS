import React, { useState } from 'react';
import { 
  CheckCircle2, ShieldCheck, Award, Plus, Calendar,
  Monitor, Laptop, Smartphone, Key, FileText, Check, Play, UserPlus, X
} from 'lucide-react';

interface OnboardingHire {
  id: string;
  name: string;
  email: string;
  role: string;
  department: string;
  joining_date: string;
  status: 'Incomplete' | 'Verified' | 'Completed';
  emp_id: string | null;
  docsVerified: boolean;
  inductionScheduled: boolean;
  inductionDetails?: { date: string; time: string; link: string; host: string };
  assets: Array<{ type: 'laptop' | 'monitor' | 'phone' | 'keys'; model: string; serial: string }>;
  checklist: Record<string, boolean>;
}

export const Onboarding: React.FC = () => {
  const [hires, setHires] = useState<OnboardingHire[]>([
    {
      id: 'h1',
      name: 'Alice Developer',
      email: 'alice@humanode.net',
      role: 'Backend Engineer',
      department: 'Software Engineering',
      joining_date: '2026-06-25',
      status: 'Incomplete',
      emp_id: null,
      docsVerified: false,
      inductionScheduled: false,
      assets: [],
      checklist: {
        'Codebase Access': false,
        'Slack Channel': true,
        'Compliance Training': false,
        'AWS Sandbox credentials': false
      }
    },
    {
      id: 'h2',
      name: 'Bob Designer',
      email: 'bob@humanode.net',
      role: 'Lead UI/UX Designer',
      department: 'Product Management',
      joining_date: '2026-06-20',
      status: 'Incomplete',
      emp_id: 'EMP-2026-0005',
      docsVerified: true,
      inductionScheduled: true,
      inductionDetails: {
        date: '2026-06-20',
        time: '10:00 AM',
        link: 'https://meet.google.com/abc-defg-hij',
        host: 'Sarah HR'
      },
      assets: [
        { type: 'laptop', model: 'MacBook Pro 16" M3', serial: 'AAPL12345678' }
      ],
      checklist: {
        'Figma Access': true,
        'Slack Channel': true,
        'Compliance Training': true,
        'HR Induction Session': true
      }
    }
  ]);

  const [selectedHireId, setSelectedHireId] = useState('h1');
  const selectedHire = hires.find(h => h.id === selectedHireId) || hires[0];

  // Forms states
  const [assetType, setAssetType] = useState<'laptop' | 'monitor' | 'phone' | 'keys'>('laptop');
  const [assetModel, setAssetModel] = useState('');
  const [assetSerial, setAssetSerial] = useState('');

  const [indDate, setIndDate] = useState('');
  const [indTime, setIndTime] = useState('');
  const [indLink, setIndLink] = useState('https://meet.google.com/hmn-join-2026');
  const [indHost] = useState('Sarah HR');

  const [newHireName, setNewHireName] = useState('');
  const [newHireEmail, setNewHireEmail] = useState('');
  const [newHireRole, setNewHireRole] = useState('Software Engineer');
  const [newHireDept, setNewHireDept] = useState('Software Engineering');
  const [showAddHire, setShowAddHire] = useState(false);

  const updateHire = (id: string, updates: Partial<OnboardingHire>) => {
    setHires(prev => prev.map(h => h.id === id ? { ...h, ...updates } as OnboardingHire : h));
  };

  const handleVerifyDocs = () => {
    updateHire(selectedHire.id, { docsVerified: true });
  };

  const handleGenerateId = () => {
    const nextSeq = hires.length + 5;
    const generated = `EMP-2026-000${nextSeq}`;
    updateHire(selectedHire.id, { emp_id: generated });
  };

  const handleAddAsset = (e: React.FormEvent) => {
    e.preventDefault();
    if (!assetModel || !assetSerial) return;

    const newAsset = { type: assetType, model: assetModel, serial: assetSerial };
    const updatedAssets = [...selectedHire.assets, newAsset];
    updateHire(selectedHire.id, { assets: updatedAssets });

    setAssetModel('');
    setAssetSerial('');
  };

  const handleScheduleInduction = (e: React.FormEvent) => {
    e.preventDefault();
    if (!indDate || !indTime) return;

    updateHire(selectedHire.id, {
      inductionScheduled: true,
      inductionDetails: { date: indDate, time: indTime, link: indLink, host: indHost }
    });
    setIndDate('');
    setIndTime('');
  };

  const handleToggleChecklist = (item: string) => {
    const current = selectedHire.checklist[item];
    const updatedChecklist = { ...selectedHire.checklist, [item]: !current };
    
    // Check if everything is complete
    const allDone = Object.values(updatedChecklist).every(val => val === true);
    const hasEmpId = selectedHire.emp_id !== null;
    const hasDocs = selectedHire.docsVerified;
    const isScheduled = selectedHire.inductionScheduled;
    
    let nextStatus: 'Incomplete' | 'Verified' | 'Completed' = 'Incomplete';
    if (hasEmpId && hasDocs && isScheduled && allDone) {
      nextStatus = 'Completed';
    } else if (hasDocs || hasEmpId) {
      nextStatus = 'Verified';
    }

    updateHire(selectedHire.id, {
      checklist: updatedChecklist,
      status: nextStatus
    });
  };

  const handleAddNewHire = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newHireName || !newHireEmail) return;

    const newHire: OnboardingHire = {
      id: `h${hires.length + 1}`,
      name: newHireName,
      email: newHireEmail,
      role: newHireRole,
      department: newHireDept,
      joining_date: new Date().toISOString().split('T')[0],
      status: 'Incomplete',
      emp_id: null,
      docsVerified: false,
      inductionScheduled: false,
      assets: [],
      checklist: newHireDept === 'Software Engineering' 
        ? { 'Codebase Access': false, 'Slack Channel': false, 'Compliance Training': false }
        : { 'Figma Access': false, 'Slack Channel': false, 'Compliance Training': false }
    };

    setHires([...hires, newHire]);
    setSelectedHireId(newHire.id);
    setNewHireName('');
    setNewHireEmail('');
    setShowAddHire(false);
  };

  const getAssetIcon = (type: string) => {
    switch (type) {
      case 'laptop': return <Laptop size={14} />;
      case 'monitor': return <Monitor size={14} />;
      case 'phone': return <Smartphone size={14} />;
      case 'keys': return <Key size={14} />;
      default: return <Monitor size={14} />;
    }
  };

  return (
    <div className="space-y-8 max-w-7xl mx-auto">
      {/* Title */}
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Onboarding & Asset Center</h1>
          <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Track digital onboarding pipelines, verify identities, generate IDs, schedule inductions, and allocate hardware configurations.
          </p>
        </div>
        <button 
          onClick={() => setShowAddHire(!showAddHire)}
          className="px-4 py-2.5 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl btn-glow text-xs flex items-center gap-2 cursor-pointer"
        >
          <UserPlus size={16} /> Register New Hire
        </button>
      </div>

      {/* Register Hire Modal Drawer */}
      {showAddHire && (
        <div className="glass-panel p-6 max-w-xl mx-auto">
          <h3 className="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800/80 pb-3 mb-4 flex justify-between items-center">
            <span>Register Candidate joining form</span>
            <button onClick={() => setShowAddHire(false)} className="text-slate-400 hover:text-slate-200"><X size={18} /></button>
          </h3>
          <form onSubmit={handleAddNewHire} className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-[10px] font-bold text-slate-400 uppercase">Full Name</label>
                <input type="text" value={newHireName} onChange={(e) => setNewHireName(e.target.value)} className="glass-input text-xs" placeholder="Alice Smith" required />
              </div>
              <div className="space-y-1">
                <label className="text-[10px] font-bold text-slate-400 uppercase">Personal Email</label>
                <input type="email" value={newHireEmail} onChange={(e) => setNewHireEmail(e.target.value)} className="glass-input text-xs" placeholder="alice@gmail.com" required />
              </div>
            </div>
            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-1">
                <label className="text-[10px] font-bold text-slate-400 uppercase">Job Title</label>
                <input type="text" value={newHireRole} onChange={(e) => setNewHireRole(e.target.value)} className="glass-input text-xs" placeholder="Data Scientist" required />
              </div>
              <div className="space-y-1">
                <label className="text-[10px] font-bold text-slate-400 uppercase">Department</label>
                <select value={newHireDept} onChange={(e) => setNewHireDept(e.target.value)} className="glass-input text-xs" required>
                  <option value="Software Engineering">Software Engineering</option>
                  <option value="Product Management">Product Management</option>
                  <option value="Human Resources">Human Resources</option>
                </select>
              </div>
            </div>
            <div className="flex justify-end gap-2 pt-2">
              <button type="button" onClick={() => setShowAddHire(false)} className="px-4 py-2 text-xs font-bold text-slate-400 hover:text-slate-200">Cancel</button>
              <button type="submit" className="px-4 py-2 bg-primary-500 text-slate-950 font-bold rounded-lg text-xs btn-glow cursor-pointer">Register Candidate</button>
            </div>
          </form>
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {/* Left Side: Pipeline list (col-span-4) */}
        <div className="lg:col-span-4 glass-panel p-4 max-h-[550px] overflow-y-auto space-y-2.5">
          <h3 className="text-xs font-black tracking-widest text-slate-400 uppercase px-2 mb-3">
            Onboarding Pipeline ({hires.length})
          </h3>
          {hires.map((h) => {
            const isSelected = h.id === selectedHireId;
            return (
              <div 
                key={h.id}
                onClick={() => setSelectedHireId(h.id)}
                className={`p-3.5 rounded-xl border transition-all duration-300 cursor-pointer flex justify-between items-center ${
                  isSelected 
                    ? 'bg-primary-500/10 border-primary-500/40 shadow-lg' 
                    : 'bg-slate-100/50 dark:bg-slate-950/20 border-slate-200 dark:border-slate-800/80 hover:border-slate-300 dark:hover:border-slate-700'
                }`}
              >
                <div>
                  <h4 className="text-xs font-bold text-slate-800 dark:text-slate-100">{h.name}</h4>
                  <p className="text-[10px] text-slate-400 font-semibold tracking-wide uppercase mt-0.5">{h.role}</p>
                  <p className="text-[9px] text-slate-500 mt-2 font-medium">Joining: {new Date(h.joining_date).toLocaleDateString()}</p>
                </div>
                <div className="text-right">
                  <span className={`px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider ${
                    h.status === 'Completed' ? 'bg-success/10 text-success' :
                    h.status === 'Verified' ? 'bg-warning/10 text-warning' : 'bg-slate-800 text-slate-400'
                  }`}>
                    {h.status}
                  </span>
                  <p className="text-[9px] font-bold text-primary-500 mt-2">
                    {h.emp_id || 'Pending ID'}
                  </p>
                </div>
              </div>
            );
          })}
        </div>

        {/* Right Side: Selected Details Workspace (col-span-8) */}
        <div className="lg:col-span-8 space-y-6">
          <div className="glass-panel p-6 space-y-6">
            
            {/* Header info */}
            <div className="flex justify-between items-start border-b border-slate-200 dark:border-slate-800/80 pb-4">
              <div>
                <h2 className="text-lg font-extrabold text-slate-800 dark:text-slate-100 leading-tight">{selectedHire.name}</h2>
                <p className="text-xs text-slate-400 font-semibold mt-0.5">{selectedHire.role} • {selectedHire.department}</p>
                <p className="text-[10px] text-slate-500 font-semibold mt-1">Email link: {selectedHire.email}</p>
              </div>
              <span className={`px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-wider bg-primary-500/10 text-primary-500`}>
                Onboarding Portal
              </span>
            </div>

            {/* Quick action buttons (ID & Docs) */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {/* Document verification */}
              <div className="p-4 bg-slate-100/30 dark:bg-slate-950/10 border border-slate-200 dark:border-slate-800/60 rounded-2xl flex flex-col justify-between">
                <div>
                  <h4 className="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5"><ShieldCheck size={16} className="text-primary-500" /> Verify Onboarding Docs</h4>
                  <p className="text-[10px] text-slate-400 mt-1 leading-relaxed">Check and confirm candidates passport, ID, and certificate uploads.</p>
                </div>
                <div className="flex items-center gap-2 mt-4">
                  {selectedHire.docsVerified ? (
                    <span className="text-xs font-bold text-success flex items-center gap-1"><CheckCircle2 size={14} /> Documents Verified</span>
                  ) : (
                    <button 
                      onClick={handleVerifyDocs}
                      className="px-3 py-1.5 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-lg text-[10px] cursor-pointer btn-glow"
                    >
                      Approve & Verify Docs
                    </button>
                  )}
                </div>
              </div>

              {/* ID Generation */}
              <div className="p-4 bg-slate-100/30 dark:bg-slate-950/10 border border-slate-200 dark:border-slate-800/60 rounded-2xl flex flex-col justify-between">
                <div>
                  <h4 className="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5"><Award size={16} className="text-primary-500" /> Employee ID Generation</h4>
                  <p className="text-[10px] text-slate-400 mt-1 leading-relaxed">Auto-allocate corporate sequence ID to finalize database registry.</p>
                </div>
                <div className="flex items-center gap-2 mt-4">
                  {selectedHire.emp_id ? (
                    <span className="text-xs font-bold text-primary-500 flex items-center gap-1"><CheckCircle2 size={14} /> ID: {selectedHire.emp_id}</span>
                  ) : (
                    <button 
                      onClick={handleGenerateId}
                      className="px-3 py-1.5 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-lg text-[10px] cursor-pointer btn-glow"
                    >
                      Generate Employee ID
                    </button>
                  )}
                </div>
              </div>
            </div>

            {/* Asset Allocation Panel */}
            <div className="p-5 bg-slate-100/30 dark:bg-slate-950/10 border border-slate-200 dark:border-slate-800/60 rounded-2xl space-y-4">
              <h4 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800/40 pb-2 flex items-center gap-1.5">
                <Laptop size={16} className="text-primary-500" /> Allocate Corporate Assets
              </h4>
              
              {/* Add asset form */}
              <form onSubmit={handleAddAsset} className="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                <div>
                  <label className="text-[9px] font-bold text-slate-400 uppercase">Asset Type</label>
                  <select value={assetType} onChange={(e) => setAssetType(e.target.value as 'laptop' | 'monitor' | 'phone' | 'keys')} className="glass-input text-[11px] py-2">
                    <option value="laptop">Laptop / PC</option>
                    <option value="monitor">External Monitor</option>
                    <option value="phone">Mobile Phone</option>
                    <option value="keys">Office Keys</option>
                  </select>
                </div>
                <div>
                  <label className="text-[9px] font-bold text-slate-400 uppercase">Model / Desk No</label>
                  <input type="text" value={assetModel} onChange={(e) => setAssetModel(e.target.value)} className="glass-input text-[11px] py-2" placeholder="e.g. Dell UltraSharp 27" required />
                </div>
                <div className="flex gap-2">
                  <div className="flex-grow">
                    <label className="text-[9px] font-bold text-slate-400 uppercase">Serial Number</label>
                    <input type="text" value={assetSerial} onChange={(e) => setAssetSerial(e.target.value)} className="glass-input text-[11px] py-2" placeholder="e.g. SN-098765" required />
                  </div>
                  <button type="submit" className="p-2.5 bg-primary-500 text-slate-950 rounded-xl flex items-center justify-center cursor-pointer btn-glow"><Plus size={16} /></button>
                </div>
              </form>

              {/* Allocated list */}
              {selectedHire.assets.length === 0 ? (
                <p className="text-[10px] text-slate-500 italic">No corporate hardware assets allocated yet.</p>
              ) : (
                <div className="flex flex-wrap gap-2.5">
                  {selectedHire.assets.map((asset, index) => (
                    <div key={index} className="flex items-center gap-2 px-3 py-1.5 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-[10px] font-bold">
                      <span className="text-primary-500">{getAssetIcon(asset.type)}</span>
                      <span>{asset.model}</span>
                      <span className="text-slate-400">({asset.serial})</span>
                    </div>
                  ))}
                </div>
              )}
            </div>

            {/* Induction Scheduling Panel */}
            <div className="p-5 bg-slate-100/30 dark:bg-slate-950/10 border border-slate-200 dark:border-slate-800/60 rounded-2xl space-y-4">
              <h4 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800/40 pb-2 flex items-center gap-1.5">
                <Calendar size={16} className="text-primary-500" /> Induction Scheduler
              </h4>

              {selectedHire.inductionScheduled && selectedHire.inductionDetails ? (
                <div className="p-3.5 bg-success/5 border border-success/20 rounded-xl flex items-center justify-between">
                  <div className="text-xs leading-relaxed">
                    <p className="font-bold text-slate-700 dark:text-slate-300">Session Confirmed</p>
                    <p className="text-slate-400 text-[10px] mt-0.5">Date: {new Date(selectedHire.inductionDetails.date).toLocaleDateString()} at {selectedHire.inductionDetails.time}</p>
                    <p className="text-slate-400 text-[10px]">Host: {selectedHire.inductionDetails.host}</p>
                  </div>
                  <a href={selectedHire.inductionDetails.link} target="_blank" rel="noreferrer" className="px-3 py-1.5 bg-success text-slate-950 font-black rounded-lg text-[10px] flex items-center gap-1 btn-glow">
                    <Play size={10} fill="currentColor" /> Start Meet
                  </a>
                </div>
              ) : (
                <form onSubmit={handleScheduleInduction} className="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                  <div>
                    <label className="text-[9px] font-bold text-slate-400 uppercase">Session Date</label>
                    <input type="date" value={indDate} onChange={(e) => setIndDate(e.target.value)} className="glass-input text-[11px] py-2" required />
                  </div>
                  <div>
                    <label className="text-[9px] font-bold text-slate-400 uppercase">Session Time</label>
                    <input type="time" value={indTime} onChange={(e) => setIndTime(e.target.value)} className="glass-input text-[11px] py-2" required />
                  </div>
                  <div>
                    <label className="text-[9px] font-bold text-slate-400 uppercase">Meet Link</label>
                    <input type="text" value={indLink} onChange={(e) => setIndLink(e.target.value)} className="glass-input text-[11px] py-2" required />
                  </div>
                  <button type="submit" className="w-full py-2 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl text-[10px] uppercase tracking-wider btn-glow transition-all cursor-pointer">
                    Schedule Session
                  </button>
                </form>
              )}
            </div>

            {/* Department Wise Checklist */}
            <div className="p-5 bg-slate-100/30 dark:bg-slate-950/10 border border-slate-200 dark:border-slate-800/60 rounded-2xl space-y-4">
              <h4 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800/40 pb-2 flex items-center gap-1.5">
                <FileText size={16} className="text-primary-500" /> Department Onboarding Checklist
              </h4>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-3.5 pt-2">
                {Object.entries(selectedHire.checklist).map(([item, done]) => (
                  <div 
                    key={item} 
                    onClick={() => handleToggleChecklist(item)}
                    className="flex items-center justify-between p-3.5 bg-slate-100/50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800 rounded-xl hover:border-primary-500 cursor-pointer select-none transition-colors"
                  >
                    <span className="text-xs font-semibold text-slate-700 dark:text-slate-300">{item}</span>
                    <span className={`w-5 h-5 rounded-full flex items-center justify-center border transition-all ${
                      done 
                        ? 'bg-success border-success text-slate-950' 
                        : 'border-slate-300 dark:border-slate-700 text-transparent'
                    }`}>
                      <Check size={12} strokeWidth={4} />
                    </span>
                  </div>
                ))}
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>
  );
};

export default Onboarding;
