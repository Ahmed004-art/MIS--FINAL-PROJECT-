<?php
require_once __DIR__ . '/includes/auth.php';
requireRole(['Admin','Finance']);
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    verifyCsrf();
    $sid = (int)$_POST['student_id'];
    $stmt = db()->prepare('INSERT INTO payments(student_id,amount,method,reference,paid_on,recorded_by,notes) VALUES(?,?,?,?,?,?,?)');
    $stmt->execute([
        $sid,
        (float)$_POST['amount'],
        $_POST['method'],
        trim($_POST['reference']),
        $_POST['paid_on'],
        currentUser()['id'],
        trim($_POST['notes']),
    ]);
    $b = studentBalance($sid);
    logAction('record_payment',"student=$sid amount={$_POST['amount']}");
    flash('Payment recorded. New balance: ' . money($b['balance']) . ' (' . $b['status'] . ' clearance)', 'success');
    header('Location: payments.php'); exit;
}

$students = db()->query('SELECT id, student_id, full_name FROM students ORDER BY full_name')->fetchAll();
$title = 'Record Payment'; $subtitle = 'Add a new payment transaction';
require_once __DIR__ . '/includes/header.php';
?>
<form method="post" class="card" style="max-width:760px">
    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
    <div class="form-grid">
        <div class="full"><label>Student *</label>
            <select name="student_id" required>
                <option value="">— Select student —</option>
                <?php foreach($students as $s): ?>
                <option value="<?= $s['id'] ?>" <?= ((int)($_GET['student']??0)===(int)$s['id'])?'selected':'' ?>><?= e($s['student_id']) ?> — <?= e($s['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div><label>Amount (<?= CURRENCY ?>) *</label><input type="number" step="0.01" min="0.01" name="amount" required></div>
        <div><label>Payment Date *</label><input type="date" name="paid_on" required value="<?= date('Y-m-d') ?>"></div>
        <div><label>Method *</label>
            <select name="method" required>
                <?php foreach(['Cash','Bank Transfer','Mobile Money','Cheque','Card'] as $m): ?>
                <option><?= $m ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div><label>Reference / Receipt #</label><input name="reference" placeholder="TXN-12345"></div>
        <div class="full"><label>Notes</label><textarea name="notes" placeholder="Optional notes"></textarea></div>
    </div>
    <div class="form-actions">
        <button class="btn">Save Payment</button>
        <a class="btn btn-secondary" href="payments.php">Cancel</a>
    </div>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
