<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
requireRole('HR');
$user = $_SESSION['user'];
$hr_page_title = 'HR Dashboard';
ob_start();
// Organization stats
// Statistique dyal lmo2assassa
$stats = $pdo->query("
    SELECT 
        COUNT(CASE WHEN role = 'EMPLOYEE' THEN 1 END) as total_employees,
        COUNT(CASE WHEN role = 'SUPERVISOR' THEN 1 END) as total_supervisors,
        (SELECT COUNT(*) FROM survey_templates) as total_templates,
        (SELECT COUNT(*) FROM assigned_surveys) as total_assignments,
        (SELECT COUNT(*) FROM assigned_surveys WHERE status = 'completed') as completed_surveys
    FROM users
")->fetch();
// Survey templates and assignment counts
// Les templates dyal survey w ch7al men wa7ed assigniw lih
$stmt = $pdo->query("SELECT st.*, (SELECT COUNT(*) FROM assigned_surveys ass WHERE ass.template_id = st.id) as assignments_count FROM survey_templates st ORDER BY st.created_at DESC");
$templates = $stmt->fetchAll();
?>
<!-- Dashboard content starts -->
<p>This is your HR dashboard. Here you can manage survey templates, assignments, and view organization-wide results.</p>
<a href="manage-users.php" style="background:#007bff; color:#fff; padding:0.5em 1.2em; border-radius:4px; text-decoration:none; font-size:1em; margin-bottom:1.2em; display:inline-block;">Manage Users</a>
<hr style="margin: 1.5em 0;">
<h3>Organization Statistics</h3>
<table style="width:100%; border-collapse:collapse; margin-bottom:1.5em;">
    <tr><td style="padding:0.5em;">Total Employees</td><td style="padding:0.5em; font-weight:bold; color:#232946;"><?= $stats['total_employees'] ?></td></tr>
    <tr><td style="padding:0.5em;">Total Supervisors</td><td style="padding:0.5em; font-weight:bold; color:#232946;"><?= $stats['total_supervisors'] ?></td></tr>
    <tr><td style="padding:0.5em;">Survey Templates</td><td style="padding:0.5em; font-weight:bold; color:#232946;"><?= $stats['total_templates'] ?></td></tr>
    <tr><td style="padding:0.5em;">Assignments</td><td style="padding:0.5em; font-weight:bold; color:#232946;"><?= $stats['total_assignments'] ?></td></tr>
    <tr><td style="padding:0.5em;">Completed Surveys</td><td style="padding:0.5em; font-weight:bold; color:green;"><?= $stats['completed_surveys'] ?></td></tr>
</table>
<h3>Survey Templates</h3>
<a href="create-template.php" style="background:#28a745; color:#fff; padding:0.4em 1em; border-radius:4px; text-decoration:none; font-size:0.95em; margin-bottom:1em; display:inline-block;">Create New Template</a>
<table style="width:100%; border-collapse:collapse; margin-top:1em;">
    <thead>
        <tr style="background:#f0f2f5;">
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Title</th>
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Assignments</th>
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($templates) === 0): ?>
            <tr><td colspan="3" style="padding:0.5em; text-align:center; color:#888;">No survey templates found.</td></tr>
        <?php else: ?>
            <?php foreach ($templates as $tpl): ?>
                <tr style="background:#fff;">
                    <td style="padding:0.5em; border-bottom:1px solid #eee;">
                        <?= htmlspecialchars($tpl['title']) ?>
                    </td>
                    <td style="padding:0.5em; border-bottom:1px solid #eee;">
                        <?= (int)$tpl['assignments_count'] ?>
                    </td>
                    <td style="padding:0.5em; border-bottom:1px solid #eee;">
                        <a href="edit-template.php?id=<?= $tpl['id'] ?>" style="background:#007bff; color:#fff; padding:0.3em 0.8em; border-radius:4px; text-decoration:none; font-size:0.95em;">Edit</a>
                        <a href="delete-template.php?id=<?= $tpl['id'] ?>" style="background:#dc3545; color:#fff; padding:0.3em 0.8em; border-radius:4px; text-decoration:none; font-size:0.95em; margin-left:0.5em;">Delete</a>
                        <a href="assign-survey.php?template_id=<?= $tpl['id'] ?>" style="background:#ffc107; color:#333; padding:0.3em 0.8em; border-radius:4px; text-decoration:none; font-size:0.95em; margin-left:0.5em;">Assign</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<hr style="margin: 2em 0;">
<h3>Analytics & Export</h3>
<a href="analyze-results.php" style="background:#17a2b8; color:#fff; padding:0.4em 1em; border-radius:4px; text-decoration:none; font-size:0.95em; margin-right:0.5em;">Analyze Results</a>
<a href="export-data.php" style="background:#343a40; color:#fff; padding:0.4em 1em; border-radius:4px; text-decoration:none; font-size:0.95em;">Export Data</a>
<!-- Dashboard content ends -->
<?php $hr_main_content = ob_get_clean(); include 'hr-layout.php'; ?>
<script>document.querySelector('.hr-main-section').innerHTML = `<?= str_replace('`', '\`', $hr_main_content) ?>`;</script>
