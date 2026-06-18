import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import ProtectedRoute from './components/ProtectedRoute';
import Layout from './components/Layout';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import Attendance from './pages/Attendance';
import Leaves from './pages/Leaves';
import Documents from './pages/Documents';
import Profile from './pages/Profile';
import Employees from './pages/Employees';
import DocumentCenter from './pages/DocumentCenter';
import Onboarding from './pages/Onboarding';
import Payroll from './pages/Payroll';
import OrgChartPage from './pages/OrgChartPage';
import LeavePolicies from './pages/LeavePolicies';
import ManagerDashboard from './pages/ManagerDashboard';

export const App: React.FC = () => {
  return (
    <BrowserRouter>
      <AuthProvider>
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
      </AuthProvider>
    </BrowserRouter>
  );
};

export default App;
