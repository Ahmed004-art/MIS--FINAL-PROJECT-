<?php
require_once __DIR__ . '/includes/auth.php';
requireRole(['Admin','Finance']);
$title = 'Payments';
$subtitle = 'Record and verify student payments';
require_once __DIR__ . '/includes/header.php';

$q = trim($_GET['q'] ?? '');
$sql = "SELECT p.*, st.full_name, st.student_id AS sid, u.full_name AS officer
        FROM payments p JOIN students st ON st.id=p.student_id
        LEFT JOIN users u ON u.id=p.recorded_by
        WHERE 1=1";
$args = [];
if ($q !== '') { $sql .= " AND (st.full_name LIKE ? OR st.student_id LIKE ? OR p.reference LIKE ?)"; $args=["%$q%","%$q%","%$q%"]; }
$sql .= " ORDER BY p.id DESC";
$stmt = db()->prepare($sql); $stmt->execute($args);
$rows = $stmt->fetchAll();
$total = array_sum(array_column($rows,'amount'));
?>
<div class="grid grid-3" style="margin-bottom:18px">
    <div class="stat"><div class="stat-label">Total Transactions</div><div class="stat-value"><?= count($rows) ?></div></div>
    <div class="stat accent"><div class="stat-label">Total Collected</div><div class="stat-value"><?= money($total) ?></div></div>
    <div class="stat warn"><div class="stat-label">Today</div><div class="stat-value"><?= money((float)db()->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE paid_on=date('now')")->fetchColumn()) ?></div></div>
</div>
<div class="table-wrap">
    <div class="table-header">
        <h2>Payment Records</h2>
        <form class="toolbar" method="get">
            <input name="q" placeholder="Search student or reference…" value="<?= e($q) ?>">
            <button class="btn btn-secondary btn-sm">Filter</button>
            <a class="btn btn-sm" href="payment_form.php">+ Record Payment</a>
            <a class="btn btn-secondary btn-sm" href="export.php?type=payments">⬇ CSV</a>
        </form>
    </div>
    <table>
        <thead><tr><th>Date</th><th>Student</th><th>Amount</th><th>Method</th><th>Reference</th><th>Officer</th><th>Notes</th></tr></thead>
        <tbody>
        <?php foreach($rows as $r): ?>
            <tr>
                <td><?= e($r['paid_on']) ?></td>
                <td><strong><?= e($r['full_name']) ?></strong><br><small style="color:var(--muted)"><?= e($r['sid']) ?></small></td>
                <td><strong><?= money((float)$r['amount']) ?></strong></td>
                <td><?= e($r['method']) ?></td>
                <td><code><?= e($r['reference']) ?></code></td>
                <td><?= e($r['officer']) ?></td>
                <td style="color:var(--muted)"><?= e($r['notes']) ?></td>
            </tr>
        <?php endforeach; if(!$rows): ?><tr><td colspan="7" style="text-align:center;padding:30px;color:var(--muted)">No payments recorded.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
