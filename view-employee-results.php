<?php
// view-employee-results.php
require_once 'includes/auth.php';
require_once 'config/database.php';

if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'SUPERVISOR') {
    $user = $_SESSION['user'];
    $supervisor_page_title = 'Employee Results';
    $employee_id = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : 0;
    // Check if this employee is supervised by this supervisor
    $stmt = $pdo->prepare("SELECT u.id, u.full_name FROM users u JOIN assigned_surveys ass ON u.id = ass.employee_id WHERE u.id = ? AND ass.supervisor_id = ? LIMIT 1");
    $stmt->execute([$employee_id, $user['id']]);
    $employee = $stmt->fetch();
    if (!$employee) {
        header('Location: unauthorized.php');
        exit;
    }
    // Fetch assigned surveys for this employee under this supervisor
    $stmt = $pdo->prepare("SELECT ass.*, st.title as survey_title FROM assigned_surveys ass JOIN survey_templates st ON ass.template_id = st.id WHERE ass.employee_id = ? AND ass.supervisor_id = ? ORDER BY ass.assigned_at DESC");
    $stmt->execute([$employee_id, $user['id']]);
    $assigned_surveys = $stmt->fetchAll();

    // Handle feedback form submission
    $feedback_success = false;
    $feedback_error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_assigned_id'])) {
        $assigned_id = intval($_POST['feedback_assigned_id']);
        $score = isset($_POST['score']) ? intval($_POST['score']) : null;
        $comment = trim($_POST['comment'] ?? '');
        // Check if feedback already exists
        $stmt = $pdo->prepare("SELECT id FROM supervisor_feedback WHERE assigned_survey_id = ? AND supervisor_id = ?");
        $stmt->execute([$assigned_id, $user['id']]);
        $existing = $stmt->fetch();
        try {
            if ($existing) {
                $stmt = $pdo->prepare("UPDATE supervisor_feedback SET score = ?, comment = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$score, $comment, $existing['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO supervisor_feedback (assigned_survey_id, supervisor_id, score, comment) VALUES (?, ?, ?, ?)");
                $stmt->execute([$assigned_id, $user['id'], $score, $comment]);
            }
            $feedback_success = true;
        } catch (Exception $e) {
            $feedback_error = 'Error saving feedback: ' . $e->getMessage();
        }
    }
    ob_start();
    ?>
    <h2>Survey Results for <?= htmlspecialchars($employee['full_name']) ?></h2>
    <?php if ($feedback_success): ?>
        <div style="background:#d4edda; color:#155724; border:1px solid #c3e6cb; padding:1em; border-radius:4px; margin-bottom:1em; text-align:center;">Feedback saved successfully.</div>
    <?php elseif ($feedback_error): ?>
        <div style="background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; padding:1em; border-radius:4px; margin-bottom:1em; text-align:center;"> <?= htmlspecialchars($feedback_error) ?> </div>
    <?php endif; ?>
    <table style="width:100%; border-collapse:collapse; margin-top:1em;">
        <thead>
            <tr style="background:#f0f2f5;">
                <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Survey</th>
                <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Status</th>
                <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Answered At</th>
                <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Answers</th>
                <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Supervisor Feedback</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($assigned_surveys) === 0): ?>
                <tr><td colspan="5" style="padding:0.5em; text-align:center; color:#888;">No surveys assigned to this employee.</td></tr>
            <?php else: ?>
                <?php foreach ($assigned_surveys as $survey): ?>
                    <tr style="background:#fff;">
                        <td style="padding:0.5em; border-bottom:1px solid #eee;"> <?= htmlspecialchars($survey['survey_title']) ?> </td>
                        <td style="padding:0.5em; border-bottom:1px solid #eee;"> <?= $survey['status'] === 'completed' ? '<span style=\'color:green;font-weight:bold;\'>Completed</span>' : 'Pending' ?> </td>
                        <td style="padding:0.5em; border-bottom:1px solid #eee;"> <?= $survey['status'] === 'completed' ? htmlspecialchars($survey['assigned_at']) : '-' ?> </td>
                        <td style="padding:0.5em; border-bottom:1px solid #eee;">
                            <?php if ($survey['status'] === 'completed'): ?>
                                <?php
                                // Fetch answers for this assigned survey
                                $stmt2 = $pdo->prepare("SELECT sq.question_text, sr.answer FROM survey_responses sr JOIN survey_questions sq ON sr.question_id = sq.id WHERE sr.assigned_survey_id = ?");
                                $stmt2->execute([$survey['id']]);
                                $answers = $stmt2->fetchAll();
                                if (count($answers) === 0) {
                                    echo '<span style="color:#888;">No answers found.</span>';
                                } else {
                                    echo '<ul style="margin:0; padding-left:1.2em;">';
                                    foreach ($answers as $ans) {
                                        echo '<li><b>' . htmlspecialchars($ans['question_text']) . ':</b> ' . htmlspecialchars($ans['answer']) . '</li>';
                                    }
                                    echo '</ul>';
                                }
                                ?>
                            <?php else: ?>
                                <span style="color:#888;">-</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:0.5em; border-bottom:1px solid #eee;">
                            <?php if ($survey['status'] === 'completed'): ?>
                                <?php
                                // Fetch existing feedback
                                $stmt3 = $pdo->prepare("SELECT score, comment FROM supervisor_feedback WHERE assigned_survey_id = ? AND supervisor_id = ?");
                                $stmt3->execute([$survey['id'], $user['id']]);
                                $feedback = $stmt3->fetch();
                                ?>
                                <form method="post" action="" style="margin:0;">
                                    <input type="hidden" name="feedback_assigned_id" value="<?= $survey['id'] ?>">
                                    <label>Score: <input type="number" name="score" min="0" max="100" value="<?= htmlspecialchars($feedback['score'] ?? '') ?>" style="width:60px;"></label><br>
                                    <label>Comment:<br><textarea name="comment" rows="2" style="width:98%; resize:vertical;"><?= htmlspecialchars($feedback['comment'] ?? '') ?></textarea></label><br>
                                    <button type="submit" style="margin-top:0.5em; background:#007bff; color:#fff; border:none; border-radius:4px; padding:0.3em 1em;">Save Feedback</button>
                                </form>
                            <?php else: ?>
                                <span style="color:#888;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <?php $supervisor_main_content = ob_get_clean(); include 'supervisor-layout.php'; ?>
    <script>document.querySelector('.supervisor-main-section').innerHTML = `<?= str_replace('`', '\`', $supervisor_main_content) ?>`;</script>
    <?php
    exit;
}
// Default: employee viewing their own results
requireRole('EMPLOYEE');
$user = $_SESSION['user'];
$employee_page_title = 'My Results';
$stmt = $pdo->prepare("SELECT ass.*, st.title as survey_title FROM assigned_surveys ass JOIN survey_templates st ON ass.template_id = st.id WHERE ass.employee_id = ? ORDER BY ass.assigned_at DESC");
$stmt->execute([$user['id']]);
$assigned_surveys = $stmt->fetchAll();
ob_start();
?>
<!-- My Results content starts -->
<h2>My Survey Results</h2>
<table style="width:100%; border-collapse:collapse; margin-top:1em;">
    <thead>
        <tr style="background:#f0f2f5;">
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Survey</th>
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Status</th>
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Answered At</th>
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Supervisor Feedback</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($assigned_surveys) === 0): ?>
            <tr><td colspan="4" style="padding:0.5em; text-align:center; color:#888;">No surveys assigned to you yet.</td></tr>
        <?php else: ?>
            <?php foreach ($assigned_surveys as $survey): ?>
                <tr style="background:#fff;">
                    <td style="padding:0.5em; border-bottom:1px solid #eee;"> <?= htmlspecialchars($survey['survey_title']) ?> </td>
                    <td style="padding:0.5em; border-bottom:1px solid #eee;"> <?= $survey['status'] === 'completed' ? '<span style=\'color:green;font-weight:bold;\'>Completed</span>' : 'Pending' ?> </td>
                    <td style="padding:0.5em; border-bottom:1px solid #eee;"> <?= $survey['status'] === 'completed' ? htmlspecialchars($survey['assigned_at']) : '-' ?> </td>
                    <td style="padding:0.5em; border-bottom:1px solid #eee;">
                        <?php if ($survey['status'] === 'completed'): ?>
                            <?php
                            // Fetch supervisor feedback for this survey
                            $stmt4 = $pdo->prepare("SELECT score, comment FROM supervisor_feedback WHERE assigned_survey_id = ?");
                            $stmt4->execute([$survey['id']]);
                            $feedback = $stmt4->fetch();
                            if ($feedback): ?>
                                <div><b>Score:</b> <?= htmlspecialchars($feedback['score']) ?></div>
                                <div><b>Comment:</b> <?= nl2br(htmlspecialchars($feedback['comment'])) ?></div>
                            <?php else: ?>
                                <span style="color:#888;">No feedback yet.</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#888;">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<!-- My Results content ends -->
<?php $employee_main_content = ob_get_clean(); include 'employee-layout.php'; ?>
<script>document.querySelector('.employee-main-section').innerHTML = `<?= str_replace('`', '\`', $employee_main_content) ?>`;</script> 