<?php
$title = 'Reports';
$subtitle = 'Management reports and data exports';
require_once __DIR__ . '/includes/header.php';

$pdo = db();
$byFaculty = $pdo->query("SELECT faculty, COUNT(*) AS n, SUM(total_fees) AS fees FROM students GROUP BY faculty")->fetchAll();
$byMethod  = $pdo->query("SELECT method, COUNT(*) AS n, SUM(amount) AS amt FROM payments GROUP BY method")->fetchAll();
$overdue = [];
foreach($pdo->query("SELECT id, student_id, full_name, total_fees FROM students") as $r) {
    $b = studentBalance((int)$r['id']);
    if ($b['balance'] > 0) $overdue[] = array_merge($r, $b);
}
usort($overdue, fn($a,$b) => $b['balance'] <=> $a['balance']);
$overdue = array_slice($overdue, 0, 10);
?>
<div class="grid grid-2">
    <div class="card">
        <h3>Students by Faculty</h3>
        <table><thead><tr><th>Faculty</th><th>Students</th><th>Total Fees</th></tr></thead><tbody>
        <?php foreach($byFaculty as $f): ?>
        <tr><td><?= e($f['faculty']) ?: '—' ?></td><td><?= $f['n'] ?></td><td><?= money((float)$f['fees']) ?></td></tr>
        <?php endforeach; ?>
        </tbody></table>
    </div>
    <div class="card">
        <h3>Payments by Method</h3>
        <table><thead><tr><th>Method</th><th>Count</th><th>Amount</th></tr></thead><tbody>
        <?php foreach($byMethod as $m): ?>
        <tr><td><?= e($m['method']) ?></td><td><?= $m['n'] ?></td><td><?= money((float)$m['amt']) ?></td></tr>
        <?php endforeach; if(!$byMethod): ?><tr><td colspan="3" style="color:var(--muted)">No data</td></tr><?php endif; ?>
        </tbody></table>
    </div>
</div>

<div class="table-wrap" style="margin-top:18px">
    <div class="table-header">
        <h2>Top 10 Overdue Balances</h2>
        <div>
            <a class="btn btn-secondary btn-sm" href="export.php?type=students">⬇ Students CSV</a>
            <a class="btn btn-secondary btn-sm" href="export.php?type=payments">⬇ Payments CSV</a>
            <a class="btn btn-secondary btn-sm" href="export.php?type=clearances">⬇ Clearances CSV</a>
        </div>
    </div>
    <table>
        <thead><tr><th>Student ID</th><th>Name</th><th>Total Fees</th><th>Paid</th><th>Balance</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach($overdue as $o): ?>
            <tr>
                <td><strong><?= e($o['student_id']) ?></strong></td>
                <td><?= e($o['full_name']) ?></td>
                <td><?= money($o['total']) ?></td>
                <td><?= money($o['paid']) ?></td>
                <td><strong style="color:var(--danger)"><?= money($o['balance']) ?></strong></td>
                <td><span class="badge badge-<?= strtolower($o['status']) ?>"><?= $o['status'] ?></span></td>
            </tr>
        <?php endforeach; if(!$overdue): ?><tr><td colspan="6" style="text-align:center;padding:24px;color:var(--success)">🎉 No overdue balances!</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
