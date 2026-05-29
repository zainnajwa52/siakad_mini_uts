<?php
/**
 * Validator.php
 * Validasi reusable untuk form
 */

class Validator {
    private $errors = [];
    
    /**
     * Validasi required (wajib diisi)
     */
    public function required($value, $field_name) {
        if (empty(trim($value))) {
            $this->errors[] = "$field_name wajib diisi";
            return false;
        }
        return true;
    }
    
    /**
     * Validasi email
     */
    public function email($value, $field_name) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "$field_name tidak valid";
            return false;
        }
        return true;
    }
    
    /**
     * Validasi unique (tidak boleh sama di DB)
     */
    public function unique($pdo, $table, $column, $value, $exclude_id = null) {
        $sql = "SELECT COUNT(*) FROM $table WHERE $column = ?";
        $params = [$value];
        
        if ($exclude_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $this->errors[] = "$column sudah ada!";
            return false;
        }
        return true;
    }
    
    /**
     * Validasi MIME file upload
     */
    public function mime($file_tmp_name, $allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file_tmp_name);
        
        if (!in_array($mime, $allowed_mime)) {
            $this->errors[] = "File harus berupa gambar (JPEG, PNG, GIF, WebP)";
            return false;
        }
        return true;
    }
    
    /**
     * Validasi max length
     */
    public function maxLength($value, $max, $field_name) {
        if (strlen($value) > $max) {
            $this->errors[] = "$field_name maksimal $max karakter";
            return false;
        }
        return true;
    }
    
    /**
     * Validasi min length
     */
    public function minLength($value, $min, $field_name) {
        if (strlen($value) < $min) {
            $this->errors[] = "$field_name minimal $min karakter";
            return false;
        }
        return true;
    }
    
    /**
     * Validasi enum (pilih dari opsi)
     */
    public function in($value, $options, $field_name) {
        if (!in_array($value, $options)) {
            $this->errors[] = "$field_name tidak valid";
            return false;
        }
        return true;
    }
    
    /**
     * Ambil semua error
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Cek ada error apa ga
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    /**
     * Validasi NIDN (format)
     */
    public function nidn($value) {
        if (!preg_match('/^[0-9]{3,10}$/', $value)) {
            $this->errors[] = "NIDN harus angka 3-10 digit";
            return false;
        }
        return true;
    }
}