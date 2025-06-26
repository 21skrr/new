<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Tchecki wach user dakhil
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

// Tchecki wach user 3ando role li khas
function hasRole($required_role) {
    return isLoggedIn() && $_SESSION['user_role'] === $required_role;
}

// Ila user ma dakhilch, rje3h l login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Ila user ma 3andouch role li khas, rje3h l unauthorized
function requireRole($required_role) {
    requireLogin();
    if (!hasRole($required_role)) {
        header('Location: unauthorized.php');
        exit();
    }
}

// Jib info dyal user li dakhil daba
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

// Log out user
function logout() {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Tchecki role b tari9a okhra
function require_role($role) {
    if ($_SESSION['user']['role'] !== $role) {
        header('Location: unauthorized.php');
        exit;
    }
}
?>
