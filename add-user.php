<?php
// add-user.php
require_once 'includes/auth.php';
require_once 'config/database.php';
requireRole('HR');
$user = $_SESSION['user'];
$hr_page_title = 'Add User';
$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    if ($full_name && $email && $password && in_array($role, ['EMPLOYEE','SUPERVISOR','HR'])) {
        // Tchecki wach kayn email m3awd
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'A user with this email already exists.';
        } else {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$full_name, $email, $hash, $role]);
                header('Location: manage-users.php?success=1');
                exit;
            } catch (Exception $e) {
                $error = 'Error adding user: ' . $e->getMessage();
            }
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
ob_start();
?>
<!-- front kay bda -->
<h2>Add New User</h2>
<?php if ($error): ?>
    <div style="background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; padding:1em; border-radius:4px; margin-bottom:1em; text-align:center;"> <?= htmlspecialchars($error) ?> </div>
<?php endif; ?>
<form method="post" action="add-user.php" style="max-width:400px; margin-top:2em;">
    <label for="full_name" style="display:block; margin-bottom:0.5em;">Full Name</label>
    <input type="text" name="full_name" id="full_name" required style="width:100%; padding:0.5em; margin-bottom:1em; border-radius:4px; border:1px solid #ccc;">
    <label for="email" style="display:block; margin-bottom:0.5em;">Email</label>
    <input type="email" name="email" id="email" required style="width:100%; padding:0.5em; margin-bottom:1em; border-radius:4px; border:1px solid #ccc;">
    <label for="role" style="display:block; margin-bottom:0.5em;">Role</label>
    <select name="role" id="role" required style="width:100%; padding:0.5em; margin-bottom:1em; border-radius:4px; border:1px solid #ccc;">
        <option value="EMPLOYEE">Employee</option>
        <option value="SUPERVISOR">Supervisor</option>
        <option value="HR">HR</option>
    </select>
    <label for="password" style="display:block; margin-bottom:0.5em;">Password</label>
    <input type="password" name="password" id="password" required style="width:100%; padding:0.5em; margin-bottom:1.5em; border-radius:4px; border:1px solid #ccc;">
    <button type="submit" style="background:#28a745; color:#fff; padding:0.5em 1.2em; border-radius:4px; border:none; font-size:1em;">Add User</button>
    <a href="manage-users.php" style="margin-left:1em; color:#007bff; text-decoration:none;">Cancel</a>
</form>
<!-- Add User content ends -->
<?php $hr_main_content = ob_get_clean(); include 'hr-layout.php'; ?>
<script>document.querySelector('.hr-main-section').innerHTML = `<?= str_replace('`', '\`', $hr_main_content) ?>`;</script> 