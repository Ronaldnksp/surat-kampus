<?php
// ============================================
// AUTHENTICATION FUNCTIONS
// ============================================

require_once __DIR__ . '/config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

function requireRole($roles) {
    requireLogin();
    if (!in_array($_SESSION['role'], (array)$roles)) {
        header('Location: dashboard.php?error=unauthorized');
        exit;
    }
}

function login($email, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['role_label'] = getRoleLabel($user['role']);
        
        // Update last login
        $stmt = $db->prepare("UPDATE users SET updated_at = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    header('Location: index.php');
    exit;
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function getRoleLabel($role) {
    return match($role) {
        'mahasiswa' => 'Mahasiswa',
        'staff' => 'Staff Administrasi',
        'dekan' => 'Dekan Fakultas',
        default => $role,
    };
}

function getRoleBadgeClass($role) {
    return match($role) {
        'mahasiswa' => 'badge-info',
        'staff' => 'badge-warning',
        'dekan' => 'badge-success',
        default => 'badge-secondary',
    };
}
