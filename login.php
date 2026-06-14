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
        header('Location: dashboard.php'); exit;
    }
    $error = 'Invalid username or password.';
}
?><!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sign in — <?= APP_NAME ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
<form class="login-card" method="post" autocomplete="off">
    <div class="login-brand">
        <div class="mark">SL</div>
        <h1><?= APP_NAME ?></h1>
        <p><?= APP_TAGLINE ?></p>
    </div>
    <?php if($error): ?><div class="flash flash-error" style="margin:0 0 16px"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div style="margin-bottom:14px">
        <label>Username</label>
        <input name="username" required autofocus>
    </div>
    <div style="margin-bottom:18px">
        <label>Password</label>
        <input name="password" type="password" required>
    </div>
    <button class="btn" style="width:100%;justify-content:center">Sign in</button>
    <div class="demo-creds">
        <strong>Demo accounts:</strong><br>
        Admin: <code>admin</code> / <code>admin123</code><br>
        Finance: <code>finance</code> / <code>finance123</code><br>
        Registry: <code>registry</code> / <code>registry123</code>
    </div>
</form>
</body></html>
