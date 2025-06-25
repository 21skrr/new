<?php
// edit-template.php
require_once 'includes/auth.php';
require_once 'config/database.php';
requireRole('HR');

$template_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$success = false;
$error = '';
$template = null;
$questions = [];
if ($template_id) {
    // Fetch template
    $stmt = $pdo->prepare("SELECT * FROM survey_templates WHERE id = ?");
    $stmt->execute([$template_id]);
    $template = $stmt->fetch();
    // Fetch questions
    $stmt = $pdo->prepare("SELECT * FROM survey_questions WHERE template_id = ?");
    $stmt->execute([$template_id]);
    $questions = $stmt->fetchAll();
}
if (!$template) {
    die('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Edit Survey Template</title></head><body><div style="max-width:600px;margin:40px auto;background:#fff;padding:2rem 2.5rem;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,0.08);font-family:Arial,sans-serif;text-align:center;"><h2>Edit Survey Template</h2><div style="color:#721c24;background:#f8d7da;border:1px solid #f5c6cb;padding:1em;border-radius:4px;">Survey template not found.</div><a href="hr-dashboard.php" style="color:#007bff;">&larr; Back to HR Dashboard</a></div></body></html>');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $question_ids = $_POST['question_id'] ?? [];
    $question_texts = $_POST['question_text'] ?? [];
    $question_types = $_POST['question_type'] ?? [];
    $options_arr = $_POST['options'] ?? [];
    if ($title && $description && count($question_texts) > 0) {
        try {
            $pdo->beginTransaction();
            // Update template
            $stmt = $pdo->prepare("UPDATE survey_templates SET title = ?, description = ? WHERE id = ?");
            $stmt->execute([$title, $description, $template_id]);
            // Remove all old questions for this template
            $pdo->prepare("DELETE FROM survey_questions WHERE template_id = ?")->execute([$template_id]);
            // Insert updated questions
            for ($i = 0; $i < count($question_texts); $i++) {
                $q_text = trim($question_texts[$i]);
                $q_type = $question_types[$i];
                $opts = ($q_type === 'multiple_choice') ? trim($options_arr[$i]) : null;
                $stmt = $pdo->prepare("INSERT INTO survey_questions (template_id, question_text, question_type, options) VALUES (?, ?, ?, ?)");
                $stmt->execute([$template_id, $q_text, $q_type, $opts]);
            }
            $pdo->commit();
            $success = true;
            // Reload updated data
            $stmt = $pdo->prepare("SELECT * FROM survey_questions WHERE template_id = ?");
            $stmt->execute([$template_id]);
            $questions = $stmt->fetchAll();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Error updating template: ' . $e->getMessage();
        }
    } else {
        $error = 'Please fill in all required fields and add at least one question.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Survey Template</title>
    <style>
        body { background: #f4f6f8; font-family: Arial, sans-serif; }
        .container { max-width: 700px; margin: 40px auto; background: #fff; padding: 2rem 2.5rem; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        h2 { text-align: center; margin-bottom: 1.5rem; }
        label { display: block; margin-top: 1em; font-weight: bold; }
        input[type=text], textarea, select { width: 100%; padding: 0.7em; margin-top: 0.3em; border: 1px solid #ccc; border-radius: 4px; font-size: 1em; }
        .questions-section { margin-top: 2em; }
        .question-block { background: #f9f9f9; border: 1px solid #eee; border-radius: 6px; padding: 1em; margin-bottom: 1.5em; position: relative; }
        .remove-btn { background: #dc3545; color: #fff; border: none; border-radius: 4px; padding: 0.3em 0.8em; cursor: pointer; position: absolute; top: 1em; right: 1em; }
        .add-btn, .submit-btn { background: #007bff; color: #fff; border: none; border-radius: 4px; padding: 0.6em 1.5em; font-size: 1em; cursor: pointer; margin-top: 1.5em; }
        .add-btn { background: #28a745; margin-left: 0.5em; }
        .submit-btn { display: block; width: 100%; margin-top: 2em; }
        .options-area { margin-top: 0.5em; }
        .options-area label { font-weight: normal; margin-top: 0.2em; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 1em; border-radius: 4px; margin-bottom: 1em; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 1em; border-radius: 4px; margin-bottom: 1em; }
    </style>
    <script>
    function addQuestion() {
        const questionsDiv = document.getElementById('questions');
        const block = document.createElement('div');
        block.className = 'question-block';
        block.innerHTML = `
            <button type="button" class="remove-btn" onclick="this.parentNode.remove()">Remove</button>
            <input type="hidden" name="question_id[]" value="">
            <label>Question Text
                <input type="text" name="question_text[]" required>
            </label>
            <label>Question Type
                <select name="question_type[]" onchange="toggleOptions(this, this.parentNode.parentNode)">
                    <option value="multiple_choice">Multiple Choice</option>
                    <option value="open_ended">Open Ended</option>
                </select>
            </label>
            <div class="options-area">
                <label>Options (comma separated)
                    <input type="text" name="options[]" placeholder="e.g. Yes,No,Maybe">
                </label>
            </div>
        `;
        questionsDiv.appendChild(block);
    }
    function toggleOptions(select, block) {
        const optionsArea = block.querySelector('.options-area');
        if (select.value === 'multiple_choice') {
            optionsArea.style.display = '';
        } else {
            optionsArea.style.display = 'none';
        }
    }
    window.onload = function() {
        document.querySelectorAll('select[name="question_type[]"]').forEach(function(sel) {
            toggleOptions(sel, sel.parentNode.parentNode);
        });
    };
    </script>
</head>
<body>
    <div class="container">
        <h2>Edit Survey Template</h2>
        <a href="hr-dashboard.php" style="float:right; background:#007bff; color:#fff; padding:0.5em 1.2em; border-radius:4px; text-decoration:none; font-size:1em; margin-top:-1.5em;">&larr; Back to Dashboard</a>
        <?php if ($success): ?>
            <div class="alert-success">Survey template updated successfully!</div>
        <?php elseif ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <label>Title
                <input type="text" name="title" value="<?= htmlspecialchars($template['title']) ?>" required>
            </label>
            <label>Description
                <textarea name="description" rows="3" required><?= htmlspecialchars($template['description']) ?></textarea>
            </label>
            <div class="questions-section">
                <h3>Questions</h3>
                <div id="questions">
                    <?php foreach ($questions as $q): ?>
                        <div class="question-block">
                            <button type="button" class="remove-btn" onclick="this.parentNode.remove()">Remove</button>
                            <input type="hidden" name="question_id[]" value="<?= $q['id'] ?>">
                            <label>Question Text
                                <input type="text" name="question_text[]" value="<?= htmlspecialchars($q['question_text']) ?>" required>
                            </label>
                            <label>Question Type
                                <select name="question_type[]" onchange="toggleOptions(this, this.parentNode.parentNode)">
                                    <option value="multiple_choice" <?= $q['question_type'] === 'multiple_choice' ? 'selected' : '' ?>>Multiple Choice</option>
                                    <option value="open_ended" <?= $q['question_type'] === 'open_ended' ? 'selected' : '' ?>>Open Ended</option>
                                </select>
                            </label>
                            <div class="options-area" style="<?= $q['question_type'] === 'multiple_choice' ? '' : 'display:none;' ?>">
                                <label>Options (comma separated)
                                    <input type="text" name="options[]" value="<?= htmlspecialchars($q['options']) ?>" placeholder="e.g. Yes,No,Maybe">
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="add-btn" onclick="addQuestion()">+ Add Question</button>
            </div>
            <button type="submit" class="submit-btn">Update Template</button>
        </form>
    </div>
</body>
</html> 