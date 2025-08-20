<?php
require_once '../db.php';
require_once '../csrf.php';

// Check login
if (empty($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$dept_id = $_SESSION['admin_department'];
$token = csrf_token();

// Fetch department name
$stmt = $pdo->prepare("SELECT name FROM departments WHERE id=?");
$stmt->execute([$dept_id]);
$dept_name = $stmt->fetchColumn() ?: 'Unknown Department';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        die("Invalid CSRF token");
    }

    $action = $_POST['action'] ?? '';
    $queue_id = intval($_POST['queue_id'] ?? 0);

    if ($queue_id > 0) {
        // Ensure queue belongs to this department
        $stmt = $pdo->prepare("SELECT id FROM queue WHERE id=? AND transaction_type_id=?");
        $stmt->execute([$queue_id, $dept_id]);
        if ($stmt->fetch()) {
            if ($action === 'next' || $action === 'serve') {
                $stmt = $pdo->prepare("UPDATE queue SET status='serving' WHERE id=?");
                $stmt->execute([$queue_id]);
            } elseif ($action === 'done') {
                $stmt = $pdo->prepare("UPDATE queue SET status='done', served_at=NOW() WHERE id=?");
                $stmt->execute([$queue_id]);
            } elseif ($action === 'hold') {
                $stmt = $pdo->prepare("UPDATE queue SET status='hold' WHERE id=?");
                $stmt->execute([$queue_id]);
            }
        }
        header("Location: dashboard.php");
        exit;
    }
}

// Fetch priority queue
$stmt = $pdo->prepare("SELECT * FROM queue 
    WHERE transaction_type_id=? AND client_type='priority' 
      AND status IN ('waiting','hold','serving')
    ORDER BY created_at ASC");
$stmt->execute([$dept_id]);
$priority_queue = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch regular queue
$stmt = $pdo->prepare("SELECT * FROM queue 
    WHERE transaction_type_id=? AND client_type='regular' 
      AND status IN ('waiting','hold','serving')
    ORDER BY created_at ASC");
$stmt->execute([$dept_id]);
$regular_queue = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Department Dashboard</title>
<style>
@keyframes blink { 50% { opacity: 0; } }
.blink { color: red; font-weight: bold; animation: blink 1s infinite; font-size: 24px; }
table { border-collapse: collapse; width: 50%; margin-bottom: 30px; }
th, td { border: 1px solid #333; padding: 8px; text-align: center; }
button { padding: 5px 10px; }
</style>
</head>
<body>
<h1>Dashboard - <?= htmlspecialchars($dept_name) ?></h1>

<!-- Priority Clients -->
<?php if($priority_queue): ?>
<h2 class="blink">PRIORITY</h2>
<table>
<tr>
    <th>Queue Number</th>
    <th>Status</th>
    <th>Actions</th>
</tr>
<?php foreach($priority_queue as $q): ?>
<tr>
    <td><?= htmlspecialchars($q['queue_number']) ?></td>
    <td><?= htmlspecialchars($q['status']) ?></td>
    <td>
        <form method="post" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
            <input type="hidden" name="queue_id" value="<?= $q['id'] ?>">
            <?php if(in_array($q['status'], ['waiting','hold'])): ?>
                <button name="action" value="next">Next</button>
                <button name="action" value="serve">Serve</button>
            <?php endif; ?>
            <?php if($q['status'] === 'serving'): ?>
                <button name="action" value="done">Done</button>
                <button name="action" value="hold">Hold</button>
            <?php endif; ?>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<!-- Regular Clients -->
<?php if($regular_queue): ?>
<h2>Regular Clients</h2>
<table>
<tr>
    <th>Queue Number</th>
    <th>Status</th>
    <th>Actions</th>
</tr>
<?php foreach($regular_queue as $q): ?>
<tr>
    <td><?= htmlspecialchars($q['queue_number']) ?></td>
    <td><?= htmlspecialchars($q['status']) ?></td>
    <td>
        <form method="post" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
            <input type="hidden" name="queue_id" value="<?= $q['id'] ?>">
            <?php if(in_array($q['status'], ['waiting','hold'])): ?>
                <button name="action" value="next">Next</button>
                <button name="action" value="serve">Serve</button>
            <?php endif; ?>
            <?php if($q['status'] === 'serving'): ?>
                <button name="action" value="done">Done</button>
                <button name="action" value="hold">Hold</button>
            <?php endif; ?>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

</body>
</html>
