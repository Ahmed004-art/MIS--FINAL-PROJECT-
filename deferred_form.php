<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/functions.php';

$me = currentUser();
$isStudent = (($me['role'] ?? '') === 'Student');
$myStudent = null;

if ($isStudent) {
    $stmt = db()->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$me['username']]);
    $myStudent = $stmt->fetch();
    if (!$myStudent) {
        die("Student profile not found for username: " . e($me['username']));
    }
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    verifyCsrf();
    $studentId = $isStudent ? (int)$myStudent['id'] : (int)$_POST['student_id'];
    $stmt = db()->prepare('INSERT INTO deferred_assessments(student_id,course_code,course_name,reason,fee) VALUES(?,?,?,?,?)');
    $stmt->execute([
        $studentId,
        trim($_POST['course_code']),
        trim($_POST['course_name']),
        trim($_POST['reason']),
        (float)$_POST['fee'],
    ]);
    logAction('submit_deferred','student='.$studentId);
    flash('Deferred assessment application submitted.', 'success');
    header('Location: ' . ($isStudent ? 'student_dashboard.php' : 'deferred.php')); exit;
}

$students = [];
if (!$isStudent) {
    $students = db()->query('SELECT id, student_id, full_name FROM students ORDER BY full_name')->fetchAll();
}
$title = 'New Deferred Application';
$subtitle = 'Submit a deferred examination request';
require_once __DIR__ . '/includes/header.php';
?>
<form method="post" class="card" style="max-width:760px">
    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
    <div class="form-grid">
        <?php if ($isStudent): ?>
            <div class="full"><label>Student</label>
                <input type="text" readonly value="<?= e($myStudent['student_id']) ?> — <?= e($myStudent['full_name']) ?>">
            </div>
        <?php else: ?>
            <div class="full"><label>Student *</label>
                <select name="student_id" required>
                    <option value="">— Select student —</option>
                    <?php foreach($students as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= e($s['student_id']) ?> — <?= e($s['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        <div><label>Course Code *</label><input name="course_code" required placeholder="e.g. ICT201"></div>
        <div><label>Course Name</label><input name="course_name" placeholder="e.g. Database Systems"></div>
        <div class="full"><label>Reason for Deferral *</label><textarea name="reason" required placeholder="Medical, bereavement, etc."></textarea></div>
        <div><label>Application Fee (<?= CURRENCY ?>)</label><input type="number" step="0.01" min="0" name="fee" value="500"></div>
    </div>
    <div class="form-actions">
        <button class="btn">Submit Application</button>
        <a class="btn btn-secondary" href="<?= $isStudent ? 'student_dashboard.php' : 'deferred.php' ?>">Cancel</a>
    </div>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
