<?php
session_start();
if (isset($_SESSION['user'])) {
    $role = strtolower($_SESSION['user']['role']);
    header("Location: {$role}-dashboard.php");
    exit;
}
$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Role-Based System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: linear-gradient(to right, #e0eafc, #cfdef3);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .login-container {
            width: 100%;
            max-width: 380px;
            background: #ffffff;
            padding: 2.5rem 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 1.8rem;
            font-weight: 600;
        }

        .error {
            background-color: #ffe0e0;
            color: #cc0000;
            padding: 0.75rem;
            margin-bottom: 1.25rem;
            text-align: center;
            border-radius: 6px;
            font-size: 0.95rem;
        }

        input[type=email],
        input[type=password] {
            width: 100%;
            padding: 0.75rem 1rem;
            margin-bottom: 1.2rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        input[type=email]:focus,
        input[type=password]:focus {
            border-color: #007bff;
            outline: none;
        }

        button[type=submit] {
            width: 100%;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 0.8rem 0;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }

        button[type=submit]:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="process_login.php">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
