<?php
require_once 'db.php';
require_once 'csrf.php';

$token = csrf_token();

// Fetch active transactions dynamically
$transactions = $pdo->query("SELECT id, name FROM departments ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Hospital Queue System</title>
<style>
body { font-family: Arial, sans-serif; text-align: center; background: #f9f9f9; }
h1 { color: #333; margin-top: 40px; }
.container { max-width: 500px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
label { display: block; margin: 15px 0 5px; font-weight: bold; }
select, button { width: 100%; padding: 10px; font-size: 16px; margin-bottom: 20px; }
button { background-color: #28a745; color: white; border: none; cursor: pointer; border-radius: 4px; }
button:hover { background-color: #218838; }
.queue-number { font-size: 32px; font-weight: bold; color: green; margin-top: 20px; }
.now-serving { margin-top: 30px; text-align: left; }
.now-serving h2 { color: #c00; }
.now-serving table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.now-serving th, .now-serving td { border: 1px solid #ccc; padding: 8px; text-align: center; }
.login-btn { background-color: #007bff; margin-top: 20px; }
.login-btn:hover { background-color: #0069d9; }
</style>
</head>
<body>
<div class="container">
<h1>Welcome to Hospital Queue</h1>

<form method="post" action="kiosk.php">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
    
    <label>Client Type:</label>
    <select name="client_type">
        <option value="regular">Regular</option>
        <option value="priority">Priority</option>
    </select>

    <label>Transaction:</label>
    <select name="transaction_id" required>
        <?php foreach($transactions as $t): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Take a Queue Number</button>
</form>

<a href="admin/login.php">
    <button class="login-btn">Admin Login</button>
</a>

<div class="now-serving">
    <h2>Now Serving</h2>
    <?php
    // Fetch the latest serving per department
    $stmt = $pdo->query("
        SELECT d.name AS department, q.queue_number, q.client_type
        FROM queue q
        JOIN departments d ON q.transaction_type_id = d.id
        WHERE q.status='serving'
        ORDER BY q.created_at ASC
    ");
    $serving = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($serving):
    ?>
    <table>
        <tr>
            <th>Department</th>
            <th>Queue Number</th>
            <th>Client Type</th>
        </tr>
        <?php foreach($serving as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['department']) ?></td>
            <td><?= htmlspecialchars($s['queue_number']) ?></td>
            <td><?= htmlspecialchars($s['client_type']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
        <p>No clients are being served right now.</p>
    <?php endif; ?>
</div>
</div>
</body>
</html>
