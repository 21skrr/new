<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

requireRole('EMPLOYEE');
$user = $_SESSION['user'];
$employee_page_title = 'Employee Dashboard';
ob_start();
// Fetch assigned surveys for this employee
$stmt = $pdo->prepare("SELECT ass.*, st.title FROM assigned_surveys ass JOIN survey_templates st ON ass.template_id = st.id WHERE ass.employee_id = ? ORDER BY ass.assigned_at DESC");
$stmt->execute([$user['id']]);
$assigned_surveys = $stmt->fetchAll();
?>
<!-- Dashboard content starts -->
<p>Welcome to your Employee Dashboard. Here you can answer assigned surveys and view your results.</p>
<a href="view-employee-results.php" style="background:#28a745; color:#fff; padding:0.5em 1.2em; border-radius:4px; text-decoration:none; font-size:1em; margin-bottom:1.2em; display:inline-block;">My Results</a>
<hr style="margin: 1.5em 0;">
<h3>Assigned Surveys</h3>
<table style="width:100%; border-collapse:collapse; margin-bottom:1.5em;">
    <thead>
        <tr style="background:#f0f2f5;">
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Survey</th>
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Status</th>
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($assigned_surveys) === 0): ?>
            <tr><td colspan="3" style="padding:0.5em; text-align:center; color:#888;">No surveys assigned to you yet.</td></tr>
        <?php else: ?>
            <?php foreach ($assigned_surveys as $survey): ?>
                <tr style="background:#fff;">
                    <td style="padding:0.5em; border-bottom:1px solid #eee;">
                        <?= htmlspecialchars($survey['title']) ?>
                    </td>
                    <td style="padding:0.5em; border-bottom:1px solid #eee;">
                        <?= $survey['status'] === 'completed' ? '<span style=\'color:green;font-weight:bold;\'>Completed</span>' : 'Not Started' ?>
                    </td>
                    <td style="padding:0.5em; border-bottom:1px solid #eee;">
                        <?php if ($survey['status'] !== 'completed'): ?>
                            <a href="answer-survey.php?assigned_id=<?= $survey['id'] ?>" style="background:#007bff; color:#fff; padding:0.4em 1em; border-radius:4px; text-decoration:none; font-size:0.95em;">Answer</a>
                        <?php else: ?>
                            <span style="color:#888;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<!-- Dashboard content ends -->
<?php $employee_main_content = ob_get_clean(); include 'employee-layout.php'; ?>
<script>document.querySelector('.employee-main-section').innerHTML = `<?= str_replace('`', '\`', $employee_main_content) ?>`;</script>
