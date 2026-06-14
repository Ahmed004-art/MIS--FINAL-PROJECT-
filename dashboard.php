<?php
$title = 'Dashboard';
$subtitle = 'Real-time financial clearance overview';
require_once __DIR__ . '/includes/header.php';
$s = dashboardStats();
$recent = db()->query("SELECT p.*, st.full_name, st.student_id AS sid FROM payments p JOIN students st ON st.id=p.student_id ORDER BY p.id DESC LIMIT 6")->fetchAll();
$pending = db()->query("SELECT d.*, st.full_name, st.student_id AS sid FROM deferred_assessments d JOIN students st ON st.id=d.student_id WHERE d.status='Pending' ORDER BY d.id DESC LIMIT 5")->fetchAll();
?>
<div class="grid grid-4">
    <div class="stat"><div class="stat-label">Total Students</div><div class="stat-value"><?= $s['students'] ?></div><div class="stat-sub">Enrolled in system</div></div>
    <div class="stat accent"><div class="stat-label">Total Fees</div><div class="stat-value"><?= money($s['fees']) ?></div><div class="stat-sub">Expected revenue</div></div>
    <div class="stat"><div class="stat-label">Collected</div><div class="stat-value"><?= money($s['payments']) ?></div><div class="stat-sub"><?= $s['fees']>0 ? round($s['payments']/$s['fees']*100,1) : 0 ?>% of fees</div></div>
    <div class="stat danger"><div class="stat-label">Outstanding</div><div class="stat-value"><?= money($s['outstanding']) ?></div><div class="stat-sub">Awaiting payment</div></div>
</div>

<div class="grid grid-3" style="margin-top:18px">
    <div class="card">
        <h3>Clearance Status</h3>
        <canvas id="clearanceChart" height="180"></canvas>
    </div>
    <div class="card" style="grid-column: span 2">
        <h3>Fee Collection Progress</h3>
        <canvas id="collectionChart" height="180"></canvas>
    </div>
</div>

<div class="grid grid-2" style="margin-top:18px">
    <div class="table-wrap">
        <div class="table-header"><h2>Recent Payments</h2><a class="btn btn-sm btn-secondary" href="payments.php">View all</a></div>
        <table>
            <thead><tr><th>Student</th><th>Amount</th><th>Method</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach($recent as $r): ?>
                <tr>
                    <td><strong><?= e($r['full_name']) ?></strong><br><small style="color:var(--muted)"><?= e($r['sid']) ?></small></td>
                    <td><strong><?= money((float)$r['amount']) ?></strong></td>
                    <td><?= e($r['method']) ?></td>
                    <td><?= e($r['paid_on']) ?></td>
                </tr>
            <?php endforeach; if(!$recent): ?><tr><td colspan="4" style="text-align:center;color:var(--muted);padding:24px">No payments yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="table-wrap">
        <div class="table-header"><h2>Pending Deferred Assessments</h2><a class="btn btn-sm btn-secondary" href="deferred.php">Review</a></div>
        <table>
            <thead><tr><th>Student</th><th>Course</th><th>Fee</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach($pending as $p): ?>
                <tr>
                    <td><strong><?= e($p['full_name']) ?></strong><br><small style="color:var(--muted)"><?= e($p['sid']) ?></small></td>
                    <td><?= e($p['course_code']) ?><br><small style="color:var(--muted)"><?= e($p['course_name']) ?></small></td>
                    <td><?= money((float)$p['fee']) ?></td>
                    <td><span class="badge badge-pending">Pending</span></td>
                </tr>
            <?php endforeach; if(!$pending): ?><tr><td colspan="4" style="text-align:center;color:var(--muted);padding:24px">No pending applications.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
window.addEventListener('load', () => {
  new Chart(document.getElementById('clearanceChart'), {
    type: 'doughnut',
    data: {
      labels: ['Full Clearance','Provisional','Denied'],
      datasets: [{
        data: [<?= $s['cleared'] ?>, <?= $s['provisional'] ?>, <?= $s['denied'] ?>],
        backgroundColor: ['#16a34a','#d97706','#dc2626'],
        borderWidth: 0
      }]
    },
    options: { plugins: { legend: { position: 'bottom' } }, cutout: '65%' }
  });
  new Chart(document.getElementById('collectionChart'), {
    type: 'bar',
    data: {
      labels: ['Expected Fees','Collected','Outstanding'],
      datasets: [{
        data: [<?= $s['fees'] ?>, <?= $s['payments'] ?>, <?= $s['outstanding'] ?>],
        backgroundColor: ['#1e4faa','#0a7d3b','#dc2626'],
        borderRadius: 8
      }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
  });
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
