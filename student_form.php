<?php
require_once __DIR__ . '/includes/auth.php';
requireRole(['Admin','Registry']);
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$student = ['id'=>0,'student_id'=>'','full_name'=>'','gender'=>'','email'=>'','phone'=>'','program'=>'','faculty'=>'','level'=>'','semester'=>'','total_fees'=>0];
if ($id) {
    $s = db()->prepare('SELECT * FROM students WHERE id=?'); $s->execute([$id]); $found = $s->fetch();
    if ($found) $student = $found;
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    verifyCsrf();
    if ($_POST['_action'] ?? '' === 'delete') {
        db()->prepare('DELETE FROM students WHERE id=?')->execute([$id]);
        logAction('delete_student',"#$id");
        flash('Student deleted.','success');
        header('Location: students.php'); exit;
    }
    $data = [
        trim($_POST['student_id']),
        trim($_POST['full_name']),
        $_POST['gender'] ?? '',
        trim($_POST['email']),
        trim($_POST['phone']),
        trim($_POST['program']),
        trim($_POST['faculty']),
        trim($_POST['level']),
        trim($_POST['semester']),
        (float)$_POST['total_fees'],
    ];
    if ($id) {
        $data[] = $id;
        db()->prepare('UPDATE students SET student_id=?,full_name=?,gender=?,email=?,phone=?,program=?,faculty=?,level=?,semester=?,total_fees=? WHERE id=?')->execute($data);
        logAction('update_student',"#$id");
        flash('Student updated.','success');
    } else {
        db()->prepare('INSERT INTO students(student_id,full_name,gender,email,phone,program,faculty,level,semester,total_fees) VALUES(?,?,?,?,?,?,?,?,?,?)')->execute($data);
        logAction('create_student',$data[0]);
        flash('Student added.','success');
    }
    header('Location: students.php'); exit;
}

$title = $id ? 'Edit Student' : 'Add Student';
$subtitle = $id ? e($student['student_id']) : 'Register a new student record';
require_once __DIR__ . '/includes/header.php';
?>
<form method="post" class="card" style="max-width:900px">
    <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
    <div class="form-grid">
        <div><label>Student ID *</label><input name="student_id" required value="<?= e($student['student_id']) ?>" placeholder="LU/2024/123"></div>
        <div><label>Full Name *</label><input name="full_name" required value="<?= e($student['full_name']) ?>"></div>
        <div><label>Gender</label>
            <select name="gender">
                <option value="">—</option>
                <?php foreach(['Male','Female','Other'] as $g): ?>
                <option <?= $student['gender']===$g?'selected':'' ?>><?= $g ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div><label>Email</label><input type="email" name="email" value="<?= e($student['email']) ?>"></div>
        <div><label>Phone</label><input name="phone" value="<?= e($student['phone']) ?>"></div>
        <div><label>Faculty</label><input name="faculty" value="<?= e($student['faculty']) ?>" placeholder="ICT / Business / Arts"></div>
        <div class="full"><label>Program</label><input name="program" value="<?= e($student['program']) ?>" placeholder="BSc Information Technology"></div>
        <div><label>Level</label><input name="level" value="<?= e($student['level']) ?>" placeholder="Year 2"></div>
        <div><label>Semester</label><input name="semester" value="<?= e($student['semester']) ?>" placeholder="Semester 4"></div>
        <div class="full"><label>Total Fees (<?= CURRENCY ?>) *</label><input type="number" step="0.01" min="0" name="total_fees" required value="<?= e((string)$student['total_fees']) ?>"></div>
    </div>
    <div class="form-actions">
        <button class="btn"><?= $id ? 'Update' : 'Create' ?> Student</button>
        <a class="btn btn-secondary" href="students.php">Cancel</a>
        <?php if ($id && $me['role']==='Admin'): ?>
        <button class="btn btn-danger" name="_action" value="delete" data-confirm="Delete this student and all related records?" style="margin-left:auto">Delete</button>
        <?php endif; ?>
    </div>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
