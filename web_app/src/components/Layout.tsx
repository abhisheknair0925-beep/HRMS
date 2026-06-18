import React, { useState } from 'react';
import { useNavigate, useLocation, Outlet } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { 
  Home, Calendar, Clock, FolderClosed, 
  Menu, Bell, Sun, Moon, Briefcase, Settings, Users,
  FileText, UserPlus, GitFork, Settings2, DollarSign
} from 'lucide-react';

export const Layout: React.FC = () => {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [collapsed, setCollapsed] = useState(false);
  const [darkMode, setDarkMode] = useState(() => {
    const isDark = localStorage.getItem('darkMode') === 'true';
    if (isDark) document.documentElement.classList.add('dark');
    return isDark;
  });

  const [notifOpen, setNotifOpen] = useState(false);
  const [profileOpen, setProfileOpen] = useState(false);

  const toggleDarkMode = () => {
    const newMode = !darkMode;
    setDarkMode(newMode);
    localStorage.setItem('darkMode', String(newMode));
    if (newMode) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  };

  const menuItems = [
    { name: 'Dashboard', path: '/dashboard', icon: Home, role: 'All' },
    { name: 'My Attendance', path: '/attendance', icon: Clock, role: 'All' },
    { name: 'My Leaves', path: '/leaves', icon: Calendar, role: 'All' },
    { name: 'My Documents', path: '/documents', icon: FolderClosed, role: 'All' },
    
    // Manager/HR items
    { name: 'Manager Portal', path: '/manager/dashboard', icon: Briefcase, role: ['Manager', 'HR', 'Admin'] },
    { name: 'Employee Registry', path: '/admin/employees', icon: Users, role: ['HR', 'Admin'] },
    { name: 'Document Center', path: '/admin/documents', icon: FileText, role: ['HR', 'Admin'] },
    { name: 'Onboarding & Assets', path: '/admin/onboarding', icon: UserPlus, role: ['HR', 'Admin'] },
    { name: 'Payroll Hub', path: '/admin/payroll', icon: DollarSign, role: ['HR', 'Admin'] },
    { name: 'Org Chart', path: '/admin/org-chart', icon: GitFork, role: ['HR', 'Admin'] },
    { name: 'Leave Policies', path: '/admin/leave-policies', icon: Settings2, role: ['Admin'] },
    { name: 'Settings', path: '/admin/settings', icon: Settings, role: ['Admin'] },
  ];

  const filteredMenu = menuItems.filter(item => {
    if (item.role === 'All') return true;
    if (!user) return false;
    return Array.isArray(item.role) ? item.role.includes(user.role) : item.role === user.role;
  });

  return (
    <div className="flex min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased font-sans">
      
      {/* Sidebar */}
      <aside className={`bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col transition-all duration-300 ease-in-out select-none z-30 ${collapsed ? 'w-20' : 'w-64'}`}>
        
        {/* Sidebar Header */}
        <div className="h-16 flex items-center justify-between px-6 border-b border-slate-200 dark:border-slate-800">
          {!collapsed && (
            <span className="text-xl font-bold bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent tracking-wide">
              HumaNode
            </span>
          )}
          {collapsed && <span className="text-xl font-bold text-primary-500 mx-auto">H</span>}
          
          <button onClick={() => setCollapsed(!collapsed)} 
                  className="p-1.5 rounded-lg bg-slate-50 dark:bg-slate-800/80 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 dark:text-slate-300 transition-colors">
            <Menu size={18} />
          </button>
        </div>

        {/* Navigation List */}
        <nav className="flex-grow px-4 py-6 space-y-1 overflow-y-auto">
          {filteredMenu.map((item) => {
            const Icon = item.icon;
            const active = location.pathname === item.path;
            return (
              <button key={item.name}
                      onClick={() => navigate(item.path)}
                      className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 ${
                        active 
                          ? 'bg-primary-500/10 text-primary-500 border border-primary-500/20' 
                          : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-slate-200'
                      }`}>
                <Icon size={18} />
                {!collapsed && <span>{item.name}</span>}
              </button>
            );
          })}
        </nav>

        {/* Profile Info Bottom */}
        <div className="p-4 border-t border-slate-200 dark:border-slate-800">
          <div className="flex items-center gap-3">
            <div className="w-9 h-9 rounded-xl bg-gradient-to-tr from-primary-500 to-secondary-500 flex items-center justify-center font-bold text-slate-950 text-sm">
              {user?.name ? user.name[0] : 'U'}
            </div>
            {!collapsed && (
              <div className="min-w-0 flex-1">
                <p className="text-xs font-bold truncate text-slate-800 dark:text-slate-200">{user?.name || 'Loading...'}</p>
                <p className="text-[10px] text-slate-400 truncate">{user?.role || 'Employee'}</p>
              </div>
            )}
          </div>
        </div>
      </aside>

      {/* Main Content Pane */}
      <div className="flex-1 flex flex-col min-h-screen overflow-x-hidden min-w-0">
        
        {/* Header */}
        <header className="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 z-20">
          <div className="flex-1 max-w-lg hidden md:block">
            <div className="relative">
              <span className="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">🔍</span>
              <input type="text" 
                     placeholder="Search employees, documents, requests..." 
                     className="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-800 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent text-slate-900 dark:text-slate-100 placeholder-slate-400 transition-all" />
            </div>
          </div>

          <div className="flex items-center gap-4 ml-auto">
            {/* Theme switcher */}
            <button onClick={toggleDarkMode}
                    className="p-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800/80 text-slate-500 dark:text-slate-400 transition-all">
              {darkMode ? <Sun size={16} /> : <Moon size={16} />}
            </button>

            {/* Notification drop */}
            <div className="relative">
              <button onClick={() => setNotifOpen(!notifOpen)}
                      className="p-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800/80 text-slate-500 dark:text-slate-400 transition-all relative">
                <Bell size={16} />
                <span className="absolute top-1.5 right-1.5 w-2 h-2 bg-danger rounded-full ring-2 ring-white dark:ring-slate-900"></span>
              </button>
              {notifOpen && (
                <div onMouseLeave={() => setNotifOpen(false)}
                     className="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl py-2 z-50">
                  <div className="px-4 py-2 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <span className="font-bold text-xs">Notifications</span>
                  </div>
                  <div className="divide-y divide-slate-100 dark:divide-slate-800/50 max-h-64 overflow-y-auto">
                    <div className="px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                      <p className="text-xs font-semibold text-slate-800 dark:text-slate-200">Leave Request Approved</p>
                      <p className="text-[10px] text-slate-400 mt-0.5">Your casual leave request has been approved by manager.</p>
                    </div>
                  </div>
                </div>
              )}
            </div>

            {/* User Dropdown */}
            <div className="relative">
              <button onClick={() => setProfileOpen(!profileOpen)} className="flex items-center gap-2 outline-none">
                <div className="w-8.5 h-8.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-800 flex items-center justify-center font-bold text-xs">
                  {user?.name ? user.name[0] : 'U'}
                </div>
              </button>
              {profileOpen && (
                <div onMouseLeave={() => setProfileOpen(false)}
                     className="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl py-2 z-50 text-xs">
                  <div className="px-4 py-2 border-b border-slate-200 dark:border-slate-800">
                    <p className="font-bold text-slate-900 dark:text-slate-100 truncate">{user?.name}</p>
                    <p className="text-[10px] text-slate-400 truncate">{user?.email}</p>
                  </div>
                  <button onClick={() => { navigate('/profile'); setProfileOpen(false); }}
                          className="w-full text-left px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60">My Profile</button>
                  <button onClick={() => { logout(); navigate('/login'); }}
                          className="w-full text-left px-4 py-2 text-danger hover:bg-danger/10 border-t border-slate-200 dark:border-slate-800 mt-1">Logout</button>
                </div>
              )}
            </div>
          </div>
        </header>

        {/* View content slots */}
        <main className="flex-grow p-4 md:p-6 lg:p-8">
          <Outlet />
        </main>
      </div>
    </div>
  );
};
export default Layout;
