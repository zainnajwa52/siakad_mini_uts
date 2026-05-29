<?php
/**
 * File: tools/hash.php
 * Buka: http://localhost/siakad-mini/tools/hash.php
 */

$hash = '';
if (isset($_POST['password'])) {
    $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hash Generator</title>
    <style>
        body { font-family: sans-serif; padding: 2rem; }
        .box { border: 1px solid #ddd; padding: 1.5rem; max-width: 600px; }
        input { width: 100%; padding: 0.5rem; margin-bottom: 1rem; }
        button { padding: 0.5rem 1rem; background: #2563eb; color: white; border: none; }
        .result { background: #d1fae5; padding: 1rem; margin-top: 1rem; word-break: break-all; }
    </style>
</head>
<body>
    <h1>🔐 Generate Password Hash</h1>
    <div class="box">
        <form method="POST">
            <label>Masukkan Password:</label>
            <input type="text" name="password" value="password123" required>
            <button type="submit">Generate</button>
        </form>
        
        <?php if ($hash): ?>
        <div class="result">
            <strong>Hash untuk "password123":</strong><br><br>
            <code><?= $hash ?></code>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>