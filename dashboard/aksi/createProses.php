<?php
require_once '../../koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis_transaksi = mysqli_real_escape_string($conn, $_POST['jenis_transaksi']);
    $waktu_tanggal = mysqli_real_escape_string($conn, $_POST['waktu_tanggal']);
    $jenis_dana = mysqli_real_escape_string($conn, $_POST['jenis_dana']);
    $total = mysqli_real_escape_string($conn, $_POST['total']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

    if (empty($jenis_transaksi) || empty($waktu_tanggal) || empty($jenis_dana) || empty($total)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
        exit;
    }

    $query = "INSERT INTO dana_masjid (jenis_transaksi, waktu_tanggal, jenis_dana, total, keterangan) 
              VALUES ('$jenis_transaksi', '$waktu_tanggal', '$jenis_dana', '$total', '$keterangan')";

    if (mysqli_query($conn, $query)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Data berhasil ditambahkan']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan data: ' . mysqli_error($conn)]);
    }
}
?>