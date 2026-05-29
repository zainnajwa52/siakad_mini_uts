<?php
class DosenRepository {
    private $pdo;
    
    public function __construct() {
        $this->pdo = $this->get_pdo();
    }
    
    private function get_pdo() {
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
    
    public function all($search = '', $status = '', $progdi = '', $sort = 'nama', $dir = 'ASC', $page = 1, $per_page = 5) {
        $conds = [];
        $params = [];
        
        if ($search) {
            $conds[] = "(nidn LIKE ? OR nama LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($status) { $conds[] = "status = ?"; $params[] = $status; }
        if ($progdi) { $conds[] = "program_studi = ?"; $params[] = $progdi; }
        
        $where = $conds ? 'WHERE ' . implode(' AND ', $conds) . ' AND deleted_at IS NULL' : 'WHERE deleted_at IS NULL';
        $offset = ($page - 1) * $per_page;
        
        $sql = "SELECT d.*, 
                (SELECT COUNT(*) FROM dosen_matakuliah WHERE dosen_id = d.id) as total_mk 
                FROM dosen d $where ORDER BY $sort $dir LIMIT $per_page OFFSET $offset";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function count($search = '', $status = '', $progdi = '') {
        $conds = [];
        $params = [];
        
        if ($search) {
            $conds[] = "(nidn LIKE ? OR nama LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($status) { $conds[] = "status = ?"; $params[] = $status; }
        if ($progdi) { $conds[] = "program_studi = ?"; $params[] = $progdi; }
        
        $where = $conds ? 'WHERE ' . implode(' AND ', $conds) . ' AND deleted_at IS NULL' : 'WHERE deleted_at IS NULL';
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM dosen $where");
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM dosen WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($data, $mk = []) {
        $pdo = $this->pdo;
        $pdo->beginTransaction();
        
        try {
            $stmt = $pdo->prepare("INSERT INTO dosen (nidn, nama, email, program_studi, foto, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['nidn'], $data['nama'], $data['email'], $data['program_studi'], $data['foto'] ?? null, $data['status'] ?? 'aktif']);
            $id = $pdo->lastInsertId();
            
            if (!empty($mk)) {
                $s = $pdo->prepare("INSERT INTO dosen_matakuliah (dosen_id, matakuliah_id, semester) VALUES (?, ?, 'Ganjil')");
                foreach ($mk as $m) { $s->execute([$id, $m]); }
            }
            
            log_activity($_SESSION['user_id'] ?? 1, 'create', 'dosen', $id, 'Created');
            $pdo->commit();
            return $id;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    
    public function update($id, $data, $mk = []) {
        $pdo = $this->pdo;
        $pdo->beginTransaction();
        
        try {
            $stmt = $pdo->prepare("UPDATE dosen SET nidn=?, nama=?, email=?, program_studi=?, foto=COALESCE(?, foto), status=? WHERE id=?");
            $stmt->execute([$data['nidn'], $data['nama'], $data['email'], $data['program_studi'], $data['foto'] ?? null, $data['status'], $id]);
            
            $pdo->prepare("DELETE FROM dosen_matakuliah WHERE dosen_id = ?")->execute([$id]);
            
            if (!empty($mk)) {
                $s = $pdo->prepare("INSERT INTO dosen_matakuliah (dosen_id, matakuliah_id, semester) VALUES (?, ?, 'Ganjil')");
                foreach ($mk as $m) { $s->execute([$id, $m]); }
            }
            
            log_activity($_SESSION['user_id'] ?? 1, 'update', 'dosen', $id, 'Updated');
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    
    public function delete($id) {
        $pdo = $this->pdo;
        $pdo->beginTransaction();
        
        try {
            $pdo->prepare("UPDATE dosen SET deleted_at = NOW() WHERE id = ?")->execute([$id]);
            log_activity($_SESSION['user_id'] ?? 1, 'delete', 'dosen', $id, 'Deleted');
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    
    public function restore($id) {
        $pdo = $this->pdo;
        $pdo->beginTransaction();
        
        try {
            $pdo->prepare("UPDATE dosen SET deleted_at = NULL WHERE id = ?")->execute([$id]);
            log_activity($_SESSION['user_id'] ?? 1, 'restore', 'dosen', $id, 'Restored');
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    
    public function getTrash() {
        return $this->pdo->query("SELECT * FROM dosen WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getMatakuliah($dosen_id) {
        $stmt = $this->pdo->prepare("SELECT matakuliah_id FROM dosen_matakuliah WHERE dosen_id = ?");
        $stmt->execute([$dosen_id]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'matakuliah_id');
    }
    
    public function getAllMatakuliah() {
        return $this->pdo->query("SELECT * FROM mata_kuliah ORDER BY nama")->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getStatistics() {
        $stats = [];
        $stats['total'] = $this->pdo->query("SELECT COUNT(*) FROM dosen WHERE deleted_at IS NULL")->fetchColumn();
        $stats['aktif'] = $this->pdo->query("SELECT COUNT(*) FROM dosen WHERE status = 'aktif' AND deleted_at IS NULL")->fetchColumn();
        $stats['nonaktif'] = $this->pdo->query("SELECT COUNT(*) FROM dosen WHERE status = 'nonaktif' AND deleted_at IS NULL")->fetchColumn();
        $stats['sks'] = $this->pdo->query("SELECT SUM(m.sks) FROM mata_kuliah m JOIN dosen_matakuliah dm ON m.id = dm.matakuliah_id JOIN dosen d ON d.id = dm.dosen_id WHERE d.deleted_at IS NULL")->fetchColumn();
        
        $stmt = $this->pdo->query("SELECT program_studi, COUNT(*) as jumlah FROM dosen WHERE deleted_at IS NULL GROUP BY program_studi");
        $stats['per_progdi'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }
    
    public function getActivityLog($limit = 50) {
        $stmt = $this->pdo->query("
            SELECT al.*, u.username 
            FROM activity_log al 
            LEFT JOIN users u ON al.user_id = u.id 
            ORDER BY al.created_at DESC 
            LIMIT $limit
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}