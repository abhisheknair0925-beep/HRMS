import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export const Login: React.FC = () => {
  const { login } = useAuth();
  const navigate = useNavigate();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setSubmitting(true);
    try {
      await login(email, password);
      navigate('/dashboard');
    } catch (err: unknown) {
      const error = err as { response?: { data?: { message?: string } } };
      setError(error.response?.data?.message || 'Invalid email credentials or password.');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center overflow-hidden relative select-none antialiased w-full">
      {/* Background blobs */}
      <div className="absolute w-[350px] h-[350px] rounded-full bg-primary-500/20 blur-[100px] left-[15%] top-[10%] animate-pulse"></div>
      <div className="absolute w-[400px] h-[400px] rounded-full bg-secondary-500/10 blur-[100px] right-[15%] bottom-[10%] animate-pulse delay-2000"></div>

      {/* Login box */}
      <div className="glass-panel w-full max-w-[440px] px-8 py-10 shadow-2xl relative z-10 border border-white/10 mx-4">
        <div className="text-center mb-8">
          <h1 className="text-3xl font-black bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent tracking-wide">
            HumaNode
          </h1>
          <p className="text-[10px] font-bold text-slate-400 tracking-widest uppercase mt-2">
            Employee Self Service
          </p>
        </div>

        {error && (
          <div className="mb-6 p-4 rounded-xl bg-danger/20 border border-danger/30 text-danger flex items-start gap-2 text-xs font-semibold shadow-lg backdrop-blur-sm">
            <span>❌</span>
            <span>{error}</span>
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="space-y-1">
            <label className="text-[10px] font-bold text-slate-400 uppercase">Work Email</label>
            <input type="email" 
                   value={email}
                   onChange={(e) => setEmail(e.target.value)}
                   className="glass-input text-xs" 
                   placeholder="you@company.com" 
                   required 
                   autoFocus />
          </div>

          <div className="space-y-1">
            <label className="text-[10px] font-bold text-slate-400 uppercase">Password</label>
            <input type="password" 
                   value={password}
                   onChange={(e) => setPassword(e.target.value)}
                   className="glass-input text-xs" 
                   placeholder="••••••••" 
                   required />
          </div>

          <button type="submit" 
                  disabled={submitting}
                  className="w-full py-3 bg-primary-500 hover:bg-primary-400 text-slate-950 font-bold rounded-xl text-xs uppercase tracking-wider transition-all duration-300 btn-glow cursor-pointer disabled:opacity-50">
            {submitting ? 'Signing In...' : 'Sign In'}
          </button>
        </form>

        <div className="mt-6 text-center">
          <button className="text-xs text-primary-500 hover:text-primary-600 hover:underline font-semibold bg-transparent border-none cursor-pointer">
            Forgot your password?
          </button>
        </div>
      </div>
    </div>
  );
};
export default Login;
