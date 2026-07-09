<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$me = currentUser();
if (($me['role'] ?? '') !== 'Student') {
    header('Location: dashboard.php');
    exit;
}

// Get student record
$sStmt = db()->prepare("SELECT * FROM students WHERE student_id = ?");
$sStmt->execute([$me['username']]);
$student = $sStmt->fetch();

if (!$student) {
    $title = 'Error';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="card" style="text-align:center;padding:40px;color:var(--danger)">';
    echo '<h3>Student profile not found</h3>';
    echo '<p style="margin-top:10px">Your user account is not linked to any student profile. Please contact the administrator.</p>';
    echo '</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$sid = (int)$student['id'];
$b = studentBalance($sid);

// Get payment history
$payStmt = db()->prepare("SELECT * FROM payments WHERE student_id = ? ORDER BY id DESC");
$payStmt->execute([$sid]);
$payments = $payStmt->fetchAll();

// Get deferred applications
$defStmt = db()->prepare("SELECT d.*, u.full_name AS reviewer
                          FROM deferred_assessments d
                          LEFT JOIN users u ON u.id=d.reviewed_by
                          WHERE d.student_id = ? ORDER BY d.id DESC");
$defStmt->execute([$sid]);
$deferred = $defStmt->fetchAll();

// Get latest clearance record
$clrStmt = db()->prepare("SELECT id FROM clearances WHERE student_id = ? ORDER BY id DESC LIMIT 1");
$clrStmt->execute([$sid]);
$latestClearanceId = $clrStmt->fetchColumn();

$title = 'Student Dashboard';
$subtitle = 'Welcome back, ' . e($student['full_name']);
require_once __DIR__ . '/includes/header.php';
?>

<!-- Profile & Clearance Overview Banner -->
<div class="welcome-banner">
    <div class="welcome-text">
        <h2><?= e($student['full_name']) ?> (<?= e($student['student_id']) ?>)</h2>
        <p><?= e($student['program']) ?> &middot; <?= e($student['faculty']) ?> &middot; <?= e($student['level']) ?> &middot; <?= e($student['semester']) ?></p>
    </div>
    <div class="quick-actions">
        <?php if ($latestClearanceId): ?>
            <a class="btn" href="clearance_slip.php?id=<?= $latestClearanceId ?>" target="_blank">🖨 View Clearance Slip</a>
        <?php endif; ?>
        <a class="btn btn-secondary" href="deferred_form.php">+ Apply Deferral</a>
    </div>
</div>

<!-- Financial Summary Cards -->
<div class="grid grid-3" style="margin-bottom:24px">
    <div class="stat">
        <div class="stat-label">Expected Fees</div>
        <div class="stat-value"><?= money($b['total']) ?></div>
        <div class="stat-sub">For current semester</div>
    </div>
    <div class="stat success">
        <div class="stat-label">Total Paid</div>
        <div class="stat-value"><?= money($b['paid']) ?></div>
        <div class="stat-sub"><?= round($b['percent'], 1) ?>% paid</div>
    </div>
    <div class="stat <?= $b['balance'] > 0 ? 'danger' : 'accent' ?>">
        <div class="stat-label">Outstanding Balance</div>
        <div class="stat-value"><?= money($b['balance']) ?></div>
        <div class="stat-sub">Awaiting settlement</div>
    </div>
</div>

<div class="card" style="margin-bottom:24px">
    <h3>Financial Clearance Status</h3>
    <div class="progress" style="margin:12px 0;height:12px"><div style="width:<?= min(100,$b['percent']) ?>%"></div></div>
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px">
        <p style="font-size:18px; font-weight:600">Status: <span class="badge badge-<?= strtolower($b['status']) ?>" style="font-size:13px;padding:6px 14px"><?= $b['status'] ?> Clearance</span></p>
        <?php if ($b['status'] === 'Denied'): ?>
            <p style="color:var(--danger); font-size:14px; font-weight:500">⚠ Access Denied. Minimum 70% threshold required for examination clearance.</p>
        <?php elseif ($b['status'] === 'Provisional'): ?>
            <p style="color:var(--warning); font-size:14px; font-weight:500">ℹ Provisional clearance issued. Remaining balance must be settled.</p>
        <?php else: ?>
            <p style="color:var(--success); font-size:14px; font-weight:500">✓ Fully cleared for examinations.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Details Grid -->
<div class="grid grid-2">
    <!-- Payment Records -->
    <div class="table-wrap">
        <div class="table-header">
            <h2>Payment Credentials & History</h2>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($payments as $p): ?>
                        <tr>
                            <td><?= e($p['paid_on']) ?></td>
                            <td><strong style="color:var(--primary)"><?= money((float)$p['amount']) ?></strong></td>
                            <td><?= e($p['method']) ?></td>
                            <td><code><?= e($p['reference']) ?></code></td>
                            <td style="color:var(--muted)"><?= e($p['notes']) ?></td>
                        </tr>
                    <?php endforeach; if(!$payments): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;padding:24px;color:var(--muted)">No payments recorded yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Deferred Assessments -->
    <div class="table-wrap">
        <div class="table-header">
            <h2>Deferred Exam Requests</h2>
            <a class="btn btn-sm btn-secondary" href="deferred_form.php">+ New Request</a>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Course</th>
                        <th>Reason</th>
                        <th>Fee</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($deferred as $d): ?>
                        <tr>
                            <td><?= e(substr($d['submitted_on'], 0, 10)) ?></td>
                            <td><strong><?= e($d['course_code']) ?></strong><br><small style="color:var(--muted)"><?= e($d['course_name']) ?></small></td>
                            <td style="color:var(--muted); max-width:180px"><?= e($d['reason']) ?></td>
                            <td><?= money((float)$d['fee']) ?></td>
                            <td>
                                <span class="badge badge-<?= strtolower($d['status']) ?>"><?= e($d['status']) ?></span>
                                <?php if ($d['reviewer']): ?><br><small style="color:var(--muted)">by <?= e($d['reviewer']) ?></small><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; if(!$deferred): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;padding:24px;color:var(--muted)">No deferred applications requested.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
