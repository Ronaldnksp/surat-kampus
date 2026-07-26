<?php
// ============================================
// LOGIN PAGE
// ============================================
require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi';
    } elseif (login($email, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Email atau password salah';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>📋 <?= APP_NAME ?></h1>
                <p>Silakan masuk ke akun Anda</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="Masukkan email" value="<?= htmlspecialchars($email ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Masukkan password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Masuk</button>
            </form>
            
            <div class="login-footer">
                <p><strong>Demo Accounts:</strong></p>
                <small>
                    Staff: staff@kampus.ac.id / password<br>
                    Dekan: dekan@kampus.ac.id / password<br>
                    Mahasiswa: mahasiswa@kampus.ac.id / password
                </small>
            </div>
        </div>
    </div>
</body>
</html>
