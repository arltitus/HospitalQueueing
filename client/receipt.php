<?php
require_once '../db.php';
require_once '../csrf.php';

$token = csrf_token();
$queue_number = $_GET['queue_number'] ?? '';

if (!$queue_number) {
    header("Location: kiosk.php");
    exit;
}

// Fetch the queue info along with transaction type and date
$stmt = $pdo->prepare("
    SELECT q.id, q.client_type, q.created_at, d.name AS transaction_name
    FROM queue q
    JOIN departments d ON q.transaction_type_id = d.id
    WHERE q.queue_number = ? AND q.status IN ('waiting','hold')
");
$stmt->execute([$queue_number]);
$queue = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if queue not found or already served/cancelled
if (!$queue) {
    echo "<p>This queue number is no longer active.</p>";
    echo '<a href="kiosk.php">Back to Kiosk</a>';
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Queue Receipt</title>
<style>
body { 
    font-family: Arial; 
    text-align: center; 
    margin-top: 50px; 
}

h1 { 
    font-size: 32px; 
    color: #333; 
}

.queue-number { 
    font-size: 48px; 
    font-weight: bold; 
    color: green; 
    margin: 20px 0; 
}

p { 
    font-size: 18px; 
}

button { 
    font-size: 16px; 
    padding: 10px 20px; 
    margin-top: 20px; 
    cursor: pointer;
    }
</style>

</head>
<body>
<h1>Your Queue Number</h1>
<div class="queue-number"><?= htmlspecialchars($queue_number) ?></div>
<p>Transaction: <strong><?= htmlspecialchars($queue['transaction_name']) ?></strong></p>
<p>Date: <strong><?= date('F j, Y H:i', strtotime($queue['created_at'])) ?></strong></p>
<p>Please wait for your turn. Thank you!</p>

<h2>Cancel Your Queue</h2>
<form method="post" action="cancel.php">
    <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($token)?>">
    <input type="hidden" name="queue_id" value="<?= $queue['id'] ?>">
    <button type="submit">Cancel Queue</button>
</form>

<br>
<button onclick="window.location='kiosk.php'">Back to Kiosk</button>
</body>
</html>
