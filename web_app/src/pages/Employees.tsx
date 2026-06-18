import React, { useState, useEffect, useRef } from 'react';
import api from '../lib/api';
import { 
  Search, Users, Clock, 
  MessageSquare, Send, Award, Star, Phone, Mail
} from 'lucide-react';
import { BarChart, Bar, XAxis, YAxis, ResponsiveContainer, Tooltip, Cell } from 'recharts';

interface EmployeeType {
  id: string;
  employee_id: string;
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  status: 'Active' | 'Probation' | 'Suspended' | 'Terminated';
  joining_date: string;
  department?: { name: string };
  designation?: { title: string };
  manager?: { name: string };
  user?: {
    roles: Array<{ name: string }>;
  };
}

interface ChatMessage {
  id: string;
  sender: 'admin' | 'employee';
  text: string;
  timestamp: string;
}

interface PerformanceMetric {
  name: string;
  score: number;
}

interface AttendanceLogDetail {
  date: string;
  clock_in: string;
  clock_out: string;
  status: string;
  geofence: string;
}

// Fallback Mock Employees matching Seeded accounts
const mockEmployees: EmployeeType[] = [
  {
    id: 'emp-1',
    employee_id: 'EMP-2026-0001',
    first_name: 'Sarah',
    last_name: 'Manager',
    email: 'manager@humanode.net',
    phone: '+1 555-0155',
    status: 'Active',
    joining_date: '2024-03-10',
    department: { name: 'Product Management' },
    designation: { title: 'Senior Product Manager' },
    manager: { name: 'Admin User' },
    user: { roles: [{ name: 'Manager' }] }
  },
  {
    id: 'emp-2',
    employee_id: 'EMP-2026-0002',
    first_name: 'John',
    last_name: 'Employee',
    email: 'employee@humanode.net',
    phone: '+1 555-0199',
    status: 'Active',
    joining_date: '2026-01-01',
    department: { name: 'Software Engineering' },
    designation: { title: 'Frontend Developer' },
    manager: { name: 'Sarah Manager' },
    user: { roles: [{ name: 'Employee' }] }
  },
  {
    id: 'emp-3',
    employee_id: 'EMP-2026-0003',
    first_name: 'Sarah',
    last_name: 'HR',
    email: 'hr@humanode.net',
    phone: '+1 555-0188',
    status: 'Active',
    joining_date: '2025-01-15',
    department: { name: 'Human Resources' },
    designation: { title: 'HR Director' },
    manager: { name: 'Admin User' },
    user: { roles: [{ name: 'HR' }] }
  },
  {
    id: 'emp-4',
    employee_id: 'EMP-2026-0004',
    first_name: 'Admin',
    last_name: 'User',
    email: 'admin@humanode.net',
    phone: '+1 555-0100',
    status: 'Active',
    joining_date: '2023-08-01',
    department: { name: 'Executive Operations' },
    designation: { title: 'Systems Administrator' },
    manager: { name: 'None' },
    user: { roles: [{ name: 'Admin' }] }
  }
];

// Mock Attendance Logs for Selected Employee
const mockAttendanceStats: Record<string, { present: number; late: number; absent: number; halfDay: number; logs: AttendanceLogDetail[] }> = {
  'EMP-2026-0001': {
    present: 20, late: 1, absent: 1, halfDay: 0,
    logs: [
      { date: '2026-06-17', clock_in: '08:55 AM', clock_out: '06:05 PM', status: 'Present', geofence: 'Verified' },
      { date: '2026-06-16', clock_in: '09:00 AM', clock_out: '06:00 PM', status: 'Present', geofence: 'Verified' },
      { date: '2026-06-15', clock_in: '09:15 AM', clock_out: '06:10 PM', status: 'Late', geofence: 'Verified' },
      { date: '2026-06-14', clock_in: '08:50 AM', clock_out: '06:00 PM', status: 'Present', geofence: 'Verified' }
    ]
  },
  'EMP-2026-0002': {
    present: 18, late: 3, absent: 0, halfDay: 1,
    logs: [
      { date: '2026-06-17', clock_in: '09:40 AM', clock_out: '06:00 PM', status: 'Late', geofence: 'Verified' },
      { date: '2026-06-16', clock_in: '09:05 AM', clock_out: '06:15 PM', status: 'Present', geofence: 'Verified' },
      { date: '2026-06-15', clock_in: '09:30 AM', clock_out: '06:00 PM', status: 'Late', geofence: 'Verified' },
      { date: '2026-06-14', clock_in: '09:00 AM', clock_out: '01:00 PM', status: 'Half-Day', geofence: 'Verified' }
    ]
  },
  'EMP-2026-0003': {
    present: 21, late: 0, absent: 1, halfDay: 0,
    logs: [
      { date: '2026-06-17', clock_in: '08:45 AM', clock_out: '05:45 PM', status: 'Present', geofence: 'Verified' },
      { date: '2026-06-16', clock_in: '08:50 AM', clock_out: '05:55 PM', status: 'Present', geofence: 'Verified' },
      { date: '2026-06-15', clock_in: '--:--', clock_out: '--:--', status: 'Absent', geofence: 'Failed' },
      { date: '2026-06-14', clock_in: '08:40 AM', clock_out: '05:50 PM', status: 'Present', geofence: 'Verified' }
    ]
  },
  'EMP-2026-0004': {
    present: 22, late: 0, absent: 0, halfDay: 0,
    logs: [
      { date: '2026-06-17', clock_in: '08:30 AM', clock_out: '07:00 PM', status: 'Present', geofence: 'Verified' },
      { date: '2026-06-16', clock_in: '08:35 AM', clock_out: '06:30 PM', status: 'Present', geofence: 'Verified' },
      { date: '2026-06-15', clock_in: '08:40 AM', clock_out: '06:45 PM', status: 'Present', geofence: 'Verified' },
      { date: '2026-06-14', clock_in: '08:30 AM', clock_out: '06:30 PM', status: 'Present', geofence: 'Verified' }
    ]
  }
};

export const Employees: React.FC = () => {
  const [employees, setEmployees] = useState<EmployeeType[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [filterRole, setFilterRole] = useState('All');
  const [filterDept, setFilterDept] = useState('All');
  
  const [selectedEmployee, setSelectedEmployee] = useState<EmployeeType | null>(null);
  const [activeTab, setActiveTab] = useState<'performance' | 'attendance' | 'chat'>('performance');

  // Custom States for Reviews
  const [quality, setQuality] = useState(4);
  const [productivity, setProductivity] = useState(4);
  const [teamwork, setTeamwork] = useState(4);
  const [communication, setCommunication] = useState(4);
  const [comment, setComment] = useState('');
  const [submittingReview, setSubmittingReview] = useState(false);
  const [reviewSuccess, setReviewSuccess] = useState(false);

  // Review List State
  const [performanceHistory, setPerformanceHistory] = useState<Record<string, Array<{
    id: string;
    reviewer: string;
    date: string;
    overall: number;
    metrics: PerformanceMetric[];
    comment: string;
  }>>>({
    'EMP-2026-0001': [
      {
        id: 'r1',
        reviewer: 'Executive Board',
        date: '2026-05-15',
        overall: 4.3,
        metrics: [
          { name: 'Quality', score: 4.5 },
          { name: 'Productivity', score: 4.2 },
          { name: 'Teamwork', score: 4.0 },
          { name: 'Communication', score: 4.5 }
        ],
        comment: 'Sarah is an outstanding manager who guides the product lifecycle effectively. Her communication with stakeholders is top-tier.'
      }
    ],
    'EMP-2026-0002': [
      {
        id: 'r2',
        reviewer: 'Sarah Manager',
        date: '2026-06-01',
        overall: 4.5,
        metrics: [
          { name: 'Quality', score: 4.8 },
          { name: 'Productivity', score: 4.5 },
          { name: 'Teamwork', score: 4.2 },
          { name: 'Communication', score: 4.5 }
        ],
        comment: 'John shows incredible frontend skills, maintaining clean React architectures. Very reliable and quick turnaround times.'
      }
    ],
    'EMP-2026-0003': [
      {
        id: 'r3',
        reviewer: 'Executive Operations',
        date: '2026-04-10',
        overall: 4.0,
        metrics: [
          { name: 'Quality', score: 4.0 },
          { name: 'Productivity', score: 4.0 },
          { name: 'Teamwork', score: 4.5 },
          { name: 'Communication', score: 3.5 }
        ],
        comment: 'Sarah HR maintains excellent company culture and tenant branch guidelines. Communication could be optimized during crunch workflows.'
      }
    ],
    'EMP-2026-0004': [
      {
        id: 'r4',
        reviewer: 'Internal Audit',
        date: '2026-03-20',
        overall: 4.8,
        metrics: [
          { name: 'Quality', score: 4.9 },
          { name: 'Productivity', score: 4.8 },
          { name: 'Teamwork', score: 4.5 },
          { name: 'Communication', score: 5.0 }
        ],
        comment: 'Maintains server architecture flawlessly. Quick resolution of docker/SaaS geofence routing checkpoints.'
      }
    ]
  });

  // Chat Interactive Engine
  const [chatMessages, setChatMessages] = useState<Record<string, ChatMessage[]>>({
    'EMP-2026-0001': [
      { id: '1', sender: 'employee', text: 'Hi Admin! Let me know if you need to review the Q3 product spec draft.', timestamp: '10:30 AM' },
      { id: '2', sender: 'admin', text: 'Thanks Sarah. Please make sure the engineering leads have signed off first.', timestamp: '10:32 AM' }
    ],
    'EMP-2026-0002': [
      { id: '1', sender: 'employee', text: 'Hey, I submitted my leave request for next week. Hope it is okay.', timestamp: 'Yesterday' },
      { id: '2', sender: 'admin', text: 'I will check with your manager. Should be fine!', timestamp: 'Yesterday' }
    ],
    'EMP-2026-0003': [
      { id: '1', sender: 'employee', text: 'Hi Admin, I completed updating the company documents locker.', timestamp: 'Monday' },
      { id: '2', sender: 'admin', text: 'Perfect. Let us double check the security configurations.', timestamp: 'Monday' }
    ],
    'EMP-2026-0004': [
      { id: '1', sender: 'admin', text: 'Is the dev server operational on port 8000?', timestamp: '09:00 AM' },
      { id: '2', sender: 'employee', text: 'Yes, both Laravel API and Vite SPA built successfully.', timestamp: '09:02 AM' }
    ]
  });

  const [newMsg, setNewMsg] = useState('');
  const [isTyping, setIsTyping] = useState(false);
  const chatEndRef = useRef<HTMLDivElement>(null);

  const getRole = (emp: EmployeeType) => {
    return emp.user?.roles?.[0]?.name || 'Employee';
  };

  useEffect(() => {
    const loadData = async () => {
      try {
        const res = await api.get('/employees');
        const loadedData = res.data.data.data;
        if (Array.isArray(loadedData) && loadedData.length > 0) {
          const processed: EmployeeType[] = loadedData.map((e: EmployeeType) => ({
            ...e,
            status: e.status || 'Active',
            user: e.user || { roles: [{ name: e.email === 'admin@humanode.net' ? 'Admin' : e.email === 'hr@humanode.net' ? 'HR' : e.email === 'manager@humanode.net' ? 'Manager' : 'Employee' }] }
          }));
          setEmployees(processed);
        } else {
          setEmployees(mockEmployees);
        }
      } catch {
        await Promise.resolve();
        setEmployees(mockEmployees);
      } finally {
        setLoading(false);
      }
    };

    Promise.resolve().then(() => {
      loadData();
    });
  }, []);

  // Scroll Chat to Bottom
  useEffect(() => {
    chatEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [chatMessages, isTyping, selectedEmployee]);

  const handleSendMessage = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newMsg.trim() || !selectedEmployee) return;

    const empId = selectedEmployee.employee_id;
    const adminMsg: ChatMessage = {
      id: String(Date.now()),
      sender: 'admin',
      text: newMsg.trim(),
      timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    };

    // Update messages
    const currentMessages = chatMessages[empId] || [];
    setChatMessages({
      ...chatMessages,
      [empId]: [...currentMessages, adminMsg]
    });
    setNewMsg('');
    setIsTyping(true);

    // Dynamic Bot Chat Response based on employee role
    setTimeout(() => {
      const role = getRole(selectedEmployee);
      let replyText: string;

      const textLower = adminMsg.text.toLowerCase();
      if (textLower.includes('hello') || textLower.includes('hi') || textLower.includes('hey')) {
        replyText = `Hello Admin! Glad you reached out. Hope you are having a productive day.`;
      } else if (textLower.includes('attendance') || textLower.includes('clock') || textLower.includes('late')) {
        replyText = `Regarding attendance: I verified that my GPS coordinates matched the geofence perimeter correctly today. Let me know if you see any sync anomalies.`;
      } else if (textLower.includes('performance') || textLower.includes('kpi') || textLower.includes('review')) {
        replyText = `Thank you for reviewing my KPIs. I am focusing heavily on Q2 targets and appreciate any feedback you write on my review scorecard.`;
      } else if (role === 'HR') {
        replyText = `As HR director, I am currently organizing the employee document lockers and reviewing the pending leave policies. I will send you a sync report later today.`;
      } else if (role === 'Manager') {
        replyText = `Team operations are on track. I am reviewing the pending leave requests in the MSS portal right now and will make sure shift structures are covered.`;
      } else {
        replyText = `Understood. I will sync with my reporting manager on this and ensure we coordinate in our next daily standup.`;
      }

      const replyMsg: ChatMessage = {
        id: String(Date.now() + 1),
        sender: 'employee',
        text: replyText,
        timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
      };

      setChatMessages(prev => ({
        ...prev,
        [empId]: [...(prev[empId] || []), replyMsg]
      }));
      setIsTyping(false);
    }, 1000);
  };

  const handleAddReview = (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedEmployee) return;

    setSubmittingReview(true);
    const empId = selectedEmployee.employee_id;
    const avgScore = Number(((quality + productivity + teamwork + communication) / 4).toFixed(1));

    const newReview = {
      id: String(Date.now()),
      reviewer: 'Admin User',
      date: new Date().toISOString().split('T')[0],
      overall: avgScore,
      metrics: [
        { name: 'Quality', score: quality },
        { name: 'Productivity', score: productivity },
        { name: 'Teamwork', score: teamwork },
        { name: 'Communication', score: communication }
      ],
      comment: comment.trim() || 'Scorecard updated by system administrator.'
    };

    setTimeout(() => {
      setPerformanceHistory(prev => ({
        ...prev,
        [empId]: [newReview, ...(prev[empId] || [])]
      }));
      setSubmittingReview(false);
      setReviewSuccess(true);
      setComment('');
      setTimeout(() => setReviewSuccess(false), 3000);
    }, 8000);
  };

  // Filter logic
  const filteredEmployees = employees.filter(emp => {
    const role = getRole(emp);
    const fullName = `${emp.first_name} ${emp.last_name}`.toLowerCase();
    const matchesSearch = fullName.includes(search.toLowerCase()) || emp.employee_id.toLowerCase().includes(search.toLowerCase());
    const matchesRole = filterRole === 'All' || role.toLowerCase() === filterRole.toLowerCase();
    const matchesDept = filterDept === 'All' || emp.department?.name === filterDept;

    return matchesSearch && matchesRole && matchesDept;
  });

  const getDepartmentsList = () => {
    const depts = new Set<string>();
    employees.forEach(e => {
      if (e.department?.name) depts.add(e.department.name);
    });
    return Array.from(depts);
  };

  if (loading) {
    return <div className="text-center py-12 text-slate-400 text-sm">Loading HumaNode registry profiles...</div>;
  }

  // Get active attendance logs and score history
  const activeEmpId = selectedEmployee?.employee_id || '';
  const activeAttendance = selectedEmployee ? mockAttendanceStats[activeEmpId] || { present: 0, late: 0, absent: 0, halfDay: 0, logs: [] } : { present: 0, late: 0, absent: 0, halfDay: 0, logs: [] };
  const activePerformance = selectedEmployee ? performanceHistory[activeEmpId] || [] : [];
  const latestPerformanceReview = activePerformance[0];
  const chartData = latestPerformanceReview ? latestPerformanceReview.metrics : [
    { name: 'Quality', score: 0 },
    { name: 'Productivity', score: 0 },
    { name: 'Teamwork', score: 0 },
    { name: 'Communication', score: 0 }
  ];

  return (
    <div className="space-y-8 max-w-7xl mx-auto">
      <div>
        <h1 className="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Admin Operations Center</h1>
        <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
          Monitor employee registries, audit time/attendance, evaluate performance, and conduct secure line chat.
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {/* Left Side: Directory search and list (col-span-5) */}
        <div className="lg:col-span-5 space-y-6">
          <div className="glass-panel p-5 space-y-4">
            
            {/* Search inputs */}
            <div className="relative">
              <span className="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                <Search size={16} />
              </span>
              <input 
                type="text" 
                placeholder="Search by name or Employee ID..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="glass-input pl-10 text-xs" 
              />
            </div>

            {/* Filters grid */}
            <div className="grid grid-cols-2 gap-3">
              <div>
                <label className="text-[10px] font-bold text-slate-400 uppercase tracking-wide block mb-1">Filter Role</label>
                <select 
                  value={filterRole} 
                  onChange={(e) => setFilterRole(e.target.value)} 
                  className="glass-input text-[11px] py-2"
                >
                  <option value="All">All Roles</option>
                  <option value="Admin">Admin</option>
                  <option value="HR">HR</option>
                  <option value="Manager">Manager</option>
                  <option value="Employee">Employee</option>
                </select>
              </div>

              <div>
                <label className="text-[10px] font-bold text-slate-400 uppercase tracking-wide block mb-1">Filter Dept</label>
                <select 
                  value={filterDept} 
                  onChange={(e) => setFilterDept(e.target.value)} 
                  className="glass-input text-[11px] py-2"
                >
                  <option value="All">All Depts</option>
                  {getDepartmentsList().map(d => (
                    <option key={d} value={d}>{d}</option>
                  ))}
                </select>
              </div>
            </div>

          </div>

          {/* Directory list */}
          <div className="glass-panel p-4 max-h-[550px] overflow-y-auto space-y-2.5">
            <h3 className="text-xs font-black tracking-widest text-slate-400 uppercase px-2 mb-3">
              Employees Found ({filteredEmployees.length})
            </h3>
            
            {filteredEmployees.length === 0 ? (
              <div className="text-center py-12 text-slate-400">
                <p className="text-sm">No profiles match filters.</p>
              </div>
            ) : (
              filteredEmployees.map((emp) => {
                const role = getRole(emp);
                const isSelected = selectedEmployee?.employee_id === emp.employee_id;
                
                return (
                  <div 
                    key={emp.employee_id}
                    onClick={() => {
                      setSelectedEmployee(emp);
                      // Clear review states when changing employee
                      setQuality(4);
                      setProductivity(4);
                      setTeamwork(4);
                      setCommunication(4);
                      setComment('');
                    }}
                    className={`flex items-center justify-between p-3.5 rounded-xl border transition-all duration-300 cursor-pointer ${
                      isSelected 
                        ? 'bg-primary-500/10 border-primary-500/40' 
                        : 'bg-slate-100/50 dark:bg-slate-950/20 border-slate-200 dark:border-slate-800/80 hover:border-slate-300 dark:hover:border-slate-700'
                    }`}
                  >
                    <div className="flex items-center gap-3">
                      <div className="w-10 h-10 rounded-xl bg-gradient-to-tr from-primary-500 to-secondary-500 flex items-center justify-center font-bold text-slate-950 text-sm">
                        {emp.first_name[0]}{emp.last_name[0]}
                      </div>
                      <div className="min-w-0">
                        <p className="text-xs font-bold text-slate-800 dark:text-slate-100 leading-tight">
                          {emp.first_name} {emp.last_name}
                        </p>
                        <p className="text-[10px] text-slate-400 font-semibold uppercase mt-0.5 leading-none">
                          {emp.employee_id}
                        </p>
                        <p className="text-[10px] text-slate-500 mt-1 font-medium truncate max-w-[150px]">
                          {emp.designation?.title || 'Associate'} • {emp.department?.name || 'Staff'}
                        </p>
                      </div>
                    </div>

                    <div className="text-right">
                      <span className={`px-2 py-0.5 rounded-full text-[8px] font-black tracking-wider uppercase inline-block ${
                        role === 'Admin' ? 'bg-danger/10 text-danger' :
                        role === 'HR' ? 'bg-warning/10 text-warning' :
                        role === 'Manager' ? 'bg-secondary-500/10 text-secondary-500' : 'bg-primary-500/10 text-primary-500'
                      }`}>
                        {role}
                      </span>
                      <p className="text-[9px] text-success font-black tracking-wide mt-2 uppercase">
                        {emp.status}
                      </p>
                    </div>
                  </div>
                );
              })
            )}
          </div>
        </div>

        {/* Right Side: Detail panels workspace (col-span-7) */}
        <div className="lg:col-span-7">
          {!selectedEmployee ? (
            <div className="glass-panel p-12 text-center text-slate-400 min-h-[500px] flex flex-col items-center justify-center">
              <Users size={48} className="text-slate-500 mb-4 animate-bounce" />
              <h3 className="text-sm font-bold text-slate-700 dark:text-slate-300">Selected Employee Dashboard</h3>
              <p className="text-xs max-w-sm mt-2 leading-relaxed">
                Choose an employee from the directory list on the left to evaluate performance ratings, inspect attendance check-ins, and initiate line communication.
              </p>
            </div>
          ) : (
            <div className="space-y-6">
              
              {/* Employee Summary Card Header */}
              <div className="glass-panel p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div className="flex items-center gap-4">
                  <div className="w-14 h-14 rounded-2xl bg-gradient-to-tr from-primary-500 to-secondary-500 flex items-center justify-center font-bold text-slate-950 text-xl border border-white/10 shadow-lg">
                    {selectedEmployee.first_name[0]}{selectedEmployee.last_name[0]}
                  </div>
                  <div>
                    <h2 className="text-lg font-extrabold text-slate-800 dark:text-slate-100 leading-tight">
                      {selectedEmployee.first_name} {selectedEmployee.last_name}
                    </h2>
                    <p className="text-xs text-slate-400 font-semibold tracking-wide mt-0.5">
                      {selectedEmployee.employee_id} • {selectedEmployee.designation?.title || 'Associate'}
                    </p>
                    <div className="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-[10px] text-slate-500 font-semibold">
                      <span className="flex items-center gap-1"><Mail size={10} /> {selectedEmployee.email}</span>
                      <span className="flex items-center gap-1"><Phone size={10} /> {selectedEmployee.phone}</span>
                    </div>
                  </div>
                </div>

                <div className="flex items-center gap-2 self-end sm:self-center">
                  <span className={`px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-wider ${
                    getRole(selectedEmployee) === 'Admin' ? 'bg-danger/10 text-danger' :
                    getRole(selectedEmployee) === 'HR' ? 'bg-warning/10 text-warning' :
                    getRole(selectedEmployee) === 'Manager' ? 'bg-secondary-500/10 text-secondary-500' : 'bg-primary-500/10 text-primary-500'
                  }`}>
                    {getRole(selectedEmployee)}
                  </span>
                  <span className="px-2.5 py-1 bg-success/15 text-success rounded-full text-[9px] font-black uppercase tracking-wider">
                    {selectedEmployee.status}
                  </span>
                </div>
              </div>

              {/* Workspace Navigation Tabs */}
              <div className="flex gap-2 border-b border-slate-200 dark:border-slate-800/80 pb-0.5">
                <button 
                  onClick={() => setActiveTab('performance')}
                  className={`px-4 py-2 text-xs font-bold border-b-2 transition-all duration-200 flex items-center gap-2 cursor-pointer ${
                    activeTab === 'performance' 
                      ? 'border-primary-500 text-primary-500' 
                      : 'border-transparent text-slate-400 hover:text-slate-300'
                  }`}
                >
                  <Award size={14} /> Performance Hub
                </button>
                <button 
                  onClick={() => setActiveTab('attendance')}
                  className={`px-4 py-2 text-xs font-bold border-b-2 transition-all duration-200 flex items-center gap-2 cursor-pointer ${
                    activeTab === 'attendance' 
                      ? 'border-primary-500 text-primary-500' 
                      : 'border-transparent text-slate-400 hover:text-slate-300'
                  }`}
                >
                  <Clock size={14} /> Attendance Logs
                </button>
                <button 
                  onClick={() => setActiveTab('chat')}
                  className={`px-4 py-2 text-xs font-bold border-b-2 transition-all duration-200 flex items-center gap-2 cursor-pointer ${
                    activeTab === 'chat' 
                      ? 'border-primary-500 text-primary-500' 
                      : 'border-transparent text-slate-400 hover:text-slate-300'
                  }`}
                >
                  <MessageSquare size={14} /> Direct Chat
                </button>
              </div>

              {/* Tab: Performance Hub */}
              {activeTab === 'performance' && (
                <div className="space-y-6">
                  
                  {/* KPI Metrics Visual */}
                  <div className="grid grid-cols-1 md:grid-cols-12 gap-6 items-stretch">
                    
                    {/* Left: Overall rating dial */}
                    <div className="md:col-span-4 glass-panel p-6 flex flex-col justify-center items-center text-center">
                      <p className="text-[10px] font-bold tracking-widest text-slate-400 uppercase mb-3">Overall Performance</p>
                      <div className="w-24 h-24 rounded-full border-4 border-primary-500/20 flex flex-col items-center justify-center bg-primary-500/5 shadow-[0_0_20px_rgba(0,229,255,0.05)]">
                        <span className="text-3xl font-black bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent">
                          {latestPerformanceReview ? latestPerformanceReview.overall : 'N/A'}
                        </span>
                        <span className="text-[10px] font-bold text-slate-400 uppercase mt-0.5">Out of 5.0</span>
                      </div>
                      <div className="flex gap-1 mt-4 text-warning">
                        {Array.from({ length: 5 }).map((_, i) => (
                          <Star 
                            key={i} 
                            size={14} 
                            fill={latestPerformanceReview && i < Math.floor(latestPerformanceReview.overall) ? 'currentColor' : 'none'} 
                          />
                        ))}
                      </div>
                      <p className="text-[10px] text-slate-500 font-semibold tracking-wide uppercase mt-3">
                        Last Reviewed: {latestPerformanceReview ? latestPerformanceReview.date : 'Never'}
                      </p>
                    </div>

                    {/* Right: Bar charts */}
                    <div className="md:col-span-8 glass-panel p-5 flex flex-col justify-between">
                      <h4 className="text-xs font-bold text-slate-800 dark:text-slate-200 mb-4 uppercase tracking-wider">KPI Ratings Breakdown</h4>
                      <div className="h-44 w-full">
                        <ResponsiveContainer width="100%" height="100%">
                          <BarChart data={chartData} margin={{ top: 0, right: 0, left: -25, bottom: 0 }}>
                            <XAxis dataKey="name" stroke="#64748b" fontSize={10} tickLine={false} />
                            <YAxis stroke="#64748b" fontSize={10} tickLine={false} domain={[0, 5]} />
                            <Tooltip contentStyle={{ background: '#0f172a', borderColor: '#334155', borderRadius: '12px', fontSize: '11px' }} />
                            <Bar dataKey="score" radius={[8, 8, 0, 0]} barSize={36}>
                              {chartData.map((_, index) => (
                                <Cell key={`cell-${index}`} fill={index % 2 === 0 ? '#00e5ff' : '#8a2be2'} />
                              ))}
                            </Bar>
                          </BarChart>
                        </ResponsiveContainer>
                      </div>
                    </div>

                  </div>

                  {/* Submit Performance Appraisal Review */}
                  <div className="glass-panel p-6">
                    <h4 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800/80 pb-3 mb-4 flex items-center gap-1.5">
                      <Award size={16} className="text-primary-500" /> Grade Employee Scorecard
                    </h4>

                    {reviewSuccess && (
                      <div className="mb-4 p-3 rounded-xl bg-success/20 border border-success/30 text-success text-xs font-semibold shadow-lg backdrop-blur-sm">
                        ✅ Performance Appraisal Scorecard updated successfully!
                      </div>
                    )}

                    <form onSubmit={handleAddReview} className="space-y-4">
                      
                      {/* Grid sliders */}
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div className="space-y-1.5">
                          <div className="flex justify-between text-[10px] font-bold text-slate-400 uppercase">
                            <span>Quality of Work</span>
                            <span className="text-primary-500 font-extrabold">{quality}.0 / 5.0</span>
                          </div>
                          <input 
                            type="range" min="1" max="5" step="1" 
                            value={quality} onChange={(e) => setQuality(Number(e.target.value))}
                            className="w-full accent-primary-500 cursor-pointer bg-slate-950/40 rounded-lg h-2 border border-slate-800"
                          />
                        </div>

                        <div className="space-y-1.5">
                          <div className="flex justify-between text-[10px] font-bold text-slate-400 uppercase">
                            <span>Productivity</span>
                            <span className="text-primary-500 font-extrabold">{productivity}.0 / 5.0</span>
                          </div>
                          <input 
                            type="range" min="1" max="5" step="1" 
                            value={productivity} onChange={(e) => setProductivity(Number(e.target.value))}
                            className="w-full accent-primary-500 cursor-pointer bg-slate-950/40 rounded-lg h-2 border border-slate-800"
                          />
                        </div>

                        <div className="space-y-1.5">
                          <div className="flex justify-between text-[10px] font-bold text-slate-400 uppercase">
                            <span>Teamwork</span>
                            <span className="text-primary-500 font-extrabold">{teamwork}.0 / 5.0</span>
                          </div>
                          <input 
                            type="range" min="1" max="5" step="1" 
                            value={teamwork} onChange={(e) => setTeamwork(Number(e.target.value))}
                            className="w-full accent-primary-500 cursor-pointer bg-slate-950/40 rounded-lg h-2 border border-slate-800"
                          />
                        </div>

                        <div className="space-y-1.5">
                          <div className="flex justify-between text-[10px] font-bold text-slate-400 uppercase">
                            <span>Communication</span>
                            <span className="text-primary-500 font-extrabold">{communication}.0 / 5.0</span>
                          </div>
                          <input 
                            type="range" min="1" max="5" step="1" 
                            value={communication} onChange={(e) => setCommunication(Number(e.target.value))}
                            className="w-full accent-primary-500 cursor-pointer bg-slate-950/40 rounded-lg h-2 border border-slate-800"
                          />
                        </div>
                      </div>

                      {/* Comment */}
                      <div className="space-y-1.5">
                        <label className="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Review Comments</label>
                        <textarea 
                          placeholder="Provide descriptive review appraisal detail..." 
                          value={comment}
                          onChange={(e) => setComment(e.target.value)}
                          className="glass-input text-xs resize-none"
                          rows={3}
                          required
                        />
                      </div>

                      <div className="flex justify-end pt-2">
                        <button 
                          type="submit"
                          disabled={submittingReview}
                          className="px-4 py-2 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl text-xs uppercase tracking-wider btn-glow transition-all disabled:opacity-50 cursor-pointer"
                        >
                          {submittingReview ? 'Updating Scorecard...' : 'Submit Appraisal Review'}
                        </button>
                      </div>

                    </form>
                  </div>

                  {/* Review History Logs */}
                  <div className="glass-panel p-6 space-y-4">
                    <h4 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800/80 pb-3 mb-2">
                      Review Audit History
                    </h4>
                    
                    {activePerformance.length === 0 ? (
                      <div className="text-center py-6 text-slate-400 text-xs">
                        No prior performance reviews logged for this profile.
                      </div>
                    ) : (
                      activePerformance.map((rev) => (
                        <div key={rev.id} className="p-4 bg-slate-100/30 dark:bg-slate-950/10 border border-slate-200 dark:border-slate-800/60 rounded-xl space-y-2">
                          <div className="flex justify-between items-start">
                            <div>
                              <p className="text-xs font-bold text-slate-700 dark:text-slate-300">Reviewed by {rev.reviewer}</p>
                              <p className="text-[9px] text-slate-400 font-semibold">{new Date(rev.date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</p>
                            </div>
                            <span className="px-2 py-1 bg-gradient-to-r from-primary-500 to-secondary-500 text-slate-950 font-black rounded-lg text-xs">
                              {rev.overall} / 5.0
                            </span>
                          </div>
                          <p className="text-xs text-slate-600 dark:text-slate-400 italic leading-relaxed pt-1">
                            "{rev.comment}"
                          </p>
                        </div>
                      ))
                    )}
                  </div>

                </div>
              )}

              {/* Tab: Attendance Logs */}
              {activeTab === 'attendance' && (
                <div className="space-y-6">
                  
                  {/* Summary row cards */}
                  <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div className="glass-panel p-4 text-center">
                      <p className="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Present Days</p>
                      <h3 className="text-xl font-extrabold text-success mt-1">{activeAttendance.present}</h3>
                    </div>
                    <div className="glass-panel p-4 text-center">
                      <p className="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Late Arrivals</p>
                      <h3 className="text-xl font-extrabold text-warning mt-1">{activeAttendance.late}</h3>
                    </div>
                    <div className="glass-panel p-4 text-center">
                      <p className="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Absent Days</p>
                      <h3 className="text-xl font-extrabold text-danger mt-1">{activeAttendance.absent}</h3>
                    </div>
                    <div className="glass-panel p-4 text-center">
                      <p className="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Half-Days</p>
                      <h3 className="text-xl font-extrabold text-secondary-500 mt-1">{activeAttendance.halfDay}</h3>
                    </div>
                  </div>

                  {/* Attendance Log Table */}
                  <div className="glass-panel p-6">
                    <h4 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest border-b border-slate-200 dark:border-slate-800/80 pb-4 mb-4">
                      Time & Check-In History
                    </h4>

                    {activeAttendance.logs.length === 0 ? (
                      <div className="text-center py-12 text-slate-400 text-xs">
                        No attendance activity logged for this period.
                      </div>
                    ) : (
                      <div className="overflow-x-auto">
                        <table className="w-full text-left text-xs border-collapse">
                          <thead>
                            <tr className="border-b border-slate-200 dark:border-slate-800/80 text-slate-400 uppercase tracking-widest font-bold">
                              <th className="pb-3 pr-4">Date</th>
                              <th className="pb-3 px-4">Clock In</th>
                              <th className="pb-3 px-4">Clock Out</th>
                              <th className="pb-3 px-4">GPS Verification</th>
                              <th className="pb-3 pl-4 text-right">Status</th>
                            </tr>
                          </thead>
                          <tbody className="divide-y divide-slate-100 dark:divide-slate-800/40">
                            {activeAttendance.logs.map((log, index) => (
                              <tr key={index} className="hover:bg-slate-100/50 dark:hover:bg-slate-800/20 transition-colors">
                                <td className="py-3.5 pr-4 font-bold text-slate-700 dark:text-slate-300">{log.date}</td>
                                <td className="py-3.5 px-4 text-slate-600 dark:text-slate-400 font-medium">{log.clock_in}</td>
                                <td className="py-3.5 px-4 text-slate-600 dark:text-slate-400 font-medium">{log.clock_out}</td>
                                <td className="py-3.5 px-4">
                                  <span className={`inline-flex items-center gap-1 text-[10px] font-semibold ${
                                    log.geofence === 'Verified' ? 'text-success' : 'text-danger'
                                  }`}>
                                    <span className="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    {log.geofence}
                                  </span>
                                </td>
                                <td className="py-3.5 pl-4 text-right">
                                  <span className={`px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider ${
                                    log.status === 'Present' ? 'bg-success/10 text-success' :
                                    log.status === 'Late' ? 'bg-warning/10 text-warning' :
                                    log.status === 'Half-Day' ? 'bg-secondary-500/10 text-secondary-500' : 'bg-danger/10 text-danger'
                                  }`}>
                                    {log.status}
                                  </span>
                                </td>
                              </tr>
                            ))}
                          </tbody>
                        </table>
                      </div>
                    )}
                  </div>

                </div>
              )}

              {/* Tab: Direct Chat */}
              {activeTab === 'chat' && (
                <div className="glass-panel p-6 flex flex-col h-[500px] justify-between">
                  
                  {/* Chat Box Header */}
                  <div className="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800/80 mb-4 text-xs font-bold text-slate-500 uppercase">
                    <span>Direct chat connection</span>
                    <span className="text-primary-500 flex items-center gap-1 font-bold">
                      <span className="w-2 h-2 rounded-full bg-success animate-ping"></span>
                      Encrypted Link Active
                    </span>
                  </div>

                  {/* Messages Viewport */}
                  <div className="flex-1 overflow-y-auto px-1 space-y-4 scroll-smooth min-h-[300px]">
                    {(chatMessages[activeEmpId] || []).map((msg) => {
                      const isAdmin = msg.sender === 'admin';
                      return (
                        <div 
                          key={msg.id}
                          className={`flex flex-col max-w-[75%] ${
                            isAdmin ? 'ml-auto items-end' : 'mr-auto items-start'
                          }`}
                        >
                          <div className={`p-3.5 rounded-2xl text-xs font-medium leading-relaxed shadow-md ${
                            isAdmin 
                              ? 'bg-gradient-to-tr from-primary-500 to-secondary-500 text-slate-950 rounded-tr-none font-bold' 
                              : 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-100 rounded-tl-none'
                          }`}>
                            {msg.text}
                          </div>
                          <span className="text-[8px] text-slate-400 font-bold mt-1 uppercase tracking-wider">
                            {msg.timestamp}
                          </span>
                        </div>
                      );
                    })}

                    {/* Chatbot Typing Indicator bubble */}
                    {isTyping && (
                      <div className="mr-auto items-start flex flex-col max-w-[75%]">
                        <div className="p-3 bg-slate-100 dark:bg-slate-800 text-slate-400 rounded-2xl rounded-tl-none flex items-center gap-1 animate-pulse">
                          <span className="w-1.5 h-1.5 rounded-full bg-slate-400 animate-bounce"></span>
                          <span className="w-1.5 h-1.5 rounded-full bg-slate-400 animate-bounce delay-100"></span>
                          <span className="w-1.5 h-1.5 rounded-full bg-slate-400 animate-bounce delay-200"></span>
                        </div>
                      </div>
                    )}
                    <div ref={chatEndRef}></div>
                  </div>

                  {/* Chat Input form */}
                  <form onSubmit={handleSendMessage} className="flex gap-3 pt-4 border-t border-slate-200 dark:border-slate-800/80 mt-4">
                    <input 
                      type="text" 
                      placeholder={`Type secure message to ${selectedEmployee.first_name}...`}
                      value={newMsg}
                      onChange={(e) => setNewMsg(e.target.value)}
                      className="glass-input text-xs flex-1 focus:ring-secondary-500" 
                    />
                    <button 
                      type="submit" 
                      className="p-3 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl btn-glow transition-all flex items-center justify-center cursor-pointer"
                    >
                      <Send size={16} />
                    </button>
                  </form>

                </div>
              )}

            </div>
          )}
        </div>

      </div>
    </div>
  );
};

export default Employees;
