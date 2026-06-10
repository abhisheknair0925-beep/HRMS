# Web App Development Guide (React + Axios + Laravel)

This guide provides the design architecture, visual aesthetic standards, component structures, and API integration guidelines for the **HumaNode HRMS Web App** communicating with the **Laravel API Backend**.

---

## 🎨 Visual Design System (Modern Glassmorphism)

To create a premium look, the Web App follows a sleek **dark/light adaptive design** featuring harmonious, non-generic HSL color palettes, subtle gradients, and glassmorphic panels.

### Tailwind Config & Color Palette

Add this configuration to your CSS variables in `index.css`:

```css
@layer base {
  :root {
    --background: 224 71% 4%;
    --foreground: 210 40% 98%;

    --card: 224 71% 45%;
    --card-foreground: 210 40% 98%;

    /* Neon Cyan & Indigo Accents */
    --primary: 188 100% 45%;
    --primary-foreground: 224 71% 4%;
    --secondary: 263 70% 50%;
    
    --border: 217.2 32.6% 17.5%;
    --radius: 0.75rem;
  }
}

.glass-card {
  background: rgba(15, 23, 42, 0.45);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: var(--radius);
}
```

*Ensure all buttons and micro-animations utilize smooth transitions:*
`transition-all duration-300 ease-in-out hover:scale-[1.01] active:scale-[0.99]`

---

## 📂 Web App Folder Structure

Use this clean module-based structure:

```text
web_app/
├── public/
├── src/
│   ├── components/
│   │   ├── ui/                    # Base elements (button, input, modal, dialog)
│   │   ├── OrgChart.tsx           # Interactive CSS D3/HTML tree chart
│   │   └── Sidebar.tsx            # Left pane navigation with responsive collapse
│   ├── features/
│   │   ├── auth/                  # Login, password reset
│   │   ├── onboarding/            # Digital joining checklist & forms
│   │   ├── employee-master/       # Profile management and documents
│   │   ├── attendance/            # Shift planners, timesheet approval logs
│   │   ├── payroll/               # Salary structure and F&F Settlements
│   │   └── performance/           # KPI tracker and review creation
│   ├── hooks/                     # Custom React Hooks (useAuth, useFetch)
│   ├── lib/                       # API configurations (api.ts - Axios setup)
│   ├── App.tsx                    # Routes switcher and layouts
│   ├── index.css
│   └── main.tsx
└── package.json
```

---

## 🔌 API Integration (Axios client setup)

To communicate with the Laravel backend API, create an Axios client with interceptors to automatically append the authentication bearer token.

Create the file `src/lib/api.ts`:

```typescript
import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request Interceptor: Attach Sanctum Bearer token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token && config.headers) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
}, (error) => {
  return Promise.reject(error);
});

// Response Interceptor: Handle unauthenticated errors (expired session)
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

---

## 🛠️ Step-by-Step Setup Instructions

Run these commands to initialize the React web app project:

```bash
# Initialize Vite React + TS App
npx -y create-vite@latest web_app --template react-ts

# Install Primary Dependencies
cd web_app
npm install axios react-router-dom lucide-react recharts canvas-confetti @react-pdf/renderer
npm install -D tailwindcss postcss autoprefixer @types/canvas-confetti
npx tailwindcss init -p
```

---

## 📑 Core Screen API Calls

Map your page elements to these Laravel backend REST API endpoints:

### 1. Login Page (`/login`)
*   **Action:** Submit credentials.
*   **Endpoint:** `POST /api/login` (Body: `{ email, password }`).
*   **Result:** Saves response token to `localStorage.setItem('auth_token', token)` and profile details to global state.

### 2. Dashboard (`/dashboard`)
*   **Action:** Fetch metrics, live logs, and birthdays.
*   **Endpoint:** `GET /api/dashboard/stats`
*   **Response payload:**
    ```json
    {
      "total_employees": 128,
      "presence_today": 94.2,
      "on_leave": 4,
      "pending_checklists": 3,
      "recent_announcements": [...],
      "birthdays_today": [...]
    }
    ```

### 3. Onboarding Suite (`/onboarding`)
*   **Action:** Create a new profile and assign initial assets.
*   **Endpoints:**
    *   `POST /api/employees/onboard` (Saves Bio, Details, and generates ID).
    *   `POST /api/employees/{id}/assets` (Allocates hardware list).
    *   `PATCH /api/onboarding/{id}/step` (Updates onboarding step progress).

### 4. Employee Master (`/employees`)
*   **Action:** View/filter records and open profile files.
*   **Endpoints:**
    *   `GET /api/employees` (Supports query params `?search=xx&department_id=yy`).
    *   `GET /api/employees/{id}` (Returns full profile details + history + org-tree node).

### 5. Document Center (`/documents`)
*   **Action:** Compile and view PDFs.
*   **Endpoints:**
    *   `POST /api/documents/generate` (Params: `employee_id`, `type`, `vars`). Invokes Laravel DOMPDF engine, registers file to storage, returns PDF binary preview.
    *   `GET /api/documents/{id}/download` (Downloads signed document PDF).
