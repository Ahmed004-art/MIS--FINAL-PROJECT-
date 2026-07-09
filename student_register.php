<?php
// student_register.php — Self-service student account creation
require_once __DIR__ . '/includes/auth.php';

// Already logged in? Redirect
if (currentUser()) {
    header('Location: ' . (currentUser()['role'] === 'Student' ? 'student_dashboard.php' : 'dashboard.php'));
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = trim($_POST['student_id'] ?? '');
    $pass      = $_POST['password'] ?? '';
    $pass2     = $_POST['password2'] ?? '';

    if ($studentId === '' || $pass === '') {
        $error = 'Student ID and password are required.';
    } elseif (!preg_match('/^90500\d{4}$/', $studentId)) {
        $error = 'Student ID must start with 90500 followed by exactly 4 digits (e.g., 905001234).';
    } elseif (strlen($pass) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($pass !== $pass2) {
        $error = 'Passwords do not match.';
    } else {
        // Verify the student exists in the students table
        $sStmt = db()->prepare("SELECT * FROM students WHERE student_id = ?");
        $sStmt->execute([$studentId]);
        $student = $sStmt->fetch();

        if (!$student) {
            $error = 'No student record found with that Student ID. Please contact the Finance Office.';
        } else {
            // Check if a login account already exists
            $uStmt = db()->prepare("SELECT id FROM users WHERE username = ?");
            $uStmt->execute([$studentId]);
            $existing = $uStmt->fetch();

            if ($existing) {
                $error = 'An account already exists for this Student ID. Please log in instead.';
            } else {
                // Create the account
                $ins = db()->prepare("INSERT INTO users (username, password_hash, full_name, role) VALUES (?, ?, ?, 'Student')");
                $ins->execute([$studentId, password_hash($pass, PASSWORD_DEFAULT), $student['full_name']]);
                logAction('student_register', "Student $studentId registered");
                $success = true;
            }
        }
    }
}
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Registration — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
<form class="login-card" method="post" autocomplete="off">
    <div class="login-brand">
        <div class="mark"><img src="assets/luct_logo.png" alt="LUCT Logo"></div>
        <h1>Student Portal</h1>
        <p>Create your account to access fees &amp; credentials</p>
    </div>

    <?php if ($success): ?>
        <div class="flash flash-success">
            ✓ Account created successfully! You can now <a href="login.php" style="color:inherit;font-weight:700;text-decoration:underline">sign in</a>.
        </div>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="flash flash-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <div style="margin-bottom:14px">
            <label>Student ID *</label>
            <input name="student_id" required autofocus placeholder="e.g. 905001234"
                   value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>">
            <small style="color:var(--muted);display:block;margin-top:5px">
                Use the 9-digit Student ID (starting with 90500) issued by the Registry Office.
            </small>
        </div>
        <div style="margin-bottom:14px">
            <label>Password *</label>
            <input name="password" type="password" required placeholder="Min. 6 characters">
        </div>
        <div style="margin-bottom:20px">
            <label>Confirm Password *</label>
            <input name="password2" type="password" required placeholder="Repeat password">
        </div>
        <button class="btn" style="width:100%;justify-content:center">Create Account</button>
    <?php endif; ?>

    <div class="demo-creds" style="text-align:center; margin-top:18px">
        Already have an account?
        <a href="login.php" style="color:var(--primary);font-weight:600">Sign in here</a>
    </div>
</form>
</body>
</html>
