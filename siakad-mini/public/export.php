<?php
require_once __DIR__ . '/../src/Auth.php';
require_login();

require_once __DIR__ . '/../src/DosenRepository.php';
$repo = new DosenRepository();

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$progdi = $_GET['progdi'] ?? '';

$dosens = $repo->all($search, $status, $progdi, 'nama', 'ASC', 1, 1000);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="dosen_export_' . date('YmdHis') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['NIDN', 'Nama', 'Email', 'Program Studi', 'Status', 'Total MK']);

foreach ($dosens as $d) {
    fputcsv($output, [$d['nidn'], $d['nama'], $d['email'], $d['program_studi'], $d['status'], $d['total_mk'] ?? 0]);
}

fclose($output);
exit;