<?php
require_once '../koneksi.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$where = "";
if ($search != '') {
    $where = "WHERE jenis_dana LIKE '%$search%' OR keterangan LIKE '%$search%'";
}

$countQuery = "SELECT COUNT(*) as total FROM dana_masjid $where";
$countResult = mysqli_query($conn, $countQuery);
$totalData = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalData / $limit);

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'current_page' => $page,
    'total_pages' => $totalPages,
    'total_data' => $totalData,
    'limit' => $limit
]);
?>