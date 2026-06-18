import React, { useState, useEffect } from 'react';
import api from '../lib/api';
import { FolderOpen, Download, DollarSign, Printer } from 'lucide-react';

interface EmployeeDocument {
  id: string;
  document_name: string;
  file_path: string;
  created_at: string;
}

interface Payslip {
  id: string;
  month_name: string;
  net_pay: number;
}

export const Documents: React.FC = () => {
  const [documents, setDocuments] = useState<EmployeeDocument[]>([]);
  const [payslips, setPayslips] = useState<Payslip[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchDocs = async () => {
      try {
        const res = await api.get('/documents');
        setDocuments(res.data.data.documents || []);
        setPayslips(res.data.data.payslips || []);
      } catch {
        // Mock data
        setDocuments([
          { id: '1', document_name: 'Offer Letter.pdf', file_path: '/mock/offer.pdf', created_at: '2026-06-01T09:00:00Z' },
          { id: '2', document_name: 'Appointment Letter.pdf', file_path: '/mock/appointment.pdf', created_at: '2026-06-02T09:00:00Z' }
        ]);
        setPayslips([
          { id: '1', month_name: 'May 2026', net_pay: 4500.00 },
          { id: '2', month_name: 'April 2026', net_pay: 4500.00 }
        ]);
      } finally {
        setLoading(false);
      }
    };
    fetchDocs();
  }, []);

  const handlePrint = (payslipId: string) => {
    // Open printable Blade page served by backend inside new window
    window.open(`http://localhost:8000/ess/payslips/${payslipId}/download`, '_blank');
  };

  if (loading) {
    return <div className="text-center py-12 text-slate-400 text-sm">Loading document registries...</div>;
  }

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">Documents & Payslips</h1>
        <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
          Access your digital onboarding documents, official letters, and monthly salary slips.
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        {/* Left Card: Official Documents */}
        <div className="glass-panel p-6">
          <h3 className="text-base font-bold text-slate-900 dark:text-slate-100 mb-6 border-b border-slate-200 dark:border-slate-800/80 pb-4 flex items-center gap-2">
            <FolderOpen size={18} /> Official Letters & Documents
          </h3>

          {documents.length === 0 ? (
            <div className="text-center py-12 text-slate-400">
              <span className="text-4xl block mb-2">📄</span>
              <p className="text-sm">No official letters or documents uploaded yet.</p>
            </div>
          ) : (
            <div className="space-y-3">
              {documents.map((doc) => (
                <div key={doc.id} className="flex items-center justify-between p-4 bg-slate-100/50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800/80 rounded-2xl hover:border-primary-500/30 transition-all duration-300">
                  <div className="flex items-center gap-3">
                    <span className="text-2xl">📄</span>
                    <div className="min-w-0">
                      <p className="text-xs font-bold text-slate-800 dark:text-slate-200 truncate max-w-[180px]" title={doc.document_name}>
                        {doc.document_name}
                      </p>
                      <p className="text-[9px] text-slate-400 mt-0.5">Uploaded {new Date(doc.created_at).toLocaleDateString()}</p>
                    </div>
                  </div>
                  <a href={`http://localhost:8000/storage/${doc.file_path}`} 
                     target="_blank" 
                     rel="noreferrer"
                     download
                     className="px-3 py-1.5 border border-slate-200 dark:border-slate-800 hover:border-primary-500 text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-slate-950 hover:bg-primary-500 font-bold rounded-lg text-[10px] flex items-center gap-1 transition-all duration-300 cursor-pointer">
                    <Download size={12} /> Download
                  </a>
                </div>
              ))}
            </div>
          )}
        </div>

        {/* Right Card: Payslips */}
        <div className="glass-panel p-6">
          <h3 className="text-base font-bold text-slate-900 dark:text-slate-100 mb-6 border-b border-slate-200 dark:border-slate-800/80 pb-4 flex items-center gap-2">
            <DollarSign size={18} /> Monthly Payslips
          </h3>

          {payslips.length === 0 ? (
            <div className="text-center py-12 text-slate-400">
              <span className="text-4xl block mb-2">💳</span>
              <p className="text-sm">No salary slips generated yet.</p>
            </div>
          ) : (
            <div className="space-y-3">
              {payslips.map((slip) => (
                <div key={slip.id} className="flex items-center justify-between p-4 bg-slate-100/50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800/80 rounded-2xl hover:border-primary-500/30 transition-all duration-300">
                  <div className="flex items-center gap-3">
                    <span className="text-2xl">💵</span>
                    <div className="min-w-0">
                      <p className="text-xs font-bold text-slate-800 dark:text-slate-200">{slip.month_name}</p>
                      <p className="text-[9px] text-slate-400 mt-0.5">Net Pay: ${slip.net_pay.toLocaleString()}</p>
                    </div>
                  </div>
                  <button onClick={() => handlePrint(slip.id)}
                          className="px-3 py-1.5 border border-slate-200 dark:border-slate-800 hover:border-primary-500 text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-slate-950 hover:bg-primary-500 font-bold rounded-lg text-[10px] flex items-center gap-1 transition-all duration-300 cursor-pointer">
                    <Printer size={12} /> Print Slip
                  </button>
                </div>
              ))}
            </div>
          )}
        </div>

      </div>
    </div>
  );
};
export default Documents;
