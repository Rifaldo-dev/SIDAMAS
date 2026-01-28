<?php
require_once '../../koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $jenis_transaksi = mysqli_real_escape_string($conn, $_POST['jenis_transaksi']);
    $waktu_tanggal = mysqli_real_escape_string($conn, $_POST['waktu_tanggal']);
    $jenis_dana = mysqli_real_escape_string($conn, $_POST['jenis_dana']);
    $total = mysqli_real_escape_string($conn, $_POST['total']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

    if ($id == 0 || empty($jenis_transaksi) || empty($waktu_tanggal) || empty($jenis_dana) || empty($total)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
        exit;
    }

    $query = "UPDATE dana_masjid SET 
              jenis_transaksi = '$jenis_transaksi',
              waktu_tanggal = '$waktu_tanggal',
              jenis_dana = '$jenis_dana',
              total = '$total',
              keterangan = '$keterangan'
              WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Data berhasil diupdate']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Gagal update data: ' . mysqli_error($conn)]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    // Get data untuk edit
    $id = (int)$_GET['id'];
    $query = "SELECT * FROM dana_masjid WHERE id = $id";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
    }
}
?>