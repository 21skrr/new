<?php
// hr-layout.php
if (!isset($hr_page_title)) $hr_page_title = 'HR Dashboard';
if (!isset($user)) $user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($hr_page_title) ?> - HR Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f4f6f8;
        }
        .hr-layout {
            display: flex;
            min-height: 100vh;
        }
        .hr-sidebar {
            width: 220px;
            background: #232946;
            color: #fff;
            display: flex;
            flex-direction: column;
            padding: 2em 1em 1em 1em;
            box-shadow: 2px 0 12px rgba(0,0,0,0.04);
        }
        .hr-sidebar h2 {
            font-size: 1.3em;
            margin-bottom: 2em;
            letter-spacing: 1px;
            color: #fff;
        }
        .hr-sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 0.7em 1em;
            border-radius: 6px;
            margin-bottom: 0.5em;
            font-size: 1.05em;
            display: block;
            transition: background 0.15s;
        }
        .hr-sidebar a.active, .hr-sidebar a:hover {
            background: #394867;
        }
        .hr-sidebar .sidebar-footer {
            margin-top: auto;
            font-size: 0.95em;
            color: #bfc9d1;
            padding-top: 2em;
        }
        .hr-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .hr-header {
            background: #fff;
            padding: 1.2em 2em 1em 2em;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .hr-header .hr-title {
            font-size: 1.3em;
            font-weight: 600;
            color: #232946;
        }
        .hr-header .hr-user {
            color: #232946;
            font-size: 1em;
            font-weight: 500;
        }
        .hr-main-section {
            padding: 2.5em 3em 2em 3em;
            flex: 1;
            background: #f4f6f8;
            min-height: 0;
        }
        @media (max-width: 900px) {
            .hr-layout { flex-direction: column; }
            .hr-sidebar { width: 100%; flex-direction: row; padding: 1em; }
            .hr-sidebar a { margin-bottom: 0; margin-right: 0.5em; }
            .hr-main-section { padding: 1.5em 1em; }
        }
    </style>
</head>
<body>
    <div class="hr-layout">
        <nav class="hr-sidebar">
            <h2>HR Portal</h2>
            <a href="hr-dashboard.php"<?= strpos($hr_page_title, 'Dashboard') !== false ? ' class="active"' : '' ?>>Dashboard</a>
            <a href="manage-users.php"<?= strpos($hr_page_title, 'Manage Users') !== false ? ' class="active"' : '' ?>>Manage Users</a>
            <a href="profile.php"<?= strpos($hr_page_title, 'Profile') !== false ? ' class="active"' : '' ?>>Profile</a>
            <a href="logout.php">Logout</a>
            <div class="sidebar-footer">
                <span><?= htmlspecialchars($user['full_name'] ?? '') ?></span><br>
                <span style="font-size:0.95em; color:#bfc9d1;">HR</span>
            </div>
        </nav>
        <div class="hr-main">
            <header class="hr-header">
                <span class="hr-title"><?= htmlspecialchars($hr_page_title) ?></span>
                <span class="hr-user">👤 <?= htmlspecialchars($user['full_name'] ?? '') ?></span>
            </header>
            <main class="hr-main-section">
                <!-- Main content will be injected here -->
            </main>
        </div>
    </div>
</body>
</html> 