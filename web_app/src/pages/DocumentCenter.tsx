import React, { useState } from 'react';
import { FileText, Printer, FileDown, CheckCircle } from 'lucide-react';

interface EmployeeSimple {
  id: string;
  name: string;
  employee_id: string;
  title: string;
  department: string;
}

const mockEmployees: EmployeeSimple[] = [
  { id: '1', name: 'John Employee', employee_id: 'EMP-2026-0002', title: 'Frontend Developer', department: 'Software Engineering' },
  { id: '2', name: 'Sarah Manager', employee_id: 'EMP-2026-0001', title: 'Senior Product Manager', department: 'Product Management' },
  { id: '3', name: 'Sarah HR', employee_id: 'EMP-2026-0003', title: 'HR Director', department: 'Human Resources' }
];

export const DocumentCenter: React.FC = () => {
  const [empId, setEmpId] = useState('1');
  const [template, setTemplate] = useState<'offer' | 'appointment' | 'salary' | 'promotion' | 'warning' | 'relieving'>('offer');
  
  // Dynamic parameters
  const [salary, setSalary] = useState('5000');
  const [revisedSalary, setRevisedSalary] = useState('6200');
  const [newTitle, setNewTitle] = useState('Lead React Architect');
  const [effectiveDate, setEffectiveDate] = useState('2026-07-01');
  const [warningReason, setWarningReason] = useState('Repeated tardiness and failure to secure geofenced check-in perimeters.');
  const [relievingDate, setRelievingDate] = useState('2026-08-01');
  
  const [success, setSuccess] = useState(false);

  const selectedEmp = mockEmployees.find(e => e.id === empId) || mockEmployees[0];

  const handleGenerate = (e: React.FormEvent) => {
    e.preventDefault();
    setSuccess(true);
    setTimeout(() => setSuccess(false), 3000);
  };

  const handlePrint = () => {
    window.print();
  };

  // Get Letter Content template based on choice
  const renderLetterContent = () => {
    const today = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    switch (template) {
      case 'offer':
        return (
          <div className="space-y-4 text-xs leading-relaxed text-slate-800 dark:text-slate-200">
            <p className="font-bold">Date: {today}</p>
            <p>To,<br /><span className="font-bold">{selectedEmp.name}</span><br />Candidate ID: HUMN-OFF-2026</p>
            <h3 className="text-center font-extrabold text-sm uppercase underline decoration-primary-500 my-4 text-slate-900 dark:text-slate-100">Subject: Offer of Employment</h3>
            <p>Dear {selectedEmp.name.split(' ')[0]},</p>
            <p>We are pleased to offer you the position of <span className="font-bold text-primary-500">{selectedEmp.title}</span> in our <span className="font-bold">{selectedEmp.department}</span> department. You will be reporting directly to your department head at our HumaNode headquarters.</p>
            <p>Your gross monthly salary will be <span className="font-bold text-success">${Number(salary).toLocaleString()} USD</span>, subject to standard statutory tax deductions. Your tentative date of joining will be <span className="font-bold text-secondary-500">{new Date(effectiveDate).toLocaleDateString()}</span>.</p>
            <p>Please review and sign this offer letter within three business days to confirm your acceptance.</p>
            <div className="pt-8 flex justify-between">
              <div>
                <p>Best Regards,</p>
                <div className="h-10 w-24 border-b border-slate-300 dark:border-slate-700 mt-2"></div>
                <p className="font-bold text-[10px] uppercase mt-1">HumaNode Operations</p>
              </div>
              <div className="text-right">
                <p>Accepted By,</p>
                <div className="h-10 w-24 border-b border-slate-300 dark:border-slate-700 mt-2 ml-auto"></div>
                <p className="font-bold text-[10px] uppercase mt-1">{selectedEmp.name}</p>
              </div>
            </div>
          </div>
        );
      case 'appointment':
        return (
          <div className="space-y-4 text-xs leading-relaxed text-slate-800 dark:text-slate-200">
            <p className="font-bold">Date: {today}</p>
            <p>To,<br /><span className="font-bold">{selectedEmp.name}</span><br />Employee ID: {selectedEmp.employee_id}</p>
            <h3 className="text-center font-extrabold text-sm uppercase underline decoration-primary-500 my-4 text-slate-900 dark:text-slate-100">Subject: Letter of Appointment</h3>
            <p>Dear {selectedEmp.name},</p>
            <p>Following your successful onboarding checks, we are delighted to officially appoint you as <span className="font-bold text-primary-500">{selectedEmp.title}</span>, effective <span className="font-bold text-secondary-500">{new Date(effectiveDate).toLocaleDateString()}</span>.</p>
            <p>Your annual remuneration package and benefits remain as agreed during the interview phase. You will be governed by the standard employment terms, compliance protocols, and leave policies of HumaNode Inc.</p>
            <p>We welcome you to the team and look forward to a successful relationship.</p>
            <div className="pt-8">
              <p>Sincerely,</p>
              <div className="h-10 w-24 border-b border-slate-300 dark:border-slate-700 mt-2"></div>
              <p className="font-bold text-[10px] uppercase mt-1">Director of Human Resources</p>
            </div>
          </div>
        );
      case 'salary':
        return (
          <div className="space-y-4 text-xs leading-relaxed text-slate-800 dark:text-slate-200">
            <p className="font-bold">Date: {today}</p>
            <p>To,<br /><span className="font-bold">{selectedEmp.name}</span><br />Employee ID: {selectedEmp.employee_id}</p>
            <h3 className="text-center font-extrabold text-sm uppercase underline decoration-primary-500 my-4 text-slate-900 dark:text-slate-100">Subject: Salary Revision Notification</h3>
            <p>Dear {selectedEmp.name},</p>
            <p>In recognition of your contributions, performance assessments, and milestones, we are pleased to notify you that your salary has been revised effective <span className="font-bold text-secondary-500">{new Date(effectiveDate).toLocaleDateString()}</span>.</p>
            <p>Your gross monthly salary will be revised from <span className="font-bold text-slate-500">${Number(salary).toLocaleString()}</span> to <span className="font-bold text-success">${Number(revisedSalary).toLocaleString()} USD</span>.</p>
            <p>All other terms of your employment contract remain unchanged. We appreciate your dedication and drive.</p>
            <div className="pt-8">
              <p>Warm Regards,</p>
              <div className="h-10 w-24 border-b border-slate-300 dark:border-slate-700 mt-2"></div>
              <p className="font-bold text-[10px] uppercase mt-1">Compensation Committee</p>
            </div>
          </div>
        );
      case 'promotion':
        return (
          <div className="space-y-4 text-xs leading-relaxed text-slate-800 dark:text-slate-200">
            <p className="font-bold">Date: {today}</p>
            <p>To,<br /><span className="font-bold">{selectedEmp.name}</span><br />Employee ID: {selectedEmp.employee_id}</p>
            <h3 className="text-center font-extrabold text-sm uppercase underline decoration-primary-500 my-4 text-slate-900 dark:text-slate-100">Subject: Promotion to {newTitle}</h3>
            <p>Dear {selectedEmp.name},</p>
            <p>We are thrilled to congratulate you on your promotion. Effective <span className="font-bold text-secondary-500">{new Date(effectiveDate).toLocaleDateString()}</span>, your designation will change from <span className="text-slate-400 font-semibold">{selectedEmp.title}</span> to <span className="font-bold text-primary-500">{newTitle}</span>.</p>
            <p>In addition to this title change, your compensation will be revised to <span className="font-bold text-success">${Number(revisedSalary).toLocaleString()} USD</span> per month. Your department head will schedule a sync to discuss your expanded leadership guidelines.</p>
            <p>Thank you for your exceptional performance and leadership within HumaNode.</p>
            <div className="pt-8">
              <p>Best Regards,</p>
              <div className="h-10 w-24 border-b border-slate-300 dark:border-slate-700 mt-2"></div>
              <p className="font-bold text-[10px] uppercase mt-1">Chief Executive Officer</p>
            </div>
          </div>
        );
      case 'warning':
        return (
          <div className="space-y-4 text-xs leading-relaxed text-slate-800 dark:text-slate-200">
            <p className="font-bold">Date: {today}</p>
            <p>To,<br /><span className="font-bold">{selectedEmp.name}</span><br />Employee ID: {selectedEmp.employee_id}</p>
            <h3 className="text-center font-extrabold text-sm uppercase underline decoration-danger text-danger my-4">Subject: Written Warning Letter</h3>
            <p>Dear {selectedEmp.name},</p>
            <p>This is a formal written warning regarding compliance issues noted in your department records. Specifically, the management has flagged the following infraction:</p>
            <div className="p-3.5 bg-danger/5 border border-danger/20 rounded-xl text-[11px] font-bold text-danger italic">
              "{warningReason}"
            </div>
            <p>Please note that consistent adherence to company shift guidelines and geofenced checkpoint tracking is mandatory. Failure to improve performance or correct these issues within the current review period may result in further disciplinary action up to termination.</p>
            <div className="pt-8 flex justify-between">
              <div>
                <p>Issued By,</p>
                <div className="h-10 w-24 border-b border-slate-300 dark:border-slate-700 mt-2"></div>
                <p className="font-bold text-[10px] uppercase mt-1">HR Compliance Audit</p>
              </div>
              <div className="text-right">
                <p>Employee Acknowledgment,</p>
                <div className="h-10 w-24 border-b border-slate-300 dark:border-slate-700 mt-2 ml-auto"></div>
                <p className="font-bold text-[10px] uppercase mt-1">{selectedEmp.name}</p>
              </div>
            </div>
          </div>
        );
      case 'relieving':
        return (
          <div className="space-y-4 text-xs leading-relaxed text-slate-800 dark:text-slate-200">
            <p className="font-bold">Date: {today}</p>
            <p>To,<br /><span className="font-bold">{selectedEmp.name}</span><br />Employee ID: {selectedEmp.employee_id}</p>
            <h3 className="text-center font-extrabold text-sm uppercase underline decoration-secondary-500 my-4 text-slate-900 dark:text-slate-100">Subject: Relieving & Experience Certificate</h3>
            <p>Dear {selectedEmp.name},</p>
            <p>This is to confirm that you have been relieved of your duties as <span className="font-bold">{selectedEmp.title}</span> at HumaNode, effective close of business on <span className="font-bold text-secondary-500">{new Date(relievingDate).toLocaleDateString()}</span>.</p>
            <p>During your tenure from joining date until relieving date, you proved to be a dedicated team player. We confirm that your full and final settlement accounts have been calculated and completed.</p>
            <p>We thank you for your services and wish you success in your future endeavors.</p>
            <div className="pt-8">
              <p>Best Regards,</p>
              <div className="h-10 w-24 border-b border-slate-300 dark:border-slate-700 mt-2"></div>
              <p className="font-bold text-[10px] uppercase mt-1">HR Director, HumaNode</p>
            </div>
          </div>
        );
    }
  };

  return (
    <div className="space-y-8 max-w-7xl mx-auto">
      {/* Title */}
      <div>
        <h1 className="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Official Document Center</h1>
        <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
          Generate, verify, and print official letters (Offer Letters, Warnings, Promotions, Relieving Certificates) using automated company templates.
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
        
        {/* Left Form: Parameters (col-span-5) */}
        <div className="lg:col-span-5 flex flex-col justify-between">
          <div className="glass-panel p-6 space-y-6">
            <h3 className="text-xs font-black tracking-widest text-slate-400 uppercase border-b border-slate-200 dark:border-slate-800/80 pb-3">
              Configure Parameters
            </h3>

            {success && (
              <div className="p-3 bg-success/20 border border-success/30 text-success rounded-xl text-xs font-semibold shadow-lg backdrop-blur-sm flex items-center gap-2">
                <CheckCircle size={16} /> Letter generated and saved to document locker!
              </div>
            )}

            <form onSubmit={handleGenerate} className="space-y-4">
              {/* Select Employee */}
              <div className="space-y-1">
                <label className="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Target Profile</label>
                <select 
                  value={empId} 
                  onChange={(e) => setEmpId(e.target.value)} 
                  className="glass-input text-xs"
                  required
                >
                  {mockEmployees.map(e => (
                    <option key={e.id} value={e.id}>{e.name} ({e.employee_id})</option>
                  ))}
                </select>
              </div>

              {/* Select Template Type */}
              <div className="space-y-1">
                <label className="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Letter Template</label>
                <select 
                  value={template} 
                  onChange={(e) => setTemplate(e.target.value as 'offer' | 'appointment' | 'salary' | 'promotion' | 'warning' | 'relieving')} 
                  className="glass-input text-xs"
                  required
                >
                  <option value="offer">Offer Letter</option>
                  <option value="appointment">Appointment Letter</option>
                  <option value="salary">Salary Revision Notice</option>
                  <option value="promotion">Promotion Letter</option>
                  <option value="warning">Written Warning Letter</option>
                  <option value="relieving">Experience & Relieving Certificate</option>
                </select>
              </div>

              {/* Conditional parameters based on template */}
              {(template === 'offer' || template === 'appointment' || template === 'salary') && (
                <div className="space-y-1">
                  <label className="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Current Compensation ($/mo)</label>
                  <input 
                    type="number" 
                    value={salary} 
                    onChange={(e) => setSalary(e.target.value)} 
                    className="glass-input text-xs" 
                    placeholder="5000"
                    required
                  />
                </div>
              )}

              {(template === 'salary' || template === 'promotion') && (
                <div className="space-y-1">
                  <label className="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Revised Compensation ($/mo)</label>
                  <input 
                    type="number" 
                    value={revisedSalary} 
                    onChange={(e) => setRevisedSalary(e.target.value)} 
                    className="glass-input text-xs" 
                    placeholder="6200"
                    required
                  />
                </div>
              )}

              {template === 'promotion' && (
                <div className="space-y-1">
                  <label className="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Revised Job Title</label>
                  <input 
                    type="text" 
                    value={newTitle} 
                    onChange={(e) => setNewTitle(e.target.value)} 
                    className="glass-input text-xs" 
                    placeholder="Lead React Architect"
                    required
                  />
                </div>
              )}

              {template === 'warning' && (
                <div className="space-y-1">
                  <label className="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Warning Reason</label>
                  <textarea 
                    value={warningReason} 
                    onChange={(e) => setWarningReason(e.target.value)} 
                    className="glass-input text-xs resize-none" 
                    rows={4}
                    required
                  />
                </div>
              )}

              {template === 'relieving' && (
                <div className="space-y-1">
                  <label className="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Relieving Effective Date</label>
                  <input 
                    type="date" 
                    value={relievingDate} 
                    onChange={(e) => setRelievingDate(e.target.value)} 
                    className="glass-input text-xs" 
                    required
                  />
                </div>
              )}

              {template !== 'warning' && template !== 'relieving' && (
                <div className="space-y-1">
                  <label className="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Effective Date</label>
                  <input 
                    type="date" 
                    value={effectiveDate} 
                    onChange={(e) => setEffectiveDate(e.target.value)} 
                    className="glass-input text-xs" 
                    required
                  />
                </div>
              )}

              <button 
                type="submit" 
                className="w-full py-3 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl text-xs uppercase tracking-wider btn-glow transition-all cursor-pointer"
              >
                Compile Draft Letter
              </button>
            </form>
          </div>
        </div>

        {/* Right Preview Pane (col-span-7) */}
        <div className="lg:col-span-7 flex flex-col justify-between">
          <div className="glass-panel p-8 flex flex-col justify-between h-full bg-white/60 dark:bg-slate-900/60 shadow-xl border border-white/20 dark:border-slate-800 relative overflow-hidden print:bg-white print:text-black print:border-none print:shadow-none print:p-0">
            
            {/* Watermark Background icon */}
            <div className="absolute top-[40%] left-[50%] -translate-x-[50%] -translate-y-[50%] opacity-5 text-slate-500 dark:text-slate-100 select-none pointer-events-none print:hidden">
              <FileText size={450} />
            </div>

            {/* Letter Head */}
            <div className="relative z-10">
              <div className="flex justify-between items-start border-b border-slate-200 dark:border-slate-800/80 pb-6 mb-6">
                <div>
                  <h2 className="text-xl font-black bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent print:text-slate-900">
                    HUMN.
                  </h2>
                  <p className="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">HumaNode SaaS Enterprise Platform</p>
                </div>
                <div className="text-right text-[9px] text-slate-400 font-semibold space-y-0.5">
                  <p>100 Cyber Tower, Sector-V</p>
                  <p>San Francisco, CA 94103</p>
                  <p>ops@humanode.net</p>
                </div>
              </div>

              {/* Letter Body */}
              <div className="min-h-[350px]">
                {renderLetterContent()}
              </div>
            </div>

            {/* Print & Download panel */}
            <div className="relative z-10 flex gap-3 border-t border-slate-200 dark:border-slate-800/80 pt-6 mt-8 print:hidden">
              <button 
                onClick={handlePrint}
                className="flex-1 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-xl text-xs transition-colors flex items-center justify-center gap-2 cursor-pointer"
              >
                <Printer size={14} /> Print Letter
              </button>
              <button 
                onClick={handleGenerate}
                className="flex-1 py-2.5 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl text-xs transition-colors btn-glow flex items-center justify-center gap-2 cursor-pointer"
              >
                <FileDown size={14} /> Save & Download PDF
              </button>
            </div>

          </div>
        </div>

      </div>
    </div>
  );
};

export default DocumentCenter;
