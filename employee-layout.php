<?php
if (!isset($employee_page_title)) $employee_page_title = '';
if (!isset($user)) $user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($employee_page_title) ?> - Employee Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f4f6f8;
        }
        .employee-layout {
            display: flex;
            min-height: 100vh;
        }
        .employee-sidebar {
            width: 220px;
            background: #232946;
            color: #fff;
            display: flex;
            flex-direction: column;
            padding: 2em 1em 1em 1em;
            box-shadow: 2px 0 12px rgba(0,0,0,0.04);
        }
        .employee-sidebar h2 {
            font-size: 1.3em;
            margin-bottom: 2em;
            letter-spacing: 1px;
            color: #fff;
        }
        .employee-sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 0.7em 1em;
            border-radius: 6px;
            margin-bottom: 0.5em;
            font-size: 1.05em;
            display: block;
            transition: background 0.15s;
        }
        .employee-sidebar a.active, .employee-sidebar a:hover {
            background: #394867;
        }
        .employee-sidebar .sidebar-footer {
            margin-top: auto;
            font-size: 0.95em;
            color: #bfc9d1;
            padding-top: 2em;
        }
        .employee-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .employee-header {
            background: #fff;
            padding: 1.2em 2em 1em 2em;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .employee-header .employee-title {
            font-size: 1.3em;
            font-weight: 600;
            color: #232946;
        }
        .employee-header .employee-user {
            color: #232946;
            font-size: 1em;
            font-weight: 500;
        }
        .employee-main-section {
            padding: 2.5em 3em 2em 3em;
            flex: 1;
            background: #f4f6f8;
            min-height: 0;
        }
        @media (max-width: 900px) {
            .employee-layout { flex-direction: column; }
            .employee-sidebar { width: 100%; flex-direction: row; padding: 1em; }
            .employee-sidebar a { margin-bottom: 0; margin-right: 0.5em; }
            .employee-main-section { padding: 1.5em 1em; }
        }
    </style>
</head>
<body>
<div class="employee-layout">
    <nav class="employee-sidebar">
        <h2>Employee Portal</h2>
        <a href="employee-dashboard.php"<?= strpos($employee_page_title, 'Dashboard') !== false ? ' class="active"' : '' ?>>Dashboard</a>
        <a href="view-employee-results.php"<?= strpos($employee_page_title, 'Results') !== false ? ' class="active"' : '' ?>>My Results</a>
        <a href="employee-profile.php"<?= strpos($employee_page_title, 'Profile') !== false ? ' class="active"' : '' ?>>Profile</a>
        <a href="logout.php">Logout</a>
        <div class="sidebar-footer">
            <span><?= htmlspecialchars($user['full_name'] ?? '') ?></span><br>
            <span style="font-size:0.95em; color:#bfc9d1;">Employee</span>
        </div>
    </nav>
    <div class="employee-main">
        <header class="employee-header">
            <span class="employee-title"><?= htmlspecialchars($employee_page_title) ?></span>
            <span class="employee-user">👤 <?= htmlspecialchars($user['full_name'] ?? '') ?></span>
        </header>
        <main class="employee-main-section">
            <!-- Main content will be injected here -->
        </main>
    </div>
</div>
</body>
</html> 