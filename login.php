<?php
require_once __DIR__ . '/includes/auth.php';
if (currentUser()) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    $stmt = db()->prepare('SELECT * FROM users WHERE username=?');
    $stmt->execute([$u]);
    $user = $stmt->fetch();
    if ($user && password_verify($p, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        logAction('login', "User {$user['username']} signed in");
        $dest = ($user['role'] === 'Student') ? 'student_dashboard.php' : 'dashboard.php';
        header('Location: ' . $dest); exit;
    }
    $error = 'Invalid username or password.';
}
?><!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sign in — <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
.demo-section {
    margin-top: 24px;
    padding: 16px;
    background: var(--surface-2);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border);
}
.demo-section h3 {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--text-dim);
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    text-align: center;
}
.demo-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.demo-btn {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 12px 8px;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text);
    text-align: center;
    cursor: pointer;
    transition: all var(--transition);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
}
.demo-btn:hover {
    border-color: var(--primary);
    background: var(--primary-light);
    color: var(--primary);
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}
.demo-btn span.role-label {
    font-size: 0.7rem;
    color: var(--muted);
    font-weight: 500;
}
.demo-btn:hover span.role-label {
    color: var(--primary);
}
.highlight-flash {
    animation: flash-input 0.3s ease-out;
}
@keyframes flash-input {
    0% { background-color: var(--primary-light); border-color: var(--primary); }
    100% { background-color: transparent; }
}
</style>
</head>
<body class="login-page">
<form class="login-card" method="post" autocomplete="off">
    <div class="login-brand">
        <div class="mark"><img src="assets/luct_logo.png" alt="LUCT Logo"></div>
        <h1><?= APP_NAME ?></h1>
        <p><?= APP_TAGLINE ?></p>
    </div>
    <?php if($error): ?><div class="flash flash-error" style="margin:0 0 16px"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div style="margin-bottom:14px">
        <label>Username / Student ID</label>
        <input name="username" required autofocus placeholder="admin  or  905000001">
    </div>
    <div style="margin-bottom:18px">
        <label>Password</label>
        <input name="password" type="password" required>
    </div>
    <button class="btn" style="width:100%;justify-content:center">Sign in</button>

    <!-- Student self-registration CTA -->
    <div style="text-align:center;margin-top:18px;padding:14px 0;border-top:1px solid var(--border)">
        <p style="font-size:.88rem;color:var(--muted);margin-bottom:10px">Are you a student? First time here?</p>
        <a href="student_register.php" class="btn btn-outline" style="width:100%;justify-content:center">
            🎓 Register / Create Student Account
        </a>
    </div>

    <!-- Quick Login Buttons Section -->
    <div class="demo-section">
        <h3>⚡ Fill Demo Credentials</h3>
        <div class="demo-grid">
            <button type="button" class="demo-btn" onclick="quickLogin('admin', 'admin123')">
                <span>🔒 ADMIN</span>
                <span class="role-label">System Admin</span>
            </button>
            <button type="button" class="demo-btn" onclick="quickLogin('registry', 'registry123')">
                <span>📝 REGISTRY</span>
                <span class="role-label">Registry Officer</span>
            </button>
            <button type="button" class="demo-btn" onclick="quickLogin('finance', 'finance123')">
                <span>💰 FINANCES</span>
                <span class="role-label">Finance Officer</span>
            </button>
            <button type="button" class="demo-btn" onclick="quickLogin('905000001', 'student123')">
                <span>🎓 STUDENTT</span>
                <span class="role-label">905000001</span>
            </button>
        </div>
    </div>
</form>

<script>
function quickLogin(user, pass) {
    var uInput = document.querySelector('input[name="username"]');
    var pInput = document.querySelector('input[name="password"]');
    
    uInput.value = user;
    pInput.value = pass;
    
    uInput.classList.remove('highlight-flash');
    pInput.classList.remove('highlight-flash');
    
    void uInput.offsetWidth; // Trigger reflow to restart animation
    
    uInput.classList.add('highlight-flash');
    pInput.classList.add('highlight-flash');
}
</script>
</body></html>
