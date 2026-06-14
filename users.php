<?php
require_once __DIR__ . '/includes/auth.php';
requireRole(['Admin']);
$title = 'Users';
$subtitle = 'Manage system users and roles';
require_once __DIR__ . '/includes/header.php';
$rows = db()->query('SELECT * FROM users ORDER BY role, username')->fetchAll();
?>
<div class="table-wrap">
    <div class="table-header">
        <h2><?= count($rows) ?> Users</h2>
        <a class="btn btn-sm" href="user_form.php">+ Add User</a>
    </div>
    <table>
        <thead><tr><th>Username</th><th>Full Name</th><th>Role</th><th>Created</th><th></th></tr></thead>
        <tbody>
        <?php foreach($rows as $u): ?>
            <tr>
                <td><strong><?= e($u['username']) ?></strong></td>
                <td><?= e($u['full_name']) ?></td>
                <td><span class="badge badge-<?= strtolower($u['role']) ?>"><?= e($u['role']) ?></span></td>
                <td><?= e(substr($u['created_at'],0,10)) ?></td>
                <td><a class="btn btn-ghost btn-sm" href="user_form.php?id=<?= $u['id'] ?>">Edit</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
