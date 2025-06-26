<?php
// manage-users.php
require_once 'includes/auth.php';
require_once 'config/database.php';
requireRole('HR');
$user = $_SESSION['user'];
$hr_page_title = 'Manage Users';
ob_start();

// Jib jami3 lusers
$users = $pdo->query("SELECT id, full_name, email, role FROM users ORDER BY full_name")->fetchAll();
$success = isset($_GET['success']) ? intval($_GET['success']) : 0;
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!-- // lcontenu dyal manage users kaybda -->

<h2>Manage Users</h2>
<?php if ($success == 1): ?>
    <div style="background:#d4edda; color:#155724; border:1px solid #c3e6cb; padding:1em; border-radius:4px; margin-bottom:1em; text-align:center;">User added successfully!</div>
<?php elseif ($success == 2): ?>
    <div style="background:#d4edda; color:#155724; border:1px solid #c3e6cb; padding:1em; border-radius:4px; margin-bottom:1em; text-align:center;">User updated successfully!</div>
<?php elseif ($success == 3): ?>
    <div style="background:#d4edda; color:#155724; border:1px solid #c3e6cb; padding:1em; border-radius:4px; margin-bottom:1em; text-align:center;">User deleted successfully!</div>
<?php endif; ?>
<?php if ($error == 'nouser'): ?>
    <div style="background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; padding:1em; border-radius:4px; margin-bottom:1em; text-align:center;">User not found.</div>
<?php endif; ?>
<a href="add-user.php" style="background:#28a745; color:#fff; padding:0.4em 1em; border-radius:4px; text-decoration:none; font-size:0.95em; margin-bottom:1em; display:inline-block;">Add New User</a>
<table style="width:100%; border-collapse:collapse; margin-top:1em;">
    <thead>
        <tr style="background:#f0f2f5;">
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Name</th>
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Email</th>
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Role</th>
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($users) === 0): ?>
            <tr><td colspan="4" style="padding:0.5em; text-align:center; color:#888;">No users found.</td></tr>
        <?php else: ?>
            <?php foreach ($users as $u): ?>
                <tr style="background:#fff;">
                    <td style="padding:0.5em; border-bottom:1px solid #eee;"><?= htmlspecialchars($u['full_name']) ?></td>
                    <td style="padding:0.5em; border-bottom:1px solid #eee;"><?= htmlspecialchars($u['email']) ?></td>
                    <td style="padding:0.5em; border-bottom:1px solid #eee;"><?= htmlspecialchars($u['role']) ?></td>
                    <td style="padding:0.5em; border-bottom:1px solid #eee;">
                        <a href="edit-user.php?id=<?= $u['id'] ?>" style="background:#007bff; color:#fff; padding:0.3em 0.8em; border-radius:4px; text-decoration:none; font-size:0.95em;">Edit</a>
                        <a href="delete-user.php?id=<?= $u['id'] ?>" style="background:#dc3545; color:#fff; padding:0.3em 0.8em; border-radius:4px; text-decoration:none; font-size:0.95em; margin-left:0.5em;">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<!-- // lcontenu dyal manage users tsalaq -->

<?php $hr_main_content = ob_get_clean(); include 'hr-layout.php'; ?>
<script>document.querySelector('.hr-main-section').innerHTML = `<?= str_replace('`', '\`', $hr_main_content) ?>`;</script> 