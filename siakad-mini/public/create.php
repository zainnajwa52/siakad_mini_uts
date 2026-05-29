<?php
require_once __DIR__ . '/../src/Auth.php';
require_role('admin');
require_once __DIR__ . '/../src/DosenRepository.php';
require_once __DIR__ . '/../src/Validator.php';

$repo = new DosenRepository();
$all_mk = $repo->getAllMatakuliah();
$csrf_token = csrf_token();

$errors = [];
$old = ['nidn' => '', 'nama' => '', 'email' => '', 'program_studi' => 'Teknik Informatika', 'status' => 'aktif'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF
    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token CSRF tidak valid!';
    }
    
    $old['nidn'] = trim($_POST['nidn'] ?? '');
    $old['nama'] = trim($_POST['nama'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['program_studi'] = $_POST['program_studi'] ?? 'Teknik Informatika';
    $old['status'] = $_POST['status'] ?? 'aktif';
    $mk_selected = $_POST['matakuliah'] ?? [];
    
    // Pakai Validator Class
    $v = new Validator();
    
    $v->required($old['nidn'], 'NIDN');
    $v->nidn($old['nidn']);
    $v->required($old['nama'], 'Nama');
    $v->maxLength($old['nama'], 100, 'Nama');
    $v->required($old['email'], 'Email');
    $v->email($old['email'], 'Email');
    $v->in($old['program_studi'], ['Teknik Informatika', 'Sistem Informasi', 'Teknik Elektro'], 'Program Studi');
    $v->in($old['status'], ['aktif', 'nonaktif'], 'Status');
    
    // Cek unique NIDN & Email
    $pdo = get_pdo();
    $v->unique($pdo, 'dosen', 'nidn', $old['nidn']);
    $v->unique($pdo, 'dosen', 'email', $old['email']);
    
    if (!$v->hasErrors()) {
        $foto_name = null;
        
        // Upload with Validator
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            if ($v->mime($_FILES['foto']['tmp_name'])) {
                $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $foto_name = bin2hex(random_bytes(8)) . '.' . $ext;
                move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . '/../uploads/' . $foto_name);
            }
        }
        
        if (!$v->hasErrors()) {
            $repo->create([
                'nidn' => $old['nidn'],
                'nama' => $old['nama'],
                'email' => $old['email'],
                'program_studi' => $old['program_studi'],
                'foto' => $foto_name,
                'status' => $old['status']
            ], $mk_selected);
            
            header('Location: index.php?success=1');
            exit;
        }
    }
    
    $errors = $v->getErrors();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Dosen - SIAKAD Mini</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header-main">
            <h1>➕ Tambah Dosen</h1>
            <a href="index.php" class="btn-reset">← Kembali</a>
        </header>
        
        <main class="card-main">
            <?php if ($errors): ?>
            <div class="error-msg">
                <?php foreach ($errors as $e): ?>
                <?= h($e) ?><br>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="form-row">
                    <div class="form-full">
                        <label>NIDN</label>
                        <input type="text" name="nidn" value="<?= h($old['nidn']) ?>" required>
                    </div>
                    <div class="form-full">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= h($old['nama']) ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-full">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= h($old['email']) ?>" required>
                    </div>
                    <div class="form-full">
                        <label>Program Studi</label>
                        <select name="program_studi">
                            <option value="Teknik Informatika" <?= $old['program_studi'] === 'Teknik Informatika' ? 'selected' : '' ?>>Teknik Informatika</option>
                            <option value="Sistem Informasi" <?= $old['program_studi'] === 'Sistem Informasi' ? 'selected' : '' ?>>Sistem Informasi</option>
                            <option value="Teknik Elektro" <?= $old['program_studi'] === 'Teknik Elektro' ? 'selected' : '' ?>>Teknik Elektro</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-full">
                    <label>Status</label>
                    <select name="status">
                        <option value="aktif" <?= $old['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="nonaktif" <?= $old['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
                
                <div class="form-full">
                    <label>Foto</label>
                    <input type="file" name="foto" accept="image/*">
                </div>
                
                <div class="form-full">
                    <label>Mata Kuliah</label>
                    <div class="mk-grid">
                        <?php foreach ($all_mk as $mk): ?>
                        <div class="mk-item">
                            <input type="checkbox" name="matakuliah[]" value="<?= $mk['id'] ?>" id="mk<?= $mk['id'] ?>">
                            <label for="mk<?= $mk['id'] ?>"><?= h($mk['kode'] . ' - ' . $mk['nama']) ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <button type="submit" class="btn-simpan">💾 Simpan</button>
                <a href="index.php" class="btn-batal">Batal</a>
            </form>
        </main>
    </div>
</body>
</html>