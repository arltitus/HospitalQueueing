<?php
require_once 'db.php';
session_start();

// Check if superadmin already exists
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role='superadmin'");
$exists = $stmt->fetchColumn();

if ($exists > 0) {
    die("A superadmin account already exists. Delete this script after setup.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($password) || empty($confirm)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'superadmin')");
        $stmt->execute([$username, $hash]);
        $success = "Superadmin account created successfully!";
    }
}
?>

<h2>Register Super Admin</h2>
<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
<?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>
<form method="post">
    Username: <input type="text" name="username"><br>
    Password: <input type="password" name="password"><br>
    Confirm Password: <input type="password" name="confirm_password"><br>
    <button type="submit">Create Super Admin</button>
</form>
