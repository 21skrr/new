<?php
// export-data.php
require_once 'includes/auth.php';
require_once 'config/database.php';
requireRole('HR');

// Fetch filter options
$templates = $pdo->query("SELECT id, title FROM survey_templates ORDER BY title")->fetchAll();
$employees = $pdo->query("SELECT id, full_name FROM users WHERE role = 'EMPLOYEE' ORDER BY full_name")->fetchAll();

// Handle export
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_csv'])) {
    $template_id = $_POST['template_id'] ?? '';
    $employee_ids = $_POST['employee_ids'] ?? [];
    $date_from = $_POST['date_from'] ?? '';
    $date_to = $_POST['date_to'] ?? '';
    $status = $_POST['status'] ?? '';

    $params = [];
    $where = [];
    if ($template_id) {
        $where[] = 'st.id = ?';
        $params[] = $template_id;
    }
    if (!empty($employee_ids)) {
        $in = implode(',', array_fill(0, count($employee_ids), '?'));
        $where[] = 'u.id IN (' . $in . ')';
        $params = array_merge($params, $employee_ids);
    }
    if ($date_from) {
        $where[] = 'sr.responded_at >= ?';
        $params[] = $date_from . ' 00:00:00';
    }
    if ($date_to) {
        $where[] = 'sr.responded_at <= ?';
        $params[] = $date_to . ' 23:59:59';
    }
    if ($status === 'completed') {
        $where[] = "ass.status = 'completed'";
    }
    $where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
    $sql = "SELECT u.full_name AS employee_name, st.title AS survey_title, sq.question_text, sq.question_type, sr.answer, sr.responded_at, st.description, ass.due_date, ass.status, sup.full_name AS supervisor_name
            FROM survey_responses sr
            JOIN survey_questions sq ON sr.question_id = sq.id
            JOIN assigned_surveys ass ON sr.assigned_survey_id = ass.id
            JOIN users u ON ass.employee_id = u.id
            JOIN users sup ON ass.supervisor_id = sup.id
            JOIN survey_templates st ON ass.template_id = st.id
            $where_sql
            ORDER BY sr.responded_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Output CSV headers
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="survey_export_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Employee Name', 'Survey Title', 'Question', 'Type', 'Answer', 'Date Answered', 'Template Description', 'Due Date', 'Supervisor Name', 'Status']);
    foreach ($rows as $row) {
        fputcsv($out, [
            $row['employee_name'],
            $row['survey_title'],
            $row['question_text'],
            $row['question_type'],
            $row['answer'],
            $row['responded_at'],
            $row['description'],
            $row['due_date'],
            $row['supervisor_name'],
            $row['status'],
        ]);
    }
    fclose($out);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Export Survey Data</title>
    <style>
        body { background: #f4f6f8; font-family: Arial, sans-serif; height: 100vh; margin: 0; }
        .centered-container { min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; }
        .content-box { background: #fff; padding: 2.5rem 2.5rem 2rem 2.5rem; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); min-width: 350px; position: relative; max-width: 700px; }
        h2 { margin-bottom: 1.5rem; text-align: center; }
        .back-btn { position: absolute; top: 1.5rem; right: 2rem; background:#007bff; color:#fff; padding:0.5em 1.2em; border-radius:4px; text-decoration:none; font-size:1em; }
        .back-btn:hover { background: #0056b3; }
        form { margin-top: 1.5em; }
        label { display: block; margin-top: 1em; font-weight: bold; }
        select, input[type=date] { width: 100%; padding: 0.7em; margin-top: 0.3em; border: 1px solid #ccc; border-radius: 4px; font-size: 1em; }
        .submit-btn { background: #007bff; color: #fff; border: none; border-radius: 4px; padding: 0.6em 1.5em; font-size: 1em; cursor: pointer; margin-top: 2em; width: 100%; }
    </style>
</head>
<body>
    <div class="centered-container">
        <div class="content-box">
            <a href="hr-dashboard.php" class="back-btn">&larr; Back to Dashboard</a>
            <h2>Export Survey Data</h2>
            <form method="post" action="">
                <label>Survey Template
                    <select name="template_id">
                        <option value="">All Surveys</option>
                        <?php foreach ($templates as $tpl): ?>
                            <option value="<?= $tpl['id'] ?>"><?= htmlspecialchars($tpl['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Employee(s)
                    <select name="employee_ids[]" multiple size="5">
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Date From
                    <input type="date" name="date_from">
                </label>
                <label>Date To
                    <input type="date" name="date_to">
                </label>
                <label>Status
                    <select name="status">
                        <option value="">All</option>
                        <option value="completed">Completed Only</option>
                    </select>
                </label>
                <button type="submit" name="export_csv" class="submit-btn">Export CSV</button>
            </form>
        </div>
    </div>
</body>
</html> 