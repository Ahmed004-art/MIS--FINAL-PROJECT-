<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    verifyCsrf();
    $stmt = db()->prepare('INSERT INTO deferred_assessments(student_id,course_code,course_name,reason,fee) VALUES(?,?,?,?,?)');
    $stmt->execute([
        (int)$_POST['student_id'],
        trim($_POST['course_code']),
        trim($_POST['course_name']),
        trim($_POST['reason']),
        (float)$_POST['fee'],
    ]);
    logAction('submit_deferred','student='.$_POST['student_id']);
    flash('Deferred assessment application submitted.', 'success');
    header('Location: deferred.php'); exit;
}

$students = db()->query('SELECT id, student_id, full_name FROM students ORDER BY full_name')->fetchAll();
$title = 'New Deferred Application';
$subtitle = 'Submit a deferred examination request';
require_once __DIR__ . '/includes/header.php';
?>
<form method="post" class="card" style="max-width:760px">
    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
    <div class="form-grid">
        <div class="full"><label>Student *</label>
            <select name="student_id" required>
                <option value="">— Select student —</option>
                <?php foreach($students as $s): ?>
                <option value="<?= $s['id'] ?>"><?= e($s['student_id']) ?> — <?= e($s['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div><label>Course Code *</label><input name="course_code" required placeholder="e.g. ICT201"></div>
        <div><label>Course Name</label><input name="course_name" placeholder="e.g. Database Systems"></div>
        <div class="full"><label>Reason for Deferral *</label><textarea name="reason" required placeholder="Medical, bereavement, etc."></textarea></div>
        <div><label>Application Fee (<?= CURRENCY ?>)</label><input type="number" step="0.01" min="0" name="fee" value="500"></div>
    </div>
    <div class="form-actions">
        <button class="btn">Submit Application</button>
        <a class="btn btn-secondary" href="deferred.php">Cancel</a>
    </div>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
