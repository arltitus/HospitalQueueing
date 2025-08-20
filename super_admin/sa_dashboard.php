<?php
require_once '../db.php';
require_once '../csrf.php';
if (empty($_SESSION['superadmin_id'])) { header("Location: sa_login.php"); exit; }

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(24)); }

$error = '';
$success = '';

// Handle admin account actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) die("Invalid CSRF token");

    $action = $_POST['action'] ?? '';
    $admin_id = intval($_POST['admin_id'] ?? 0);

    if ($action === 'delete' && $admin_id > 0) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id=? AND role='admin'");
        $stmt->execute([$admin_id]);
        header("Location: superadmin_dashboard.php"); exit;
    } elseif ($action === 'reset_password' && $admin_id > 0) {
        $new_password = password_hash('default123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password=? WHERE id=? AND role='admin'");
        $stmt->execute([$new_password, $admin_id]);
        header("Location: superadmin_dashboard.php"); exit;
    }
}

// Fetch admins
$stmt = $pdo->query("SELECT u.id, u.username, d.name as department 
                     FROM users u 
                     LEFT JOIN departments d ON u.department_id = d.id
                     WHERE role='admin' 
                     ORDER BY u.id ASC");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch departments
$departments = $pdo->query("SELECT * FROM departments ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Dashboard stats
$today = date('Y-m-d');
$total_clients = $pdo->prepare("SELECT COUNT(*) FROM queue WHERE DATE(created_at)=?");
$total_clients->execute([$today]);
$total_clients = $total_clients->fetchColumn();

$served_clients = $pdo->prepare("SELECT COUNT(*) FROM queue WHERE DATE(served_at)=?");
$served_clients->execute([$today]);
$served_clients = $served_clients->fetchColumn();

$pending_clients = $pdo->prepare("SELECT COUNT(*) FROM queue WHERE DATE(created_at)=? AND status IN ('waiting','hold','serving')");
$pending_clients->execute([$today]);
$pending_clients = $pending_clients->fetchColumn();

$cancelled_clients = $pdo->prepare("SELECT COUNT(*) FROM queue WHERE DATE(cancelled_at)=?");
$cancelled_clients->execute([$today]);
$cancelled_clients = $cancelled_clients->fetchColumn();

// Fetch audit logs (latest 50)
$audit_logs = $pdo->query("SELECT a.id, u.username, a.action, a.meta, a.created_at 
                           FROM audit_logs a 
                           LEFT JOIN users u ON a.user_id = u.id 
                           ORDER BY a.created_at DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<title>Super Admin Dashboard</title>
<style>
table { border-collapse: collapse; width: 100%; }
table, th, td { border: 1px solid #000; padding: 5px; }
h2 { margin-top: 30px; }
</style>
</head>
<body>
<h1>Super Admin Dashboard</h1>

<!-- Dashboard Stats -->
<h2>Today Stats</h2>
<ul>
    <li>Total Clients: <?= $total_clients ?></li>
    <li>Served: <?= $served_clients ?></li>
    <li>Pending: <?= $pending_clients ?></li>
    <li>Cancelled: <?= $cancelled_clients ?></li>
</ul>

<!-- Account Management -->
<h2>Admin Accounts</h2>
<table>
<tr><th>ID</th><th>Username</th><th>Department</th><th>Actions</th></tr>
<?php foreach($admins as $a): ?>
<tr>
    <td><?= htmlspecialchars($a['id']) ?></td>
    <td><?= htmlspecialchars($a['username']) ?></td>
    <td><?= htmlspecialchars($a['department']) ?></td>
    <td>
        <form method="post" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
            <input type="hidden" name="admin_id" value="<?= $a['id'] ?>">
            <button name="action" value="reset_password">Reset Password</button>
            <button name="action" value="delete" onclick="return confirm('Delete this admin?');">Delete</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>

<!-- Departments Management -->
<h2>Departments</h2>
<table>
<tr><th>ID</th><th>Code</th><th>Name</th></tr>
<?php foreach($departments as $d): ?>
<tr>
    <td><?= htmlspecialchars($d['id']) ?></td>
    <td><?= htmlspecialchars($d['code']) ?></td>
    <td><?= htmlspecialchars($d['name']) ?></td>
</tr>
<?php endforeach; ?>
</table>

<!-- Audit Logs -->
<h2>Audit Logs (Latest 50)</h2>
<table>
<tr><th>ID</th><th>User</th><th>Action</th><th>Meta</th><th>Created At</th></tr>
<?php foreach($audit_logs as $log): ?>
<tr>
    <td><?= htmlspecialchars($log['id']) ?></td>
    <td><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
    <td><?= htmlspecialchars($log['action']) ?></td>
    <td><?= htmlspecialchars($log['meta']) ?></td>
    <td><?= htmlspecialchars($log['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table>

<!-- Reports / Exports placeholders -->
<h2>Reports</h2>
<p>Filters: Date Range, Transaction Type, Client Type.</p>
<p><a href="#">Export Excel</a> | <a href="#">Export CSV</a> | <a href="#">Export PDF</a></p>

<!-- System Settings -->
<h2>System Settings</h2>
<p>Reset queue numbers daily at midnight (cron recommended).</p>
<p>Manage departments (add/remove) via Departments Management section.</p>

</body>
</html>
