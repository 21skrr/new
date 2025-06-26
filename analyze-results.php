<?php
// had lfile dyal analyze-results.php
require_once 'includes/auth.php'; // ndkhl l auth
require_once 'config/database.php'; // ndkhl l config dyal database
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'HR') {
    $user = $_SESSION['user'];
    $hr_page_title = 'Analytics';
    ob_start();
    // l7isab dyal HR statistique dyal lmo2assassa w natija dyal kol so2al
    $stats = $pdo->query("
        SELECT 
            COUNT(CASE WHEN role = 'EMPLOYEE' THEN 1 END) as total_employees,
            COUNT(CASE WHEN role = 'SUPERVISOR' THEN 1 END) as total_supervisors,
            (SELECT COUNT(*) FROM survey_templates) as total_templates,
            (SELECT COUNT(*) FROM assigned_surveys) as total_assignments,
            (SELECT COUNT(*) FROM assigned_surveys WHERE status = 'completed') as completed_surveys
        FROM users
    ")->fetch();
    $templates = $pdo->query("SELECT id, title FROM survey_templates ORDER BY created_at DESC")->fetchAll();
    $template_questions = [];
    foreach ($templates as $tpl) {
        $qstmt = $pdo->prepare("SELECT * FROM survey_questions WHERE template_id = ?");
        $qstmt->execute([$tpl['id']]);
        $template_questions[$tpl['id']] = $qstmt->fetchAll();
    }
    $responses = $pdo->query("SELECT sr.*, sq.question_text, sq.question_type, sq.options, u.full_name, st.title as template_title FROM survey_responses sr JOIN survey_questions sq ON sr.question_id = sq.id JOIN assigned_surveys ass ON sr.assigned_survey_id = ass.id JOIN users u ON ass.employee_id = u.id JOIN survey_templates st ON ass.template_id = st.id")->fetchAll();
    // Build a new $analytics array that always includes all questions
    $analytics = [];
    foreach ($templates as $tpl) {
        $tpl_title = $tpl['title'];
        $analytics[$tpl_title] = [];
        foreach ($template_questions[$tpl['id']] as $q) {
            $analytics[$tpl_title][$q['id']] = [
                'question' => $q['question_text'],
                'type' => $q['question_type'],
                'options' => $q['options'],
                'answers' => []
            ];
        }
    }
    // Fill in answers from $responses
    foreach ($responses as $r) {
        $tid = $r['template_title'];
        $qid = $r['question_id'];
        if (isset($analytics[$tid][$qid])) {
            $analytics[$tid][$qid]['answers'][] = $r['answer'];
        }
    }
    // Query all completed assignments
    $completed = $pdo->query("
        SELECT ass.id, u.full_name, st.title, ass.completed_at
        FROM assigned_surveys ass
        JOIN users u ON ass.employee_id = u.id
        JOIN survey_templates st ON ass.template_id = st.id
        WHERE ass.status = 'completed'
        ORDER BY ass.completed_at DESC
    ")->fetchAll();
    ?>
    <h2>Organization Analytics</h2>
    <div style="margin-bottom:2em;">
        <b>Total Employees:</b> <?= $stats['total_employees'] ?> &nbsp;|
        <b>Total Supervisors:</b> <?= $stats['total_supervisors'] ?> &nbsp;|
        <b>Survey Templates:</b> <?= $stats['total_templates'] ?> &nbsp;|
        <b>Assignments:</b> <?= $stats['total_assignments'] ?> &nbsp;|
        <b>Completed Surveys:</b> <span style="color:green;"><?= $stats['completed_surveys'] ?></span>
    </div>
    <?php foreach ($analytics as $template_title => $questions): ?>
        <div style="margin-bottom:2em;">
            <h3><?= htmlspecialchars($template_title) ?></h3>
            <?php foreach ($questions as $q): ?>
                <div style="margin-bottom:1em;">
                    <b>Q: <?= htmlspecialchars($q['question']) ?></b><br>
                    <?php if ($q['type'] === 'multiple_choice'): ?>
                        <?php
                            $opts = array_map('trim', explode(',', $q['options']));
                            $counts = array_count_values(array_map('trim', $q['answers']));
                            $max = max($counts ?: [1]);
                        ?>
                        <?php foreach ($opts as $opt): ?>
                            <div style="margin:0.3em 0;">
                                <span style="font-size:0.98em;"> <?= htmlspecialchars($opt) ?> (<?= $counts[$opt] ?? 0 ?>)</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="background:#f9f9f9; border-radius:6px; padding:1em; margin:0.5em 0 1em 0;">
                            <?php foreach ($q['answers'] as $ans): ?>
                                <div>- <?= htmlspecialchars($ans) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
    <h3>All Completed Surveys</h3>
    <table style="margin-bottom:2em; min-width:400px;">
      <tr style="background:#f0f2f5;">
        <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Employee</th>
        <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Survey</th>
        <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Completed At</th>
      </tr>
      <?php foreach ($completed as $row): ?>
      <tr style="background:#fff;">
        <td style="padding:0.5em; border-bottom:1px solid #eee;"><?= htmlspecialchars($row['full_name']) ?></td>
        <td style="padding:0.5em; border-bottom:1px solid #eee;"><?= htmlspecialchars($row['title']) ?></td>
        <td style="padding:0.5em; border-bottom:1px solid #eee;"><?= htmlspecialchars($row['completed_at']) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
    <?php $hr_main_content = ob_get_clean(); include 'hr-layout.php'; ?>
    <script>document.querySelector('.hr-main-section').innerHTML = `<?= str_replace('`', '\`', $hr_main_content) ?>`;</script>
    <?php
    exit;
}
requireRole('SUPERVISOR'); // khas supervisor ykoun dakhil
$user = $_SESSION['user'];
$supervisor_page_title = 'Team Results';
ob_start();
?>
<!-- lmawdo3 dyal natija dyal lteam kaybda mn hna -->
<h2>Team Survey Results</h2>
<table style="width:100%; border-collapse:collapse; margin-top:1em;">
    <thead>
        <tr style="background:#f0f2f5;">
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Employee</th>
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Survey</th>
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Score</th>
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Action</th>
        </tr>
    </thead>
    <tbody>
<?php
// Njibou jami3 lkhddam li kaynin ta7t had supervisor
$stmt = $pdo->prepare("
    SELECT u.id, u.full_name
    FROM users u
    JOIN assigned_surveys ass ON u.id = ass.employee_id
    WHERE ass.supervisor_id = ? AND u.role = 'EMPLOYEE'
    GROUP BY u.id, u.full_name
");
$stmt->execute([$user['id']]);
$employees = $stmt->fetchAll();

if (count($employees) === 0) {
    echo "<tr><td colspan='3' style='padding:0.5em; text-align:center; color:#888;'>No team results found.</td></tr>";
}

foreach ($employees as $emp) {
    // Fetch all surveys (completed and pending) for this employee under this supervisor
    $stmt2 = $pdo->prepare("
        SELECT ass.id, st.title, ass.status
        FROM assigned_surveys ass
        JOIN survey_templates st ON ass.template_id = st.id
        WHERE ass.employee_id = ? AND ass.supervisor_id = ?
        ORDER BY ass.assigned_at DESC
    ");
    $stmt2->execute([$emp['id'], $user['id']]);
    $surveys = $stmt2->fetchAll();

    if (count($surveys) === 0) {
        continue;
    }

    foreach ($surveys as $survey) {
        if ($survey['status'] === 'completed') {
            // Fetch supervisor feedback score for this survey
            $stmtFeedback = $pdo->prepare("SELECT score FROM supervisor_feedback WHERE assigned_survey_id = ? AND supervisor_id = ?");
            $stmtFeedback->execute([$survey['id'], $user['id']]);
            $feedback = $stmtFeedback->fetch();
            $score = $feedback ? $feedback['score'] : '-';
            $action = "<a href='view-employee-results.php?employee_id=" . $emp['id'] . "&assigned_survey_id=" . $survey['id'] . "' style='background:#007bff; color:#fff; padding:0.3em 0.9em; border-radius:4px; text-decoration:none; font-size:0.95em;'>View Details</a>";
        } else {
            $score = 'Pending';
            $action = '<span style="color:#888;">Pending</span>';
        }
        echo "<tr style='background:#fff;'>\n"
            . "<td style='padding:0.5em; border-bottom:1px solid #eee;'>" . htmlspecialchars($emp['full_name']) . "</td>\n"
            . "<td style='padding:0.5em; border-bottom:1px solid #eee;'>" . htmlspecialchars($survey['title']) . "</td>\n"
            . "<td style='padding:0.5em; border-bottom:1px solid #eee;'>" . htmlspecialchars($score) . "</td>\n"
            . "<td style='padding:0.5em; border-bottom:1px solid #eee;'>$action</td>\n"
            . "</tr>";
    }
}
?>
    </tbody>
</table>
<!-- lmawdo3 dyal natija dyal lteam kaytsala hna -->
<?php $supervisor_main_content = ob_get_clean(); include 'supervisor-layout.php'; ?>
<script>document.querySelector('.supervisor-main-section').innerHTML = `<?= str_replace('`', '\`', $supervisor_main_content) ?>`;</script> 