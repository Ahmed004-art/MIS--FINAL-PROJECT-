-- SLeClear MIS – Database Schema (SQLite)
-- Tables are auto-created on first run by includes/db.php; this file
-- is provided for documentation / portability to MySQL/MariaDB.

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    full_name TEXT NOT NULL,
    role TEXT NOT NULL CHECK(role IN ('Admin','Finance','Registry')),
    created_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE students (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id TEXT UNIQUE NOT NULL,
    full_name TEXT NOT NULL,
    gender TEXT,
    email TEXT,
    phone TEXT,
    program TEXT,
    faculty TEXT,
    level TEXT,
    semester TEXT,
    total_fees REAL NOT NULL DEFAULT 0,
    created_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE payments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL REFERENCES students(id) ON DELETE CASCADE,
    amount REAL NOT NULL,
    method TEXT,
    reference TEXT,
    paid_on TEXT NOT NULL,
    recorded_by INTEGER REFERENCES users(id),
    notes TEXT,
    created_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE clearances (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL REFERENCES students(id) ON DELETE CASCADE,
    status TEXT NOT NULL CHECK(status IN ('Full','Provisional','Denied')),
    purpose TEXT,
    issued_by INTEGER REFERENCES users(id),
    issued_on TEXT DEFAULT (datetime('now'))
);

CREATE TABLE deferred_assessments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL REFERENCES students(id) ON DELETE CASCADE,
    course_code TEXT NOT NULL,
    course_name TEXT,
    reason TEXT,
    fee REAL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'Pending' CHECK(status IN ('Pending','Approved','Rejected')),
    submitted_on TEXT DEFAULT (datetime('now')),
    reviewed_by INTEGER REFERENCES users(id),
    reviewed_on TEXT
);

CREATE TABLE audit_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT,
    details TEXT,
    created_at TEXT DEFAULT (datetime('now'))
);
