<?php
/**
 * Hospital Management System (HMS)
 * Database Schema & Seed Data Generator
 * Implements all 13 Database Modules & Supporting Tables
 */

class Schema {
    public static function initialize(PDO $pdo): void {
        // Create Tables
        $pdo->exec("
            -- 1. Roles Module
            CREATE TABLE IF NOT EXISTS roles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT UNIQUE NOT NULL,
                label TEXT NOT NULL,
                description TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            -- 2. Users Module
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                role_id INTEGER NOT NULL,
                username TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                full_name TEXT NOT NULL,
                email TEXT UNIQUE,
                phone TEXT,
                status TEXT DEFAULT 'active' CHECK(status IN ('active', 'inactive')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
            );

            -- 3. Departments Module
            CREATE TABLE IF NOT EXISTS departments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT UNIQUE NOT NULL,
                code TEXT UNIQUE NOT NULL,
                description TEXT,
                head_doctor_name TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            -- 4. Doctors Module
            CREATE TABLE IF NOT EXISTS doctors (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                doctor_code TEXT UNIQUE NOT NULL,
                full_name TEXT NOT NULL,
                department_id INTEGER NOT NULL,
                specialization TEXT NOT NULL,
                qualification TEXT,
                phone TEXT,
                email TEXT,
                consultation_fee REAL NOT NULL DEFAULT 50.00,
                room_number TEXT,
                schedule_days TEXT DEFAULT 'Mon,Tue,Wed,Thu,Fri',
                schedule_hours TEXT DEFAULT '09:00 - 17:00',
                status TEXT DEFAULT 'active' CHECK(status IN ('active', 'inactive')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT
            );

            -- 5. Patients Module
            CREATE TABLE IF NOT EXISTS patients (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                patient_code TEXT UNIQUE NOT NULL,
                full_name TEXT NOT NULL,
                dob DATE NOT NULL,
                gender TEXT NOT NULL CHECK(gender IN ('Male', 'Female', 'Other')),
                blood_group TEXT,
                phone TEXT NOT NULL,
                email TEXT,
                address TEXT,
                emergency_contact_name TEXT,
                emergency_contact_phone TEXT,
                allergies TEXT,
                patient_type TEXT DEFAULT 'Outpatient' CHECK(patient_type IN ('Outpatient', 'Inpatient')),
                status TEXT DEFAULT 'Active' CHECK(status IN ('Active', 'Discharged', 'Inactive')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            -- 6. Admissions Module (Inpatient Management)
            CREATE TABLE IF NOT EXISTS admissions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                admission_code TEXT UNIQUE NOT NULL,
                patient_id INTEGER NOT NULL,
                doctor_id INTEGER NOT NULL,
                ward_name TEXT NOT NULL,
                room_number TEXT NOT NULL,
                bed_number TEXT NOT NULL,
                admission_date DATETIME NOT NULL,
                discharge_date DATETIME,
                daily_bed_charge REAL DEFAULT 120.00,
                status TEXT DEFAULT 'Admitted' CHECK(status IN ('Admitted', 'Discharged')),
                diagnosis_summary TEXT,
                discharge_notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
                FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE RESTRICT
            );

            -- 7. Appointments Module
            CREATE TABLE IF NOT EXISTS appointments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                appointment_code TEXT UNIQUE NOT NULL,
                patient_id INTEGER NOT NULL,
                doctor_id INTEGER NOT NULL,
                department_id INTEGER NOT NULL,
                appointment_date DATE NOT NULL,
                appointment_time TEXT NOT NULL,
                reason TEXT,
                status TEXT DEFAULT 'Scheduled' CHECK(status IN ('Scheduled', 'Confirmed', 'Completed', 'Cancelled', 'In-Progress')),
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
                FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE RESTRICT,
                FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT
            );

            -- 8. Medical Records Module (EMR)
            CREATE TABLE IF NOT EXISTS medical_records (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                patient_id INTEGER NOT NULL,
                doctor_id INTEGER NOT NULL,
                appointment_id INTEGER,
                admission_id INTEGER,
                record_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                chief_complaint TEXT,
                diagnosis TEXT NOT NULL,
                vitals_bp TEXT,
                vitals_heart_rate INTEGER,
                vitals_temp REAL,
                vitals_spo2 INTEGER,
                vitals_weight REAL,
                clinical_notes TEXT,
                treatment_plan TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
                FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE RESTRICT,
                FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
                FOREIGN KEY (admission_id) REFERENCES admissions(id) ON DELETE SET NULL
            );

            -- Prescriptions (EMR Sub-module)
            CREATE TABLE IF NOT EXISTS prescriptions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                medical_record_id INTEGER NOT NULL,
                patient_id INTEGER NOT NULL,
                doctor_id INTEGER NOT NULL,
                medicine_name TEXT NOT NULL,
                dosage TEXT NOT NULL,
                frequency TEXT NOT NULL,
                duration TEXT NOT NULL,
                instructions TEXT,
                is_dispensed INTEGER DEFAULT 0 CHECK(is_dispensed IN (0, 1)),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (medical_record_id) REFERENCES medical_records(id) ON DELETE CASCADE,
                FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
                FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE RESTRICT
            );

            -- 9. Laboratory Tests Module
            CREATE TABLE IF NOT EXISTS laboratory_tests (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                test_code TEXT UNIQUE NOT NULL,
                patient_id INTEGER NOT NULL,
                doctor_id INTEGER NOT NULL,
                test_name TEXT NOT NULL,
                category TEXT NOT NULL,
                cost REAL NOT NULL DEFAULT 35.00,
                order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                sample_status TEXT DEFAULT 'Pending' CHECK(sample_status IN ('Pending', 'Sample Collected', 'In Analysis', 'Completed')),
                sample_collected_at DATETIME,
                result_entered_at DATETIME,
                results TEXT,
                reference_range TEXT,
                is_abnormal INTEGER DEFAULT 0 CHECK(is_abnormal IN (0, 1)),
                technician_notes TEXT,
                conducted_by TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
                FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE RESTRICT
            );

            -- 10. Pharmacy Module
            CREATE TABLE IF NOT EXISTS pharmacy_medicines (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT UNIQUE NOT NULL,
                name TEXT NOT NULL,
                generic_name TEXT,
                category TEXT NOT NULL,
                dosage_form TEXT NOT NULL,
                batch_number TEXT NOT NULL,
                unit_price REAL NOT NULL,
                stock_quantity INTEGER NOT NULL DEFAULT 0,
                reorder_level INTEGER NOT NULL DEFAULT 20,
                expiry_date DATE NOT NULL,
                manufacturer TEXT,
                status TEXT DEFAULT 'Available' CHECK(status IN ('Available', 'Low Stock', 'Out of Stock', 'Expired')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS pharmacy_transactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                medicine_id INTEGER NOT NULL,
                prescription_id INTEGER,
                patient_id INTEGER,
                transaction_type TEXT NOT NULL CHECK(transaction_type IN ('Dispense', 'Restock', 'Adjustment')),
                quantity INTEGER NOT NULL,
                unit_price REAL NOT NULL,
                total_amount REAL NOT NULL,
                transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                notes TEXT,
                performed_by TEXT,
                FOREIGN KEY (medicine_id) REFERENCES pharmacy_medicines(id) ON DELETE RESTRICT,
                FOREIGN KEY (prescription_id) REFERENCES prescriptions(id) ON DELETE SET NULL,
                FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE SET NULL
            );

            -- 11. Billing Module
            CREATE TABLE IF NOT EXISTS bills (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                invoice_number TEXT UNIQUE NOT NULL,
                patient_id INTEGER NOT NULL,
                appointment_id INTEGER,
                admission_id INTEGER,
                bill_date DATE NOT NULL,
                due_date DATE,
                total_consultation REAL DEFAULT 0.00,
                total_lab REAL DEFAULT 0.00,
                total_pharmacy REAL DEFAULT 0.00,
                total_admission REAL DEFAULT 0.00,
                subtotal REAL NOT NULL DEFAULT 0.00,
                discount REAL DEFAULT 0.00,
                tax REAL DEFAULT 0.00,
                grand_total REAL NOT NULL,
                paid_amount REAL DEFAULT 0.00,
                balance_amount REAL NOT NULL,
                payment_status TEXT DEFAULT 'Unpaid' CHECK(payment_status IN ('Unpaid', 'Partial', 'Paid')),
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE RESTRICT,
                FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
                FOREIGN KEY (admission_id) REFERENCES admissions(id) ON DELETE SET NULL
            );

            CREATE TABLE IF NOT EXISTS bill_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                bill_id INTEGER NOT NULL,
                item_type TEXT NOT NULL CHECK(item_type IN ('Consultation', 'Laboratory', 'Pharmacy', 'Admission', 'Other')),
                description TEXT NOT NULL,
                quantity INTEGER NOT NULL DEFAULT 1,
                unit_price REAL NOT NULL,
                total_price REAL NOT NULL,
                FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE
            );

            -- 12. Payments Module
            CREATE TABLE IF NOT EXISTS payments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                payment_code TEXT UNIQUE NOT NULL,
                bill_id INTEGER NOT NULL,
                patient_id INTEGER NOT NULL,
                amount REAL NOT NULL,
                payment_method TEXT NOT NULL CHECK(payment_method IN ('Cash', 'Credit Card', 'Debit Card', 'UPI', 'Insurance', 'Bank Transfer')),
                transaction_reference TEXT,
                payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                received_by TEXT,
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE,
                FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE RESTRICT
            );

            -- 13. Employees & Staff Management Module
            CREATE TABLE IF NOT EXISTS employees (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                employee_code TEXT UNIQUE NOT NULL,
                user_id INTEGER,
                department_id INTEGER,
                full_name TEXT NOT NULL,
                designation TEXT NOT NULL,
                role TEXT NOT NULL,
                phone TEXT NOT NULL,
                email TEXT,
                join_date DATE NOT NULL,
                salary REAL NOT NULL,
                status TEXT DEFAULT 'Active' CHECK(status IN ('Active', 'On Leave', 'Resigned', 'Terminated')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
            );

            CREATE TABLE IF NOT EXISTS attendance (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                employee_id INTEGER NOT NULL,
                attendance_date DATE NOT NULL,
                check_in_time TEXT,
                check_out_time TEXT,
                status TEXT NOT NULL CHECK(status IN ('Present', 'Absent', 'Half-Day', 'On-Leave')),
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
                UNIQUE(employee_id, attendance_date)
            );

            CREATE TABLE IF NOT EXISTS leaves (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                employee_id INTEGER NOT NULL,
                leave_type TEXT NOT NULL CHECK(leave_type IN ('Sick Leave', 'Annual Leave', 'Casual Leave', 'Emergency Leave')),
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                days_count INTEGER NOT NULL DEFAULT 1,
                reason TEXT NOT NULL,
                status TEXT DEFAULT 'Pending' CHECK(status IN ('Pending', 'Approved', 'Rejected')),
                approved_by TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
            );

            -- Supporting: Patient Documents Upload
            CREATE TABLE IF NOT EXISTS patient_documents (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                patient_id INTEGER NOT NULL,
                file_name TEXT NOT NULL,
                original_name TEXT NOT NULL,
                file_type TEXT,
                file_size INTEGER,
                description TEXT,
                uploaded_by TEXT,
                upload_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
            );

            -- Supporting: Security Audit Logs
            CREATE TABLE IF NOT EXISTS audit_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                username TEXT,
                user_role TEXT,
                action TEXT NOT NULL,
                module TEXT NOT NULL,
                record_id INTEGER,
                ip_address TEXT,
                details TEXT,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            -- Supporting: Backup & Recovery Tracking
            CREATE TABLE IF NOT EXISTS system_backups (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                backup_name TEXT NOT NULL,
                file_path TEXT NOT NULL,
                file_size INTEGER,
                backup_type TEXT DEFAULT 'Manual' CHECK(backup_type IN ('Daily', 'Weekly', 'Manual')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // Seed Default Data
        self::seedInitialData($pdo);
    }

    public static function seedInitialData(PDO $pdo): void {
        // 1. Roles
        $roles = [
            ['admin', 'Administrator', 'Full system configuration, security, staff, reports and operations management'],
            ['doctor', 'Doctor', 'Clinical records, diagnosis, appointments, prescriptions, patient consultation'],
            ['nurse', 'Nurse', 'Patient vitals, inpatient ward and bed management, sample collection assistance'],
            ['receptionist', 'Receptionist', 'Patient registration, appointment scheduling, front desk management'],
            ['lab', 'Laboratory Staff', 'Conduct medical tests, sample tracking, enter lab results and generate reports'],
            ['pharmacist', 'Pharmacist', 'Medicine inventory control, stock reordering, dispensing prescriptions'],
            ['accountant', 'Accountant', 'Hospital billing, payment processing, invoices, and financial reporting']
        ];
        $roleStmt = $pdo->prepare("INSERT OR IGNORE INTO roles (name, label, description) VALUES (?, ?, ?)");
        foreach ($roles as $r) {
            $roleStmt->execute($r);
        }

        // Fetch role IDs
        $roleMap = [];
        $stmt = $pdo->query("SELECT id, name FROM roles");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $roleMap[$row['name']] = (int)$row['id'];
        }

        // 2. Users (Default credentials)
        $defaultPassword = password_hash('password123', PASSWORD_DEFAULT);
        $users = [
            [$roleMap['admin'], 'admin', $defaultPassword, 'Dr. Robert Vance (Medical Director & Admin)', 'admin@apexmemorial.org', '+1-555-0101'],
            [$roleMap['doctor'], 'dr.johnson', $defaultPassword, 'Dr. Sarah Johnson, MD', 's.johnson@apexmemorial.org', '+1-555-0102'],
            [$roleMap['doctor'], 'dr.chen', $defaultPassword, 'Dr. Marcus Chen, MD', 'm.chen@apexmemorial.org', '+1-555-0103'],
            [$roleMap['nurse'], 'nurse.emily', $defaultPassword, 'Emily Davis, RN (Head Nurse)', 'e.davis@apexmemorial.org', '+1-555-0104'],
            [$roleMap['receptionist'], 'receptionist', $defaultPassword, 'Jessica Martinez', 'reception@apexmemorial.org', '+1-555-0105'],
            [$roleMap['lab'], 'labtech', $defaultPassword, 'David Wilson (Lab Director)', 'lab@apexmemorial.org', '+1-555-0106'],
            [$roleMap['pharmacist'], 'pharmacist', $defaultPassword, 'Rachel Adams, PharmD', 'pharmacy@apexmemorial.org', '+1-555-0107'],
            [$roleMap['accountant'], 'accountant', $defaultPassword, 'Michael Chang, CPA', 'billing@apexmemorial.org', '+1-555-0108']
        ];
        $userStmt = $pdo->prepare("INSERT OR IGNORE INTO users (role_id, username, password_hash, full_name, email, phone) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($users as $u) {
            $userStmt->execute($u);
        }

        // 3. Departments
        $departments = [
            ['Cardiology', 'CARD', 'Comprehensive cardiovascular diagnosis, treatment and coronary care', 'Dr. Sarah Johnson, MD'],
            ['Neurology', 'NEUR', 'Advanced brain, spine and neurological disorders management', 'Dr. Marcus Chen, MD'],
            ['Pediatrics', 'PED', 'Compassionate medical care for infants, children, and adolescents', 'Dr. Elena Rostova, MD'],
            ['Orthopedics', 'ORTH', 'Bone, joint and musculoskeletal trauma and reconstruction', 'Dr. Arthur Pendelton, MD'],
            ['General Medicine', 'GENM', 'Primary healthcare, internal medicine and chronic disease management', 'Dr. Kevin Walsh, MD'],
            ['Radiology & Imaging', 'RAD', 'State-of-the-art diagnostic imaging, MRI, CT, and X-ray services', 'Dr. Priya Patel, MD'],
            ['General Surgery', 'SURG', 'Operative interventions, minimally invasive and emergency surgery', 'Dr. Gregory House, MD'],
            ['Emergency Medicine', 'EMER', '24/7 urgent trauma and life-saving critical stabilization', 'Dr. Lisa Cuddy, MD']
        ];
        $deptStmt = $pdo->prepare("INSERT OR IGNORE INTO departments (name, code, description, head_doctor_name) VALUES (?, ?, ?, ?)");
        foreach ($departments as $d) {
            $deptStmt->execute($d);
        }

        // 4. Doctors
        $doctors = [
            [2, 'DOC-001', 'Dr. Sarah Johnson, MD', 1, 'Interventional Cardiology', 'MD, FACC, Harvard Medical School', '+1-555-0102', 's.johnson@apexmemorial.org', 120.00, 'Room 301, Block A', 'Mon,Tue,Wed,Thu,Fri', '09:00 - 15:00'],
            [3, 'DOC-002', 'Dr. Marcus Chen, MD', 2, 'Neurology & Stroke Specialist', 'MD, PhD, Johns Hopkins Medicine', '+1-555-0103', 'm.chen@apexmemorial.org', 140.00, 'Room 405, Block B', 'Mon,Wed,Fri', '10:00 - 16:00'],
            [null, 'DOC-003', 'Dr. Elena Rostova, MD', 3, 'Pediatric Medicine', 'MD, Stanford University', '+1-555-0123', 'e.rostova@apexmemorial.org', 90.00, 'Room 204, Block C', 'Mon,Tue,Thu,Sat', '09:00 - 14:00'],
            [null, 'DOC-004', 'Dr. Arthur Pendelton, MD', 4, 'Orthopedic Surgery & Sports Medicine', 'MS, FRCS, Oxford University', '+1-555-0144', 'a.pendelton@apexmemorial.org', 130.00, 'Room 112, Block A', 'Tue,Thu,Fri', '11:00 - 17:00'],
            [null, 'DOC-005', 'Dr. Kevin Walsh, MD', 5, 'Internal Medicine & Geriatrics', 'MD, Columbia University', '+1-555-0155', 'k.walsh@apexmemorial.org', 75.00, 'Room 102, Block A', 'Mon,Tue,Wed,Thu,Fri', '08:30 - 16:30']
        ];
        $docStmt = $pdo->prepare("INSERT OR IGNORE INTO doctors (user_id, doctor_code, full_name, department_id, specialization, qualification, phone, email, consultation_fee, room_number, schedule_days, schedule_hours) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($doctors as $doc) {
            $docStmt->execute($doc);
        }

        // 5. Patients
        $patients = [
            ['PAT-2026-001', 'Eleanor Rigby', '1984-05-14', 'Female', 'O+', '+1-555-7811', 'eleanor.rigby@email.com', '742 Evergreen Terrace, Springfield', 'Paul McCartney', '+1-555-7812', 'Penicillin, Peanuts', 'Inpatient', 'Active'],
            ['PAT-2026-002', 'James Tiberius Kirk', '1975-03-22', 'Male', 'A+', '+1-555-8822', 'jim.kirk@starmail.com', '1701 Starfleet Way, San Francisco', 'Spock Sarek', '+1-555-8823', 'None known', 'Outpatient', 'Active'],
            ['PAT-2026-003', 'Clara Oswald', '1992-11-23', 'Female', 'B-', '+1-555-9933', 'clara.oswald@timemail.com', '45 Baker St, London', 'Danny Pink', '+1-555-9934', 'Aspirin, Sulfa drugs', 'Outpatient', 'Active'],
            ['PAT-2026-004', 'Bruce Wayne', '1981-02-19', 'Male', 'AB+', '+1-555-4411', 'bruce.wayne@waynecorp.com', 'Wayne Manor, Gotham City', 'Alfred Pennyworth', '+1-555-4412', 'None', 'Inpatient', 'Active'],
            ['PAT-2026-005', 'Amara Campbell', '2018-08-10', 'Female', 'O-', '+1-555-3322', 'sarah.campbell@parentmail.com', '12 Rosewood Lane, Tech City', 'Sarah Campbell (Mother)', '+1-555-3323', 'Dairy/Lactose', 'Outpatient', 'Active'],
            ['PAT-2026-006', 'Samuel Jackson', '1968-12-21', 'Male', 'A-', '+1-555-2211', 'sam.jackson@hollymail.com', '88 Sunset Blvd, Los Angeles', 'LaTanya Richardson', '+1-555-2212', 'NSAIDs', 'Outpatient', 'Active']
        ];
        $patStmt = $pdo->prepare("INSERT OR IGNORE INTO patients (patient_code, full_name, dob, gender, blood_group, phone, email, address, emergency_contact_name, emergency_contact_phone, allergies, patient_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($patients as $p) {
            $patStmt->execute($p);
        }

        // 6. Admissions (Inpatient Management)
        $admissions = [
            ['ADM-2026-001', 1, 1, 'Cardiology Ward (Wing 3)', '304', 'Bed-01', date('Y-m-d H:i:s', strtotime('-4 days')), null, 150.00, 'Admitted', 'Unstable Angina & Arrhythmia monitoring', null],
            ['ADM-2026-002', 4, 4, 'Trauma / VIP Ortho Care', '108', 'Bed-VIP', date('Y-m-d H:i:s', strtotime('-2 days')), null, 250.00, 'Admitted', 'Post-operative recovery: Left Clavicle & Rib fracture reconstruction', null],
            ['ADM-2026-003', 6, 5, 'General Medical Ward', '201', 'Bed-04', date('Y-m-d H:i:s', strtotime('-10 days')), date('Y-m-d H:i:s', strtotime('-3 days')), 110.00, 'Discharged', 'Pneumonia and severe dehydration', 'Patient vitals stable. Completed IV antibiotic course. Advised 1-week home rest.']
        ];
        $admStmt = $pdo->prepare("INSERT OR IGNORE INTO admissions (admission_code, patient_id, doctor_id, ward_name, room_number, bed_number, admission_date, discharge_date, daily_bed_charge, status, diagnosis_summary, discharge_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($admissions as $adm) {
            $admStmt->execute($adm);
        }

        // 7. Appointments
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $appointments = [
            ['APT-2026-001', 2, 1, 1, $today, '09:30 AM', 'Routine cardiovascular screening and palpitations check', 'Confirmed', 'Patient arrived on time, vitals normal.'],
            ['APT-2026-002', 3, 2, 2, $today, '10:30 AM', 'Severe chronic migraine and neurological follow-up', 'Scheduled', 'Fast-track appointment.'],
            ['APT-2026-003', 5, 3, 3, $today, '11:15 AM', 'Pediatric seasonal flu, high fever and throat inflammation', 'Scheduled', 'Mother accompanying child.'],
            ['APT-2026-004', 6, 5, 5, $tomorrow, '02:00 PM', 'Post-discharge follow-up and prescription renewal', 'Scheduled', 'Routine check.'],
            ['APT-2026-005', 1, 1, 1, $yesterday, '10:00 AM', 'Initial chest tightness evaluation prior to admission', 'Completed', 'Admitted to Cardiology Ward.']
        ];
        $aptStmt = $pdo->prepare("INSERT OR IGNORE INTO appointments (appointment_code, patient_id, doctor_id, department_id, appointment_date, appointment_time, reason, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($appointments as $a) {
            $aptStmt->execute($a);
        }

        // 8. Medical Records (EMR)
        $medicalRecords = [
            [1, 1, 5, 1, date('Y-m-d H:i:s', strtotime('-4 days')), 'Substernal chest pain radiating to left arm upon exertion', 'Coronary Artery Disease & Unstable Angina (ICD-10 I20.0)', '148/92', 88, 37.1, 97, 68.5, 'ECG revealed ST depression in leads V4-V6. Troponin I elevated (0.08 ng/mL). Patient admitted immediately for telemetry monitoring.', 'Start IV Heparin protocol, dual antiplatelet therapy (Aspirin + Clopidogrel), Atorvastatin 80mg, Nitroglycerin sublingual PRN.'],
            [2, 1, 1, null, date('Y-m-d H:i:s'), 'Periodic racing heart and shortness of breath after morning walk', 'Sinus Tachycardia with benign PVCs (ICD-10 R00.0)', '128/80', 76, 36.7, 99, 79.0, 'Cardiac auscultation normal S1 S2, no murmurs. ECG shows normal sinus rhythm. Holter monitor scheduled for 24 hours.', 'Prescribed Metoprolol Succinate 25mg daily. Advised reduction in caffeine intake. Scheduled 2-week follow up.']
        ];
        $emrStmt = $pdo->prepare("INSERT OR IGNORE INTO medical_records (patient_id, doctor_id, appointment_id, admission_id, record_date, chief_complaint, diagnosis, vitals_bp, vitals_heart_rate, vitals_temp, vitals_spo2, vitals_weight, clinical_notes, treatment_plan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($medicalRecords as $mr) {
            $emrStmt->execute($mr);
        }

        // 9. Prescriptions
        $prescriptions = [
            [1, 1, 1, 'Atorvastatin 40mg', '1 tablet', 'Once daily at bedtime', '30 days', 'Take with water after dinner. Monitor for muscle ache.', 1],
            [1, 1, 1, 'Clopidogrel 75mg', '1 tablet', 'Once daily with breakfast', '30 days', 'Avoid concomitant NSAIDs to reduce bleeding risk.', 1],
            [2, 2, 1, 'Metoprolol Succinate 25mg', '1 tablet', 'Once daily in the morning', '14 days', 'Do not abruptly discontinue.', 0]
        ];
        $prescStmt = $pdo->prepare("INSERT OR IGNORE INTO prescriptions (medical_record_id, patient_id, doctor_id, medicine_name, dosage, frequency, duration, instructions, is_dispensed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($prescriptions as $p) {
            $prescStmt->execute($p);
        }

        // 10. Laboratory Tests
        $labTests = [
            ['LAB-2026-001', 1, 1, 'Complete Blood Count (CBC) with Differential', 'Hematology', 45.00, date('Y-m-d H:i:s', strtotime('-3 days')), 'Completed', date('Y-m-d H:i:s', strtotime('-3 days')), date('Y-m-d H:i:s', strtotime('-3 days')), 'WBC: 8.4 x10^3/uL, RBC: 4.6 x10^6/uL, Hemoglobin: 13.8 g/dL, Platelets: 240 x10^3/uL', 'WBC: 4.5-11.0, Hb: 12.0-15.5 g/dL', 0, 'All hematologic indices within expected normal range.', 'David Wilson'],
            ['LAB-2026-002', 1, 1, 'Cardiac Troponin I & Lipid Panel', 'Biochemistry', 85.00, date('Y-m-d H:i:s', strtotime('-3 days')), 'Completed', date('Y-m-d H:i:s', strtotime('-3 days')), date('Y-m-d H:i:s', strtotime('-3 days')), 'Troponin I: 0.08 ng/mL (High), Total Cholesterol: 245 mg/dL (High), LDL: 162 mg/dL (High), HDL: 42 mg/dL', 'Troponin: < 0.04 ng/mL, LDL: < 100 mg/dL', 1, 'Elevated troponin consistent with myocardial ischemia. High LDL requires statin therapy.', 'David Wilson'],
            ['LAB-2026-003', 2, 1, 'Fasting Blood Glucose & HbA1c', 'Biochemistry', 50.00, date('Y-m-d H:i:s'), 'Sample Collected', date('Y-m-d H:i:s'), null, null, 'Fasting: 70-99 mg/dL, HbA1c: < 5.7%', 0, 'Sample received in laboratory. Running on Cobas analyzer.', 'David Wilson'],
            ['LAB-2026-004', 3, 2, 'Brain MRI with Contrast', 'Radiology', 280.00, date('Y-m-d H:i:s'), 'Pending', null, null, null, 'Normal anatomical brain architecture', 0, 'Patient scheduled for 2:30 PM scan.', null]
        ];
        $labStmt = $pdo->prepare("INSERT OR IGNORE INTO laboratory_tests (test_code, patient_id, doctor_id, test_name, category, cost, order_date, sample_status, sample_collected_at, result_entered_at, results, reference_range, is_abnormal, technician_notes, conducted_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($labTests as $lt) {
            $labStmt->execute($lt);
        }

        // 11. Pharmacy Medicines Inventory
        $medicines = [
            ['MED-001', 'Amoxicillin 500mg', 'Amoxicillin Trihydrate', 'Antibiotics', 'Capsule', 'BAT-2026-AMX', 12.50, 240, 30, '2027-08-31', 'Pfizer Inc.', 'Available'],
            ['MED-002', 'Atorvastatin 40mg', 'Atorvastatin Calcium', 'Cardiovascular', 'Tablet', 'BAT-2026-ATR', 28.00, 15, 25, '2027-05-15', 'Novartis', 'Low Stock'], // Trigger low stock alert
            ['MED-003', 'Clopidogrel 75mg', 'Clopidogrel Bisulfate', 'Cardiovascular', 'Tablet', 'BAT-2026-CLP', 32.00, 95, 20, '2027-10-30', 'Sanofi', 'Available'],
            ['MED-004', 'Metformin 500mg', 'Metformin Hydrochloride', 'Antidiabetic', 'Tablet', 'BAT-2026-MET', 9.00, 180, 40, '2027-12-31', 'Merck', 'Available'],
            ['MED-005', 'Metoprolol 25mg', 'Metoprolol Tartrate', 'Cardiovascular', 'Tablet', 'BAT-2026-MTP', 18.50, 110, 20, '2026-12-15', 'AstraZeneca', 'Available'], // Expiring soon in ~1 month
            ['MED-006', 'Paracetamol 650mg', 'Acetaminophen', 'Analgesics & Antipyretic', 'Tablet', 'BAT-2026-PCM', 4.50, 450, 50, '2028-01-20', 'GSK', 'Available'],
            ['MED-007', 'Omeprazole 20mg', 'Omeprazole', 'Gastrointestinal', 'Capsule', 'BAT-2026-OMP', 14.00, 8, 20, '2027-04-10', 'Bayer', 'Low Stock'], // Trigger low stock alert
            ['MED-008', 'Ceftriaxone 1g Injection', 'Ceftriaxone Sodium', 'Antibiotics', 'Vial', 'BAT-2026-CEF', 24.00, 45, 15, '2026-11-20', 'Roche', 'Available'], // Expiring in ~1 week!
            ['MED-009', 'Salbutamol Inhaler 100mcg', 'Albuterol Sulfate', 'Respiratory', 'Inhaler', 'BAT-2026-SAL', 22.00, 60, 15, '2027-09-15', 'GSK', 'Available'],
            ['MED-010', 'Ibuprofen 400mg', 'Ibuprofen', 'NSAID / Pain Relief', 'Tablet', 'BAT-2026-IBU', 7.50, 320, 30, '2027-11-01', 'Abbott', 'Available']
        ];
        $medStmt = $pdo->prepare("INSERT OR IGNORE INTO pharmacy_medicines (code, name, generic_name, category, dosage_form, batch_number, unit_price, stock_quantity, reorder_level, expiry_date, manufacturer, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($medicines as $m) {
            $medStmt->execute($m);
        }

        // 12. Billing & Invoicing
        $bills = [
            ['INV-2026-001', 1, 5, 1, date('Y-m-d', strtotime('-4 days')), date('Y-m-d', strtotime('+3 days')), 120.00, 130.00, 60.00, 600.00, 910.00, 50.00, 43.00, 903.00, 903.00, 0.00, 'Paid', 'Cardiac evaluation, lab panels and 4-day admission charges.'],
            ['INV-2026-002', 2, 1, null, date('Y-m-d'), date('Y-m-d', strtotime('+7 days')), 120.00, 50.00, 0.00, 0.00, 170.00, 0.00, 8.50, 178.50, 100.00, 78.50, 'Partial', 'Consultation and fasting glucose lab test. Balance payable.'],
            ['INV-2026-003', 3, 2, null, date('Y-m-d'), date('Y-m-d', strtotime('+7 days')), 140.00, 280.00, 0.00, 0.00, 420.00, 20.00, 20.00, 420.00, 0.00, 420.00, 'Unpaid', 'Neurology consultation & scheduled Brain MRI.']
        ];
        $billStmt = $pdo->prepare("INSERT OR IGNORE INTO bills (invoice_number, patient_id, appointment_id, admission_id, bill_date, due_date, total_consultation, total_lab, total_pharmacy, total_admission, subtotal, discount, tax, grand_total, paid_amount, balance_amount, payment_status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($bills as $b) {
            $billStmt->execute($b);
        }

        // Bill items
        $billItems = [
            [1, 'Consultation', 'Dr. Sarah Johnson - Specialist Consultation', 1, 120.00, 120.00],
            [1, 'Laboratory', 'CBC Differential + Cardiac Troponin Panel', 1, 130.00, 130.00],
            [1, 'Pharmacy', 'Atorvastatin 40mg (30 tabs) & Clopidogrel 75mg (30 tabs)', 1, 60.00, 60.00],
            [1, 'Admission', 'Cardiology Ward (Wing 3) Bed-01 (4 Days @ $150/day)', 4, 150.00, 600.00],
            [2, 'Consultation', 'Dr. Sarah Johnson - Routine Cardiac Screening', 1, 120.00, 120.00],
            [2, 'Laboratory', 'Fasting Blood Glucose & HbA1c Panel', 1, 50.00, 50.00],
            [3, 'Consultation', 'Dr. Marcus Chen - Neurology Specialist Exam', 1, 140.00, 140.00],
            [3, 'Laboratory', 'Brain MRI with Contrast', 1, 280.00, 280.00]
        ];
        $bItemStmt = $pdo->prepare("INSERT OR IGNORE INTO bill_items (bill_id, item_type, description, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($billItems as $bi) {
            $bItemStmt->execute($bi);
        }

        // 13. Payments
        $payments = [
            ['PAY-2026-001', 1, 1, 903.00, 'Credit Card', 'AUTH-VISA-99482', date('Y-m-d H:i:s', strtotime('-4 days')), 'Michael Chang', 'Full payment settled via Visa credit card.'],
            ['PAY-2026-002', 2, 2, 100.00, 'Cash', 'RCPT-CSH-1049', date('Y-m-d H:i:s'), 'Michael Chang', 'Initial deposit paid at cashier counter.']
        ];
        $payStmt = $pdo->prepare("INSERT OR IGNORE INTO payments (payment_code, bill_id, patient_id, amount, payment_method, transaction_reference, payment_date, received_by, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($payments as $pay) {
            $payStmt->execute($pay);
        }

        // 14. Employees (Staff Management)
        $employees = [
            ['EMP-001', 1, 5, 'Dr. Robert Vance', 'Chief Medical Officer / Admin', 'admin', '+1-555-0101', 'admin@apexmemorial.org', '2019-01-15', 18500.00, 'Active'],
            ['EMP-002', 2, 1, 'Dr. Sarah Johnson', 'Consultant Cardiologist', 'doctor', '+1-555-0102', 's.johnson@apexmemorial.org', '2021-04-01', 14200.00, 'Active'],
            ['EMP-003', 3, 2, 'Dr. Marcus Chen', 'Senior Neurologist', 'doctor', '+1-555-0103', 'm.chen@apexmemorial.org', '2020-09-15', 14800.00, 'Active'],
            ['EMP-004', 4, 1, 'Emily Davis, RN', 'Head Inpatient Nurse', 'nurse', '+1-555-0104', 'e.davis@apexmemorial.org', '2022-03-10', 6500.00, 'Active'],
            ['EMP-005', 5, 5, 'Jessica Martinez', 'Front Desk Senior Receptionist', 'receptionist', '+1-555-0105', 'reception@apexmemorial.org', '2023-01-20', 4200.00, 'Active'],
            ['EMP-006', 6, 6, 'David Wilson', 'Chief Medical Laboratory Scientist', 'lab', '+1-555-0106', 'lab@apexmemorial.org', '2021-07-01', 7800.00, 'Active'],
            ['EMP-007', 7, 5, 'Rachel Adams, PharmD', 'Head Pharmacist & Inventory Lead', 'pharmacist', '+1-555-0107', 'pharmacy@apexmemorial.org', '2022-06-15', 8200.00, 'Active'],
            ['EMP-008', 8, 5, 'Michael Chang, CPA', 'Senior Hospital Accountant', 'accountant', '+1-555-0108', 'billing@apexmemorial.org', '2020-11-01', 7500.00, 'Active']
        ];
        $empStmt = $pdo->prepare("INSERT OR IGNORE INTO employees (employee_code, user_id, department_id, full_name, designation, role, phone, email, join_date, salary, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($employees as $emp) {
            $empStmt->execute($emp);
        }

        // 15. Employee Attendance for Today
        $attendanceData = [
            [1, $today, '07:45 AM', '05:15 PM', 'Present', 'On-time arrival'],
            [2, $today, '08:30 AM', '04:45 PM', 'Present', 'Ward rounds and OPD clinic'],
            [3, $today, '09:15 AM', null, 'Present', 'In clinic'],
            [4, $today, '06:50 AM', '03:10 PM', 'Present', 'Morning shift completed'],
            [5, $today, '08:00 AM', null, 'Present', 'Reception counter 1'],
            [6, $today, '08:15 AM', null, 'Present', 'Lab samples processing'],
            [7, $today, '08:45 AM', null, 'Present', 'Pharmacy dispense counter'],
            [8, $today, '09:00 AM', null, 'Present', 'Billing and accounts desk']
        ];
        $attStmt = $pdo->prepare("INSERT OR IGNORE INTO attendance (employee_id, attendance_date, check_in_time, check_out_time, status, notes) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($attendanceData as $att) {
            $attStmt->execute($att);
        }

        // 16. Leave Records
        $leaves = [
            [2, 'Annual Leave', date('Y-m-d', strtotime('+10 days')), date('Y-m-d', strtotime('+14 days')), 5, 'Attending International Cardiology Congress in Boston', 'Approved', 'Dr. Robert Vance'],
            [4, 'Sick Leave', date('Y-m-d', strtotime('-5 days')), date('Y-m-d', strtotime('-4 days')), 2, 'Severe seasonal viral infection', 'Approved', 'Dr. Robert Vance'],
            [6, 'Casual Leave', date('Y-m-d', strtotime('+3 days')), date('Y-m-d', strtotime('+4 days')), 2, 'Personal family commitment', 'Pending', null]
        ];
        $leaveStmt = $pdo->prepare("INSERT OR IGNORE INTO leaves (employee_id, leave_type, start_date, end_date, days_count, reason, status, approved_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($leaves as $l) {
            $leaveStmt->execute($l);
        }

        // 17. Seed Initial Audit Logs
        $logs = [
            [1, 'admin', 'Administrator', 'Login', 'Security', 'Administrator logged into HMS console', null],
            [1, 'admin', 'Administrator', 'System Initialize', 'System', 'Database initialized with standard modules, roles, and security policies', null],
            [5, 'receptionist', 'Receptionist', 'Register Patient', 'Patients', 'New patient registered: Eleanor Rigby (PAT-2026-001)', 1],
            [2, 'dr.johnson', 'Doctor', 'Add Medical Record', 'EMR', 'Diagnosed CAD & Angina for Eleanor Rigby', 1],
            [6, 'labtech', 'Laboratory Staff', 'Result Entry', 'Laboratory', 'Completed results for CBC (LAB-2026-001)', 1],
            [8, 'accountant', 'Accountant', 'Record Payment', 'Billing', 'Received $903.00 via Credit Card for INV-2026-001', 1]
        ];
        $logStmt = $pdo->prepare("INSERT OR IGNORE INTO audit_logs (user_id, username, user_role, action, module, details, record_id, ip_address, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, '127.0.0.1', ?)");
        foreach ($logs as $idx => $lg) {
            $time = date('Y-m-d H:i:s', strtotime("-" . (6 - $idx) . " hours"));
            $logStmt->execute([$lg[0], $lg[1], $lg[2], $lg[3], $lg[4], $lg[5], $lg[6], $time]);
        }
    }
}
