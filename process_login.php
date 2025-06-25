<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        // Redirect by role
        switch ($user['role']) {
            case 'EMPLOYEE':
                header('Location: employee-dashboard.php');
                break;
            case 'SUPERVISOR':
                header('Location: supervisor-dashboard.php');
                break;
            case 'HR':
                header('Location: hr-dashboard.php');
                break;
            default:
                header('Location: login.php?error=Unknown user role');
        }
        exit;
    } else {
        header('Location: login.php?error=Invalid email or password');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
?>
