<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

// Check if user has required role
function hasRole($required_role) {
    return isLoggedIn() && $_SESSION['user_role'] === $required_role;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect if user doesn't have required role
function requireRole($required_role) {
    requireLogin();
    if (!hasRole($required_role)) {
        header('Location: unauthorized.php');
        exit();
    }
}

// Get current user info
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role']
    ];
}

// Logout user
function logout() {
    session_destroy();
    header('Location: login.php');
    exit();
}

function require_role($role) {
    if ($_SESSION['user']['role'] !== $role) {
        header('Location: unauthorized.php');
        exit;
    }
}
?>
