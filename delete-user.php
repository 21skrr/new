<?php
// delete-user.php
require_once 'includes/auth.php';
require_once 'config/database.php';
requireRole('HR');
$user = $_SESSION['user'];
$hr_page_title = 'Delete User';
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';
if (!$user_id) {
    header('Location: manage-users.php?error=nouser');
    exit;
}
// Fetch user to delete
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$del_user = $stmt->fetch();
if (!$del_user) {
    header('Location: manage-users.php?error=nouser');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        header('Location: manage-users.php?success=3');
        exit;
    } catch (Exception $e) {
        $error = 'Error deleting user: ' . $e->getMessage();
    }
}
ob_start();
?>
<!-- Delete User content starts -->
<h2>Delete User</h2>
<?php if ($error): ?>
    <div style="background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; padding:1em; border-radius:4px; margin-bottom:1em; text-align:center;"> <?= htmlspecialchars($error) ?> </div>
<?php endif; ?>
<p>Are you sure you want to delete user <b><?= htmlspecialchars($del_user['full_name']) ?></b>?</p>
<form method="post" action="delete-user.php?id=<?= $del_user['id'] ?>" style="margin-top:2em;">
    <button type="submit" style="background:#dc3545; color:#fff; padding:0.5em 1.2em; border-radius:4px; border:none; font-size:1em;">Delete</button>
    <a href="manage-users.php" style="margin-left:1em; color:#007bff; text-decoration:none;">Cancel</a>
</form>
<!-- Delete User content ends -->
<?php $hr_main_content = ob_get_clean(); include 'hr-layout.php'; ?>
<script>document.querySelector('.hr-main-section').innerHTML = `<?= str_replace('`', '\`', $hr_main_content) ?>`;</script> 