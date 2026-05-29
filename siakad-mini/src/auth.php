<?php
session_start();

function get_pdo() {
    static $pdo = null;
    if ($pdo === null) {
        require_once __DIR__ . '/../config/database.php';
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }
    return $pdo;
}

// Rate Limiting Login
$login_error = '';
$login_attempts = $_SESSION['login_attempts'] ?? 0;

function do_login($username, $password) {
    global $login_error, $login_attempts;
    
    // Rate limit - blokir 5 percobaan gagal
    if ($login_attempts >= 5) {
        $login_error = 'Terlalu banyak percobaan login. Tunggu 5 menit!';
        return false;
    }
    
    $pdo = get_pdo();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($password, $user['password_hash'])) {
        $_SESSION['login_attempts'] = $login_attempts + 1;
        $login_error = 'Username atau password salah!';
        return false;
    }
    
    // Reset attempts kalau berhasil
    $_SESSION['login_attempts'] = 0;
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    session_regenerate_id(true);
    
    log_activity($user['id'], 'login', 'users', $user['id'], 'Login');
    return true;
}

function logout() {
    if (isset($_SESSION['user_id'])) {
        log_activity($_SESSION['user_id'], 'logout', 'users', $_SESSION['user_id'], 'Logout');
    }
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function require_role($role) {
    require_login();
    if ($_SESSION['role'] !== $role && $_SESSION['role'] !== 'admin') {
        die("Akses ditolak! (IDOR Blocked)");
    }
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function log_activity($user_id, $aksi, $entitas, $entitas_id, $keterangan = '') {
    try {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, aksi, entitas, entitas_id, keterangan) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $aksi, $entitas, $entitas_id, $keterangan]);
    } catch (Exception $e) {}
}

function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}