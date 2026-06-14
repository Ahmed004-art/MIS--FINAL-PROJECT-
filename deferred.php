<?php
$title = 'Deferred Assessments';
$subtitle = 'Submit and approve deferred exam applications';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    verifyCsrf();
    $action = $_POST['_action'] ?? '';
    $id = (int)$_POST['id'];
    if (in_array($action,['Approved','Rejected']) && in_array($me['role'],['Admin','Registry'])) {
        db()->prepare("UPDATE deferred_assessments SET status=?, reviewed_by=?, reviewed_on=datetime('now') WHERE id=?")
            ->execute([$action, $me['id'], $id]);
        logAction('review_deferred',"#$id => $action");
        flash("Application $action.", 'success');
    }
    header('Location: deferred.php'); exit;
}

$rows = db()->query("SELECT d.*, s.full_name, s.student_id AS sid, u.full_name AS reviewer
    FROM deferred_assessments d JOIN students s ON s.id=d.student_id
    LEFT JOIN users u ON u.id=d.reviewed_by ORDER BY d.id DESC")->fetchAll();
?>
<div class="grid grid-2" style="margin-bottom:18px">
    <a class="btn" href="deferred_form.php">+ New Deferred Application</a>
    <div></div>
</div>
<div class="table-wrap">
    <div class="table-header"><h2><?= count($rows) ?> Applications</h2></div>
    <table>
        <thead><tr><th>Submitted</th><th>Student</th><th>Course</th><th>Reason</th><th>Fee</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach($rows as $r): ?>
            <tr>
                <td><?= e(substr($r['submitted_on'],0,10)) ?></td>
                <td><strong><?= e($r['full_name']) ?></strong><br><small style="color:var(--muted)"><?= e($r['sid']) ?></small></td>
                <td><strong><?= e($r['course_code']) ?></strong><br><small style="color:var(--muted)"><?= e($r['course_name']) ?></small></td>
                <td style="max-width:260px;color:var(--muted)"><?= e($r['reason']) ?></td>
                <td><?= money((float)$r['fee']) ?></td>
                <td><span class="badge badge-<?= strtolower($r['status']) ?>"><?= e($r['status']) ?></span>
                    <?php if ($r['reviewer']): ?><br><small style="color:var(--muted)">by <?= e($r['reviewer']) ?></small><?php endif; ?>
                </td>
                <td>
                    <?php if ($r['status']==='Pending' && in_array($me['role'],['Admin','Registry'])): ?>
                    <form method="post" style="display:inline">
                        <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <button name="_action" value="Approved" class="btn btn-sm">Approve</button>
                        <button name="_action" value="Rejected" class="btn btn-sm btn-danger" data-confirm="Reject this application?">Reject</button>
                    </form>
                    <?php else: ?><span style="color:var(--muted)">—</span><?php endif; ?>
                </td>
            </tr>
        <?php endforeach; if(!$rows): ?><tr><td colspan="7" style="text-align:center;padding:30px;color:var(--muted)">No applications yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
