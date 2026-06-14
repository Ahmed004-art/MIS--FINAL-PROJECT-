<?php
require_once __DIR__ . '/includes/auth.php';
logAction('logout');
$_SESSION = [];
session_destroy();
header('Location: login.php');
