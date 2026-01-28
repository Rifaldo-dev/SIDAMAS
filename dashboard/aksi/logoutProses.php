<?php
require_once '../../koneksi.php';

session_destroy();

header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Logout berhasil']);
?>