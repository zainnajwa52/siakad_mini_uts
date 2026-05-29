<?php
require_once __DIR__ . '/../src/Auth.php';
require_role('admin');

require_once __DIR__ . '/../src/DosenRepository.php';
$repo = new DosenRepository();
$trash = $repo->getTrash();

if (isset($_GET['restore'])) {
    $repo->restore($_GET['restore']);
    header('Location: trash.php?success=restore');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sampah - SIAKAD Mini</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header-main">
            <h1>🗑️ Sampah Dosen</h1>
            <a href="index.php" class="btn-reset">← Kembali</a>
        </header>
        
        <main class="card-main">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>NIDN</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Dihapus Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($trash)): ?>
                        <tr><td colspan="5" class="empty">Sampah kosong</td></tr>
                        <?php else: ?>
                            <?php foreach ($trash as $t): ?>
                            <tr>
                                <td><?= h($t['nidn']) ?></td>
                                <td><?= h($t['nama']) ?></td>
                                <td><?= h($t['email']) ?></td>
                                <td><?= h($t['deleted_at']) ?></td>
                                <td>
                                    <a href="trash.php?restore=<?= $t['id'] ?>" class="btn-action btn-edit" onclick="return confirm('Restore?')">♻️ Restore</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>