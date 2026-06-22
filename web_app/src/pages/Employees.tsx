import React, { useState, useEffect, useRef } from 'react';
import api from '../lib/api';
import { useAuth } from '../context/AuthContext';
import { 
  Search, Users, Clock, 
  MessageSquare, Send, Award, Star, Phone, Mail,
  Plus, Trash2, Edit3, X, CheckCircle2, Briefcase
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
  department_id?: string | null;
  designation_id?: string | null;
  manager_id?: string | null;
  department?: { id?: string; name: string };
  designation?: { id?: string; title: string };
  manager?: { name: string };
  user?: {
    id?: string;
    roles: Array<{ name: string }>;
  };
  employment_history?: Array<{
    company_name: string;
    designation: string;
    start_date: string;
    end_date?: string | null;
    description?: string;
  }>;
}

interface ChatMessage {
  id: string;
  sender: 'admin' | 'employee';
  text: string;
  timestamp: string;
}

interface AttendanceLogDetail {
  date: string;
  clock_in: string;
  clock_out: string;
  status: string;
  geofence: string;
}

interface DepartmentOption {
  id: string;
  name: string;
}

interface PerformanceAppraisalType {
  id: string;
  reviewer_name: string;
  review_date: string;
  overall_score: number;
  quality_score: number;
  productivity_score: number;
  teamwork_score: number;
  communication_score: number;
  comment: string;
}

interface DesignationOption {
  id: string;
  title: string;
  department_id?: string | null;
}

interface AttendanceStats {
  present: number;
  late: number;
  absent: number;
  halfDay: number;
  logs: AttendanceLogDetail[];
}

export const Employees: React.FC = () => {
  const [employees, setEmployees] = useState<EmployeeType[]>([]);
  const [departments, setDepartments] = useState<DepartmentOption[]>([]);
  const [designations, setDesignations] = useState<DesignationOption[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [filterRole, setFilterRole] = useState('All');
  const [filterDept, setFilterDept] = useState('All');
  
  const [selectedEmployee, setSelectedEmployee] = useState<EmployeeType | null>(null);
  const [activeTab, setActiveTab] = useState<'performance' | 'attendance' | 'chat' | 'workHistory'>('performance');

  // Work History Admin Form states
  const [showWorkHistoryModal, setShowWorkHistoryModal] = useState(false);
  const [editingIndex, setEditingIndex] = useState<number | null>(null);
  const [newCompany, setNewCompany] = useState('');
  const [newDesignation, setNewDesignation] = useState('');
  const [newStartDate, setNewStartDate] = useState('');
  const [newEndDate, setNewEndDate] = useState('');
  const [newDescription, setNewDescription] = useState('');
  const [savingWorkHistory, setSavingWorkHistory] = useState(false);

  const handleSaveWorkHistory = async (updatedHistory: any) => {
    if (!selectedEmployee) return;
    setSavingWorkHistory(true);
    try {
      await api.put(`/employees/${selectedEmployee.id}`, {
        first_name: selectedEmployee.first_name,
        last_name: selectedEmployee.last_name,
        joining_date: selectedEmployee.joining_date,
        employment_history: updatedHistory
      });
      const updatedEmp = { ...selectedEmployee, employment_history: updatedHistory };
      setSelectedEmployee(updatedEmp);
      setEmployees(prev => prev.map(e => e.id === selectedEmployee.id ? updatedEmp : e));
    } catch (err: unknown) {
      console.error(err);
      alert('Failed to save work history.');
    } finally {
      setSavingWorkHistory(false);
    }
  };

  const handleAddOrEditWorkHistory = (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedEmployee || !newCompany || !newDesignation || !newStartDate) return;

    const entry = {
      company_name: newCompany,
      designation: newDesignation,
      start_date: newStartDate,
      end_date: newEndDate || null,
      description: newDescription
    };

    const currentHistory = selectedEmployee.employment_history || [];
    let updatedHistory;
    if (editingIndex !== null) {
      updatedHistory = currentHistory.map((item, idx) => idx === editingIndex ? entry : item);
    } else {
      updatedHistory = [...currentHistory, entry];
    }

    handleSaveWorkHistory(updatedHistory);

    setNewCompany('');
    setNewDesignation('');
    setNewStartDate('');
    setNewEndDate('');
    setNewDescription('');
    setShowWorkHistoryModal(false);
    setEditingIndex(null);
  };

  const handleEditWorkHistoryClick = (idx: number) => {
    if (!selectedEmployee || !selectedEmployee.employment_history) return;
    const entry = selectedEmployee.employment_history[idx];
    setNewCompany(entry.company_name);
    setNewDesignation(entry.designation);
    setNewStartDate(entry.start_date);
    setNewEndDate(entry.end_date || '');
    setNewDescription(entry.description || '');
    setEditingIndex(idx);
    setShowWorkHistoryModal(true);
  };

  const handleDeleteWorkHistory = (idx: number) => {
    if (!selectedEmployee) return;
    const currentHistory = selectedEmployee.employment_history || [];
    const updatedHistory = currentHistory.filter((_, i) => i !== idx);
    handleSaveWorkHistory(updatedHistory);
  };

  const { user } = useAuth();

  // CRUD States
  const [showAddModal, setShowAddModal] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [successAlert, setSuccessAlert] = useState(false);
  const [successMsg, setSuccessMsg] = useState('');

  const [addFirstName, setAddFirstName] = useState('');
  const [addLastName, setAddLastName] = useState('');
  const [addEmail, setAddEmail] = useState('');
  const [addPhone, setAddPhone] = useState('');
  const [addRole, setAddRole] = useState('Employee');
  const [addDept, setAddDept] = useState('');
  const [addDesg, setAddDesg] = useState('');
  const [addManager, setAddManager] = useState('');

  const [editId, setEditId] = useState('');
  const [editFirstName, setEditFirstName] = useState('');
  const [editLastName, setEditLastName] = useState('');
  const [editEmail, setEditEmail] = useState('');
  const [editPhone, setEditPhone] = useState('');
  const [editRole, setEditRole] = useState('Employee');
  const [editDept, setEditDept] = useState('');
  const [editDesg, setEditDesg] = useState('');
  const [editManager, setEditManager] = useState('');
  const [activeAttendance, setActiveAttendance] = useState<AttendanceStats>({ present: 0, late: 0, absent: 0, halfDay: 0, logs: [] });

  // Custom States for Reviews
  const [quality, setQuality] = useState(4);
  const [productivity, setProductivity] = useState(4);
  const [teamwork, setTeamwork] = useState(4);
  const [communication, setCommunication] = useState(4);
  const [comment, setComment] = useState('');
  const [submittingReview, setSubmittingReview] = useState(false);
  const [reviewSuccess, setReviewSuccess] = useState(false);

  // Review List State & Fetching
  const [activePerformance, setActivePerformance] = useState<PerformanceAppraisalType[]>([]);
  const [downloadingReport, setDownloadingReport] = useState(false);

  useEffect(() => {
    if (selectedEmployee) {
      const fetchAppraisals = async () => {
        try {
          const res = await api.get(`/employees/${selectedEmployee.id}/appraisals`);
          setActivePerformance(res.data.data || []);
        } catch (err) {
          console.error('Error fetching appraisals:', err);
          setActivePerformance([]);
        }
      };
      fetchAppraisals();
    } else {
      setActivePerformance([]);
    }
  }, [selectedEmployee]);

  // Chat Interactive Engine
  const [chatMessages, setChatMessages] = useState<Record<string, ChatMessage[]>>({});

  const [newMsg, setNewMsg] = useState('');
  const chatEndRef = useRef<HTMLDivElement>(null);

  const getRole = (emp: EmployeeType) => {
    return emp.user?.roles?.[0]?.name || 'Employee';
  };

  const selectableManagers = employees.filter(emp => emp.user?.id);
  const filteredAddDesignations = designations.filter(desg => !addDept || desg.department_id === addDept);
  const filteredEditDesignations = designations.filter(desg => !editDept || desg.department_id === editDept);

  const normalizeEmployee = (emp: EmployeeType): EmployeeType => ({
    ...emp,
    status: emp.status || 'Active',
    department_id: emp.department_id ?? emp.department?.id ?? null,
    designation_id: emp.designation_id ?? emp.designation?.id ?? null,
    user: emp.user || { roles: [{ name: emp.email === 'admin@humanode.net' ? 'Admin' : emp.email === 'hr@humanode.net' ? 'HR' : emp.email === 'manager@humanode.net' ? 'Manager' : 'Employee' }] }
  });

  const employeePayload = (
    firstName: string,
    lastName: string,
    email: string,
    phone: string,
    role: string,
    departmentId: string,
    designationId: string,
    managerId: string,
    joiningDate?: string,
  ) => ({
    first_name: firstName,
    last_name: lastName,
    email,
    phone,
    role_name: role,
    department_id: departmentId || null,
    designation_id: designationId || null,
    manager_id: managerId || null,
    joining_date: joiningDate || new Date().toISOString().split('T')[0],
    status: 'Active',
  });

  const handleCreateEmployee = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!addFirstName || !addLastName || !addEmail) return;

    const res = await api.post('/employees', employeePayload(
      addFirstName,
      addLastName,
      addEmail,
      addPhone,
      addRole,
      addDept,
      addDesg,
      addManager,
    ));
    const newEmp = normalizeEmployee(res.data.data);
    setEmployees(prev => [newEmp, ...prev]);
    setSelectedEmployee(newEmp);
    setShowAddModal(false);
    
    setAddFirstName('');
    setAddLastName('');
    setAddEmail('');
    setAddPhone('');
    setAddRole('Employee');
    setAddDept(departments[0]?.id ?? '');
    setAddDesg(designations[0]?.id ?? '');
    setAddManager('');

    setSuccessMsg('New employee profile created successfully in registry!');
    setSuccessAlert(true);
    setTimeout(() => setSuccessAlert(false), 3000);
  };

  const handleOpenEditModal = (emp: EmployeeType) => {
    setEditId(emp.id);
    setEditFirstName(emp.first_name);
    setEditLastName(emp.last_name);
    setEditEmail(emp.email);
    setEditPhone(emp.phone);
    setEditRole(getRole(emp));
    setEditDept(emp.department_id || emp.department?.id || '');
    setEditDesg(emp.designation_id || emp.designation?.id || '');
    setEditManager(emp.manager_id || '');
    setShowEditModal(true);
  };

  const handleUpdateEmployee = async (e: React.FormEvent) => {
    e.preventDefault();
    const original = employees.find(emp => emp.id === editId);
    const res = await api.put(`/employees/${editId}`, employeePayload(
      editFirstName,
      editLastName,
      editEmail,
      editPhone,
      editRole,
      editDept,
      editDesg,
      editManager,
      original?.joining_date,
    ));
    const updated = normalizeEmployee(res.data.data);
    setEmployees(prev => prev.map(emp => emp.id === editId ? updated : emp));
    if (selectedEmployee?.id === editId) {
      setSelectedEmployee(updated);
    }
    setShowEditModal(false);
    setSuccessMsg('Employee profile details updated successfully.');
    setSuccessAlert(true);
    setTimeout(() => setSuccessAlert(false), 3000);
  };

  const handleDeleteEmployee = async (id: string) => {
    if (!window.confirm("Are you sure you want to permanently delete this employee record from the HRMS database?")) {
      return;
    }
    await api.delete(`/employees/${id}`);
    setEmployees(prev => prev.filter(emp => emp.id !== id));
    setSelectedEmployee(null);
    setSuccessMsg('Employee profile record has been deleted from registry.');
    setSuccessAlert(true);
    setTimeout(() => setSuccessAlert(false), 3000);
  };

  useEffect(() => {
    const loadData = async () => {
      try {
        const [employeeRes, departmentRes, designationRes] = await Promise.all([
          api.get('/employees'),
          api.get('/departments'),
          api.get('/designations'),
        ]);
        const loadedEmployees = employeeRes.data.data.data;
        const loadedDepartments = departmentRes.data.data;
        const loadedDesignations = designationRes.data.data;

        const processed = Array.isArray(loadedEmployees) ? loadedEmployees.map(normalizeEmployee) : [];
        setEmployees(processed);
        setDepartments(Array.isArray(loadedDepartments) ? loadedDepartments : []);
        setDesignations(Array.isArray(loadedDesignations) ? loadedDesignations : []);
        setAddDept(loadedDepartments?.[0]?.id ?? '');
        setAddDesg(loadedDesignations?.[0]?.id ?? '');
        setSelectedEmployee(processed[0] ?? null);
      } catch (error) {
        console.error('Failed to load employee management data', error);
        setEmployees([]);
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
  }, [chatMessages, selectedEmployee]);

  useEffect(() => {
    const loadAttendance = async () => {
      if (!selectedEmployee) {
        setActiveAttendance({ present: 0, late: 0, absent: 0, halfDay: 0, logs: [] });
        return;
      }

      const endDate = new Date();
      const startDate = new Date();
      startDate.setDate(endDate.getDate() - 30);

      try {
        const res = await api.get('/attendance/report', {
          params: {
            employee_id: selectedEmployee.id,
            start_date: startDate.toISOString().split('T')[0],
            end_date: endDate.toISOString().split('T')[0],
          },
        });
        const data = res.data.data;
        setActiveAttendance({
          present: data.total_present ?? 0,
          late: data.total_late ?? 0,
          absent: data.total_absent ?? 0,
          halfDay: data.total_half_day ?? 0,
          logs: (data.logs ?? []).map((log: any) => ({
            date: log.log_date,
            clock_in: log.clock_in ? new Date(log.clock_in).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '--:--',
            clock_out: log.clock_out ? new Date(log.clock_out).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '--:--',
            status: log.status,
            geofence: log.clock_in_latitude && log.clock_in_longitude ? 'Verified' : 'Not Captured',
          })),
        });
      } catch (error) {
        console.error('Failed to load employee attendance report', error);
        setActiveAttendance({ present: 0, late: 0, absent: 0, halfDay: 0, logs: [] });
      }
    };

    loadAttendance();
  }, [selectedEmployee]);

  useEffect(() => {
    const loadEmployeeActivity = async () => {
      if (!selectedEmployee) return;

      try {
        const messagesRes = await api.get(`/employees/${selectedEmployee.id}/messages`);
        setChatMessages(prev => ({
          ...prev,
          [selectedEmployee.employee_id]: messagesRes.data.data ?? [],
        }));
      } catch (err) {
        console.error('Failed to load employee messages:', err);
      }
    };

    loadEmployeeActivity();
  }, [selectedEmployee]);

  const handleSendMessage = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newMsg.trim() || !selectedEmployee) return;

    const empId = selectedEmployee.employee_id;
    const res = await api.post(`/employees/${selectedEmployee.id}/messages`, {
      message: newMsg.trim(),
      sender_type: 'admin',
    });
    const adminMsg: ChatMessage = res.data.data;
    const currentMessages = chatMessages[empId] || [];
    setChatMessages({
      ...chatMessages,
      [empId]: [...currentMessages, adminMsg]
    });
    setNewMsg('');
  };

  const handleAddReview = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedEmployee) return;

    setSubmittingReview(true);
    try {
      const res = await api.post(`/employees/${selectedEmployee.id}/appraisals`, {
        reviewer_name: user?.name || 'Admin User',
        quality_score: quality,
        productivity_score: productivity,
        teamwork_score: teamwork,
        communication_score: communication,
        comment: comment.trim() || 'Scorecard updated by system administrator.'
      });

      // Prepend the new appraisal to the activePerformance list
      setActivePerformance(prev => [res.data.data, ...prev]);
      setReviewSuccess(true);
      setComment('');
      setTimeout(() => setReviewSuccess(false), 3000);
    } catch (err: any) {
      console.error(err);
      alert(err.response?.data?.message || 'Failed to submit appraisal.');
    } finally {
      setSubmittingReview(false);
    }
  };

  const handleDownloadPerformanceReport = async () => {
    if (!selectedEmployee) return;
    setDownloadingReport(true);
    try {
      const res = await api.get(`/employees/${selectedEmployee.id}/appraisals/report`, {
        responseType: 'blob'
      });
      const blob = new Blob([res.data], { type: 'text/csv' });
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `performance_report_${selectedEmployee.employee_id}.csv`);
      document.body.appendChild(link);
      link.click();
      link.parentNode?.removeChild(link);
      window.URL.revokeObjectURL(url);
    } catch (err) {
      console.error('Failed to download report:', err);
      alert('Failed to download report.');
    } finally {
      setDownloadingReport(false);
    }
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
  // activePerformance is state-controlled; activeAttendance is set in useEffect
  const latestPerformanceReview = activePerformance[0];
  const chartData = latestPerformanceReview ? [
    { name: 'Quality', score: latestPerformanceReview.quality_score },
    { name: 'Productivity', score: latestPerformanceReview.productivity_score },
    { name: 'Teamwork', score: latestPerformanceReview.teamwork_score },
    { name: 'Communication', score: latestPerformanceReview.communication_score }
  ] : [
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

      {successAlert && (
        <div className="p-4 rounded-xl bg-success/20 border border-success/30 text-success text-xs font-semibold shadow-lg backdrop-blur-sm flex items-center gap-2">
          <CheckCircle2 size={16} /> {successMsg}
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {/* Left Side: Directory search and list (col-span-5) */}
        <div className="lg:col-span-5 space-y-6">
          <div className="glass-panel p-5 space-y-4">
            
            {/* Search inputs */}
            <div className="flex gap-2">
              <div className="relative flex-grow">
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
              <button 
                onClick={() => setShowAddModal(true)}
                className="px-3.5 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl text-xs flex items-center gap-1 cursor-pointer btn-glow"
              >
                <Plus size={14} /> Add
              </button>
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

                <div className="flex flex-col items-end gap-3 self-end sm:self-center">
                  <div className="flex items-center gap-2">
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
                  <div className="flex gap-2">
                    <button 
                      onClick={() => handleOpenEditModal(selectedEmployee)}
                      className="p-1.5 border border-slate-200 dark:border-slate-800 hover:border-primary-500 rounded-xl text-slate-400 hover:text-primary-500 cursor-pointer transition-colors"
                      title="Edit Profile"
                    >
                      <Edit3 size={12} />
                    </button>
                    <button 
                      onClick={() => handleDeleteEmployee(selectedEmployee.id)}
                      className="p-1.5 border border-slate-200 dark:border-slate-800 hover:border-danger rounded-xl text-slate-400 hover:text-danger cursor-pointer transition-colors"
                      title="Delete Profile"
                    >
                      <Trash2 size={12} />
                    </button>
                  </div>
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
                <button 
                  onClick={() => setActiveTab('workHistory')}
                  className={`px-4 py-2 text-xs font-bold border-b-2 transition-all duration-200 flex items-center gap-2 cursor-pointer ${
                    activeTab === 'workHistory' 
                      ? 'border-primary-500 text-primary-500' 
                      : 'border-transparent text-slate-400 hover:text-slate-300'
                  }`}
                >
                  <Briefcase size={14} /> Work History
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
                          {latestPerformanceReview ? latestPerformanceReview.overall_score : 'N/A'}
                        </span>
                        <span className="text-[10px] font-bold text-slate-400 uppercase mt-0.5">Out of 5.0</span>
                      </div>
                      <div className="flex gap-1 mt-4 text-warning">
                        {Array.from({ length: 5 }).map((_, i) => (
                          <Star 
                            key={i} 
                            size={14} 
                            fill={latestPerformanceReview && i < Math.floor(latestPerformanceReview.overall_score) ? 'currentColor' : 'none'} 
                          />
                        ))}
                      </div>
                      <p className="text-[10px] text-slate-500 font-semibold tracking-wide uppercase mt-3">
                        Last Reviewed: {latestPerformanceReview ? latestPerformanceReview.review_date : 'Never'}
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
                    <div className="flex justify-between items-center border-b border-slate-200 dark:border-slate-800/80 pb-3 mb-2">
                      <h4 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest">
                        Review Audit History
                      </h4>
                      {activePerformance.length > 0 && (
                        <button
                          onClick={handleDownloadPerformanceReport}
                          disabled={downloadingReport}
                          className="px-2.5 py-1.5 bg-secondary-500/10 hover:bg-secondary-500/20 text-secondary-500 border border-secondary-500/20 font-bold rounded-lg text-[10px] cursor-pointer transition-colors disabled:opacity-50"
                        >
                          {downloadingReport ? 'Exporting...' : 'Export CSV Report'}
                        </button>
                      )}
                    </div>
                    
                    {activePerformance.length === 0 ? (
                      <div className="text-center py-6 text-slate-400 text-xs">
                        No prior performance reviews logged for this profile.
                      </div>
                    ) : (
                      activePerformance.map((rev) => (
                        <div key={rev.id} className="p-4 bg-slate-100/30 dark:bg-slate-950/10 border border-slate-200 dark:border-slate-800/60 rounded-xl space-y-2">
                          <div className="flex justify-between items-start">
                            <div>
                              <p className="text-xs font-bold text-slate-700 dark:text-slate-300">Reviewed by {rev.reviewer_name}</p>
                              <p className="text-[9px] text-slate-400 font-semibold">{new Date(rev.review_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</p>
                            </div>
                            <span className="px-2 py-1 bg-gradient-to-r from-primary-500 to-secondary-500 text-slate-950 font-black rounded-lg text-xs">
                              {rev.overall_score} / 5.0
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

              {/* Tab: Work History */}
              {activeTab === 'workHistory' && (
                <div className="space-y-6">
                  <div className="glass-panel p-6 space-y-4">
                    <div className="flex justify-between items-center border-b border-slate-200 dark:border-slate-800/40 pb-3">
                      <h4 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest flex items-center gap-1.5">
                        <Briefcase size={16} className="text-primary-500" /> Prior Employment History
                      </h4>
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

                    {savingWorkHistory && (
                      <div className="p-3 bg-primary-500/10 border border-primary-500/20 text-primary-500 rounded-xl text-xs font-semibold animate-pulse">
                        Saving changes to database...
                      </div>
                    )}

                    {(!selectedEmployee.employment_history || selectedEmployee.employment_history.length === 0) ? (
                      <p className="text-xs text-slate-500 italic py-4">No prior work history records added for this employee.</p>
                    ) : (
                      <div className="space-y-3">
                        {selectedEmployee.employment_history.map((history, idx) => (
                          <div key={idx} className="p-4 bg-slate-100/30 dark:bg-slate-950/10 border border-slate-200 dark:border-slate-800/60 rounded-xl flex justify-between items-start">
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
                </div>
              )}

            </div>
          )}
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

      {/* Add Employee Modal */}
      {showAddModal && (
        <div className="fixed inset-0 z-50 overflow-y-auto">
          <div className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" onClick={() => setShowAddModal(false)}></div>
          <div className="flex min-h-full items-center justify-center p-4">
            <div className="relative transform overflow-hidden rounded-2xl bg-slate-900 border border-slate-800 p-6 shadow-2xl transition-all w-full max-w-lg">
              <div className="flex justify-between items-center pb-3 border-b border-slate-800 mb-4">
                <h3 className="font-bold text-slate-100 text-sm flex items-center gap-1.5"><Users size={16} className="text-primary-500" /> Register New Employee</h3>
                <button onClick={() => setShowAddModal(false)} className="text-slate-400 hover:text-slate-200 cursor-pointer">
                  <X size={18} />
                </button>
              </div>

              <form onSubmit={handleCreateEmployee} className="space-y-4 text-xs text-slate-300">
                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-1">
                    <label className="text-[10px] font-bold text-slate-400 uppercase">First Name</label>
                    <input type="text" value={addFirstName} onChange={(e) => setAddFirstName(e.target.value)} className="glass-input text-xs" placeholder="First Name" required />
                  </div>
                  <div className="space-y-1">
                    <label className="text-[10px] font-bold text-slate-400 uppercase">Last Name</label>
                    <input type="text" value={addLastName} onChange={(e) => setAddLastName(e.target.value)} className="glass-input text-xs" placeholder="Last Name" required />
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-1">
                    <label className="text-[10px] font-bold text-slate-400 uppercase">Work Email</label>
                    <input type="email" value={addEmail} onChange={(e) => setAddEmail(e.target.value)} className="glass-input text-xs" placeholder="e.g. employee@humanode.net" required />
                  </div>
                  <div className="space-y-1">
                    <label className="text-[10px] font-bold text-slate-400 uppercase">Phone Number</label>
                    <input type="text" value={addPhone} onChange={(e) => setAddPhone(e.target.value)} className="glass-input text-xs" placeholder="e.g. +1 555-0100" />
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-1">
                    <label className="text-[10px] font-bold text-slate-400 uppercase">Role Type</label>
                    <select value={addRole} onChange={(e) => setAddRole(e.target.value)} className="glass-input text-xs" required>
                      <option value="Admin">Admin</option>
                      <option value="HR">HR</option>
                      <option value="Manager">Manager</option>
                      <option value="Employee">Employee</option>
                    </select>
                  </div>
                  <div className="space-y-1">
                    <label className="text-[10px] font-bold text-slate-400 uppercase">Reporting Manager</label>
                    <select value={addManager} onChange={(e) => setAddManager(e.target.value)} className="glass-input text-xs" required>
                      <option value="">None</option>
                      {selectableManagers.map(manager => (
                        <option key={manager.user?.id} value={manager.user?.id}>
                          {manager.first_name} {manager.last_name}
                        </option>
                      ))}
                    </select>
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-1">
                    <label className="text-[10px] font-bold text-slate-400 uppercase">Department Mapping</label>
                    <select
                      value={addDept}
                      onChange={(e) => {
                        const nextDept = e.target.value;
                        setAddDept(nextDept);
                        setAddDesg(designations.find(designation => designation.department_id === nextDept)?.id ?? '');
                      }}
                      className="glass-input text-xs"
                      required
                    >
                      {departments.map(department => (
                        <option key={department.id} value={department.id}>{department.name}</option>
                      ))}
                    </select>
                  </div>
                  <div className="space-y-1">
                    <label className="text-[10px] font-bold text-slate-400 uppercase">Job Designation</label>
                    <select value={addDesg} onChange={(e) => setAddDesg(e.target.value)} className="glass-input text-xs" required>
                      {filteredAddDesignations.map(designation => (
                        <option key={designation.id} value={designation.id}>{designation.title}</option>
                      ))}
                    </select>
                  </div>
                </div>

                <div className="flex gap-4 pt-3 border-t border-slate-800">
                  <button type="button" onClick={() => setShowAddModal(false)} className="flex-1 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-xl text-xs cursor-pointer">
                    Cancel
                  </button>
                  <button type="submit" className="flex-1 py-2 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl text-xs btn-glow cursor-pointer">
                    Register Employee
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      )}

      {/* Edit Employee Modal */}
      {showEditModal && (
        <div className="fixed inset-0 z-50 overflow-y-auto">
          <div className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" onClick={() => setShowEditModal(false)}></div>
          <div className="flex min-h-full items-center justify-center p-4">
            <div className="relative transform overflow-hidden rounded-2xl bg-slate-900 border border-slate-800 p-6 shadow-2xl transition-all w-full max-w-lg">
              <div className="flex justify-between items-center pb-3 border-b border-slate-800 mb-4">
                <h3 className="font-bold text-slate-100 text-sm flex items-center gap-1.5"><Edit3 size={16} className="text-primary-500" /> Edit Employee Profile</h3>
                <button onClick={() => setShowEditModal(false)} className="text-slate-400 hover:text-slate-200 cursor-pointer">
                  <X size={18} />
                </button>
              </div>

              <form onSubmit={handleUpdateEmployee} className="space-y-4 text-xs text-slate-300">
                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-1">
                    <label className="text-[10px] font-bold text-slate-400 uppercase">First Name</label>
                    <input type="text" value={editFirstName} onChange={(e) => setEditFirstName(e.target.value)} className="glass-input text-xs" required />
                  </div>
                  <div className="space-y-1">
                    <label className="text-[10px] font-bold text-slate-400 uppercase">Last Name</label>
                    <input type="text" value={editLastName} onChange={(e) => setEditLastName(e.target.value)} className="glass-input text-xs" required />
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-1">
                    <label className="text-[10px] font-bold text-slate-400 uppercase">Work Email</label>
                    <input type="email" value={editEmail} onChange={(e) => setEditEmail(e.target.value)} className="glass-input text-xs" required />
                  </div>
                  <div className="space-y-1">
                    <label className="text-[10px] font-bold text-slate-400 uppercase">Phone Number</label>
                    <input type="text" value={editPhone} onChange={(e) => setEditPhone(e.target.value)} className="glass-input text-xs" />
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-1">
                    <label className="text-[10px] font-bold text-slate-400 uppercase">Role Type</label>
                    <select value={editRole} onChange={(e) => setEditRole(e.target.value)} className="glass-input text-xs" required>
                      <option value="Admin">Admin</option>
                      <option value="HR">HR</option>
                      <option value="Manager">Manager</option>
                      <option value="Employee">Employee</option>
                    </select>
                  </div>
                  <div className="space-y-1">
                    <label className="text-[10px] font-bold text-slate-400 uppercase">Reporting Manager</label>
                    <select value={editManager} onChange={(e) => setEditManager(e.target.value)} className="glass-input text-xs" required>
                      <option value="">None</option>
                      {selectableManagers
                        .filter(manager => manager.id !== editId)
                        .map(manager => (
                          <option key={manager.user?.id} value={manager.user?.id}>
                            {manager.first_name} {manager.last_name}
                          </option>
                        ))}
                    </select>
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-1">
                    <label className="text-[10px] font-bold text-slate-400 uppercase">Department Mapping</label>
                    <select
                      value={editDept}
                      onChange={(e) => {
                        const nextDept = e.target.value;
                        setEditDept(nextDept);
                        setEditDesg(designations.find(designation => designation.department_id === nextDept)?.id ?? '');
                      }}
                      className="glass-input text-xs"
                      required
                    >
                      {departments.map(department => (
                        <option key={department.id} value={department.id}>{department.name}</option>
                      ))}
                    </select>
                  </div>
                  <div className="space-y-1">
                    <label className="text-[10px] font-bold text-slate-400 uppercase">Job Designation</label>
                    <select value={editDesg} onChange={(e) => setEditDesg(e.target.value)} className="glass-input text-xs" required>
                      {filteredEditDesignations.map(designation => (
                        <option key={designation.id} value={designation.id}>{designation.title}</option>
                      ))}
                    </select>
                  </div>
                </div>

                <div className="flex gap-4 pt-3 border-t border-slate-800">
                  <button type="button" onClick={() => setShowEditModal(false)} className="flex-1 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-xl text-xs cursor-pointer">
                    Cancel
                  </button>
                  <button type="submit" className="flex-1 py-2 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl text-xs btn-glow cursor-pointer">
                    Save Changes
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

export default Employees;
