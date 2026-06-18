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
        emergency_phone: emergencyPhone
      });
      setStatusMsg('Your profile settings have been updated successfully.');
    } catch (err: unknown) {
      const error = err as { response?: { data?: { message?: string } } };
      setErrorMsg(error.response?.data?.message || 'Failed to update profile.');
    } finally {
      setSubmitting(false);
    }
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
    </div>
  );
};
export default Profile;
