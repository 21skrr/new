<?php
// delete-template.php
require_once 'includes/auth.php';
require_once 'config/database.php';
requireRole('HR');

$template_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$success = false;
$error = '';

if ($template_id) {
    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM survey_questions WHERE template_id = ?")->execute([$template_id]);
        $pdo->prepare("DELETE FROM survey_templates WHERE id = ?")->execute([$template_id]);
        $pdo->commit();
        $success = true;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Error deleting template: ' . $e->getMessage();
    }
} else {
    $error = 'Invalid template ID.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Survey Template</title>
    <style>
        body { background: #f4f6f8; font-family: Arial, sans-serif; }
        .container { max-width: 500px; margin: 60px auto; background: #fff; padding: 2rem 2.5rem; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); text-align: center; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 1em; border-radius: 4px; margin-bottom: 1em; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 1em; border-radius: 4px; margin-bottom: 1em; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Delete Survey Template</h2>
        <?php if ($success): ?>
            <div class="alert-success">Survey template deleted successfully!</div>
        <?php else: ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <a href="hr-dashboard.php" style="float:right; background:#007bff; color:#fff; padding:0.5em 1.2em; border-radius:4px; text-decoration:none; font-size:1em; margin-top:-1.5em;">&larr; Back to Dashboard</a>
    </div>
</body>
</html>