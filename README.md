# SLeClear MIS
**Sierra Leone Student Clearance & Financial Management Information System**
A web-based prototype for university financial compliance and decision support.

Module: DMGMT210 – Management Information System
Faculty of Information and Communication Technology
Limkokwing University of Creative Technology, Sierra Leone

License: **MIT** (open source) — see [`LICENSE`](LICENSE).

---

## 1. What this system does

Universities in Sierra Leone enforce the **"No Slip = No Exam"** policy. Tracking
fees, clearances, and deferred assessments manually causes delays and errors.
SLeClear MIS automates all of it:

- **Students** – register, edit, search, filter by faculty
- **Payments** – record transactions (Cash, Bank, Mobile Money, Cheque, Card)
- **Clearance engine** – auto-decides **Full / Provisional / Denied** based on % paid
- **Printable Clearance Slip** – professional certificate (Print → Save as PDF)
- **Deferred Assessments** – student submits → Registry/Admin approves/rejects
- **Dashboards** – live KPIs, doughnut + bar charts (Chart.js)
- **Reports & exports** – CSV export for Students, Payments, Clearances
- **Role-based access** – Admin, Finance, Registry
- **Audit log** – every important action stored

**SDG alignment:** SDG 4 (Quality Education) and SDG 10 (Reduced Inequalities) —
provisional clearance prevents students from being excluded from exams while
they finalize payments.

---

## 2. Demo accounts

| Role     | Username   | Password      |
|----------|------------|---------------|
| Admin    | `admin`    | `admin123`    |
| Finance  | `finance`  | `finance123`  |
| Registry | `registry` | `registry123` |

> Change passwords immediately in production via **Users → Edit**.

---

## 3. Run the system on your computer — **step-by-step for total beginners**

You only need **two things**: PHP and VS Code. No MySQL, no XAMPP, no internet
required after setup. The database (SQLite) is created automatically.

### Step 1 — Install PHP

**Windows**
1. Go to <https://windows.php.net/download/> and download the latest
   **VS17 x64 Thread Safe** ZIP (e.g. `php-8.3-Win32-vs17-x64.zip`).
2. Create a folder `C:\php` and unzip everything into it.
3. Add `C:\php` to the system PATH:
   - Press **Windows key**, type *"environment variables"*, open it.
   - Click **Environment Variables → Path → Edit → New** and paste `C:\php`.
   - Click OK on every window.
4. Open **Command Prompt** and type `php -v`. You should see a version number.

**macOS** – open Terminal and run:
```
brew install php
```

**Linux (Ubuntu/Debian)**:
```
sudo apt update && sudo apt install php php-sqlite3 -y
```

### Step 2 — Install VS Code
Download from <https://code.visualstudio.com/> and install it.
(Optional but nice: install the **PHP Intelephense** extension.)

### Step 3 — Get the project
1. Unzip `sleclear-mis.zip` somewhere easy, e.g. **Desktop**.
2. Open **VS Code → File → Open Folder…** and pick the `sleclear-mis` folder.

### Step 4 — Start the server
1. In VS Code, open the terminal: **Terminal → New Terminal** (or press `` Ctrl+` ``).
2. Type this **one** command and press Enter:

   ```
   php -S localhost:8000
   ```
 C:\php\php.exe -S localhost:8000
3. You should see:
   `PHP 8.x Development Server (http://localhost:8000) started`

### Step 5 — Open the system
1. Open your web browser (Chrome, Edge, Firefox).
2. Go to: **http://localhost:8000**
3. Sign in with `admin` / `admin123`.
4. 🎉 Done — explore the Dashboard, Students, Payments, Clearance, Deferred,
   Reports, Users.

> The SQLite database file is created automatically the first time you load
> the page (at `data/sleclear.sqlite`) and is pre-loaded with **6 sample
> students, 6 sample payments, and 2 deferred applications** so the dashboard
> isn't empty.

### Step 6 — Stop the server
In the VS Code terminal press **Ctrl + C**.

### Reset to a fresh database
Delete the file `data/sleclear.sqlite` and reload the page — it will rebuild
with the sample data.

---

## 4. Folder structure

```
sleclear-mis/
├── index.php                  → redirects to login or dashboard
├── login.php / logout.php
├── dashboard.php              → KPIs + charts
├── students.php / student_form.php
├── payments.php / payment_form.php
├── clearance.php / clearance_slip.php
├── deferred.php / deferred_form.php
├── reports.php
├── export.php                 → CSV downloads
├── users.php / user_form.php  → admin only
├── includes/
│   ├── config.php             → app constants
│   ├── db.php                 → SQLite + auto-schema + seed
│   ├── auth.php               → sessions, CSRF, roles, audit
│   ├── functions.php          → balance / clearance logic
│   ├── header.php / footer.php
├── assets/
│   ├── css/style.css          → modern design system
│   └── js/app.js
├── data/                      → SQLite database (auto-created)
├── schema.sql                 → DB schema reference
├── LICENSE                    → MIT
└── README.md                  → this file
```

---

## 5. Clearance rules (configurable in `includes/config.php`)

| % of total fees paid | Status        | Outcome                                  |
|----------------------|---------------|------------------------------------------|
| 100%                 | **Full**      | Cleared for exams and graduation         |
| 70% – 99%            | **Provisional** | Allowed to sit exams; balance owed     |
| Below 70%            | **Denied**    | "No Slip = No Exam"                      |

---

## 6. Switching to MySQL/MariaDB (optional)

The project uses **SQLite** for zero-setup. To use MySQL instead:
1. Run `schema.sql` against your MySQL database (adapt `INTEGER PRIMARY KEY AUTOINCREMENT` → `INT AUTO_INCREMENT PRIMARY KEY`, `TEXT` types as needed).
2. Edit `includes/db.php` and replace the PDO line with:
   ```php
   $pdo = new PDO('mysql:host=localhost;dbname=sleclear;charset=utf8mb4', 'root', '');
   ```
3. Re-seed users via SQL with `password_hash()`-generated hashes.

---

## 7. Privacy & compliance

- Passwords hashed with PHP `password_hash()` (bcrypt).
- CSRF tokens on every state-changing form.
- Role-based access enforced server-side.
- Audit log (`audit_log` table) records logins, payments, clearances, deferrals.
- No third-party tracking, no external API calls — runs fully offline.
- Aligned with general data-minimization principles (only essential PII stored).

---

## 8. Assessment-criteria coverage

| Criterion (from brief)                         | Where to find it                                  |
|------------------------------------------------|---------------------------------------------------|
| Relevance to SDGs & SL problem                 | README §1, Dashboard, Provisional clearance logic |
| Approved open-source license                   | `LICENSE` (MIT)                                   |
| Technical implementation                       | Whole codebase, modular `includes/`               |
| Documentation & repo quality                   | This README, `schema.sql`, code comments          |
| Data accessibility & interoperability          | CSV exports, plain SQLite file, `schema.sql`      |
| Privacy & legal compliance                     | README §7, password hashing, CSRF, RBAC, audit   |
| Standards & best practices                     | PDO prepared statements, sessions, semantic HTML  |
| Presentation & demonstration                   | Modern UI, charts, printable slip                 |
| Exhibition readiness                           | Pre-seeded data, runs on `php -S` in one command  |

---

Built with PHP 8, SQLite 3, Chart.js, and a custom design system. No build step, no dependencies to install.
