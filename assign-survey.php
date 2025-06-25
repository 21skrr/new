<?php
// assign-survey.php
require_once 'includes/auth.php';
require_once 'config/database.php';
requireRole('HR');

$template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;
$success = false;
$error = '';
$template = null;
$employees = [];
$supervisors = [];
if ($template_id) {
    // Fetch template
    $stmt = $pdo->prepare("SELECT * FROM survey_templates WHERE id = ?");
    $stmt->execute([$template_id]);
    $template = $stmt->fetch();
    // Fetch employees
    $employees = $pdo->query("SELECT id, full_name FROM users WHERE role = 'EMPLOYEE' ORDER BY full_name")->fetchAll();
    // Fetch supervisors
    $supervisors = $pdo->query("SELECT id, full_name FROM users WHERE role = 'SUPERVISOR' ORDER BY full_name")->fetchAll();
}
if (!$template) {
    die('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Assign Survey</title></head><body><div style="max-width:600px;margin:40px auto;background:#fff;padding:2rem 2.5rem;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,0.08);font-family:Arial,sans-serif;text-align:center;"><h2>Assign Survey</h2><div style="color:#721c24;background:#f8d7da;border:1px solid #f5c6cb;padding:1em;border-radius:4px;">Survey template not found.</div><a href="hr-dashboard.php" style="color:#007bff;">&larr; Back to HR Dashboard</a></div></body></html>');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_ids = $_POST['employee_ids'] ?? [];
    $supervisor_id = intval($_POST['supervisor_id'] ?? 0);
    $due_date = $_POST['due_date'] ?? '';
    if (count($employee_ids) > 0 && $supervisor_id && $due_date) {
        try {
            $pdo->beginTransaction();
            foreach ($employee_ids as $emp_id) {
                $stmt = $pdo->prepare("INSERT INTO assigned_surveys (template_id, employee_id, supervisor_id, due_date) VALUES (?, ?, ?, ?)");
                $stmt->execute([$template_id, intval($emp_id), $supervisor_id, $due_date]);
            }
            $pdo->commit();
            $success = true;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Error assigning survey: ' . $e->getMessage();
        }
    } else {
        $error = 'Please select at least one employee, a supervisor, and a due date.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Survey</title>
    <style>
        body { background: #f4f6f8; font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; padding: 2rem 2.5rem; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        h2 { text-align: center; margin-bottom: 1.5rem; }
        label { display: block; margin-top: 1em; font-weight: bold; }
        select, input[type=date] { width: 100%; padding: 0.7em; margin-top: 0.3em; border: 1px solid #ccc; border-radius: 4px; font-size: 1em; }
        .submit-btn { background: #007bff; color: #fff; border: none; border-radius: 4px; padding: 0.6em 1.5em; font-size: 1em; cursor: pointer; margin-top: 2em; width: 100%; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 1em; border-radius: 4px; margin-bottom: 1em; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 1em; border-radius: 4px; margin-bottom: 1em; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Assign Survey: <?= htmlspecialchars($template['title']) ?></h2>
        <a href="hr-dashboard.php" style="float:right; background:#007bff; color:#fff; padding:0.5em 1.2em; border-radius:4px; text-decoration:none; font-size:1em; margin-top:-1.5em;">&larr; Back to Dashboard</a>
        <?php if ($success): ?>
            <div class="alert-success">Survey assigned successfully!</div>
        <?php elseif ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <label>Select Employees (hold Ctrl to select multiple)</label>
            <select name="employee_ids[]" multiple size="6" required>
                <?php foreach ($employees as $emp): ?>
                    <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label>Supervisor</label>
            <select name="supervisor_id" required>
                <option value="">-- Select Supervisor --</option>
                <?php foreach ($supervisors as $sup): ?>
                    <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label>Due Date</label>
            <input type="date" name="due_date" required>
            <button type="submit" class="submit-btn">Assign Survey</button>
        </form>
    </div>
</body>
</html> 