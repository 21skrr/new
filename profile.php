<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
requireRole('HR');
$user = $_SESSION['user'];
$hr_page_title = 'Profile';
ob_start();
?>
<!-- Profile content starts -->
<div style="max-width:420px; margin:2em auto; background:#fff; border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,0.08); padding:2.5em 2em; text-align:center;">
    <img src="public/placeholder-user.jpg" alt="Profile" style="width:80px; height:80px; border-radius:50%; object-fit:cover; margin-bottom:1.2em; border:3px solid #232946;">
    <h2 style="margin-bottom:0.2em; color:#232946; font-size:1.4em; font-weight:600;"><?= htmlspecialchars($user['full_name']) ?></h2>
    <div style="color:#6c757d; margin-bottom:1.2em; font-size:1.05em;">HR</div>
    <table style="margin:1.5em auto 0 auto; text-align:left; font-size:1.05em;">
        <tr><td style="color:#888; padding:0.3em 1em 0.3em 0;">Email:</td><td><?= htmlspecialchars($user['email']) ?></td></tr>
        <tr><td style="color:#888; padding:0.3em 1em 0.3em 0;">Role:</td><td><?= htmlspecialchars($user['role']) ?></td></tr>
    </table>
</div>
<!-- Profile content ends -->
<?php $hr_main_content = ob_get_clean(); include 'hr-layout.php'; ?>
<script>document.querySelector('.hr-main-section').innerHTML = `<?= str_replace('`', '\`', $hr_main_content) ?>`;</script> 