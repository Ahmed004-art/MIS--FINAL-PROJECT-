<?php
$title = 'Clearance Slip';
$subtitle = 'Printable financial clearance certificate';
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT c.*, s.student_id AS sid, s.full_name, s.program, s.faculty, s.level, s.semester, u.full_name AS officer FROM clearances c JOIN students s ON s.id=c.student_id LEFT JOIN users u ON u.id=c.issued_by WHERE c.id=?");
$stmt->execute([$id]); $c = $stmt->fetch();
if (!$c) { echo '<div class="card">Slip not found.</div>'; require __DIR__ . '/includes/footer.php'; exit; }
$b = studentBalance((int)$c['student_id']);
$ref = 'SLC-' . str_pad((string)$c['id'], 6, '0', STR_PAD_LEFT) . '-' . date('Y');
?>
<div style="margin-bottom:14px" class="no-print">
    <button class="btn" onclick="window.print()">🖨 Print / Save as PDF</button>
    <a class="btn btn-secondary" href="clearance.php?student=<?= $c['student_id'] ?>">← Back</a>
</div>
<div class="slip">
    <div class="slip-header">
        <div style="font-size:11px;letter-spacing:2px;color:var(--muted);text-transform:uppercase">Republic of Sierra Leone</div>
        <h1>Financial Clearance Slip</h1>
        <p style="margin:4px 0 0;color:var(--muted)">Issued via <?= APP_NAME ?></p>
    </div>
    <div class="slip-grid">
        <div><span>Reference</span><strong><?= e($ref) ?></strong></div>
        <div><span>Date Issued</span><strong><?= e($c['issued_on']) ?></strong></div>
        <div><span>Student Name</span><strong><?= e($c['full_name']) ?></strong></div>
        <div><span>Student ID</span><strong><?= e($c['sid']) ?></strong></div>
        <div><span>Program</span><strong><?= e($c['program']) ?></strong></div>
        <div><span>Faculty</span><strong><?= e($c['faculty']) ?></strong></div>
        <div><span>Level / Semester</span><strong><?= e($c['level']) ?> · <?= e($c['semester']) ?></strong></div>
        <div><span>Purpose</span><strong><?= e($c['purpose']) ?></strong></div>
        <div><span>Total Fees</span><strong><?= money($b['total']) ?></strong></div>
        <div><span>Total Paid</span><strong><?= money($b['paid']) ?></strong></div>
        <div><span>Outstanding Balance</span><strong style="color:<?= $b['balance']>0?'var(--danger)':'var(--success)' ?>"><?= money($b['balance']) ?></strong></div>
        <div><span>Issued By</span><strong><?= e($c['officer']) ?></strong></div>
    </div>
    <div class="slip-stamp"><?= strtoupper($c['status']) ?> CLEARANCE — <?= e($b['percent']) ?>% PAID</div>
    <p style="margin-top:20px;font-size:12px;color:var(--muted);text-align:center">
        This document is a system-generated financial clearance certificate. Verify authenticity with the Finance Office quoting the reference above.
    </p>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
