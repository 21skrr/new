<?php
// answer-survey.php
require_once 'includes/auth.php';
require_once 'config/database.php';
requireRole('EMPLOYEE');

$assigned_id = isset($_GET['assigned_id']) ? intval($_GET['assigned_id']) : 0;
$user_id = $_SESSION['user_id'];
$success = false;
$error = '';

// Jib survey li assigniw lik
$stmt = $pdo->prepare("SELECT ass.*, st.title, st.description, st.id as template_id, ass.status FROM assigned_surveys ass JOIN survey_templates st ON ass.template_id = st.id WHERE ass.id = ? AND ass.employee_id = ?");
$stmt->execute([$assigned_id, $user_id]);
$assigned = $stmt->fetch();
if (!$assigned) {
    die('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Answer Survey</title></head><body><div style="max-width:600px;margin:40px auto;background:#fff;padding:2rem 2.5rem;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,0.08);font-family:Arial,sans-serif;text-align:center;"><h2>Answer Survey</h2><div style="color:#721c24;background:#f8d7da;border:1px solid #f5c6cb;padding:1em;border-radius:4px;">Survey not found or not assigned to you.</div><a href="employee-dashboard.php" style="color:#007bff;">&larr; Back to Dashboard</a></div></body></html>');
}

// Jib les questions
$stmt = $pdo->prepare("SELECT * FROM survey_questions WHERE template_id = ?");
$stmt->execute([$assigned['template_id']]);
$questions = $stmt->fetchAll();

// Check if already completed
// Tchecki ila survey salli
$is_completed = ($assigned['status'] === 'completed');

// Handle form submission
// T3amel m3a form dyal l'ijaba
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_completed) {
    $answers = $_POST['answer'] ?? [];
    $now = date('Y-m-d H:i:s');
    try {
        $pdo->beginTransaction();
        foreach ($questions as $q) {
            $ans = trim($answers[$q['id']] ?? '');
            if ($ans !== '') {
                $stmt = $pdo->prepare("INSERT INTO survey_responses (assigned_survey_id, question_id, answer, responded_at) VALUES (?, ?, ?, ?)");
                $stmt->execute([$assigned_id, $q['id'], $ans, $now]);
            }
        }
        // Mark as completed
        $pdo->prepare("UPDATE assigned_surveys SET status = 'completed', completed_at = NOW() WHERE id = ?")->execute([$assigned_id]);
        $pdo->commit();
        $success = true;
        $is_completed = true;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Error saving responses: ' . $e->getMessage();
    }
}

// Jib l'ijabat l9dama ila survey mkmla
$prev_answers = [];
if ($is_completed) {
    $stmt = $pdo->prepare("SELECT question_id, answer FROM survey_responses WHERE assigned_survey_id = ?");
    $stmt->execute([$assigned_id]);
    foreach ($stmt->fetchAll() as $row) {
        $prev_answers[$row['question_id']] = $row['answer'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Answer Survey</title>
    <style>
        body { background: #f4f6f8; font-family: Arial, sans-serif; height: 100vh; margin: 0; }
        .centered-container { min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; }
        .content-box { background: #fff; padding: 2.5rem 2.5rem 2rem 2.5rem; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); min-width: 350px; position: relative; max-width: 600px; }
        h2 { margin-bottom: 1.2rem; text-align: center; }
        .back-btn { position: absolute; top: 1.5rem; right: 2rem; background:#007bff; color:#fff; padding:0.5em 1.2em; border-radius:4px; text-decoration:none; font-size:1em; }
        .back-btn:hover { background: #0056b3; }
        .survey-title { font-size: 1.3em; font-weight: bold; margin-bottom: 0.5em; text-align: center; }
        .survey-desc { color: #555; margin-bottom: 1.5em; text-align: center; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 1em; border-radius: 4px; margin-bottom: 1em; text-align: center; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 1em; border-radius: 4px; margin-bottom: 1em; text-align: center; }
        .question-block { margin-bottom: 1.5em; }
        .question-label { font-weight: bold; margin-bottom: 0.5em; display: block; }
        .options-list { margin: 0.5em 0 0 0; }
        .options-list label { font-weight: normal; margin-right: 1.5em; }
        textarea { width: 100%; min-height: 60px; border-radius: 4px; border: 1px solid #ccc; padding: 0.7em; font-size: 1em; }
        .submit-btn { background: #007bff; color: #fff; border: none; border-radius: 4px; padding: 0.7em 1.5em; font-size: 1em; cursor: pointer; width: 100%; margin-top: 1.5em; }
        .submit-btn:disabled { background: #aaa; cursor: not-allowed; }
        .completed-msg { color: #28a745; font-weight: bold; text-align: center; margin-top: 1.5em; }
    </style>
</head>
<body>
    <div class="centered-container">
        <div class="content-box">
            <a href="employee-dashboard.php" class="back-btn">&larr; Back to Dashboard</a>
            <h2>Answer Survey</h2>
            <div class="survey-title"><?= htmlspecialchars($assigned['title']) ?></div>
            <div class="survey-desc"><?= htmlspecialchars($assigned['description']) ?></div>
            <?php if ($success): ?>
                <div class="alert-success">Your responses have been submitted. Thank you!</div>
            <?php elseif ($error): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" action="" autocomplete="off">
                <?php foreach ($questions as $q): ?>
                    <div class="question-block">
                        <span class="question-label">Q: <?= htmlspecialchars($q['question_text']) ?></span>
                        <?php if ($q['question_type'] === 'multiple_choice'): ?>
                            <div class="options-list">
                                <?php $opts = array_map('trim', explode(',', $q['options'])); ?>
                                <?php foreach ($opts as $opt): ?>
                                    <label>
                                        <input type="radio" name="answer[<?= $q['id'] ?>]" value="<?= htmlspecialchars($opt) ?>" <?= ($is_completed && isset($prev_answers[$q['id']]) && $prev_answers[$q['id']] === $opt) ? 'checked' : '' ?> <?= $is_completed ? 'disabled' : '' ?>>
                                        <?= htmlspecialchars($opt) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <textarea name="answer[<?= $q['id'] ?>]" <?= $is_completed ? 'readonly' : '' ?>><?= $is_completed && isset($prev_answers[$q['id']]) ? htmlspecialchars($prev_answers[$q['id']]) : '' ?></textarea>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="submit-btn" <?= $is_completed ? 'disabled' : '' ?>><?= $is_completed ? 'Survey Completed' : 'Submit Answers' ?></button>
            </form>
            <?php if ($is_completed): ?>
                <div class="completed-msg">You have already completed this survey.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 