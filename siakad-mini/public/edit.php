<?php
require_once __DIR__ . '/../src/Auth.php';
require_role('admin');

require_once __DIR__ . '/../src/DosenRepository.php';
$repo = new DosenRepository();

$id = $_GET['id'] ?? 0;
$dosen = $repo->find($id);

if (!$dosen) {
    die("Data tidak ditemukan!");
}

$all_mk = $repo->getAllMatakuliah();
$mk_terpilih = $repo->getMatakuliah($id);

$errors = [];
$old = $dosen;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['nidn'] = trim($_POST['nidn'] ?? '');
    $old['nama'] = trim($_POST['nama'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['program_studi'] = $_POST['program_studi'] ?? 'Teknik Informatika';
    $old['status'] = $_POST['status'] ?? 'aktif';
    $mk_selected = $_POST['matakuliah'] ?? [];
    
    if (empty($old['nidn'])) $errors[] = 'NIDN wajib diisi';
    if (empty($old['nama'])) $errors[] = 'Nama wajib diisi';
    if (empty($old['email'])) $errors[] = 'Email wajib diisi';
    
    if (empty($errors)) {
        $foto_name = $old['foto'];
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($_FILES['foto']['tmp_name']);
            if (in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $foto_name = bin2hex(random_bytes(8)) . '.' . $ext;
                move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . '/../uploads/' . $foto_name);
            }
        }
        
        $repo->update($id, ['nidn' => $old['nidn'], 'nama' => $old['nama'], 'email' => $old['email'], 'program_studi' => $old['program_studi'], 'foto' => $foto_name, 'status' => $old['status']], $mk_selected);
        
        header('Location: index.php?success=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Dosen - SIAKAD Mini</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header-main">
            <h1>✏️ Edit Dosen</h1>
            <a href="index.php" class="btn-reset">← Kembali</a>
        </header>
        
        <main class="card-main">
            <?php if ($errors): ?>
            <div class="error-msg"><?php foreach ($errors as $e) echo h($e) . '<br>'; ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
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
                    <label>Foto (kosongkan jika tidak diubah)</label>
                    <input type="file" name="foto" accept="image/*">
                </div>
                
                <div class="form-full">
                    <label>Mata Kuliah yang Diampu</label>
                    <div class="mk-grid">
                        <?php foreach ($all_mk as $mk): ?>
                        <div class="mk-item">
                            <input type="checkbox" name="matakuliah[]" value="<?= $mk['id'] ?>" id="mk<?= $mk['id'] ?>" <?= in_array($mk['id'], $mk_terpilih) ? 'checked' : '' ?>>
                            <label for="mk<?= $mk['id'] ?>"><?= h($mk['kode'] . ' - ' . $mk['nama']) ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <button type="submit" class="btn-simpan">💾 Simpan Perubahan</button>
                <a href="index.php" class="btn-batal">Batal</a>
            </form>
        </main>
    </div>
</body>
</html>