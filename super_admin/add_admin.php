<?php
require_once '../db.php';
session_start();
if (empty($_SESSION['superadmin_id'])) { header("Location: sa_login.php"); exit; }

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(24)); }

$error = '';
$success = '';

// Fetch departments
$stmt = $pdo->query("SELECT id, name FROM departments ORDER BY name");
$departments = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token");
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $department_id = intval($_POST['department_id'] ?? 0);

    if (empty($username) || empty($password) || empty($confirm) || $department_id <= 0) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username=?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Username already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, department_id) VALUES (?, ?, 'admin', ?)");
            $stmt->execute([$username, $hash, $department_id]);
            $success = "Admin account created successfully!";
        }
    }
}
?>

<h2>Add Department Admin</h2>
<?php if($error) echo "<p style='color:red;'>$error</p>"; ?>
<?php if($success) echo "<p style='color:green;'>$success</p>"; ?>

<form method="post">
    <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'])?>">
    Username: <input type="text" name="username"><br>
    Password: <input type="password" name="password"><br>
    Confirm Password: <input type="password" name="confirm_password"><br>
    Department:
    <select name="department_id">
        <?php foreach($departments as $d): ?>
            <option value="<?=htmlspecialchars($d['id'])?>"><?=htmlspecialchars($d['name'])?></option>
        <?php endforeach; ?>
    </select><br>
    <button type="submit">Add Admin</button>
</form>
