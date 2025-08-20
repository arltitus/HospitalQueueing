<?php
require_once '../db.php';
require_once '../csrf.php';

$token = csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        die("Invalid CSRF token");
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Set session
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['admin_department'] = $user['department_id'];

        // Log the IP in audit_logs
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, meta) VALUES (?, ?, ?)");
        $stmt->execute([
            $user['id'],
            'login',
            json_encode(['ip' => $ip_address])
        ]);

        // Redirect based on role
        if ($user['role'] === 'superadmin') {
            header("Location: superadmin/dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>

<form method="post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
    Username: <input name="username"><br>
    Password: <input type="password" name="password"><br>
    <button type="submit">Login</button>
</form>

<?php if(!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
