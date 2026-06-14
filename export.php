<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/functions.php';

$type = $_GET['type'] ?? 'students';
$filename = "sleclear-$type-" . date('Ymd-His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=$filename");
$out = fopen('php://output','w');

switch($type) {
    case 'payments':
        fputcsv($out, ['Date','Student ID','Student Name','Amount','Method','Reference','Officer','Notes']);
        $q = db()->query("SELECT p.paid_on, s.student_id, s.full_name, p.amount, p.method, p.reference, u.full_name AS officer, p.notes
                          FROM payments p JOIN students s ON s.id=p.student_id LEFT JOIN users u ON u.id=p.recorded_by ORDER BY p.id DESC");
        foreach($q as $r) fputcsv($out, $r);
        break;
    case 'clearances':
        fputcsv($out, ['Date','Student ID','Student Name','Status','Purpose','Issued By']);
        $q = db()->query("SELECT c.issued_on, s.student_id, s.full_name, c.status, c.purpose, u.full_name AS officer
                          FROM clearances c JOIN students s ON s.id=c.student_id LEFT JOIN users u ON u.id=c.issued_by ORDER BY c.id DESC");
        foreach($q as $r) fputcsv($out, $r);
        break;
    default:
        fputcsv($out, ['Student ID','Name','Gender','Email','Phone','Program','Faculty','Level','Semester','Total Fees','Paid','Balance','Status']);
        foreach (db()->query("SELECT * FROM students ORDER BY full_name") as $s) {
            $b = studentBalance((int)$s['id']);
            fputcsv($out, [$s['student_id'],$s['full_name'],$s['gender'],$s['email'],$s['phone'],$s['program'],$s['faculty'],$s['level'],$s['semester'],$b['total'],$b['paid'],$b['balance'],$b['status']]);
        }
}
fclose($out);
logAction('export', $type);
