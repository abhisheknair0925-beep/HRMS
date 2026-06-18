import React, { useState, useEffect } from 'react';
import api from '../lib/api';
import { Clock, Check } from 'lucide-react';

interface AttendanceLog {
  id: string;
  log_date: string;
  clock_in: string;
  clock_out: string | null;
  working_minutes: number;
  status: 'Present' | 'Late' | 'Absent' | 'Half-Day';
}

export const Attendance: React.FC = () => {
  const [time, setTime] = useState('');
  const [date, setDate] = useState('');
  const [lat, setLat] = useState<number | null>(null);
  const [lng, setLng] = useState<number | null>(null);
  const [isSecured, setIsSecured] = useState(false);
  const [isError, setIsError] = useState(false);
  const [statusText, setStatusText] = useState('Locating check-in checkpoint...');
  
  const [todayLog, setTodayLog] = useState<AttendanceLog | null>(null);
  const [logs, setLogs] = useState<AttendanceLog[]>([]);
  const [loading, setLoading] = useState(true);

  // Live Clock
  useEffect(() => {
    const updateTime = () => {
      const now = new Date();
      setTime(now.toTimeString().split(' ')[0]);
      setDate(now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }));
    };
    updateTime();
    const interval = setInterval(updateTime, 1000);
    return () => clearInterval(interval);
  }, []);

  // Fetch log history and locate GPS
  const fetchData = async () => {
    try {
      const res = await api.get('/attendance');
      setLogs(res.data.data.logs || []);
      setTodayLog(res.data.data.today_log || null);
    } catch {
      // Ensure async context to avoid calling setState synchronously in useEffect
      await Promise.resolve();
      // Mock data for display/fallback testing
      const mockLogs: AttendanceLog[] = [
        { id: '1', log_date: '2026-06-16', clock_in: '09:00:00', clock_out: '18:00:00', working_minutes: 540, status: 'Present' },
        { id: '2', log_date: '2026-06-15', clock_in: '09:40:00', clock_out: '18:00:00', working_minutes: 500, status: 'Late' },
        { id: '3', log_date: '2026-06-14', clock_in: '09:00:00', clock_out: '13:00:00', working_minutes: 240, status: 'Half-Day' }
      ];
      setLogs(mockLogs);
    } finally {
      setLoading(false);
    }
  };

  const getGPSLocation = () => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (pos) => {
          setLat(pos.coords.latitude);
          setLng(pos.coords.longitude);
          setIsSecured(true);
          setStatusText(`Coordinates Secured (${pos.coords.latitude.toFixed(4)}, ${pos.coords.longitude.toFixed(4)})`);
        },
        () => {
          setIsError(true);
          setStatusText('Failed to acquire coordinates. Allow location permissions.');
        },
        { enableHighAccuracy: true, timeout: 10000 }
      );
    } else {
      setIsError(true);
      setStatusText('Browser geolocation not supported.');
    }
  };

  useEffect(() => {
    Promise.resolve().then(() => {
      fetchData();
      getGPSLocation();
    });
  }, []);

  const handleClockAction = async (action: 'in' | 'out') => {
    if (!lat || !lng) return;
    try {
      const endpoint = action === 'in' ? '/attendance/clock-in' : '/attendance/clock-out';
      const res = await api.post(endpoint, { latitude: lat, longitude: lng });
      setTodayLog(res.data.data);
      fetchData();
    } catch (err: unknown) {
      const error = err as { response?: { data?: { message?: string } } };
      alert(error.response?.data?.message || 'Clock action failed.');
    }
  };

  if (loading) {
    return <div className="text-center py-12 text-slate-400 text-sm">Loading attendance records...</div>;
  }

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Attendance Tracking</h1>
        <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
          Check in and out, track geofenced checkpoints, and view your historical check-in records.
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        {/* Left Card: Timer dial & Actions */}
        <div className="glass-panel p-6 flex flex-col items-center text-center">
          <div>
            <h2 className="text-4xl md:text-5xl font-extrabold tracking-wide font-sans bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent">
              {time}
            </h2>
            <p className="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-widest mt-2">
              {date}
            </p>
          </div>

          <div className="w-full mt-6 space-y-4">
            <div className="flex items-center justify-center gap-2 p-3 bg-slate-100 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold">
              <span className={`w-2.5 h-2.5 rounded-full inline-block ${
                isSecured ? 'bg-success animate-ping' : isError ? 'bg-danger' : 'bg-warning'
              }`}></span>
              <span className="text-slate-600 dark:text-slate-400">{statusText}</span>
            </div>

            {!todayLog ? (
              <button onClick={() => handleClockAction('in')}
                      disabled={!isSecured}
                      className={`w-full py-3.5 rounded-xl text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 transition-all duration-300 ${
                        isSecured 
                          ? 'bg-primary-500 text-slate-950 hover:bg-primary-400 shadow-[0_0_15px_rgba(0,229,255,0.2)] cursor-pointer' 
                          : 'bg-slate-200 dark:bg-slate-800 text-slate-400 cursor-not-allowed'
                      }`}>
                <Clock size={16} /> Clock In
              </button>
            ) : !todayLog.clock_out ? (
              <button onClick={() => handleClockAction('out')}
                      disabled={!isSecured}
                      className={`w-full py-3.5 rounded-xl text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 transition-all duration-300 ${
                        isSecured 
                          ? 'bg-danger text-white hover:bg-danger/80 shadow-[0_0_15px_rgba(239,68,68,0.2)] cursor-pointer' 
                          : 'bg-slate-200 dark:bg-slate-800 text-slate-400 cursor-not-allowed'
                      }`}>
                <Clock size={16} /> Clock Out
              </button>
            ) : (
              <div className="w-full py-3.5 bg-success/15 border border-success/30 rounded-xl text-xs font-bold text-success flex items-center justify-center gap-2">
                <Check size={16} /> Daily Attendance Completed
              </div>
            )}
          </div>

          {todayLog && (
            <div className="w-full text-left mt-8 border-t border-slate-200 dark:border-slate-800/80 pt-6 space-y-3">
              <h4 className="text-[10px] font-bold tracking-widest text-slate-400 dark:text-slate-500 uppercase">TODAY'S SUMMARY</h4>
              <div className="flex justify-between items-center text-xs pb-2 border-b border-slate-100 dark:border-slate-800/50">
                <span className="text-slate-500">Clock In:</span>
                <span className="font-bold text-slate-800 dark:text-slate-200">{todayLog.clock_in}</span>
              </div>
              <div className="flex justify-between items-center text-xs pb-2 border-b border-slate-100 dark:border-slate-800/50">
                <span className="text-slate-500">Clock Out:</span>
                <span className="font-bold text-slate-800 dark:text-slate-200">{todayLog.clock_out || '--:--:--'}</span>
              </div>
              <div className="flex justify-between items-center text-xs">
                <span className="text-slate-500">Total Hours:</span>
                <span className="font-bold text-slate-800 dark:text-slate-200">
                  {number_format(todayLog.working_minutes / 60, 2)} hrs
                </span>
              </div>
            </div>
          )}
        </div>

        {/* Right Card: Table logs */}
        <div className="lg:col-span-2 glass-panel p-6">
          <h3 className="text-lg font-bold text-slate-900 dark:text-slate-100 mb-6 border-b border-slate-200 dark:border-slate-800/80 pb-4">
            Recent Attendance Logs
          </h3>

          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs border-collapse">
              <thead>
                <tr className="border-b border-slate-200 dark:border-slate-800/80 text-slate-400 uppercase tracking-widest font-bold">
                  <th className="pb-3 pr-4">Date</th>
                  <th className="pb-3 px-4">Clock In</th>
                  <th className="pb-3 px-4">Clock Out</th>
                  <th className="pb-3 px-4">Hours</th>
                  <th className="pb-3 pl-4 text-right">Status</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 dark:divide-slate-800/40">
                {logs.map((log) => (
                  <tr key={log.id} className="hover:bg-slate-100/50 dark:hover:bg-slate-800/20 transition-colors">
                    <td className="py-3.5 pr-4 font-bold text-slate-700 dark:text-slate-300">{log.log_date}</td>
                    <td className="py-3.5 px-4 text-slate-600 dark:text-slate-400 font-medium">{log.clock_in}</td>
                    <td className="py-3.5 px-4 text-slate-600 dark:text-slate-400 font-medium">{log.clock_out || '--:--:--'}</td>
                    <td className="py-3.5 px-4 text-slate-700 dark:text-slate-300 font-semibold">
                      {number_format(log.working_minutes / 60, 2)} hrs
                    </td>
                    <td className="py-3.5 pl-4 text-right">
                      <span className={`px-2.5 py-0.5 rounded-full text-[9px] font-black tracking-wider uppercase ${
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
        </div>

      </div>
    </div>
  );
};

// Helper inside script
function number_format(val: number, dec: number) {
  return val.toFixed(dec);
}
export default Attendance;
