import React, { lazy, Suspense } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import ProtectedRoute from './components/ProtectedRoute';
import Layout from './components/Layout';

// Lazy load pages to support bundle chunk-splitting
const Login = lazy(() => import('./pages/Login'));
const Dashboard = lazy(() => import('./pages/Dashboard'));
const Attendance = lazy(() => import('./pages/Attendance'));
const Leaves = lazy(() => import('./pages/Leaves'));
const Documents = lazy(() => import('./pages/Documents'));
const Profile = lazy(() => import('./pages/Profile'));
const Employees = lazy(() => import('./pages/Employees'));
const DocumentCenter = lazy(() => import('./pages/DocumentCenter'));
const Onboarding = lazy(() => import('./pages/Onboarding'));
const Payroll = lazy(() => import('./pages/Payroll'));
const OrgChartPage = lazy(() => import('./pages/OrgChartPage'));
const LeavePolicies = lazy(() => import('./pages/LeavePolicies'));
const ManagerDashboard = lazy(() => import('./pages/ManagerDashboard'));

export const App: React.FC = () => {
  return (
    <BrowserRouter>
      <AuthProvider>
        <Suspense fallback={
          <div className="flex items-center justify-center min-h-screen bg-slate-950 text-slate-400 text-[10px] tracking-widest uppercase">
            <div className="flex flex-col items-center gap-3">
              <div className="w-8 h-8 rounded-full border-2 border-primary-500/20 border-t-primary-500 animate-spin"></div>
              <span className="font-bold">Loading Page...</span>
            </div>
          </div>
        }>
          <Routes>
            {/* Public routes */}
            <Route path="/login" element={<Login />} />

            {/* Protected routes */}
            <Route element={<ProtectedRoute />}>
              <Route element={<Layout />}>
                <Route path="/" element={<Navigate to="/dashboard" replace />} />
                <Route path="/dashboard" element={<Dashboard />} />
                <Route path="/attendance" element={<Attendance />} />
                <Route path="/leaves" element={<Leaves />} />
                <Route path="/documents" element={<Documents />} />
                <Route path="/profile" element={<Profile />} />
                <Route path="/admin/employees" element={<Employees />} />
                <Route path="/admin/documents" element={<DocumentCenter />} />
                <Route path="/admin/onboarding" element={<Onboarding />} />
                <Route path="/admin/payroll" element={<Payroll />} />
                <Route path="/admin/org-chart" element={<OrgChartPage />} />
                <Route path="/admin/leave-policies" element={<LeavePolicies />} />
                <Route path="/manager/dashboard" element={<ManagerDashboard />} />
              </Route>
            </Route>

            {/* Fallback */}
            <Route path="*" element={<Navigate to="/" replace />} />
          </Routes>
        </Suspense>
      </AuthProvider>
    </BrowserRouter>
  );
};

export default App;
