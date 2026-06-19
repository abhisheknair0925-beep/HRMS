import React, { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import api from '../lib/api';

export const Profile: React.FC = () => {
  const { user } = useAuth();
  const [phone, setPhone] = useState('');
  const [bankName, setBankName] = useState('');
  const [accountNumber, setAccountNumber] = useState('');
  const [ifscCode, setIfscCode] = useState('');
  const [emergencyName, setEmergencyName] = useState('');
  const [emergencyRelationship, setEmergencyRelationship] = useState('');
  const [emergencyPhone, setEmergencyPhone] = useState('');
  
  // Prior Employment History States
  const [employmentHistory, setEmploymentHistory] = useState<Array<{ company_name: string; designation: string; start_date: string; end_date?: string | null; description?: string }>>([]);
  const [showWorkHistoryModal, setShowWorkHistoryModal] = useState(false);
  const [editingIndex, setEditingIndex] = useState<number | null>(null);
  const [newCompany, setNewCompany] = useState('');
  const [newDesignation, setNewDesignation] = useState('');
  const [newStartDate, setNewStartDate] = useState('');
  const [newEndDate, setNewEndDate] = useState('');
  const [newDescription, setNewDescription] = useState('');

  const [statusMsg, setStatusMsg] = useState<string | null>(null);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (user?.employee) {
      const emp = user.employee;
      // Defer state updates to avoid calling setState synchronously inside useEffect
      Promise.resolve().then(() => {
        setPhone(emp.phone || '');
        setBankName(emp.bank_details?.bank_name || '');
        setAccountNumber(emp.bank_details?.account_number || '');
        setIfscCode(emp.bank_details?.ifsc_code || '');
        setEmploymentHistory(emp.employment_history || []);
        
        const emergency = emp.emergency_contacts?.[0] || {};
        setEmergencyName(emergency.name || '');
        setEmergencyRelationship(emergency.relationship || '');
        setEmergencyPhone(emergency.phone || '');
      });
    }
  }, [user]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setStatusMsg(null);
    setErrorMsg(null);
    setSubmitting(true);

    try {
      await api.post('/profile/update', {
        phone,
        bank_name: bankName,
        account_number: accountNumber,
        ifsc_code: ifscCode,
        emergency_name: emergencyName,
        emergency_relationship: emergencyRelationship,
        emergency_phone: emergencyPhone,
        employment_history: employmentHistory
      });
      setStatusMsg('Your profile settings have been updated successfully.');
    } catch (err: unknown) {
      const error = err as { response?: { data?: { message?: string } } };
      setErrorMsg(error.response?.data?.message || 'Failed to update profile.');
    } finally {
      setSubmitting(false);
    }
  };

  const handleAddOrEditWorkHistory = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newCompany || !newDesignation || !newStartDate) return;

    const entry = {
      company_name: newCompany,
      designation: newDesignation,
      start_date: newStartDate,
      end_date: newEndDate || null,
      description: newDescription
    };

    if (editingIndex !== null) {
      setEmploymentHistory(prev => prev.map((item, idx) => idx === editingIndex ? entry : item));
    } else {
      setEmploymentHistory(prev => [...prev, entry]);
    }

    setNewCompany('');
    setNewDesignation('');
    setNewStartDate('');
    setNewEndDate('');
    setNewDescription('');
    setShowWorkHistoryModal(false);
    setEditingIndex(null);
  };

  const handleEditWorkHistoryClick = (idx: number) => {
    const entry = employmentHistory[idx];
    setNewCompany(entry.company_name);
    setNewDesignation(entry.designation);
    setNewStartDate(entry.start_date);
    setNewEndDate(entry.end_date || '');
    setNewDescription(entry.description || '');
    setEditingIndex(idx);
    setShowWorkHistoryModal(true);
  };

  const handleDeleteWorkHistory = (idx: number) => {
    setEmploymentHistory(prev => prev.filter((_, i) => i !== idx));
  };

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Profile & Settings</h1>
        <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
          Manage your personal contact details, bank configurations, and emergency contact registries.
        </p>
      </div>

      {statusMsg && (
        <div className="p-4 rounded-xl bg-success/20 border border-success/30 text-success text-xs font-semibold shadow-lg backdrop-blur-sm">
          ✅ {statusMsg}
        </div>
      )}

      {errorMsg && (
        <div className="p-4 rounded-xl bg-danger/20 border border-danger/30 text-danger text-xs font-semibold shadow-lg backdrop-blur-sm">
          ❌ {errorMsg}
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        {/* Left Side: Summary Card */}
        <div className="glass-panel p-6 flex flex-col items-center text-center">
          <div className="w-24 h-24 rounded-full bg-gradient-to-tr from-primary-500 to-secondary-500 flex items-center justify-center font-bold text-slate-950 text-3xl border-4 border-white/10 shadow-lg mb-4">
            {user?.name ? user.name[0] : 'U'}
          </div>

          <h3 className="text-lg font-bold text-slate-800 dark:text-slate-100">{user?.name}</h3>
          <p className="text-xs text-slate-400 font-semibold uppercase tracking-widest mt-1">
            {user?.employee?.employee_id || 'EMP-XXXX'}
          </p>

          <div className="w-full text-left mt-8 border-t border-slate-200 dark:border-slate-800/80 pt-6 space-y-4 text-xs font-semibold">
            <div className="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800/50">
              <span className="text-slate-400">Department</span>
              <span className="text-slate-700 dark:text-slate-300">{user?.employee?.department?.name || 'N/A'}</span>
            </div>
            <div className="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800/50">
              <span className="text-slate-400">Designation</span>
              <span className="text-slate-700 dark:text-slate-300">{user?.employee?.designation?.title || 'N/A'}</span>
            </div>
            <div className="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800/50">
              <span className="text-slate-400">Email</span>
              <span className="text-slate-700 dark:text-slate-300">{user?.email}</span>
            </div>
            <div className="flex justify-between items-center">
              <span className="text-slate-400">Joining Date</span>
              <span className="text-slate-700 dark:text-slate-300">
                {user?.employee?.joining_date ? new Date(user.employee.joining_date).toLocaleDateString() : 'N/A'}
              </span>
            </div>
          </div>
        </div>

        {/* Right Side: Edit Form */}
        <div className="lg:col-span-2 glass-panel p-6">
          <form onSubmit={handleSubmit} className="space-y-6">
            
            {/* Contact settings */}
            <div>
              <h4 className="text-xs font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase border-b border-slate-200 dark:border-slate-800/80 pb-2 mb-4">Contact Settings</h4>
              <div className="space-y-1">
                <label className="text-[10px] font-bold text-slate-400 uppercase">Phone Number</label>
                <input type="text" value={phone} onChange={(e) => setPhone(e.target.value)} className="glass-input text-xs" placeholder="+1 555-0199" />
              </div>
            </div>

            {/* Bank info */}
            <div>
              <h4 className="text-xs font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase border-b border-slate-200 dark:border-slate-800/80 pb-2 mb-4">Bank Deposit Details</h4>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="md:col-span-2 space-y-1">
                  <label className="text-[10px] font-bold text-slate-400 uppercase">Bank Name</label>
                  <input type="text" value={bankName} onChange={(e) => setBankName(e.target.value)} className="glass-input text-xs" placeholder="Federal Deposit Bank" required />
                </div>
                <div className="space-y-1">
                  <label className="text-[10px] font-bold text-slate-400 uppercase">Account Number</label>
                  <input type="text" value={accountNumber} onChange={(e) => setAccountNumber(e.target.value)} className="glass-input text-xs" placeholder="0123456789" required />
                </div>
                <div className="space-y-1">
                  <label className="text-[10px] font-bold text-slate-400 uppercase">IFSC Code / Routing No</label>
                  <input type="text" value={ifscCode} onChange={(e) => setIfscCode(e.target.value)} className="glass-input text-xs" placeholder="FED0012345" required />
                </div>
              </div>
            </div>

            {/* Emergency Contacts */}
            <div>
              <h4 className="text-xs font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase border-b border-slate-200 dark:border-slate-800/80 pb-2 mb-4">Emergency Contact</h4>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="md:col-span-2 space-y-1">
                  <label className="text-[10px] font-bold text-slate-400 uppercase">Contact Name</label>
                  <input type="text" value={emergencyName} onChange={(e) => setEmergencyName(e.target.value)} className="glass-input text-xs" placeholder="Jane Doe" required />
                </div>
                <div className="space-y-1">
                  <label className="text-[10px] font-bold text-slate-400 uppercase">Relationship</label>
                  <input type="text" value={emergencyRelationship} onChange={(e) => setEmergencyRelationship(e.target.value)} className="glass-input text-xs" placeholder="Spouse" required />
                </div>
                <div className="space-y-1">
                  <label className="text-[10px] font-bold text-slate-400 uppercase">Emergency Phone</label>
                  <input type="text" value={emergencyPhone} onChange={(e) => setEmergencyPhone(e.target.value)} className="glass-input text-xs" placeholder="+1 555-0100" required />
                </div>
              </div>
            </div>

            {/* Work History */}
            <div>
              <div className="flex justify-between items-center border-b border-slate-200 dark:border-slate-800/80 pb-2 mb-4">
                <h4 className="text-xs font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase">Prior Employment History</h4>
                <button 
                  type="button" 
                  onClick={() => {
                    setEditingIndex(null);
                    setNewCompany('');
                    setNewDesignation('');
                    setNewStartDate('');
                    setNewEndDate('');
                    setNewDescription('');
                    setShowWorkHistoryModal(true);
                  }}
                  className="px-2.5 py-1.5 bg-primary-500/10 hover:bg-primary-500/20 text-primary-500 border border-primary-500/20 font-bold rounded-lg text-[10px] cursor-pointer transition-colors"
                >
                  + Add Previous Job
                </button>
              </div>

              {employmentHistory.length === 0 ? (
                <p className="text-xs text-slate-500 italic">No prior work history records added.</p>
              ) : (
                <div className="space-y-3">
                  {employmentHistory.map((history, idx) => (
                    <div key={idx} className="p-4 bg-slate-100/50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800/80 rounded-2xl flex justify-between items-start">
                      <div className="text-xs space-y-1">
                        <p className="font-bold text-slate-800 dark:text-slate-200">{history.designation}</p>
                        <p className="text-[10px] text-slate-400 font-semibold">{history.company_name}</p>
                        <p className="text-[9px] text-slate-500 mt-1">
                          Dates: {new Date(history.start_date).toLocaleDateString()} to {history.end_date ? new Date(history.end_date).toLocaleDateString() : 'Present'}
                        </p>
                        {history.description && (
                          <p className="text-[10px] text-slate-500 italic mt-2 leading-relaxed">"{history.description}"</p>
                        )}
                      </div>
                      <div className="flex gap-2 text-[9px] font-bold uppercase tracking-wider">
                        <button 
                          type="button"
                          onClick={() => handleEditWorkHistoryClick(idx)}
                          className="px-2 py-1 border border-slate-200 dark:border-slate-800 hover:border-primary-500 rounded-lg text-slate-400 hover:text-primary-500 cursor-pointer transition-colors"
                        >
                          Edit
                        </button>
                        <button 
                          type="button"
                          onClick={() => handleDeleteWorkHistory(idx)}
                          className="px-2 py-1 border border-slate-200 dark:border-slate-800 hover:border-danger rounded-lg text-slate-400 hover:text-danger cursor-pointer transition-colors"
                        >
                          Delete
                        </button>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>

            {/* Actions button */}
            <div className="flex justify-end pt-4 border-t border-slate-200 dark:border-slate-800/80">
              <button type="submit" 
                      disabled={submitting}
                      className="px-5 py-2.5 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl text-xs transition-colors btn-glow cursor-pointer disabled:opacity-50">
                {submitting ? 'Saving...' : 'Save Profile Settings'}
              </button>
            </div>

          </form>
        </div>

      </div>

      {/* Work History Form Modal */}
      {showWorkHistoryModal && (
        <div className="fixed inset-0 flex items-center justify-center z-50 p-4">
          <div className="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" onClick={() => setShowWorkHistoryModal(false)}></div>
          <div className="glass-panel w-full max-w-lg p-6 bg-white/90 dark:bg-slate-900/90 shadow-2xl relative z-10 border border-white/10 mx-auto">
            <h3 className="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800/80 pb-3 mb-4">
              {editingIndex !== null ? 'Edit Previous Job Details' : 'Add Previous Job Details'}
            </h3>
            <form onSubmit={handleAddOrEditWorkHistory} className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-1">
                  <label className="text-[9px] font-bold text-slate-400 uppercase">Company Name</label>
                  <input type="text" value={newCompany} onChange={(e) => setNewCompany(e.target.value)} className="glass-input text-xs" placeholder="Google Inc." required />
                </div>
                <div className="space-y-1">
                  <label className="text-[9px] font-bold text-slate-400 uppercase">Designation</label>
                  <input type="text" value={newDesignation} onChange={(e) => setNewDesignation(e.target.value)} className="glass-input text-xs" placeholder="Software Engineer" required />
                </div>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-1">
                  <label className="text-[9px] font-bold text-slate-400 uppercase">Start Date</label>
                  <input type="date" value={newStartDate} onChange={(e) => setNewStartDate(e.target.value)} className="glass-input text-xs" required />
                </div>
                <div className="space-y-1">
                  <label className="text-[9px] font-bold text-slate-400 uppercase">End Date (Leave blank if present)</label>
                  <input type="date" value={newEndDate} onChange={(e) => setNewEndDate(e.target.value)} className="glass-input text-xs" />
                </div>
              </div>
              <div className="space-y-1">
                <label className="text-[9px] font-bold text-slate-400 uppercase">Job Description / Responsibilities</label>
                <textarea 
                  value={newDescription} 
                  onChange={(e) => setNewDescription(e.target.value)} 
                  className="glass-input text-xs resize-none" 
                  rows={3} 
                  placeholder="Developed frontend systems using React and TypeScript..." 
                />
              </div>
              <div className="flex justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800/80">
                <button type="button" onClick={() => setShowWorkHistoryModal(false)} className="px-4 py-2 text-xs font-bold text-slate-400 hover:text-slate-200">Cancel</button>
                <button type="submit" className="px-4 py-2 bg-primary-500 text-slate-950 font-bold rounded-lg text-xs btn-glow cursor-pointer">
                  {editingIndex !== null ? 'Save Changes' : 'Add Experience'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

    </div>
  );
};
export default Profile;
