import React, { useEffect, useState } from 'react';
import api from '../lib/api';
import { Shield, Mail, Bell, CheckCircle2, Save } from 'lucide-react';

interface RolePermissionMap {
  permission: string;
  admin: boolean;
  hr: boolean;
  manager: boolean;
  employee: boolean;
}

interface EmailTemplate {
  id: string;
  name: string;
  subject: string;
  body: string;
  variables: string[];
}

export const Settings: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'permissions' | 'templates' | 'notifications'>('permissions');
  const [success, setSuccess] = useState(false);
  const [successMsg, setSuccessMsg] = useState('');
  const [settingsData, setSettingsData] = useState<Record<string, any>>({});

  // 1. Roles & Permissions State
  const [permissions, setPermissions] = useState<RolePermissionMap[]>([
    { permission: 'View Analytics Dashboard', admin: true, hr: true, manager: true, employee: true },
    { permission: 'Add/Edit/Delete Employees', admin: true, hr: true, manager: false, employee: false },
    { permission: 'Configure Org Departments & Shifts', admin: true, hr: true, manager: false, employee: false },
    { permission: 'Approve Attendance & Regularizations', admin: true, hr: true, manager: true, employee: false },
    { permission: 'Configure Leave Policies & Limits', admin: true, hr: false, manager: false, employee: false },
    { permission: 'Process Payroll & Salary Slips', admin: true, hr: true, manager: false, employee: false },
    { permission: 'Manage Portal System Settings', admin: true, hr: false, manager: false, employee: false }
  ]);

  // 2. Email Templates State
  const [templates, setTemplates] = useState<EmailTemplate[]>([
    {
      id: 't1',
      name: 'Welcome & Onboarding Email',
      subject: 'Welcome to HumaNode, {{employee_name}}!',
      body: 'Dear {{employee_name}},\n\nWe are absolutely thrilled to welcome you to the {{department_name}} team as a {{designation_title}}!\n\nYour official joining date is set for {{joining_date}}. Please log into your employee self-service dashboard to complete your onboarding task checklist.\n\nBest Regards,\nHR Operations Team',
      variables: ['{{employee_name}}', '{{department_name}}', '{{designation_title}}', '{{joining_date}}']
    },
    {
      id: 't2',
      name: 'Leave Request Approved Notification',
      subject: 'Leave Request Approved: {{leave_type}}',
      body: 'Dear {{employee_name}},\n\nYour request for {{leave_days}} days of {{leave_type}} has been approved by your manager, {{manager_name}}.\n\nYour remaining balance has been updated accordingly in the system database.\n\nWarm Regards,\nSystem Portal',
      variables: ['{{employee_name}}', '{{leave_type}}', '{{leave_days}}', '{{manager_name}}']
    },
    {
      id: 't3',
      name: 'Performance Review Graded Notification',
      subject: 'Performance Appraisal Graded: {{review_period}}',
      body: 'Dear {{employee_name}},\n\nYour performance review for the period {{review_period}} has been successfully evaluated and locked by System Administration. Your overall score is {{overall_score}} / 5.0.\n\nPlease log into the Performance Hub on your dashboard to view comments and score charts.\n\nSincerely,\nExecutive board',
      variables: ['{{employee_name}}', '{{review_period}}', '{{overall_score}}']
    }
  ]);
  const [selectedTemplateId, setSelectedTemplateId] = useState('t1');
  const activeTemplate = templates.find(t => t.id === selectedTemplateId) || templates[0];

  // 3. Notification Settings State
const [slackWebhook, setSlackWebhook] = useState('');
  const [notifPreferences, setNotifPreferences] = useState({
    punchEmail: true,
    punchSlack: false,
    leaveEmail: true,
    leaveSlack: true,
    reviewEmail: true,
    reviewPush: true,
    announcementSlack: true,
    announcementPush: true
  });

  const showSuccess = (msg: string) => {
    setSuccessMsg(msg);
    setSuccess(true);
    setTimeout(() => setSuccess(false), 3000);
  };

  useEffect(() => {
    const loadSettings = async () => {
      const res = await api.get('/settings');
      const data = res.data.data.settings_data ?? {};
      setSettingsData(data);

      if (Array.isArray(data.permissions)) {
        setPermissions(data.permissions);
      }
      if (Array.isArray(data.email_templates)) {
        setTemplates(data.email_templates);
        setSelectedTemplateId(data.email_templates[0]?.id ?? 't1');
      }
      if (data.slack_webhook) {
        setSlackWebhook(data.slack_webhook);
      }
      if (data.notification_preferences) {
        setNotifPreferences(data.notification_preferences);
      }
    };

    loadSettings();
  }, []);

  const persistSettingsData = async (nextData: Record<string, any>, message: string) => {
    const mergedData = { ...settingsData, ...nextData };
    const res = await api.put('/settings', { settings_data: mergedData });
    setSettingsData(res.data.data.settings_data ?? mergedData);
    showSuccess(message);
  };

  const handleTogglePermission = (index: number, role: 'admin' | 'hr' | 'manager' | 'employee') => {
    setPermissions(prev => prev.map((p, idx) => {
      if (idx === index) {
        return { ...p, [role]: !p[role] };
      }
      return p;
    }));
  };

  const handleSavePermissions = async (e: React.FormEvent) => {
    e.preventDefault();
    await persistSettingsData({ permissions }, 'Roles and modular permissions configurations saved successfully!');
  };

  const handleTemplateChange = (field: 'subject' | 'body', value: string) => {
    setTemplates(prev => prev.map(t => {
      if (t.id === selectedTemplateId) {
        return { ...t, [field]: value };
      }
      return t;
    }));
  };

  const handleSaveTemplates = async (e: React.FormEvent) => {
    e.preventDefault();
    await persistSettingsData({ email_templates: templates }, 'Email communication templates updated in database.');
  };

  const handleSaveNotifications = async (e: React.FormEvent) => {
    e.preventDefault();
    await persistSettingsData({
      slack_webhook: slackWebhook,
      notification_preferences: notifPreferences,
    }, 'Notification dispatch preferences and webhooks saved.');
  };

  return (
    <div className="space-y-8 max-w-7xl mx-auto">
      {/* Title */}
      <div>
        <h1 className="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">System Settings & Controls</h1>
        <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
          Configure security roles & permissions, manage automated transactional email templates, and customize notification webhook dispatch channels.
        </p>
      </div>

      {success && (
        <div className="p-4 rounded-xl bg-success/20 border border-success/30 text-success text-xs font-semibold shadow-lg backdrop-blur-sm flex items-center gap-2">
          <CheckCircle2 size={16} /> {successMsg}
        </div>
      )}

      {/* Tabs */}
      <div className="flex gap-2 border-b border-slate-200 dark:border-slate-800/85 pb-0.5">
        <button 
          onClick={() => { setActiveTab('permissions'); setSuccess(false); }}
          className={`px-4 py-2 text-xs font-bold border-b-2 transition-all duration-200 flex items-center gap-2 cursor-pointer ${
            activeTab === 'permissions' 
              ? 'border-primary-500 text-primary-500' 
              : 'border-transparent text-slate-400 hover:text-slate-300'
          }`}
        >
          <Shield size={14} /> Roles & Permissions
        </button>
        <button 
          onClick={() => { setActiveTab('templates'); setSuccess(false); }}
          className={`px-4 py-2 text-xs font-bold border-b-2 transition-all duration-200 flex items-center gap-2 cursor-pointer ${
            activeTab === 'templates' 
              ? 'border-primary-500 text-primary-500' 
              : 'border-transparent text-slate-400 hover:text-slate-300'
          }`}
        >
          <Mail size={14} /> Email Templates
        </button>
        <button 
          onClick={() => { setActiveTab('notifications'); setSuccess(false); }}
          className={`px-4 py-2 text-xs font-bold border-b-2 transition-all duration-200 flex items-center gap-2 cursor-pointer ${
            activeTab === 'notifications' 
              ? 'border-primary-500 text-primary-500' 
              : 'border-transparent text-slate-400 hover:text-slate-300'
          }`}
        >
          <Bell size={14} /> Notifications Preferences
        </button>
      </div>

      {/* Tab content slots */}
      
      {/* 1. Permissions Tab */}
      {activeTab === 'permissions' && (
        <form onSubmit={handleSavePermissions} className="glass-panel p-6 space-y-6">
          <div className="flex justify-between items-center border-b border-slate-200 dark:border-slate-800/40 pb-3">
            <h3 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest">
              Access Matrix Control
            </h3>
            <button type="submit" className="px-3.5 py-2 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl text-xs flex items-center gap-1.5 cursor-pointer btn-glow">
              <Save size={14} /> Save Permissions
            </button>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs border-collapse">
              <thead>
                <tr className="border-b border-slate-200 dark:border-slate-800/80 text-slate-400 uppercase tracking-widest font-bold">
                  <th className="pb-3 pr-4">Module Permission</th>
                  <th className="pb-3 px-4 text-center">Admin</th>
                  <th className="pb-3 px-4 text-center">HR</th>
                  <th className="pb-3 px-4 text-center">Manager</th>
                  <th className="pb-3 pl-4 text-center">Employee</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 dark:divide-slate-800/40 font-semibold text-slate-700 dark:text-slate-300">
                {permissions.map((p, idx) => (
                  <tr key={idx} className="hover:bg-slate-100/50 dark:hover:bg-slate-800/20 transition-colors">
                    <td className="py-4 pr-4 text-slate-800 dark:text-slate-100 font-bold">{p.permission}</td>
                    <td className="py-4 px-4 text-center">
                      <input 
                        type="checkbox" checked={p.admin} 
                        onChange={() => handleTogglePermission(idx, 'admin')} 
                        className="w-4 h-4 accent-primary-500 rounded border-slate-800 cursor-pointer"
                      />
                    </td>
                    <td className="py-4 px-4 text-center">
                      <input 
                        type="checkbox" checked={p.hr} 
                        onChange={() => handleTogglePermission(idx, 'hr')} 
                        className="w-4 h-4 accent-primary-500 rounded border-slate-800 cursor-pointer"
                      />
                    </td>
                    <td className="py-4 px-4 text-center">
                      <input 
                        type="checkbox" checked={p.manager} 
                        onChange={() => handleTogglePermission(idx, 'manager')} 
                        className="w-4 h-4 accent-primary-500 rounded border-slate-800 cursor-pointer"
                      />
                    </td>
                    <td className="py-4 pl-4 text-center">
                      <input 
                        type="checkbox" checked={p.employee} 
                        onChange={() => handleTogglePermission(idx, 'employee')} 
                        className="w-4 h-4 accent-primary-500 rounded border-slate-800 cursor-pointer"
                      />
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </form>
      )}

      {/* 2. Templates Tab */}
      {activeTab === 'templates' && (
        <form onSubmit={handleSaveTemplates} className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
          
          {/* Left selector list (col-span-4) */}
          <div className="lg:col-span-4 glass-panel p-4 max-h-[500px] overflow-y-auto space-y-2.5">
            <h3 className="text-xs font-black tracking-widest text-slate-400 uppercase px-2 mb-3">
              App System Templates
            </h3>
            {templates.map(t => (
              <div 
                key={t.id}
                onClick={() => setSelectedTemplateId(t.id)}
                className={`p-3.5 rounded-xl border transition-all duration-300 cursor-pointer ${
                  t.id === selectedTemplateId 
                    ? 'bg-primary-500/10 border-primary-500/40 shadow-md' 
                    : 'bg-slate-100/50 dark:bg-slate-950/20 border-slate-200 dark:border-slate-800/80 hover:border-slate-300 dark:hover:border-slate-700'
                }`}
              >
                <h4 className="text-xs font-bold text-slate-800 dark:text-slate-100">{t.name}</h4>
                <p className="text-[10px] text-slate-400 mt-1 font-semibold truncate">Subject: {t.subject}</p>
              </div>
            ))}
          </div>

          {/* Right Editor form (col-span-8) */}
          <div className="lg:col-span-8 glass-panel p-6 space-y-6 flex flex-col justify-between">
            <div className="flex justify-between items-center border-b border-slate-200 dark:border-slate-800/40 pb-3">
              <h3 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest">
                Template Content Editor
              </h3>
              <button type="submit" className="px-3.5 py-2 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl text-xs flex items-center gap-1.5 cursor-pointer btn-glow">
                <Save size={14} /> Update Template
              </button>
            </div>

            <div className="space-y-4">
              <div className="space-y-1">
                <label className="text-[10px] font-bold text-slate-400 uppercase">Subject Heading</label>
                <input 
                  type="text" value={activeTemplate.subject} 
                  onChange={(e) => handleTemplateChange('subject', e.target.value)}
                  className="glass-input text-xs" required
                />
              </div>

              <div className="space-y-1">
                <label className="text-[10px] font-bold text-slate-400 uppercase">Template Body Draft</label>
                <textarea 
                  value={activeTemplate.body} 
                  onChange={(e) => handleTemplateChange('body', e.target.value)}
                  className="glass-input text-xs font-mono leading-relaxed resize-none" rows={8} required
                />
              </div>
            </div>

            {/* Variable chip list helper */}
            <div className="p-4 bg-slate-100/30 dark:bg-slate-950/25 border border-slate-200 dark:border-slate-800/60 rounded-xl space-y-2">
              <p className="text-[10px] font-bold text-slate-400 uppercase">Dynamic Placeholders Legend</p>
              <div className="flex flex-wrap gap-1.5">
                {activeTemplate.variables.map(v => (
                  <span key={v} className="px-2 py-0.5 bg-slate-200 dark:bg-slate-800 border border-slate-300 dark:border-slate-700/80 rounded-lg text-[9px] font-bold text-slate-400">
                    {v}
                  </span>
                ))}
              </div>
            </div>

          </div>

        </form>
      )}

      {/* 3. Notifications Tab */}
      {activeTab === 'notifications' && (
        <form onSubmit={handleSaveNotifications} className="glass-panel p-6 space-y-6">
          <div className="flex justify-between items-center border-b border-slate-200 dark:border-slate-800/40 pb-3">
            <h3 className="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase tracking-widest">
              Notification Routing
            </h3>
            <button type="submit" className="px-3.5 py-2 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl text-xs flex items-center gap-1.5 cursor-pointer btn-glow">
              <Save size={14} /> Save Preferences
            </button>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
            
            {/* Preferences grid */}
            <div className="space-y-4">
              <h4 className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Channel Preferences</h4>
              
              <div className="space-y-3">
                {[
                  { key: 'punchEmail', label: 'Email notification on check-in tardiness / delay', value: notifPreferences.punchEmail },
                  { key: 'punchSlack', label: 'Post today\'s checkout summary directly to corporate Slack channel', value: notifPreferences.punchSlack },
                  { key: 'leaveEmail', label: 'Email employees immediately on leave approval / rejection', value: notifPreferences.leaveEmail },
                  { key: 'leaveSlack', label: 'Slack manager for new leave request waiting in queue', value: notifPreferences.leaveSlack },
                  { key: 'reviewEmail', label: 'Email employees when annual performance review gets locked', value: notifPreferences.reviewEmail },
                  { key: 'reviewPush', label: 'Push alert to employee dashboard on scorecard grades updates', value: notifPreferences.reviewPush },
                  { key: 'announcementSlack', label: 'Broaden new announcements to corporate Slack workspace', value: notifPreferences.announcementSlack },
                  { key: 'announcementPush', label: 'System dashboard notification alert for company announcements', value: notifPreferences.announcementPush }
                ].map(item => (
                  <div 
                    key={item.key} 
                    onClick={() => setNotifPreferences({ ...notifPreferences, [item.key]: !item.value })}
                    className="flex items-center justify-between p-3 bg-slate-100/50 dark:bg-slate-900/30 border border-slate-200 dark:border-slate-800 rounded-xl hover:border-primary-500 cursor-pointer select-none transition-colors"
                  >
                    <span className="text-xs font-semibold text-slate-700 dark:text-slate-300 max-w-[85%]">{item.label}</span>
                    <span className={`w-5 h-5 rounded-full flex items-center justify-center border transition-all ${
                      item.value 
                        ? 'bg-primary-500 border-primary-500 text-slate-950' 
                        : 'border-slate-300 dark:border-slate-700 text-transparent'
                    }`}>
                      <CheckCircle2 size={12} strokeWidth={4} />
                    </span>
                  </div>
                ))}
              </div>
            </div>

            {/* Webhook Endpoint block */}
            <div className="space-y-4">
              <h4 className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Webhook Endpoint Connections</h4>
              
              <div className="p-4 bg-slate-100/30 dark:bg-slate-950/25 border border-slate-200 dark:border-slate-800/60 rounded-2xl space-y-3">
                <div className="space-y-1">
                  <label className="text-[9px] font-bold text-slate-400 uppercase">Incoming Slack Webhook URL</label>
                  <input 
                    type="url" value={slackWebhook} 
                    onChange={(e) => setSlackWebhook(e.target.value)}
                    className="glass-input text-xs font-mono" required
                  />
                  <p className="text-[9px] text-slate-500 mt-1">
                    System broadcasts alerts, app announcements, and appraisals notices to this Slack feed configuration.
                  </p>
                </div>
              </div>
            </div>

          </div>
        </form>
      )}

    </div>
  );
};

export default Settings;
