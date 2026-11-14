# Hospital Management System (HMS) - Apex Memorial Hospital

A comprehensive, enterprise-ready **Hospital Management System (HMS)** engineered according to the complete Software Specification Document (SSD / SRS). The system centralizes patient records, doctor schedules, outpatient & inpatient admissions, electronic medical records (EMR), laboratory diagnostics, pharmacy inventory, itemized billing, staff attendance, security audit logs, and automated disaster recovery.

---

## 🏥 System Scope & Feature Matrix

The system implements 100% of the functional and non-functional requirements defined in the specification document:

| SRS Module | Description | Core Capabilities |
| :--- | :--- | :--- |
| **1. User & Access Control** | Section 3.1 & Section 4 | Secure login, password encryption (`bcrypt`), 7 discrete roles, RBAC permissions, automatic session timeout (30 min inactivity), and real-time role switching. |
| **2. Patient Management** | Section 3.2 | Unique patient registration codes (`PAT-YYYY-XXX`), OPD & IPD tracking, full search (code, name, phone), comprehensive medical history timeline, and document uploads. |
| **3. Doctor Management** | Section 3.3 | Department assignments, specialization, qualifications, consulting fees, room allocation, and weekly roster scheduling. |
| **4. Appointment Management** | Section 3.4 | Book appointment, doctor selection, time slot allocation, rescheduling, cancellations, and status tracking (`Scheduled`, `Confirmed`, `Completed`, `Cancelled`). |
| **5. Electronic Medical Records (EMR)** | Section 3.5 | Chief complaints, clinical diagnosis (ICD-10), vital signs (BP, Pulse, Temp, SpO2, Weight), treatment plans, multi-row e-prescriptions, and printable doctor prescriptions. |
| **6. Laboratory Management** | Section 3.6 | Diagnostic test requests (`LAB-XXX`), specimen collection workflow (`Pending` &rarr; `Sample Collected` &rarr; `In Analysis` &rarr; `Completed`), result entry, abnormal/critical flags, and printable lab test reports. |
| **7. Pharmacy Management** | Section 3.7 | Medicine catalog (`MED-XXX`), batch & manufacturer tracking, stock adjustments, low stock reorder alerts, 30-day expiry monitoring, and doctor prescription dispensing. |
| **8. Inpatient & Bed Management (IPD)** | Section 1.2 & DB Module 6 | Ward & bed allocations (General, CCU, ICU, Pediatric, VIP), daily room rates, active admission tracking, and discharge summary procedures. |
| **9. Billing & Invoicing** | Section 3.8 | Itemized charge aggregation (consultations, lab tests, pharmacy, bed days), tax & discounts, payment recording (Cash, Credit/Debit Card, UPI, Insurance), and printable official tax receipts. |
| **10. Staff & Workforce Management** | Section 3.9 | Employee registry (`EMP-XXX`), departmental allocation, daily attendance clock-in/clock-out tracking, and leave management with approval workflows. |
| **11. Reports & Analytics** | Section 3.10 | Patient demographics, appointment distribution, revenue performance, pharmacy valuation, lab test metrics, staff attendance, and one-click CSV export. |
| **12. Security & Audit Trail** | Section 4 & Section 10 | Immutable audit trail capturing user, action, module, record ID, IP address, and timestamp. |
| **13. Backup & Recovery** | Section 4 & Section 10 | Automated daily snapshots, weekly full backups, instant manual database backups, and point-in-time recovery with safety pre-restore protection. |

---

## 👥 User Roles & Default Test Credentials

All accounts come pre-seeded with secure passwords (`password123`). You can either log in manually or use the **1-Click Demo Fill buttons** on the login screen or the **Test Roles switcher** on the top navigation bar:

| Role | Username | Password | Access Scope |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `password123` | Unrestricted full access across all modules, staff, audit logs, and backups. |
| **Doctor** | `dr.johnson` | `password123` | Appointments, EMR clinical encounters, prescriptions, lab requests, patient history. |
| **Doctor (Neuro)** | `dr.chen` | `password123` | Neurology consultations, medical records, patient diagnoses. |
| **Head Nurse** | `nurse.emily` | `password123` | Inpatient wards, bed tracking, patient vitals, admissions and discharges. |
| **Receptionist** | `receptionist` | `password123` | Patient registration, front desk appointments, scheduling, and billing lookups. |
| **Laboratory Staff** | `labtech` | `password123` | Specimen collection, laboratory diagnostic investigations, result entry, and lab reports. |
| **Pharmacist** | `pharmacist` | `password123` | Medicine inventory, stock reordering, expiry monitoring, and prescription dispensing. |
| **Accountant** | `accountant` | `password123` | Hospital invoices, payment settlements, revenue tracking, and financial ledgers. |

---

## 🏗️ 4-Layer System Architecture

As specified in **Section 5 of the Software Specification Document**:

```
┌────────────────────────────────────────────────────────┐
│                   PRESENTATION LAYER                   │
│  Responsive HTML5, Tailwind CSS, Lucide Icons, Chart.js│
│   Modals, Alerts, Dashboards, and Printable Letterheads│
└───────────────────────────┬────────────────────────────┘
                            │
┌───────────────────────────▼────────────────────────────┐
│                   APPLICATION LAYER                    │
│      Front Controller (index.php), REST API (api.php)  │
│       Session Security & Inactivity Timeout Middleware │
└───────────────────────────┬────────────────────────────┘
                            │
┌───────────────────────────▼────────────────────────────┐
│                  BUSINESS LOGIC LAYER                  │
│  Patient, Doctor, Appointment, EMR, Lab, Pharmacy,     │
│  Billing, Admission, Staff, Report, & System Services  │
└───────────────────────────┬────────────────────────────┘
                            │
┌───────────────────────────▼────────────────────────────┐
│                     DATABASE LAYER                     │
│  High-Performance SQLite PDO (Relational Integrity,    │
│  WAL Journaling, Foreign Keys, Triggers, & Audit Logs) │
└────────────────────────────────────────────────────────┘
```

---

## 🚀 How to Run the System

### Option 1: Double-Click Launcher (Windows)
1. Navigate to the project directory:
   `C:\Users\ASUS\.gemini\antigravity\scratch\hospital-management-system`
2. Double-click **`run-server.bat`**.
3. Your default browser will automatically open to `http://localhost:8000`.

### Option 2: Command Line (PowerShell / Command Prompt)
Run the built-in PHP web server:
```powershell
& "C:\xampp\php\php.exe" -S localhost:8000 -t "C:\Users\ASUS\.gemini\antigravity\scratch\hospital-management-system"
```
Then navigate to: **`http://localhost:8000`**

### Option 3: XAMPP Apache
You can also copy or create a directory symbolic link inside your `C:\xampp\htdocs\hms`:
```powershell
New-Item -ItemType SymbolicLink -Path "C:\xampp\htdocs\hms" -Target "C:\Users\ASUS\.gemini\antigravity\scratch\hospital-management-system"
```
Then open: **`http://localhost/hms`**

---

## 📂 Project Structure

```
hospital-management-system/
├── config/
│   ├── config.php          # Hospital settings, session constants, currency
│   ├── database.php        # SQLite PDO connection, migrations, query helper
│   └── auth.php            # RBAC session check, timeout, helpers
├── controllers/
│   ├── PatientController.php      # Registration, search, history, documents
│   ├── DoctorController.php       # Directory, specialties, schedule
│   ├── AppointmentController.php  # Booking, rescheduling, status tracking
│   ├── EMRController.php          # Encounters, diagnoses, vitals, prescriptions
│   ├── LabController.php          # Specimen orders, results, abnormal flags
│   ├── PharmacyController.php     # Stock inventory, reorders, dispensing, expiries
│   ├── AdmissionController.php    # IPD ward & bed allocations, discharges
│   ├── BillingController.php      # Itemized invoices, tax, payment receipts
│   ├── StaffController.php        # Employees, daily attendance, leaves
│   ├── ReportController.php       # Analytics, KPI metrics, CSV exports
│   └── SystemController.php       # Backup creation, download, & restore
├── models/
│   ├── Schema.php          # Complete DDL tables & pre-seeded realistic clinical data
│   └── AuditLog.php        # Audit trail logger
├── views/
│   ├── partials/           # Header, navbar, sidebar, footer, modals
│   ├── dashboard.php       # Operational dashboard with KPI cards & Chart.js
│   ├── patients.php        # Patient registry table & filters
│   ├── patient_detail.php  # Comprehensive medical history timeline & uploads
│   ├── appointments.php    # Doctor appointments schedule
│   ├── doctors.php         # Physician directory & consultation fees
│   ├── emr.php             # Clinical encounters & prescription records
│   ├── laboratory.php      # Diagnostic lab orders & result entries
│   ├── pharmacy.php        # Medication inventory & expiry warnings
│   ├── admissions.php      # Inpatient ward & bed occupancy management
│   ├── billing.php         # Invoices, settlements, & payment records
│   ├── staff.php           # Personnel registry, attendance, & leaves
│   ├── reports.php         # Intelligence reports & CSV exports
│   ├── audit_logs.php      # Security compliance audit trail
│   ├── backup_restore.php  # Disaster recovery & point-in-time restore
│   ├── print_invoice.php   # Printable official hospital tax invoice
│   ├── print_prescription.php # Printable official doctor medical prescription
│   └── print_lab_report.php   # Printable official diagnostic laboratory report
├── public/
│   ├── css/custom.css      # Custom styling, print sheets, card UI
│   ├── js/app.js           # Modal handlers, toast notifications, AJAX
│   └── uploads/            # Patient attached diagnostic scans & documents
├── backups/                # Point-in-time SQLite snapshot archives
├── sessions/               # Isolated secure session storage
├── database.sqlite         # Relational database file (auto-generated)
├── index.php               # Application router & action dispatcher
├── api.php                 # Asynchronous AJAX endpoints
├── run-server.bat          # 1-Click batch launcher
└── README.md               # System documentation
```

---

## 🔮 Future Enhancements (As per SRS Section 12)

The system architecture is structured with RESTful endpoints (`api.php`) and standard MVC separation to facilitate:
1. Mobile Application (React Native / Flutter API integration)
2. Patient Portal for self-scheduling and online invoice settlement
3. SMS & Email Automated Reminders (Twilio / SendGrid hook integration)
4. Telemedicine Video Consultations (WebRTC / Twilio Video)
5. Direct Insurance Clearinghouse Claims Integration (HL7 / FHIR)
6. AI-Based Clinical Decision Support (automated drug-drug interaction checker)
7. Biometric Authentication for staff check-ins
8. Cloud Deployment (AWS RDS / Google Cloud SQL)
