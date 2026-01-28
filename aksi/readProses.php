<?php
require_once '../koneksi.php';

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$month = isset($_GET['month']) ? mysqli_real_escape_string($conn, $_GET['month']) : '';
$offset = ($page - 1) * $limit;

// Query dengan search dan filter bulan
$where = [];
if ($search != '') {
    $where[] = "(jenis_dana LIKE '%$search%' OR keterangan LIKE '%$search%')";
}
if ($month != '') {
    $where[] = "DATE_FORMAT(waktu_tanggal, '%Y-%m') = '$month'";
}

$whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

$query = "SELECT * FROM dana_masjid $whereClause ORDER BY waktu_tanggal DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Hitung total data
$countQuery = "SELECT COUNT(*) as total FROM dana_masjid $whereClause";
$countResult = mysqli_query($conn, $countQuery);
$totalData = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalData / $limit);

// Hitung summary (dana masuk, keluar, saldo, dan jumlah transaksi)
$summaryQuery = "SELECT 
    SUM(CASE WHEN jenis_transaksi = 'masuk' THEN total ELSE 0 END) as dana_masuk,
    SUM(CASE WHEN jenis_transaksi = 'keluar' THEN total ELSE 0 END) as dana_keluar,
    COUNT(*) as total_transaksi 
    FROM dana_masjid $whereClause";
$summaryResult = mysqli_query($conn, $summaryQuery);
$summary = mysqli_fetch_assoc($summaryResult);

$danaMasuk = $summary['dana_masuk'] ?? 0;
$danaKeluar = $summary['dana_keluar'] ?? 0;
$saldo = $danaMasuk - $danaKeluar;

$data = [];
$no = $offset + 1;
while ($row = mysqli_fetch_assoc($result)) {
    $row['no'] = $no++;
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'data' => $data,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_data' => $totalData,
        'limit' => $limit
    ],
    'summary' => [
        'dana_masuk' => $danaMasuk,
        'dana_keluar' => $danaKeluar,
        'saldo' => $saldo,
        'total_transaksi' => $summary['total_transaksi'] ?? 0
    ]
]);
?>