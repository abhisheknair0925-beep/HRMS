import React, { useState } from 'react';
import { FileText, TrendingUp, Printer } from 'lucide-react';

interface SalaryRevisionRecord {
  id: string;
  reviewer: string;
  date: string;
  previous_base: number;
  new_base: number;
}

interface EmployeePayroll {
  id: string;
  name: string;
  employee_id: string;
  base_pay: number;
  hra: number;
  allowance: number;
  pf: number;
  tax: number;
  revisions: SalaryRevisionRecord[];
  payslips: Array<{ month: string; net: number; status: string }>;
}

export const Payroll: React.FC = () => {
  const [employees, setEmployees] = useState<EmployeePayroll[]>([
    {
      id: 'p1',
      name: 'John Employee',
      employee_id: 'EMP-2026-0002',
      base_pay: 4000,
      hra: 800,
      allowance: 400,
      pf: 320,
      tax: 380,
      revisions: [
        { id: 'r1', reviewer: 'Annual Appraisal Board', date: '2026-01-15', previous_base: 3500, new_base: 4000 }
      ],
      payslips: [
        { month: 'May 2026', net: 4500, status: 'Released' },
        { month: 'April 2026', net: 4500, status: 'Released' }
      ]
    },
    {
      id: 'p2',
      name: 'Sarah Manager',
      employee_id: 'EMP-2026-0001',
      base_pay: 6000,
      hra: 1200,
      allowance: 600,
      pf: 480,
      tax: 620,
      revisions: [],
      payslips: [
        { month: 'May 2026', net: 6700, status: 'Released' },
        { month: 'April 2026', net: 6700, status: 'Released' }
      ]
    }
  ]);

  const [selectedEmpId, setSelectedEmpId] = useState('p1');
  const selectedEmp = employees.find(e => e.id === selectedEmpId) || employees[0];

  // Forms states
  const [revisionAmt, setRevisionAmt] = useState('4500');
  const [success, setSuccess] = useState(false);
  const [generateMonth, setGenerateMonth] = useState('June 2026');
  const [generating, setGenerating] = useState(false);

  // Computations
  const grossPay = selectedEmp.base_pay + selectedEmp.hra + selectedEmp.allowance;
  const totalDeductions = selectedEmp.pf + selectedEmp.tax;
  const netPay = grossPay - totalDeductions;

  const handleRevision = (e: React.FormEvent) => {
    e.preventDefault();
    if (!revisionAmt) return;

    const parsedBase = Number(revisionAmt);
    const newRev: SalaryRevisionRecord = {
      id: String(Date.now()),
      reviewer: 'Compensation Committee',
      date: new Date().toISOString().split('T')[0],
      previous_base: selectedEmp.base_pay,
      new_base: parsedBase
    };

    setEmployees(prev => prev.map(emp => {
      if (emp.id === selectedEmp.id) {
        // Recalculate PF (8%) and Tax (10%) based on new base
        return {
          ...emp,
          base_pay: parsedBase,
          pf: Math.round(parsedBase * 0.08),
          tax: Math.round(parsedBase * 0.10),
          revisions: [newRev, ...emp.revisions]
        };
      }
      return emp;
    }));

    setSuccess(true);
    setTimeout(() => setSuccess(false), 3000);
  };

  const handleGeneratePayslips = () => {
    setGenerating(true);
    setTimeout(() => {
      setEmployees(prev => prev.map(emp => {
        const gross = emp.base_pay + emp.hra + emp.allowance;
        const net = gross - (emp.pf + emp.tax);
        // Avoid duplicate payslips for the same month
        if (emp.payslips.some(p => p.month === generateMonth)) return emp;
        return {
          ...emp,
          payslips: [{ month: generateMonth, net: net, status: 'Released' }, ...emp.payslips]
        };
      }));
      setGenerating(false);
      alert(`Monthly payslips for ${generateMonth} generated successfully!`);
    }, 1500);
  };

  const handlePrintSlip = (month: string, net: number) => {
    alert(`Generating print payload for ${selectedEmp.name} (${month}) - Net: $${net.toLocaleString()}`);
  };

  return (
    <div className="space-y-8 max-w-7xl mx-auto">
      {/* Title */}
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Enterprise Payroll Hub</h1>
          <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Configure salary structures, process monthly payslips, and manage appraisal adjustments and salary revisions.
          </p>
        </div>
        <div className="flex gap-2 items-center">
          <select 
            value={generateMonth} 
            onChange={(e) => setGenerateMonth(e.target.value)}
            className="glass-input text-xs py-2 h-10 w-36"
          >
            <option value="June 2026">June 2026</option>
            <option value="July 2026">July 2026</option>
            <option value="August 2026">August 2026</option>
          </select>
          <button 
            onClick={handleGeneratePayslips}
            disabled={generating}
            className="px-4 py-2.5 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl btn-glow text-xs flex items-center gap-2 cursor-pointer disabled:opacity-50"
          >
            {generating ? 'Processing...' : 'Run Monthly Payroll'}
          </button>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {/* Left List (col-span-4) */}
        <div className="lg:col-span-4 glass-panel p-4 max-h-[550px] overflow-y-auto space-y-2.5">
          <h3 className="text-xs font-black tracking-widest text-slate-400 uppercase px-2 mb-3">
            Employee Directory ({employees.length})
          </h3>
          {employees.map((e) => {
            const isSelected = e.id === selectedEmpId;
            return (
              <div 
                key={e.id}
                onClick={() => {
                  setSelectedEmpId(e.id);
                  setSuccess(false);
                }}
                className={`p-3.5 rounded-xl border transition-all duration-300 cursor-pointer flex justify-between items-center ${
                  isSelected 
                    ? 'bg-primary-500/10 border-primary-500/40 shadow-lg' 
                    : 'bg-slate-100/50 dark:bg-slate-950/20 border-slate-200 dark:border-slate-800/80 hover:border-slate-300 dark:hover:border-slate-700'
                }`}
              >
                <div>
                  <h4 className="text-xs font-bold text-slate-800 dark:text-slate-100">{e.name}</h4>
                  <p className="text-[10px] text-slate-400 font-semibold tracking-wide uppercase mt-0.5">{e.employee_id}</p>
                </div>
                <div className="text-right">
                  <p className="text-xs font-bold text-success">${e.base_pay.toLocaleString()}</p>
                  <p className="text-[8px] text-slate-500 font-semibold tracking-wide uppercase mt-1">Base Salary</p>
                </div>
              </div>
            );
          })}
        </div>

        {/* Right Details (col-span-8) */}
        <div className="lg:col-span-8 space-y-6">
          
          {/* Salary Structure Info */}
          <div className="glass-panel p-6 space-y-6">
            <div className="flex justify-between items-start border-b border-slate-200 dark:border-slate-800/80 pb-4">
              <div>
                <h2 className="text-lg font-extrabold text-slate-800 dark:text-slate-100 leading-tight">Salary Structure</h2>
                <p className="text-xs text-slate-400 font-semibold mt-0.5">{selectedEmp.name} ({selectedEmp.employee_id})</p>
              </div>
              <div className="text-right">
                <span className="text-2xl font-black bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent">${netPay.toLocaleString()}</span>
                <p className="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Expected Monthly Net Pay</p>
              </div>
            </div>

            {/* Calculations Breakdown */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
              
              {/* Earnings */}
              <div className="p-4 bg-success/5 border border-success/15 rounded-2xl space-y-3">
                <h4 className="text-[10px] font-bold tracking-widest text-success uppercase">Gross Earnings</h4>
                <div className="space-y-2 text-xs font-semibold">
                  <div className="flex justify-between">
                    <span className="text-slate-400">Basic Base Salary</span>
                    <span className="text-slate-800 dark:text-slate-200">${selectedEmp.base_pay.toLocaleString()}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-slate-400">HRA Allowance (20%)</span>
                    <span className="text-slate-800 dark:text-slate-200">${selectedEmp.hra.toLocaleString()}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-slate-400">Special Allowances</span>
                    <span className="text-slate-800 dark:text-slate-200">${selectedEmp.allowance.toLocaleString()}</span>
                  </div>
                  <div className="flex justify-between border-t border-success/10 pt-2 font-bold text-success text-sm">
                    <span>Total Gross Salary</span>
                    <span>${grossPay.toLocaleString()}</span>
                  </div>
                </div>
              </div>

              {/* Deductions */}
              <div className="p-4 bg-danger/5 border border-danger/15 rounded-2xl space-y-3">
                <h4 className="text-[10px] font-bold tracking-widest text-danger uppercase">Statutory Deductions</h4>
                <div className="space-y-2 text-xs font-semibold">
                  <div className="flex justify-between">
                    <span className="text-slate-400">Provident Fund Contribution (8%)</span>
                    <span className="text-slate-800 dark:text-slate-200">${selectedEmp.pf.toLocaleString()}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-slate-400">Professional Income Tax (10%)</span>
                    <span className="text-slate-800 dark:text-slate-200">${selectedEmp.tax.toLocaleString()}</span>
                  </div>
                  <div className="flex justify-between border-t border-danger/10 pt-2 font-bold text-danger text-sm">
                    <span>Total Deductions</span>
                    <span>${totalDeductions.toLocaleString()}</span>
                  </div>
                </div>
              </div>

            </div>
          </div>

          {/* Salary Revision Form & Log */}
          <div className="glass-panel p-6 space-y-4">
            <h4 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800/80 pb-3 mb-2 flex items-center gap-1.5">
              <TrendingUp size={16} className="text-primary-500" /> Salary Revision Center
            </h4>

            {success && (
              <div className="p-3 bg-success/20 border border-success/30 text-success rounded-xl text-xs font-semibold shadow-lg backdrop-blur-sm">
                ✅ Base Pay and deduction percentages updated successfully!
              </div>
            )}

            <form onSubmit={handleRevision} className="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
              <div className="md:col-span-2 space-y-1">
                <label className="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Enter New Basic Salary ($/mo)</label>
                <input 
                  type="number" 
                  value={revisionAmt} 
                  onChange={(e) => setRevisionAmt(e.target.value)} 
                  className="glass-input text-xs" 
                  placeholder="e.g. 5200" 
                  required 
                />
              </div>
              <button 
                type="submit" 
                className="w-full py-2.5 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl text-xs uppercase tracking-wider btn-glow transition-all cursor-pointer"
              >
                Approve Salary Rev.
              </button>
            </form>

            {/* Revision Audit History */}
            {selectedEmp.revisions.length > 0 && (
              <div className="space-y-2.5 pt-3">
                <p className="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Salary Adjustments Audit Trail</p>
                {selectedEmp.revisions.map(rev => (
                  <div key={rev.id} className="flex justify-between items-center p-3.5 bg-slate-100/30 dark:bg-slate-950/15 border border-slate-200 dark:border-slate-800/60 rounded-xl text-xs font-semibold">
                    <div>
                      <p className="text-slate-700 dark:text-slate-300">Revised by {rev.reviewer}</p>
                      <p className="text-[9px] text-slate-400 mt-0.5">Approved on {new Date(rev.date).toLocaleDateString()}</p>
                    </div>
                    <div className="text-right">
                      <p className="text-success font-bold">${rev.new_base.toLocaleString()}</p>
                      <p className="text-[8px] text-slate-400 mt-0.5">Prior: ${rev.previous_base.toLocaleString()}</p>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>

          {/* Generated Slips */}
          <div className="glass-panel p-6 space-y-4">
            <h4 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800/80 pb-3 mb-2 flex items-center gap-1.5">
              <FileText size={16} className="text-primary-500" /> Payslip registry
            </h4>

            {selectedEmp.payslips.length === 0 ? (
              <p className="text-xs text-slate-500 italic">No payslips released in this cycle.</p>
            ) : (
              <div className="space-y-3">
                {selectedEmp.payslips.map((slip, i) => (
                  <div key={i} className="flex justify-between items-center p-4 bg-slate-100/50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800/80 rounded-2xl">
                    <div className="flex items-center gap-3">
                      <span className="text-2xl">💵</span>
                      <div>
                        <p className="text-xs font-bold text-slate-800 dark:text-slate-200">{slip.month}</p>
                        <p className="text-[10px] text-slate-400 mt-0.5">Release Status: <span className="text-success font-bold">{slip.status}</span></p>
                      </div>
                    </div>
                    <div className="flex items-center gap-3">
                      <div className="text-right mr-2">
                        <p className="text-xs font-bold text-slate-800 dark:text-slate-200">${slip.net.toLocaleString()}</p>
                        <p className="text-[8px] text-slate-400 mt-0.5 uppercase">Net Credited</p>
                      </div>
                      <button 
                        onClick={() => handlePrintSlip(slip.month, slip.net)}
                        className="p-2 border border-slate-200 dark:border-slate-800 hover:border-primary-500 rounded-xl text-slate-400 hover:text-primary-500 cursor-pointer transition-colors"
                      >
                        <Printer size={14} />
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>

        </div>

      </div>
    </div>
  );
};

export default Payroll;
