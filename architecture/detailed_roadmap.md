# 19-Phase SaaS HRMS Detailed Roadmap

This document serves as your master execution checklist. It breaks down the development of the SaaS HRMS (Multi-Tenant, Billing, ATS, and AI-enabled) into 19 manageable, testable phases. Use this guide to vibe-code the project step-by-step.

---

## 🗺️ Roadmap Overview

```mermaid
gantt
    title SaaS HRMS Phase Dependencies
    dateFormat  YYYY-MM-DD
    section Core Base
    Phase 0: Foundation            :active, p0, 2026-06-10, 5d
    Phase 1: Auth & RBAC           :p1, after p0, 5d
    Phase 2: Multi-Tenancy         :p2, after p1, 7d
    section Basic HR
    Phase 3: Employee Master       :p3, after p2, 6d
    Phase 4: Org Structure         :p4, after p3, 4d
    section Operations
    Phase 5: Attendance            :p5, after p4, 8d
    Phase 6: Leaves                :p6, after p5, 6d
    Phase 7: ESS Portal            :p7, after p6, 5d
    Phase 8: MSS Portal            :p8, after p7, 5d
    section Finance & Admin
    Phase 9: Payroll               :p9, after p8, 8d
    Phase 10: Document Center      :p10, after p9, 6d
    Phase 11: Recruitment & ATS    :p11, after p10, 10d
    Phase 12: Asset Mgt            :p12, after p11, 5d
    Phase 13: Performance          :p13, after p12, 7d
    section Release
    Phase 14: Reports & Analytics  :p14, after p13, 6d
    Phase 15: Mobile App           :p15, after p14, 12d
    Phase 16: SaaS Billing (Stripe):p16, after p15, 8d
    Phase 17: AI Features          :p17, after p16, 7d
    Phase 18: Deployment           :p18, after p17, 5d
```

---

## Phase 0: Project Foundation
*Objective: Create the baseline structure for the monorepo, set up development servers, and verify baseline hello-world apps.*

*   **[ ] Backend (Laravel):**
    *   Initialize Laravel 11 in `/backend`.
    *   Set up `.env` with local PostgreSQL/SQLite configuration.
    *   Verify API route responses on `/api/health`.
*   **[ ] Web App (React):**
    *   Initialize Vite React + TS + Tailwind in `/web_app`.
    *   Configure basic Axios api client.
*   **[ ] Verification:**
    *   Verify Laravel server returns `{ "status": "ok" }`.
    *   Verify React prints "HumaNode Web Client" on `npm run dev`.
*   **🤖 Vibe Coder Prompt:**
    > "Scaffold a new monorepo containing a Laravel 11 backend API in `/backend` and a React + Vite + TS + Tailwind app in `/web_app`. Verify they can start independently and configure a hello-world API ping test."

---

## Phase 1: Authentication & RBAC
*Objective: Introduce user signup/login with roles (Employee, Manager, HR, Admin) using Laravel Sanctum.*

*   **[ ] Backend (Laravel):**
    *   Configure Laravel Sanctum token auth.
    *   Implement `AuthController` (login, logout, register).
    *   Define User Roles enum.
*   **[ ] Web App (React):**
    *   Create a modern glassmorphic Login Page.
    *   Add Axios middleware interceptors to store JWT in LocalStorage and attach to headers.
*   **[ ] Verification:**
    *   Test user registration, logging in, receiving token, and accessing protected `/api/user` profile route.
*   **🤖 Vibe Coder Prompt:**
    > "Configure Laravel Sanctum token auth. Define roles: Admin, HR, Manager, Employee. Build a glassmorphic React login page that queries `/api/login` and saves the Bearer token in LocalStorage."

---

## Phase 2: Company Setup & Multi-Tenant Architecture
*Objective: Implement SaaS logical isolation where each company (tenant) owns its users, departments, and logs.*

*   **[ ] Backend (Laravel):**
    *   Create `companies` (tenants) table.
    *   Add `company_id` columns to all master and child tables (users, employees, etc.).
    *   Implement global Eloquent tenant scopes (`TenantScope`) to filter every database query by user's `company_id`.
*   **[ ] Web App (React):**
    *   Create Tenant setup page (Company Name, Domain, Admin Email) for new SaaS clients.
*   **[ ] Verification:**
    *   Insert test records for Company A and Company B. Verify Company A users cannot query Company B records.
*   **🤖 Vibe Coder Prompt:**
    > "Implement single-database multi-tenancy. Create a `companies` table. Add a `company_id` column to relevant tables and write a Laravel global scope `TenantScope` to isolate queries automatically."

---

## Phase 3: Employee Master Management
*Objective: Build the comprehensive employee registry.*

*   **[ ] Backend (Laravel):**
    *   Create `employees` table (relates to users, stores emergency contacts, history, bio details).
    *   Create `OnboardingChecklist` model and table.
*   **[ ] Web App (React):**
    *   Create "Employee Directory" grid view with filters (Status, Department, Role).
    *   Create Employee profile creation form (Multi-step wizard).
*   **[ ] Verification:**
    *   Create a profile, verify checklist defaults to `Pending`, verify JSON payload updates.
*   **🤖 Vibe Coder Prompt:**
    > "Build the Employee Registry. Create the `employees` and `onboarding_checklists` tables and models. Connect them to a React Employee Master page containing filters, searching, and profile creation wizard."

---

## Phase 4: Organization Structure
*Objective: Dynamic departments, designations, and tree mapping.*

*   **[ ] Backend (Laravel):**
    *   Create `departments` and `designations` migrations.
    *   Define self-referencing manager hierarchies in `employees` (`manager_id` -> users/employees).
*   **[ ] Web App (React):**
    *   Build visual interactive Organization Chart using D3 or simple HTML tree structures.
*   **[ ] Verification:**
    *   Verify updating an employee's department updates their org node position.
*   **🤖 Vibe Coder Prompt:**
    > "Create the hierarchy tables. Write Eloquent models for `Department` and `Designation` and set up self-referencing relationship mapping on the `Employee` model. Render an Org Chart tree in React."

---

## Phase 5: Attendance Management
*Objective: Geofenced daily clock-in/out and shift scheduling.*

*   **[ ] Backend (Laravel):**
    *   Create `shifts` and `attendance_logs` tables.
    *   Create `/api/attendance/clock-in` endpoint. Verify GPS latitude and longitude within geofenced range.
*   **[ ] Web App (React):**
    *   Build Admin Shift Planner calendar and daily team check-in list tracker.
*   **[ ] Verification:**
    *   Submit a check-in request. Verify status marks as `Late` if after grace period.
*   **🤖 Vibe Coder Prompt:**
    > "Build attendance tracking. Write migration for `attendance_logs` containing lat/lng fields. Create a Laravel controller to compute if clock-in is within a 100m geofence coordinate range."

---

## Phase 6: Leave Management
*Objective: Core leave allocation and multi-level approval workflows.*

*   **[ ] Backend (Laravel):**
    *   Create `leave_policies`, `leave_balances`, and `leave_requests` tables.
    *   Write a balance check algorithm to reject requests exceeding available allocations.
*   **[ ] Web App (React):**
    *   Build leave request panel with custom calendar selector.
*   **[ ] Verification:**
    *   Request a 3-day leave, check if balance decreases upon manager approval.
*   **🤖 Vibe Coder Prompt:**
    > "Create Leave management system. Write database migrations for `leave_policies`, `leave_balances`, and `leave_requests`. Implement Laravel controller logic to check balances before saving requests."

---

## Phase 7: Employee Self Service (ESS)
*Objective: Employee-facing portal dashboards.*

*   **[ ] Backend (Laravel):**
    *   Optimize profile updates API and announcements listing.
*   **[ ] Web App (React):**
    *   Create employee dashboard view: Apply leaves, check attendance logs, view payslips, read announcements.
*   **[ ] Verification:**
    *   Ensure an Employee role account has zero admin panels visible and only loads ESS data.
*   **🤖 Vibe Coder Prompt:**
    > "Design employee-specific screens. Build a dashboard containing active announcement feeds, quick leave calculators, and profile settings using React."

---

## Phase 8: Manager Self Service (MSS)
*Objective: Delegation portals for line managers.*

*   **[ ] Backend (Laravel):**
    *   Create `/api/manager/team-status` and `/api/manager/leaves/approve` routes.
*   **[ ] Web App (React):**
    *   Create Manager View: Team presence stats, pending approvals queue, team leave schedules.
*   **[ ] Verification:**
    *   Login as Manager, approve Employee's leave request, verify status transitions instantly.
*   **🤖 Vibe Coder Prompt:**
    > "Build Manager Self Service panel. Build a React interface for managers to review pending team leave requests and check team attendance today, updating status via PATCH requests."

---

## Phase 9: Payroll Management
*Objective: Monthly salary structures and Full & Final calculations.*

*   **[ ] Backend (Laravel):**
    *   Create `salary_structures` and `final_settlements` tables.
    *   Write payroll calculation triggers (compute net pay after PF, Tax, and unpaid leave deductions).
*   **[ ] Web App (React):**
    *   Create Payroll Configuration Dashboard and F&F Settlement Wizard.
*   **[ ] Verification:**
    *   Calculate monthly pay structure, deduct 2 days unpaid leave, verify net pay math matches.
*   **🤖 Vibe Coder Prompt:**
    > "Create Payroll modules. Generate migrations for `salary_structures` and `final_settlements`. Write Laravel calculation helpers to deduct unpaid leaves and compute net pay."

---

## Phase 10: Document Center
*Objective: Template-based official letters engine (Offer, Warnings, Relieving).*

*   **[ ] Backend (Laravel):**
    *   Integrate PDF generator (e.g. `barryvdh/laravel-dompdf`).
    *   Create `documents` record tracker table.
*   **[ ] Web App (React):**
    *   Create dynamic forms template center. Show side-by-side edit/preview interface.
*   **[ ] Verification:**
    *   Compile an Offer Letter, view PDF download, verify record saved to company's S3/local storage.
*   **🤖 Vibe Coder Prompt:**
    > "Implement document engine in Laravel using `barryvdh/laravel-dompdf`. Create routes to generate PDFs dynamically using blade views, store them in Laravel Storage, and save database metadata logs."

---

## Phase 11: Recruitment & ATS
*Objective: Applicant Tracking System (Job postings, applicant pipelines).*

*   **[ ] Backend (Laravel):**
    *   Create `job_postings` (title, description, status) and `job_applications` (resume_url, status [Applied, Interviewing, Offered, Rejected]) tables.
*   **[ ] Web App (React):**
    *   Build Job Postings Creator and Kanban Pipeline board (drag-and-drop candidates across stages).
*   **[ ] Verification:**
    *   Create job, drag applicant from 'Applied' to 'Interviewing', check history log.
*   **🤖 Vibe Coder Prompt:**
    > "Create ATS module. Build migrations for `job_postings` and `job_applications`. In React, build a Kanban drag-and-drop pipeline to track candidates through interview stages."

---

## Phase 12: Asset Management
*Objective: Corporate inventory assignment tracker.*

*   **[ ] Backend (Laravel):**
    *   Create `assets` table (name, serial, type, value, status [Available, Assigned, Under-Maintenance]).
    *   Create `asset_allocations` history tracking.
*   **[ ] Web App (React):**
    *   Build Inventory Dashboard with quick-assign options.
*   **[ ] Verification:**
    *   Assign a laptop to employee, verify asset status switches to `Assigned` and logs in employee profile.
*   **🤖 Vibe Coder Prompt:**
    > "Build Asset Inventory. Migrate `assets` and `asset_allocations` tables. Build a React interface to register hardware items, track inventory, and allocate them to onboarding checklists."

---

## Phase 13: Performance Management
*Objective: Periodic KPI evaluation modules.*

*   **[ ] Backend (Laravel):**
    *   Create `performance_kpis` and `performance_reviews` tables.
*   **[ ] Web App (React):**
    *   Build self-appraisal form and manager rating matrix review board.
*   **[ ] Verification:**
    *   Complete self-review, check status shifts to manager-review. Compute final weighted average score.
*   **🤖 Vibe Coder Prompt:**
    > "Build Performance Review structures. Create migrations for `performance_kpis` and `performance_reviews`. Design a React review form with self/manager rating sections."

---

## Phase 14: Reports & Analytics
*Objective: Exportable data metrics dashboards.*

*   **[ ] Backend (Laravel):**
    *   Build export tools (Excel/CSV/PDF summaries for attendance, leave logs, and payroll).
*   **[ ] Web App (React):**
    *   Build dedicated reports dashboard featuring filters and chart configurations.
*   **[ ] Verification:**
    *   Filter attendance history by month, click export, download valid formatted CSV sheet.
*   **🤖 Vibe Coder Prompt:**
    > "Create analytics reports in Laravel. Build endpoints returning filtered metrics for attendance and leaves. Implement standard CSV/Excel download handlers for HR logs."

---

## Phase 15: Mobile App
*Objective: Employee self-service Flutter mobile application.*

*   **[ ] Mobile App (Flutter):**
    *   Build splash authentication screen with biometric FaceID integration.
    *   Build geofenced clock-in interface.
    *   Build leave balance indicators and request screens.
*   **[ ] Verification:**
    *   Authenticate via FaceID, select dashboard, submit leave request, watch it load in Laravel API database.
*   **🤖 Vibe Coder Prompt:**
    > "Build the mobile client. Initialize a Flutter app in `/mobile_app` with Dio and Riverpod. Write mobile screens for biometric login, leave management, and geofenced attendance logs."

---

## Phase 16: SaaS Billing & Subscription
*Objective: Stripe payment gates and multi-tier package plans.*

*   **[ ] Backend (Laravel):**
    *   Integrate **Laravel Cashier (Stripe)**.
    *   Set up webhook endpoints to handle Stripe subscription events.
*   **[ ] Web App (React):**
    *   Build pricing tier matrix checkout cards and Stripe Portal integration.
*   **[ ] Verification:**
    *   Check if choosing Pro plan directs to Stripe Checkout. Verify company profile unlocks features after checkout webhook fires.
*   **🤖 Vibe Coder Prompt:**
    > "Integrate Stripe subscription engine using Laravel Cashier. Create a plans controller, handle Stripe webhook events for successful payments, and update company subscription status."

---

## Phase 17: AI Features
*Objective: Gemini API integrations (CV parser, assistant chatbot).*

*   **[ ] Backend (Laravel):**
    *   Create AI integrations controller.
    *   *Feature 1:* Parse resumes (PDF files) from ATS and return structured JSON (skills, experience).
    *   *Feature 2:* Chatbot agent answering HR policy queries (fed with holiday calendar/company documents context).
*   **[ ] Web App (React):**
    *   Add chat drawer inside dashboard and "AI auto-parse" button to ATS file upload.
*   **[ ] Verification:**
    *   Upload a dummy PDF resume, check if fields (First Name, Skills) fill in automatically.
*   **🤖 Vibe Coder Prompt:**
    > "Integrate AI services. Write a Laravel integration class to send uploaded PDF resume binaries to the Gemini API, prompt it to parse contents into structured JSON, and return it to React."

---

## Phase 18: Production Deployment
*Objective: Deploying the application to staging/production.*

*   **[ ] Server setup:**
    *   Deploy Laravel API to platform (e.g. AWS, DigitalOcean, Heroku) connected to Managed RDS.
    *   Deploy React build assets to host (Vercel, Netlify, Cloudflare Pages).
    *   Configure SSL certificates, env vars, CORS rules, and queue workers (`php artisan queue:work`).
*   **[ ] Verification:**
    *   Execute production smoke test. Verify entire cycle (Registration -> Auth -> Attendance -> Leaves -> Stripe Billing).
*   **🤖 Vibe Coder Prompt:**
    > "Assist in production configurations. Generate a Dockerfile for the Laravel API backend, configure supervisor configs for queue workers, and establish CORS configurations for hosting React."
