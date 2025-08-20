<?php
require_once '../db.php';
require_once '../csrf.php';

$token = csrf_token();

// Fetch transactions dynamically from departments table
$transactions = $pdo->query("SELECT id, name, code FROM departments ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Handle queue generation
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) die("Invalid CSRF token");

    $client_type = $_POST['client_type'] ?? 'regular';
    $transaction_id = intval($_POST['transaction_id'] ?? 0);

    if ($transaction_id > 0) {
       // Get next counter for this transaction
        $stmt = $pdo->prepare("SELECT COUNT(*)+1 AS next_num FROM queue WHERE transaction_type_id=? AND DATE(created_at)=CURDATE()");
        $stmt->execute([$transaction_id]);
        $next_num = $stmt->fetchColumn();

        // Just use the number, padded to 3 digits
        $queue_number = str_pad($next_num, 3, '0', STR_PAD_LEFT);

        // Insert into queue
        $stmt = $pdo->prepare("INSERT INTO queue (queue_number, transaction_type_id, client_type) VALUES (?, ?, ?)");
        $stmt->execute([$queue_number, $transaction_id, $client_type]);

        // Redirect to receipt page
        header("Location: receipt.php?queue_number=" . urlencode($queue_number));
        exit;
    }
}

// Fetch active queues for cancellation
$active_queues = $pdo->query("SELECT id, queue_number, client_type FROM queue WHERE status IN ('waiting','hold') ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<title>Hospital Kiosk</title>
<style>
body { font-family: Arial; }
h1 { color: #333; }
.queue-number { font-size: 32px; font-weight: bold; color: green; }
</style>
</head>
<body>
<h1>Take a Queue Number</h1>

<?php if($message): ?>
<p class="queue-number"><?= $message ?></p>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($token)?>">
    <label>Client Type:</label>
    <select name="client_type">
        <option value="regular">Regular</option>
        <option value="priority">Priority</option>
    </select>
    <br><br>
    <label>Transaction:</label>
    <select name="transaction_id" required>
        <?php foreach($transactions as $t): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <br><br>
    <button type="submit">Print Queue Number</button>
</form>
</form>
</body>
</html>
