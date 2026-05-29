<?php
require_once __DIR__ . '/../src/Auth.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$csrf_token = csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Token CSRF tidak valid!';
    } else {
        if (do_login($_POST['username'] ?? '', $_POST['password'] ?? '')) {
            header('Location: index.php');
            exit;
        } else {
            // Ambil error dari Auth
            global $login_error;
            $error = $login_error ?? 'Login gagal!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIAKAD Mini</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
    <div class="login-card">
        <div class="login-header">
            <div class="logo">🎓</div>
            <h1> Sistem Informasi Manajemen Dosen & Mata Kuliah</h1>
            <p>Login untuk melanjutkan</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-msg">❌ <?= h($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            
            <div class="form-group">
                <label>👤 Username</label>
                <input type="text" name="username" placeholder="Username" required>
            </div>
            
            <div class="form-group">
                <label>🔑 Password</label>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            
            <button type="submit" class="btn-login">🚀 MASUK</button>
        </form>
        
        <div class="demo-box">
            <strong>Akun Demo:</strong><br>
            admin / password123<br>
            operator / password123
        </div>
    </div>
</body>
</html>