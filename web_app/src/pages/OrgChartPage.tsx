import React, { useState } from 'react';
import { Move, ArrowRight, CheckCircle2 } from 'lucide-react';

interface OrgMember {
  id: string;
  name: string;
  role: string;
  department: string;
  manager_id: string | null;
}

export const OrgChartPage: React.FC = () => {
  const [members, setMembers] = useState<OrgMember[]>([
    { id: '1', name: 'Admin User', role: 'Systems Administrator', department: 'Executive Operations', manager_id: null },
    { id: '2', name: 'Sarah Manager', role: 'Senior Product Manager', department: 'Product Management', manager_id: '1' },
    { id: '3', name: 'Sarah HR', role: 'HR Director', department: 'Human Resources', manager_id: '1' },
    { id: '4', name: 'John Employee', role: 'Frontend Developer', department: 'Software Engineering', manager_id: '2' },
    { id: '5', name: 'Alice Developer', role: 'Backend Engineer', department: 'Software Engineering', manager_id: '2' }
  ]);

  const [selectedEmpId, setSelectedEmpId] = useState('4');
  const [newManagerId, setNewManagerId] = useState('3'); // Default target Sarah HR
  const [newDept, setNewDept] = useState('Human Resources');
  const [success, setSuccess] = useState(false);

  const handleTransfer = (e: React.FormEvent) => {
    e.preventDefault();
    if (selectedEmpId === newManagerId) {
      alert("Employee cannot report to themselves!");
      return;
    }

    setMembers(prev => prev.map(m => {
      if (m.id === selectedEmpId) {
        return {
          ...m,
          manager_id: newManagerId,
          department: newDept
        };
      }
      return m;
    }));

    setSuccess(true);
    setTimeout(() => setSuccess(false), 3000);
  };

  // Build Tree Structure for visual rendering
  const getSubordinates = (managerId: string | null) => {
    return members.filter(m => m.manager_id === managerId);
  };

  const getManagerName = (id: string | null) => {
    if (!id) return 'None';
    const m = members.find(x => x.id === id);
    return m ? m.name : 'Unknown';
  };

  // Recursive Tree Node Renderer
  const renderTreeNode = (member: OrgMember) => {
    const subs = getSubordinates(member.id);
    return (
      <div key={member.id} className="flex flex-col items-center">
        {/* Member Node card */}
        <div className="glass-panel p-4 text-center min-w-[200px] border border-primary-500/20 shadow-md relative z-10 bg-slate-900/60 backdrop-blur-xl">
          <div className="w-10 h-10 rounded-xl bg-gradient-to-tr from-primary-500 to-secondary-500 flex items-center justify-center font-bold text-slate-950 text-xs mx-auto mb-2">
            {member.name[0]}{member.name.split(' ')[1]?.[0] || ''}
          </div>
          <h4 className="text-xs font-bold text-slate-100">{member.name}</h4>
          <p className="text-[9px] text-slate-400 font-semibold tracking-wide uppercase mt-0.5">{member.role}</p>
          <span className="inline-block mt-2 px-2 py-0.5 bg-primary-500/10 text-primary-500 text-[8px] font-black uppercase tracking-wider rounded-full">
            {member.department}
          </span>
        </div>

        {/* Connectors & Subordinates */}
        {subs.length > 0 && (
          <div className="flex flex-col items-center mt-6 w-full">
            {/* Vertical pipe */}
            <div className="w-0.5 h-6 bg-slate-200 dark:bg-slate-800"></div>

            {/* Horizontal pipe container */}
            <div className="flex gap-8 relative pt-6 border-t border-slate-200 dark:border-slate-800/80">
              {subs.map(sub => renderTreeNode(sub))}
            </div>
          </div>
        )}
      </div>
    );
  };

  const rootMember = members.find(m => m.manager_id === null);

  return (
    <div className="space-y-8 max-w-7xl mx-auto">
      {/* Title */}
      <div>
        <h1 className="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Organization Chart & Transfers</h1>
        <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
          Visualize active reporting relationships and run employee transfer processes across departments and management nodes.
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {/* Left Form: Transfer Employee (col-span-4) */}
        <div className="lg:col-span-4">
          <div className="glass-panel p-5 space-y-6">
            <h3 className="text-xs font-black tracking-widest text-slate-400 uppercase border-b border-slate-200 dark:border-slate-800/80 pb-3 flex items-center gap-1.5">
              <Move size={16} className="text-primary-500" /> Execute Employee Transfer
            </h3>

            {success && (
              <div className="p-3 bg-success/20 border border-success/30 text-success rounded-xl text-xs font-semibold shadow-lg backdrop-blur-sm flex items-center gap-2">
                <CheckCircle2 size={16} /> Employee transfer executed and Org Tree updated!
              </div>
            )}

            <form onSubmit={handleTransfer} className="space-y-4">
              
              {/* Select Employee to transfer */}
              <div className="space-y-1">
                <label className="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Target Employee</label>
                <select 
                  value={selectedEmpId} 
                  onChange={(e) => setSelectedEmpId(e.target.value)} 
                  className="glass-input text-xs"
                  required
                >
                  {members.filter(m => m.manager_id !== null).map(m => (
                    <option key={m.id} value={m.id}>{m.name} ({m.department})</option>
                  ))}
                </select>
              </div>

              {/* Show current reporting manager */}
              <div className="p-3 bg-slate-100 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-800 rounded-xl text-[10px] font-semibold text-slate-500 flex justify-between items-center">
                <span>Current Manager:</span>
                <span className="font-bold text-slate-700 dark:text-slate-300">
                  {getManagerName(members.find(x => x.id === selectedEmpId)?.manager_id || null)}
                </span>
              </div>

              <div className="flex justify-center text-slate-400 py-1">
                <ArrowRight size={16} className="rotate-90 md:rotate-0" />
              </div>

              {/* Select New Manager */}
              <div className="space-y-1">
                <label className="text-[10px] font-bold text-slate-400 uppercase tracking-wide">New Reporting Manager</label>
                <select 
                  value={newManagerId} 
                  onChange={(e) => setNewManagerId(e.target.value)} 
                  className="glass-input text-xs"
                  required
                >
                  {members.map(m => (
                    <option key={m.id} value={m.id}>{m.name} ({m.role})</option>
                  ))}
                </select>
              </div>

              {/* Select New Department */}
              <div className="space-y-1">
                <label className="text-[10px] font-bold text-slate-400 uppercase tracking-wide">New Department Mapping</label>
                <select 
                  value={newDept} 
                  onChange={(e) => setNewDept(e.target.value)} 
                  className="glass-input text-xs"
                  required
                >
                  <option value="Software Engineering">Software Engineering</option>
                  <option value="Product Management">Product Management</option>
                  <option value="Human Resources">Human Resources</option>
                  <option value="Executive Operations">Executive Operations</option>
                </select>
              </div>

              <button 
                type="submit" 
                className="w-full py-2.5 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl text-xs uppercase tracking-wider btn-glow transition-all cursor-pointer"
              >
                Apply Transfer Map
              </button>
            </form>
          </div>
        </div>

        {/* Right Preview Tree: Org Chart Visual (col-span-8) */}
        <div className="lg:col-span-8 overflow-x-auto pb-6">
          <div className="glass-panel p-8 min-h-[500px] flex items-start justify-center min-w-[700px]">
            {rootMember && renderTreeNode(rootMember)}
          </div>
        </div>

      </div>
    </div>
  );
};

export default OrgChartPage;
