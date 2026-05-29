<?php
/**
 * File: public/restore.php
 * Restore data dosen dari sampah (NULL-kan deleted_at)
 */

require_once __DIR__ . '/../src/Auth.php';
require_login(); // Pastikan sudah login

// RBAC: Hanya admin yang bisa restore
require_role('admin');

// ============================================
// LANGKAH 1: Cek Method POST
// ============================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Method tidak dizinkan! Gunakan form untuk merestore.");
}

// ============================================
// LANGKAH 2: CSRF Validation
// ============================================
$csrf = $_POST['csrf_token'] ?? '';
if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
    die("Token CSRF tidak valid!");
}

// ============================================
// LANGKAH 3: Validasi ID
// ============================================
$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

if (!$id) {
    die("ID tidak valid!");
}

require_once __DIR__ . '/../src/DosenRepository.php';
$repo = new DosenRepository();

// ============================================
// LANGKAH 4: Cek Apakah Data Ada di Sampah
// ============================================
$dosens = $repo->getTrash();
$exists = false;

foreach ($dosens as $d) {
    if ($d['id'] == $id) {
        $exists = true;
        break;
    }
}

if (!$exists) {
    die("Data tidak ada di sampah!");
}

// ============================================
// LANGKAH 5: Restore (SET deleted_at = NULL)
// ============================================
try {
    $repo->restore($id);
    
    // Regenerate CSRF token
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    
    // Tambah audit log
    $repo->logActivity(
    $_SESSION['user_id'], 
    'restore', 
    'dosen', 
    $id, 
    'Restore dosen ID: ' . $id
    );
    
    header('Location: trash.php?success=restore');
    exit;
    
} catch (PDOException $e) {
    die("Gagal merestore data: " . $e->getMessage());
}