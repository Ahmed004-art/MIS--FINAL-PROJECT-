<?php
$title = 'Students';
$subtitle = 'Manage student records and view balances';
require_once __DIR__ . '/includes/header.php';

$q = trim($_GET['q'] ?? '');
$faculty = trim($_GET['faculty'] ?? '');
$sql = "SELECT * FROM students WHERE 1=1";
$args = [];
if ($q !== '') { $sql .= " AND (full_name LIKE ? OR student_id LIKE ? OR email LIKE ?)"; $args[] = "%$q%"; $args[] = "%$q%"; $args[] = "%$q%"; }
if ($faculty !== '') { $sql .= " AND faculty = ?"; $args[] = $faculty; }
$sql .= " ORDER BY full_name";
$stmt = db()->prepare($sql); $stmt->execute($args);
$students = $stmt->fetchAll();
$faculties = db()->query("SELECT DISTINCT faculty FROM students WHERE faculty<>'' ORDER BY faculty")->fetchAll(PDO::FETCH_COLUMN);
?>
<div class="table-wrap">
    <div class="table-header">
        <h2><?= count($students) ?> Students</h2>
        <form class="toolbar" method="get">
            <input name="q" placeholder="Search name, ID, email…" value="<?= e($q) ?>">
            <select name="faculty">
                <option value="">All faculties</option>
                <?php foreach($faculties as $f): ?>
                    <option <?= $f===$faculty?'selected':'' ?>><?= e($f) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-secondary btn-sm">Filter</button>
            <?php if (in_array($me['role'],['Admin','Registry'])): ?>
            <a class="btn btn-sm" href="student_form.php">+ Add Student</a>
            <?php endif; ?>
        </form>
    </div>
    <table>
        <thead><tr><th>Student ID</th><th>Name</th><th>Program</th><th>Total Fees</th><th>Paid</th><th>Balance</th><th>Clearance</th><th></th></tr></thead>
        <tbody>
        <?php foreach($students as $st): $b = studentBalance((int)$st['id']); ?>
            <tr>
                <td><strong><?= e($st['student_id']) ?></strong></td>
                <td><?= e($st['full_name']) ?><br><small style="color:var(--muted)"><?= e($st['email']) ?></small></td>
                <td><?= e($st['program']) ?><br><small style="color:var(--muted)"><?= e($st['level']) ?> · <?= e($st['semester']) ?></small></td>
                <td><?= money($b['total']) ?></td>
                <td><?= money($b['paid']) ?></td>
                <td><strong style="color:<?= $b['balance']>0?'var(--danger)':'var(--success)' ?>"><?= money($b['balance']) ?></strong></td>
                <td><span class="badge badge-<?= strtolower($b['status']) ?>"><?= $b['status'] ?></span><div class="progress" style="margin-top:6px;width:80px"><div style="width:<?= min(100,$b['percent']) ?>%"></div></div></td>
                <td style="text-align:right; white-space:nowrap">
                    <a class="btn btn-ghost btn-sm" href="clearance.php?student=<?= $st['id'] ?>">View</a>
                    <?php if (in_array($me['role'],['Admin','Registry'])): ?>
                    <a class="btn btn-ghost btn-sm" href="student_form.php?id=<?= $st['id'] ?>">Edit</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; if(!$students): ?><tr><td colspan="8" style="text-align:center;padding:30px;color:var(--muted)">No students found.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
