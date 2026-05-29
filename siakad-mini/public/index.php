<?php
require_once __DIR__ . '/../src/Auth.php';
require_login();

require_once __DIR__ . '/../src/DosenRepository.php';
$repo = new DosenRepository();

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$progdi = $_GET['progdi'] ?? '';
$sort = $_GET['sort'] ?? 'nama';
$dir = strtoupper($_GET['dir'] ?? 'ASC');
$page = max(1, (int)($_GET['page'] ?? 1));

$allowed = ['nidn', 'nama', 'email', 'program_studi', 'status'];
if (!in_array($sort, $allowed)) $sort = 'nama';
if (!in_array($dir, ['ASC', 'DESC'])) $dir = 'ASC';

$per_page = 5;
$dosens = $repo->all($search, $status, $progdi, $sort, $dir, $page, $per_page);
$total = $repo->count($search, $status, $progdi);
$pages = ceil($total / $per_page);

function sort_url($c, $cs, $cd, $p) {
    $nd = ($cs === $c) ? ($cd === 'ASC' ? 'DESC' : 'ASC') : 'ASC';
    return '?' . http_build_query(array_merge($p, ['sort' => $c, 'dir' => $nd]));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Dosen - SIAKAD Mini</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header-main">
            <div class="header-left">
                <h1>📚 Daftar Dosen</h1>
                <span class="total-data">Total: <?= $total ?> data</span>
            </div>
            <div class="header-right">
                <span class="user-info">👤 <?= h($_SESSION['username']) ?> (<?= h(ucfirst($_SESSION['role'])) ?>)</span>
            </div>
        </header>
        
        <?php if (isset($_GET['success'])): ?>
        <div class="success-msg">
            <?= match($_GET['success']) {
                '1' => '✅ Data berhasil disimpan!',
                'delete' => '🗑️ Data berhasil dihapus!',
                'restore' => '♻️ Data berhasil direstore!'
            } ?>
        </div>
        <?php endif; ?>
        
        <main class="card-main">
            <form method="GET" class="search-bar">
                <input type="text" name="search" placeholder="🔍 Cari NIDN atau Nama..." value="<?= h($search) ?>">
                <select name="status">
                    <option value="">Semua Status</option>
                    <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="nonaktif" <?= $status === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                </select>
                <select name="progdi">
                    <option value="">Semua Prodi</option>
                    <option value="Teknik Informatika" <?= $progdi === 'Teknik Informatika' ? 'selected' : '' ?>>Teknik Informatika</option>
                    <option value="Sistem Informasi" <?= $progdi === 'Sistem Informasi' ? 'selected' : '' ?>>Sistem Informasi</option>
                    <option value="Teknik Elektro" <?= $progdi === 'Teknik Elektro' ? 'selected' : '' ?>>Teknik Elektro</option>
                </select>
                <button type="submit" class="btn-search">🔎 Cari</button>
                <?php if ($search || $status || $progdi): ?>
                    <a href="index.php" class="btn-reset">🔄 Reset</a>
                <?php endif; ?>
            </form>
            
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Foto</th>
                            <th><a href="<?= sort_url('nama', $sort, $dir, $_GET) ?>">Nama <?php if($sort=='nama') echo $dir=='ASC'?'↑':'↓'; ?></a></th>
                            <th><a href="<?= sort_url('nidn', $sort, $dir, $_GET) ?>">NIDN <?php if($sort=='nidn') echo $dir=='ASC'?'↑':'↓'; ?></a></th>
                            <th><a href="<?= sort_url('email', $sort, $dir, $_GET) ?>">Email <?php if($sort=='email') echo $dir=='ASC'?'↑':'↓'; ?></a></th>
                            <th><a href="<?= sort_url('program_studi', $sort, $dir, $_GET) ?>">Prodi <?php if($sort=='program_studi') echo $dir=='ASC'?'↑':'↓'; ?></a></th>
                            <th><a href="<?= sort_url('status', $sort, $dir, $_GET) ?>">Status <?php if($sort=='status') echo $dir=='ASC'?'↑':'↓'; ?></a></th>
                            <th>MK</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($dosens)): ?>
                        <tr><td colspan="9" class="empty">📭 Data tidak ditemukan</td></tr>
                        <?php else: ?>
                            <?php $no = ($page - 1) * $per_page + 1; ?>
                            <?php foreach ($dosens as $d): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <?php if (!empty($d['foto']) && file_exists(__DIR__ . '/../uploads/' . $d['foto'])): ?>
                                        <img src="../uploads/<?= h($d['foto']) ?>" class="foto-tabel">
                                    <?php else: ?>
                                        <div class="foto-tidak">👤</div>
                                    <?php endif; ?>
                                </td>
                                <td><?= h($d['nama']) ?></td>
                                <td><?= h($d['nidn']) ?></td>
                                <td><?= h($d['email']) ?></td>
                                <td><?= h($d['program_studi']) ?></td>
                                <td><span class="status-<?= $d['status'] ?>"><?= h(ucfirst($d['status'])) ?></span></td>
                                <td class="text-center"><?= (int)($d['total_mk'] ?? 0) ?></td>
                                <td>
                                    <a href="edit.php?id=<?= $d['id'] ?>" class="btn-action btn-edit" title="Edit">✏️</a>
                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <a href="delete.php?id=<?= $d['id'] ?>" class="btn-action btn-delete" title="Hapus" onclick="return confirm('Yakin hapus data ini?')">🗑️</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="page-link">« Prev</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="page-active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="page-link"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($page < $pages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="page-link">Next »</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </main>
        
        <nav class="nav-bottom">
            <div class="nav-left">
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="create.php" class="btn-nav btn-success">➕ Tambah</a>
                <a href="trash.php" class="btn-nav btn-warning">🗑️ Sampah</a>
                <a href="activity.php" class="btn-nav btn-purple">📋 Log</a>
                <?php endif; ?>
                <a href="dashboard.php" class="btn-nav btn-info">📊 Dashboard</a>
            </div>
            <div class="nav-right">
                <a href="export.php?<?= http_build_query($_GET) ?>" class="btn-nav btn-export">📥 Export CSV</a>
                <a href="logout.php" class="btn-nav btn-logout">🚪 Logout</a>
            </div>
        </nav>
    </div>
</body>
</html>