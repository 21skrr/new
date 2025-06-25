<?php
if (!isset($supervisor_page_title)) $supervisor_page_title = '';
if (!isset($user)) $user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($supervisor_page_title) ?> - Supervisor Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f4f6f8;
        }
        .supervisor-layout {
            display: flex;
            min-height: 100vh;
        }
        .supervisor-sidebar {
            width: 220px;
            background: #232946;
            color: #fff;
            display: flex;
            flex-direction: column;
            padding: 2em 1em 1em 1em;
            box-shadow: 2px 0 12px rgba(0,0,0,0.04);
        }
        .supervisor-sidebar h2 {
            font-size: 1.3em;
            margin-bottom: 2em;
            letter-spacing: 1px;
            color: #fff;
        }
        .supervisor-sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 0.7em 1em;
            border-radius: 6px;
            margin-bottom: 0.5em;
            font-size: 1.05em;
            display: block;
            transition: background 0.15s;
        }
        .supervisor-sidebar a.active, .supervisor-sidebar a:hover {
            background: #394867;
        }
        .supervisor-sidebar .sidebar-footer {
            margin-top: auto;
            font-size: 0.95em;
            color: #bfc9d1;
            padding-top: 2em;
        }
        .supervisor-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .supervisor-header {
            background: #fff;
            padding: 1.2em 2em 1em 2em;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .supervisor-header .supervisor-title {
            font-size: 1.3em;
            font-weight: 600;
            color: #232946;
        }
        .supervisor-header .supervisor-user {
            color: #232946;
            font-size: 1em;
            font-weight: 500;
        }
        .supervisor-main-section {
            padding: 2.5em 3em 2em 3em;
            flex: 1;
            background: #f4f6f8;
            min-height: 0;
        }
        @media (max-width: 900px) {
            .supervisor-layout { flex-direction: column; }
            .supervisor-sidebar { width: 100%; flex-direction: row; padding: 1em; }
            .supervisor-sidebar a { margin-bottom: 0; margin-right: 0.5em; }
            .supervisor-main-section { padding: 1.5em 1em; }
        }
    </style>
</head>
<body>
<div class="supervisor-layout">
    <nav class="supervisor-sidebar">
        <h2>Supervisor Portal</h2>
        <a href="supervisor-dashboard.php"<?= strpos($supervisor_page_title, 'Dashboard') !== false ? ' class="active"' : '' ?>>Dashboard</a>
        <a href="analyze-results.php"<?= strpos($supervisor_page_title, 'Results') !== false ? ' class="active"' : '' ?>>Team Results</a>
        <a href="supervisor-profile.php"<?= strpos($supervisor_page_title, 'Profile') !== false ? ' class="active"' : '' ?>>Profile</a>
        <a href="logout.php">Logout</a>
        <div class="sidebar-footer">
            <span><?= htmlspecialchars($user['full_name'] ?? '') ?></span><br>
            <span style="font-size:0.95em; color:#bfc9d1;">Supervisor</span>
        </div>
    </nav>
    <div class="supervisor-main">
        <header class="supervisor-header">
            <span class="supervisor-title"><?= htmlspecialchars($supervisor_page_title) ?></span>
            <span class="supervisor-user">👤 <?= htmlspecialchars($user['full_name'] ?? '') ?></span>
        </header>
        <main class="supervisor-main-section">
            <!-- Main content will be injected here -->
        </main>
    </div>
</div>
</body>
</html> 