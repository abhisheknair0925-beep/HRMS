/* eslint-disable react-refresh/only-export-components */
import React, { createContext, useContext, useState, useEffect } from 'react';
import api from '../lib/api';

interface User {
  id: string;
  name: string;
  email: string;
  role: 'Employee' | 'Manager' | 'HR' | 'Admin';
  employee?: {
    id: string;
    first_name: string;
    last_name: string;
    employee_id: string;
    joining_date: string;
    department?: { name: string };
    designation?: { title: string };
    manager?: { name: string };
    phone?: string;
    bank_details?: {
      bank_name: string;
      account_number: string;
      ifsc_code: string;
    };
    emergency_contacts?: Array<{
      name?: string;
      relationship?: string;
      phone?: string;
    }>;
  };
}

interface AuthContextType {
  user: User | null;
  token: string | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState<string | null>(localStorage.getItem('auth_token'));
  const [loading, setLoading] = useState(true);

  const logout = () => {
    localStorage.removeItem('auth_token');
    setToken(null);
    setUser(null);
  };

  useEffect(() => {
    const fetchUser = async () => {
      if (token) {
        try {
          const res = await api.get('/profile');
          setUser(res.data.data);
        } catch (err) {
          console.error("Failed to load user profile", err);
          logout();
        }
      }
      setLoading(false);
    };
    fetchUser();
  }, [token]);

  const login = async (email: string, password: string) => {
    const res = await api.post('/login', { 
      email, 
      password, 
      device_name: 'web_app' 
    });
    const { access_token: receivedToken, user: userData } = res.data.data;
    localStorage.setItem('auth_token', receivedToken);
    setToken(receivedToken);
    setUser(userData);
  };

  return (
    <AuthContext.Provider value={{ user, token, loading, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
