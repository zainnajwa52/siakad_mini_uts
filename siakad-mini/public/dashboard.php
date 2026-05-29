<?php
require_once __DIR__ . '/../src/Auth.php';
require_login();

require_once __DIR__ . '/../src/DosenRepository.php';
$repo = new DosenRepository();
$stats = $repo->getStatistics();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIAKAD Mini</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header-main">
            <h1>📊 Dashboard</h1>
            <a href="index.php" class="btn-reset">← Kembali</a>
        </header>
        
        <main class="card-main">
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Dosen</h3>
                    <div class="stat-number"><?= $stats['total'] ?></div>
                </div>
                <div class="stat-card">
                    <h3>Aktif</h3>
                    <div class="stat-number stat-aktif-stat"><?= $stats['aktif'] ?></div>
                </div>
                <div class="stat-card">
                    <h3>Nonaktif</h3>
                    <div class="stat-number stat-nonaktif-stat"><?= $stats['nonaktif'] ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total SKS</h3>
                    <div class="stat-number"><?= $stats['sks'] ?? 0 ?></div>
                </div>
            </div>
            
            <h3 style="margin-top: 20px; color: #0284c7;">Per Program Studi</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Program Studi</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['per_progdi'] as $p): ?>
                        <tr>
                            <td><?= h($p['program_studi']) ?></td>
                            <td><?= (int)$p['jumlah'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>