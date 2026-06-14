<?php
$title = 'Clearance';
$subtitle = 'Check clearance status and generate slips';
require_once __DIR__ . '/includes/header.php';

$sid = (int)($_GET['student'] ?? 0);
$students = db()->query('SELECT id, student_id, full_name, program FROM students ORDER BY full_name')->fetchAll();
$student = null; $b = null; $history = [];
if ($sid) {
    $s = db()->prepare('SELECT * FROM students WHERE id=?'); $s->execute([$sid]); $student = $s->fetch();
    if ($student) {
        $b = studentBalance($sid);
        $h = db()->prepare("SELECT c.*, u.full_name AS officer FROM clearances c LEFT JOIN users u ON u.id=c.issued_by WHERE c.student_id=? ORDER BY c.id DESC");
        $h->execute([$sid]); $history = $h->fetchAll();
    }
}

if ($_SERVER['REQUEST_METHOD']==='POST' && $student) {
    verifyCsrf();
    $stmt = db()->prepare('INSERT INTO clearances(student_id,status,purpose,issued_by) VALUES(?,?,?,?)');
    $stmt->execute([$sid, $b['status'], trim($_POST['purpose']) ?: 'Examination', currentUser()['id']]);
    $cid = (int)db()->lastInsertId();
    logAction('issue_clearance', "student=$sid status={$b['status']}");
    header('Location: clearance_slip.php?id=' . $cid); exit;
}
?>
<div class="card" style="margin-bottom:18px">
    <form method="get" class="toolbar">
        <select name="student" required onchange="this.form.submit()">
            <option value="">— Select student to check clearance —</option>
            <?php foreach($students as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $sid===(int)$s['id']?'selected':'' ?>><?= e($s['student_id']) ?> — <?= e($s['full_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?php if ($student && $b): ?>
<div class="grid grid-3" style="margin-bottom:18px">
    <div class="stat"><div class="stat-label">Total Fees</div><div class="stat-value"><?= money($b['total']) ?></div></div>
    <div class="stat accent"><div class="stat-label">Paid</div><div class="stat-value"><?= money($b['paid']) ?></div><div class="stat-sub"><?= $b['percent'] ?>% complete</div></div>
    <div class="stat <?= $b['balance']>0?'danger':'' ?>"><div class="stat-label">Balance</div><div class="stat-value"><?= money($b['balance']) ?></div></div>
</div>

<div class="card">
    <h3>Clearance Decision</h3>
    <p><strong><?= e($student['full_name']) ?></strong> — <?= e($student['student_id']) ?> — <?= e($student['program']) ?></p>
    <div class="progress" style="margin:12px 0"><div style="width:<?= min(100,$b['percent']) ?>%"></div></div>
    <p style="font-size:18px">Status: <span class="badge badge-<?= strtolower($b['status']) ?>" style="font-size:13px;padding:6px 14px"><?= $b['status'] ?> Clearance</span></p>
    <?php if ($b['status']==='Denied'): ?>
        <div class="flash flash-error" style="margin:14px 0">⚠ This student does not meet the minimum 70% payment threshold. <strong>No Slip = No Exam.</strong></div>
    <?php elseif ($b['status']==='Provisional'): ?>
        <div class="flash flash-info" style="margin:14px 0">ℹ Provisional clearance — student must settle the remaining balance before final results are released.</div>
    <?php else: ?>
        <div class="flash flash-success" style="margin:14px 0">✓ Student is fully cleared for examinations and graduation.</div>
    <?php endif; ?>
    <?php if (in_array($me['role'],['Admin','Registry','Finance'])): ?>
    <form method="post" style="display:flex;gap:10px;align-items:end;margin-top:14px">
        <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
        <div style="flex:1"><label>Purpose</label><input name="purpose" value="Examination" required></div>
        <button class="btn">Issue Clearance Slip</button>
    </form>
    <?php endif; ?>
</div>

<?php if ($history): ?>
<div class="table-wrap" style="margin-top:18px">
    <div class="table-header"><h2>Clearance History</h2></div>
    <table>
        <thead><tr><th>Date</th><th>Status</th><th>Purpose</th><th>Issued By</th><th></th></tr></thead>
        <tbody>
        <?php foreach($history as $h): ?>
            <tr>
                <td><?= e($h['issued_on']) ?></td>
                <td><span class="badge badge-<?= strtolower($h['status']) ?>"><?= e($h['status']) ?></span></td>
                <td><?= e($h['purpose']) ?></td>
                <td><?= e($h['officer']) ?></td>
                <td><a class="btn btn-ghost btn-sm" href="clearance_slip.php?id=<?= $h['id'] ?>" target="_blank">View slip</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php else: ?>
<div class="card" style="text-align:center;color:var(--muted);padding:40px">Select a student above to view clearance status.</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
