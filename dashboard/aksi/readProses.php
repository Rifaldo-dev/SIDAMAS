<?php
require_once '../../koneksi.php';

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$offset = ($page - 1) * $limit;

// Query dengan search
$where = "";
if ($search != '') {
    $where = "WHERE jenis_dana LIKE '%$search%' OR keterangan LIKE '%$search%'";
}

$query = "SELECT * FROM dana_masjid $where ORDER BY waktu_tanggal DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Hitung total data
$countQuery = "SELECT COUNT(*) as total FROM dana_masjid $where";
$countResult = mysqli_query($conn, $countQuery);
$totalData = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalData / $limit);

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
    ]
]);
?>