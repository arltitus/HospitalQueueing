<?php
require_once '../db.php';
require_once '../csrf.php';
session_start();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) die("Invalid CSRF token");

    $queue_id = intval($_POST['queue_id'] ?? 0);
    if ($queue_id > 0) {
        $stmt = $pdo->prepare("UPDATE queue SET status='cancelled', cancelled_at=NOW() WHERE id=?");
        $stmt->execute([$queue_id]);
    }
}

header("Location: kiosk.php");
exit;
