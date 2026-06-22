import React, { useState } from 'react';
import { FileText, Download, Filter, BarChart2, Calendar, DollarSign, Award } from 'lucide-react';
import { BarChart, Bar, XAxis, YAxis, ResponsiveContainer, Tooltip, AreaChart, Area } from 'recharts';

interface AttendanceSummary {
  name: string;
  id: string;
  dept: string;
  presentRate: number;
  avgClockIn: string;
  lateArrivals: number;
}

export const Reports: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'attendance' | 'leaves' | 'payroll'>('attendance');
  const [deptFilter, setDeptFilter] = useState('All');

  // 1. Attendance Data
  const attendanceSummaries: AttendanceSummary[] = [
    { name: 'John Employee', id: 'EMP-0002', dept: 'Software Engineering', presentRate: 95, avgClockIn: '09:05 AM', lateArrivals: 3 },
    { name: 'Sarah Manager', id: 'EMP-0001', dept: 'Product Management', presentRate: 98, avgClockIn: '08:58 AM', lateArrivals: 1 },
    { name: 'Sarah HR', id: 'EMP-0003', dept: 'Human Resources', presentRate: 96, avgClockIn: '09:02 AM', lateArrivals: 0 },
    { name: 'Alice Developer', id: 'EMP-0005', dept: 'Software Engineering', presentRate: 92, avgClockIn: '09:12 AM', lateArrivals: 4 }
  ];

  const filteredAttendance = deptFilter === 'All' 
    ? attendanceSummaries 
    : attendanceSummaries.filter(a => a.dept === deptFilter);

  // 2. Leave Distribution Data
  const leaveDistribution = [
    { type: 'Sick Leave', allocated: 40, used: 12, color: 'bg-primary-500' },
    { type: 'Casual Leave', allocated: 48, used: 18, color: 'bg-secondary-500' },
    { type: 'Annual Leave', allocated: 60, used: 25, color: 'bg-indigo-500' }
  ];

  // Chart Data: Leave counts by dept
  const leaveChartData = [
    { name: 'Engineering', Sick: 5, Casual: 8, Annual: 12 },
    { name: 'Product', Sick: 3, Casual: 4, Annual: 7 },
    { name: 'HR', Sick: 4, Casual: 6, Annual: 6 }
  ];

  // 3. Payroll Chart Data
  const monthlyPayrollData = [
    { month: 'Jan', Payout: 18000, Tax: 2200, Benefits: 1200 },
    { month: 'Feb', Payout: 18000, Tax: 2200, Benefits: 1200 },
    { month: 'Mar', Payout: 19500, Tax: 2400, Benefits: 1300 },
    { month: 'Apr', Payout: 19500, Tax: 2400, Benefits: 1300 },
    { month: 'May', Payout: 23800, Tax: 2900, Benefits: 1600 },
    { month: 'Jun', Payout: 23800, Tax: 2900, Benefits: 1600 }
  ];

  const handleExport = (reportType: string) => {
    alert(`Generating ${reportType} report payload. Compilation initiated (hmn_${reportType}_report_2026.csv).`);
  };

  return (
    <div className="space-y-8 max-w-7xl mx-auto">
      {/* Title */}
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Reports & Analytics Hub</h1>
          <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Analyze time sheet analytics, evaluate team leave metrics, and inspect historical payroll payout charts.
          </p>
        </div>
        <button 
          onClick={() => handleExport(activeTab)}
          className="px-4 py-2.5 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl btn-glow text-xs flex items-center gap-2 cursor-pointer"
        >
          <Download size={16} /> Export Selected Data
        </button>
      </div>

      {/* Tabs */}
      <div className="flex gap-2 border-b border-slate-200 dark:border-slate-800/85 pb-0.5">
        <button 
          onClick={() => setActiveTab('attendance')}
          className={`px-4 py-2 text-xs font-bold border-b-2 transition-all duration-200 flex items-center gap-2 cursor-pointer ${
            activeTab === 'attendance' 
              ? 'border-primary-500 text-primary-500' 
              : 'border-transparent text-slate-400 hover:text-slate-300'
          }`}
        >
          <Calendar size={14} /> Attendance Reports
        </button>
        <button 
          onClick={() => setActiveTab('leaves')}
          className={`px-4 py-2 text-xs font-bold border-b-2 transition-all duration-200 flex items-center gap-2 cursor-pointer ${
            activeTab === 'leaves' 
              ? 'border-primary-500 text-primary-500' 
              : 'border-transparent text-slate-400 hover:text-slate-300'
          }`}
        >
          <FileText size={14} /> Leave Reports
        </button>
        <button 
          onClick={() => setActiveTab('payroll')}
          className={`px-4 py-2 text-xs font-bold border-b-2 transition-all duration-200 flex items-center gap-2 cursor-pointer ${
            activeTab === 'payroll' 
              ? 'border-primary-500 text-primary-500' 
              : 'border-transparent text-slate-400 hover:text-slate-300'
          }`}
        >
          <DollarSign size={14} /> Payroll Reports
        </button>
      </div>

      {/* 1. Attendance Reports Tab */}
      {activeTab === 'attendance' && (
        <div className="space-y-6">
          {/* Stats top */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div className="glass-panel p-5 text-center">
              <BarChart2 className="mx-auto text-primary-500 mb-2" size={24} />
              <p className="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Average Present Rate</p>
              <h3 className="text-2xl font-black mt-1 text-slate-800 dark:text-slate-100">95.2%</h3>
            </div>
            <div className="glass-panel p-5 text-center">
              <Calendar className="mx-auto text-success mb-2" size={24} />
              <p className="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Average Check-in Time</p>
              <h3 className="text-2xl font-black mt-1 text-success">09:04 AM</h3>
            </div>
            <div className="glass-panel p-5 text-center">
              <Award className="mx-auto text-warning mb-2" size={24} />
              <p className="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Total Late Arrivals</p>
              <h3 className="text-2xl font-black mt-1 text-warning">8</h3>
            </div>
          </div>

          {/* Table list */}
          <div className="glass-panel p-6 space-y-4">
            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-slate-200 dark:border-slate-800/40 pb-4">
              <h4 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest">
                Staff Presenteeism Summary
              </h4>
              <div className="flex items-center gap-2">
                <Filter size={14} className="text-slate-400" />
                <select 
                  value={deptFilter} 
                  onChange={(e) => setDeptFilter(e.target.value)} 
                  className="glass-input text-[10px] py-1.5 w-40"
                >
                  <option value="All">All Departments</option>
                  <option value="Software Engineering">Software Engineering</option>
                  <option value="Product Management">Product Management</option>
                  <option value="Human Resources">Human Resources</option>
                </select>
              </div>
            </div>

            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs border-collapse">
                <thead>
                  <tr className="border-b border-slate-200 dark:border-slate-800/80 text-slate-400 uppercase tracking-widest font-bold">
                    <th className="pb-3 pr-4">Employee</th>
                    <th className="pb-3 px-4">Department</th>
                    <th className="pb-3 px-4 text-center">Present Rate</th>
                    <th className="pb-3 px-4 text-center">Avg Clock-In</th>
                    <th className="pb-3 pl-4 text-right">Late Counts</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 dark:divide-slate-800/40 font-semibold text-slate-700 dark:text-slate-300">
                  {filteredAttendance.map((a) => (
                    <tr key={a.id} className="hover:bg-slate-100/50 dark:hover:bg-slate-800/20 transition-colors">
                      <td className="py-3.5 pr-4 text-slate-800 dark:text-slate-100 font-bold">{a.name}</td>
                      <td className="py-3.5 px-4 text-slate-500 font-medium">{a.dept}</td>
                      <td className="py-3.5 px-4 text-center text-primary-500 font-black">{a.presentRate}%</td>
                      <td className="py-3.5 px-4 text-center text-success">{a.avgClockIn}</td>
                      <td className="py-3.5 pl-4 text-right text-warning">{a.lateArrivals} Times</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      {/* 2. Leave Reports Tab */}
      {activeTab === 'leaves' && (
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
          {/* Left panel: Leave distributions (col-span-5) */}
          <div className="lg:col-span-5 glass-panel p-6 space-y-6">
            <h4 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800/40 pb-3">
              Leave Policy Distributions
            </h4>
            <div className="space-y-4 pt-2">
              {leaveDistribution.map(item => {
                const percent = Math.round((item.used / item.allocated) * 100);
                return (
                  <div key={item.type} className="space-y-2 text-xs">
                    <div className="flex justify-between font-semibold">
                      <span className="text-slate-800 dark:text-slate-200 font-bold">{item.type}</span>
                      <span className="text-slate-500">{item.used} / {item.allocated} Days ({percent}%)</span>
                    </div>
                    <div className="w-full bg-slate-950/40 border border-slate-800 rounded-lg h-2 overflow-hidden">
                      <div className={`h-full ${item.color}`} style={{ width: `${percent}%` }}></div>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>

          {/* Right panel: Leave request charts by department (col-span-7) */}
          <div className="lg:col-span-7 glass-panel p-6 space-y-4">
            <h4 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800/40 pb-3">
              Department Leave Allocations (June)
            </h4>
            <div className="h-64 w-full pt-4">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={leaveChartData}>
                  <XAxis dataKey="name" stroke="#64748b" fontSize={10} tickLine={false} />
                  <YAxis stroke="#64748b" fontSize={10} tickLine={false} />
                  <Tooltip contentStyle={{ background: '#0f172a', borderColor: '#334155', borderRadius: '12px', fontSize: '11px' }} />
                  <Bar dataKey="Sick" fill="#00e5ff" radius={[4, 4, 0, 0]} />
                  <Bar dataKey="Casual" fill="#8a2be2" radius={[4, 4, 0, 0]} />
                  <Bar dataKey="Annual" fill="#f59e0b" radius={[4, 4, 0, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </div>
          </div>
        </div>
      )}

      {/* 3. Payroll Reports Tab */}
      {activeTab === 'payroll' && (
        <div className="space-y-6">
          {/* Stats top */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div className="glass-panel p-5 text-center flex items-center gap-4 justify-center">
              <DollarSign className="text-success" size={28} />
              <div className="text-left">
                <p className="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Gross Month Payout</p>
                <h3 className="text-xl font-bold mt-0.5 text-slate-800 dark:text-slate-100">$23,800</h3>
              </div>
            </div>
            <div className="glass-panel p-5 text-center flex items-center gap-4 justify-center">
              <FileText className="text-warning" size={28} />
              <div className="text-left">
                <p className="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Tax Deductions</p>
                <h3 className="text-xl font-bold mt-0.5 text-slate-800 dark:text-slate-100">$2,900</h3>
              </div>
            </div>
            <div className="glass-panel p-5 text-center flex items-center gap-4 justify-center">
              <BarChart2 className="text-primary-500" size={28} />
              <div className="text-left">
                <p className="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Net Cost projections</p>
                <h3 className="text-xl font-bold mt-0.5 text-slate-800 dark:text-slate-100">$20,900</h3>
              </div>
            </div>
          </div>

          {/* Cost trend Chart */}
          <div className="glass-panel p-6 space-y-4">
            <h4 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800/40 pb-3">
              Historical Payout & Compensation Trend
            </h4>
            <div className="h-64 w-full pt-4">
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={monthlyPayrollData}>
                  <XAxis dataKey="month" stroke="#64748b" fontSize={10} tickLine={false} />
                  <YAxis stroke="#64748b" fontSize={10} tickLine={false} />
                  <Tooltip contentStyle={{ background: '#0f172a', borderColor: '#334155', borderRadius: '12px', fontSize: '11px' }} />
                  <Area type="monotone" dataKey="Payout" stroke="#00e5ff" fill="url(#colorCyan)" strokeWidth={2} />
                  <Area type="monotone" dataKey="Tax" stroke="#f59e0b" fill="url(#colorAmber)" strokeWidth={1} />
                </AreaChart>
              </ResponsiveContainer>
            </div>
          </div>
        </div>
      )}

    </div>
  );
};

export default Reports;
