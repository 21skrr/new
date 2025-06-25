<?php
// analyze-results.php
require_once 'includes/auth.php';
require_once 'config/database.php';
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'HR') {
    $user = $_SESSION['user'];
    $hr_page_title = 'Analytics';
    ob_start();
    // HR analytics: organization-wide stats and per-question results
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
    $agg = [];
    foreach ($responses as $r) {
        $tid = $r['template_title'];
        $qid = $r['question_id'];
        if (!isset($agg[$tid])) $agg[$tid] = [];
        if (!isset($agg[$tid][$qid])) $agg[$tid][$qid] = ['question' => $r['question_text'], 'type' => $r['question_type'], 'options' => $r['options'], 'answers' => []];
        $agg[$tid][$qid]['answers'][] = $r['answer'];
    }
    ?>
    <h2>Organization Analytics</h2>
    <div style="margin-bottom:2em;">
        <b>Total Employees:</b> <?= $stats['total_employees'] ?> &nbsp;|
        <b>Total Supervisors:</b> <?= $stats['total_supervisors'] ?> &nbsp;|
        <b>Survey Templates:</b> <?= $stats['total_templates'] ?> &nbsp;|
        <b>Assignments:</b> <?= $stats['total_assignments'] ?> &nbsp;|
        <b>Completed Surveys:</b> <span style="color:green;"><?= $stats['completed_surveys'] ?></span>
    </div>
    <?php foreach ($agg as $template_title => $questions): ?>
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
                                <span style="display:inline-block; background:#007bff; height:18px; border-radius:4px; width:<?= ($max > 0 ? ($counts[$opt] ?? 0) / $max * 250 : 0) ?>px;"></span>
                                <span style="margin-left:0.5em; font-size:0.98em;"> <?= htmlspecialchars($opt) ?> (<?= $counts[$opt] ?? 0 ?>)</span>
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
    <?php $hr_main_content = ob_get_clean(); include 'hr-layout.php'; ?>
    <script>document.querySelector('.hr-main-section').innerHTML = `<?= str_replace('`', '\`', $hr_main_content) ?>`;</script>
    <?php
    exit;
}
requireRole('SUPERVISOR');
$user = $_SESSION['user'];
$supervisor_page_title = 'Team Results';
ob_start();
?>
<!-- Team Results content starts -->
<h2>Team Survey Results</h2>
<table style="width:100%; border-collapse:collapse; margin-top:1em;">
    <thead>
        <tr style="background:#f0f2f5;">
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Employee</th>
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Survey</th>
            <th style="padding:0.5em; border-bottom:1px solid #ddd; text-align:left;">Score</th>
        </tr>
    </thead>
    <tbody>
        <tr style="background:#fff;">
            <td style="padding:0.5em; border-bottom:1px solid #eee;">John Doe</td>
            <td style="padding:0.5em; border-bottom:1px solid #eee;">Survey 1</td>
            <td style="padding:0.5em; border-bottom:1px solid #eee;">90%</td>
        </tr>
    </tbody>
</table>
<!-- Team Results content ends -->
<?php $supervisor_main_content = ob_get_clean(); include 'supervisor-layout.php'; ?>
<script>document.querySelector('.supervisor-main-section').innerHTML = `<?= str_replace('`', '\`', $supervisor_main_content) ?>`;</script> 