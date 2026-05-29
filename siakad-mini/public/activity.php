<?php
require_once __DIR__ . '/../src/Auth.php';
require_role('admin');

require_once __DIR__ . '/../src/DosenRepository.php';
$repo = new DosenRepository();
$logs = $repo->getActivityLog();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log - SIAKAD Mini</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header-main">
            <h1>📋 Activity Log</h1>
            <a href="index.php" class="btn-reset">← Kembali</a>
        </header>
        
        <main class="card-main">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>User</th>
                            <th>Aksi</th>
                            <th>Entitas</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= h($log['created_at']) ?></td>
                            <td><?= h($log['username'] ?? 'System') ?></td>
                            <td><?= h($log['aksi']) ?></td>
                            <td><?= h($log['entitas']) ?></td>
                            <td><?= h($log['keterangan']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>