<?php
// edit-user.php
// Jib l'user li bghiti tbdl

require_once 'includes/auth.php';
require_once 'config/database.php';
requireRole('HR');
$user = $_SESSION['user'];
$hr_page_title = 'Edit User';
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';
if (!$user_id) {
    header('Location: manage-users.php?error=nouser');
    exit;
}
// Fetch user to edit
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$edit_user = $stmt->fetch();
if (!$edit_user) {
    header('Location: manage-users.php?error=nouser');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';
    if ($full_name && $email && in_array($role, ['EMPLOYEE','SUPERVISOR','HR'])) {
        try {
            if ($password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, role=?, password=? WHERE id=?");
                $stmt->execute([$full_name, $email, $role, $hash, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, role=? WHERE id=?");
                $stmt->execute([$full_name, $email, $role, $user_id]);
            }
            header('Location: manage-users.php?success=2');
            exit;
        } catch (Exception $e) {
            $error = 'Error updating user: ' . $e->getMessage();
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}
ob_start();
?>
// lcontenu dyal edit user kaybda

<h2>Edit User</h2>
<?php if ($error): ?>
    <div style="background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; padding:1em; border-radius:4px; margin-bottom:1em; text-align:center;"> <?= htmlspecialchars($error) ?> </div>
<?php endif; ?>
<form method="post" action="edit-user.php?id=<?= $edit_user['id'] ?>" style="max-width:400px; margin-top:2em;">
    <label for="full_name" style="display:block; margin-bottom:0.5em;">Full Name</label>
    <input type="text" name="full_name" id="full_name" value="<?= htmlspecialchars($edit_user['full_name']) ?>" required style="width:100%; padding:0.5em; margin-bottom:1em; border-radius:4px; border:1px solid #ccc;">
    <label for="email" style="display:block; margin-bottom:0.5em;">Email</label>
    <input type="email" name="email" id="email" value="<?= htmlspecialchars($edit_user['email']) ?>" required style="width:100%; padding:0.5em; margin-bottom:1em; border-radius:4px; border:1px solid #ccc;">
    <label for="role" style="display:block; margin-bottom:0.5em;">Role</label>
    <select name="role" id="role" required style="width:100%; padding:0.5em; margin-bottom:1em; border-radius:4px; border:1px solid #ccc;">
        <option value="EMPLOYEE" <?= $edit_user['role']==='EMPLOYEE'?'selected':'' ?>>Employee</option>
        <option value="SUPERVISOR" <?= $edit_user['role']==='SUPERVISOR'?'selected':'' ?>>Supervisor</option>
        <option value="HR" <?= $edit_user['role']==='HR'?'selected':'' ?>>HR</option>
    </select>
    <label for="password" style="display:block; margin-bottom:0.5em;">Password (leave blank to keep unchanged)</label>
    <input type="password" name="password" id="password" style="width:100%; padding:0.5em; margin-bottom:1.5em; border-radius:4px; border:1px solid #ccc;">
    <button type="submit" style="background:#007bff; color:#fff; padding:0.5em 1.2em; border-radius:4px; border:none; font-size:1em;">Save Changes</button>
    <a href="manage-users.php" style="margin-left:1em; color:#007bff; text-decoration:none;">Cancel</a>
</form>
// lcontenu dyal edit user tsala
<?php $hr_main_content = ob_get_clean(); include 'hr-layout.php'; ?>
<script>document.querySelector('.hr-main-section').innerHTML = `<?= str_replace('`', '\`', $hr_main_content) ?>`;</script> 