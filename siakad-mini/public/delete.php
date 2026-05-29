<?php
require_once __DIR__ . '/../src/Auth.php';
require_role('admin'); // RBAC - hanya admin

$id = $_GET['id'] ?? 0;

if ($id) {
    require_once __DIR__ . '/../src/DosenRepository.php';
    $repo = new DosenRepository();
    $repo->delete($id);
    
    header('Location: index.php?success=delete');
    exit;
}

header('Location: index.php');
exit;