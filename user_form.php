<?php
require_once __DIR__ . '/includes/auth.php';
requireRole(['Admin']);
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$user = ['id'=>0,'username'=>'','full_name'=>'','role'=>'Finance'];
if ($id) {
    $s = db()->prepare('SELECT * FROM users WHERE id=?'); $s->execute([$id]);
    $f = $s->fetch(); if ($f) $user = $f;
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    verifyCsrf();
    if (($_POST['_action'] ?? '')==='delete' && $id !== (int)currentUser()['id']) {
        db()->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
        logAction('delete_user',"#$id"); flash('User deleted','success');
        header('Location: users.php'); exit;
    }
    $username = trim($_POST['username']);
    $full = trim($_POST['full_name']);
    $role = $_POST['role'];
    $pass = $_POST['password'] ?? '';
    if ($id) {
        if ($pass !== '') {
            db()->prepare('UPDATE users SET username=?,full_name=?,role=?,password_hash=? WHERE id=?')
                ->execute([$username,$full,$role,password_hash($pass, PASSWORD_DEFAULT),$id]);
        } else {
            db()->prepare('UPDATE users SET username=?,full_name=?,role=? WHERE id=?')->execute([$username,$full,$role,$id]);
        }
        logAction('update_user',"#$id"); flash('User updated','success');
    } else {
        db()->prepare('INSERT INTO users(username,password_hash,full_name,role) VALUES(?,?,?,?)')
            ->execute([$username, password_hash($pass ?: 'changeme', PASSWORD_DEFAULT), $full, $role]);
        logAction('create_user', $username); flash('User created','success');
    }
    header('Location: users.php'); exit;
}

$title = $id ? 'Edit User' : 'Add User';
require_once __DIR__ . '/includes/header.php';
?>
<form method="post" class="card" style="max-width:640px">
    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
    <div class="form-grid">
        <div><label>Username *</label><input name="username" required value="<?= e($user['username']) ?>"></div>
        <div><label>Full Name *</label><input name="full_name" required value="<?= e($user['full_name']) ?>"></div>
        <div><label>Role *</label>
            <select name="role" required>
                <?php foreach(['Admin','Finance','Registry'] as $r): ?>
                <option <?= $user['role']===$r?'selected':'' ?>><?= $r ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div><label>Password <?= $id?'(leave blank to keep)':'*' ?></label><input type="password" name="password" <?= $id?'':'required' ?>></div>
    </div>
    <div class="form-actions">
        <button class="btn"><?= $id?'Update':'Create' ?> User</button>
        <a class="btn btn-secondary" href="users.php">Cancel</a>
        <?php if ($id && $id !== (int)currentUser()['id']): ?>
        <button class="btn btn-danger" name="_action" value="delete" data-confirm="Delete this user?" style="margin-left:auto">Delete</button>
        <?php endif; ?>
    </div>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
