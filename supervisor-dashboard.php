<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

requireRole('SUPERVISOR');
$user = $_SESSION['user'];
$supervisor_page_title = 'Supervisor Dashboard';
ob_start();

// Jib lkhddam li ta7t had supervisor w statistique dyal survey dyalhom
$stmt = $pdo->prepare("SELECT u.id, u.full_name, COUNT(ass.id) AS total_assigned, SUM(ass.status = 'completed') AS completed FROM users u JOIN assigned_surveys ass ON u.id = ass.employee_id WHERE u.role = 'EMPLOYEE' AND ass.supervisor_id = ? GROUP BY u.id, u.full_name HAVING total_assigned > 0 ORDER BY u.full_name");
$stmt->execute([$user['id']]);
$employees = $stmt->fetchAll();
?>
<!-- // lcontenu dyal dashboard kaybda -->

<p>Welcome to your Supervisor Dashboard. Here you can view your team's survey results.</p>
<a href="analyze-results.php" style="background:#17a2b8; color:#fff; padding:0.5em 1.2em; border-radius:4px; text-decoration:none; font-size:1em; margin-bottom:1.2em; display:inline-block;">Analyze Team Results</a>
<hr style="margin: 1.5em 0;">
<h3>Team Members</h3>
<table style="width:100%; border-collapse:collapse; margin-bottom:1.5em;">
    <thead>
        <tr style="background:#f0f2f5;">
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Employee</th>
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Completed/Assigned</th>
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($employees) === 0): ?>
            <tr><td colspan="3" style="padding:0.5em; text-align:center; color:#888;">No team members found.</td></tr>
        <?php else: ?>
            <?php foreach ($employees as $emp): ?>
                <tr style="background:#fff;">
                    <td style="padding:0.5em; border-bottom:1px solid #eee;">
                        <?= htmlspecialchars($emp['full_name']) ?>
                    </td>
                    <td style="padding:0.5em; border-bottom:1px solid #eee;">
                        <?= (int)$emp['completed'] ?> / <?= (int)$emp['total_assigned'] ?>
                    </td>
                    <td style="padding:0.5em; border-bottom:1px solid #eee;">
                        <a href="view-employee-results.php?employee_id=<?= $emp['id'] ?>" style="background:#007bff; color:#fff; padding:0.4em 1em; border-radius:4px; text-decoration:none; font-size:0.95em;">View Results</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<!-- //lcontenu dyal dashboard tsala -->

<?php $supervisor_main_content = ob_get_clean(); include 'supervisor-layout.php'; ?>
<script>document.querySelector('.supervisor-main-section').innerHTML = `<?= str_replace('`', '\`', $supervisor_main_content) ?>`;</script>
