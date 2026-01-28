<?php
require_once '../koneksi.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

if ($search == '') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Kata kunci pencarian kosong']);
    exit;
}

$query = "SELECT * FROM dana_masjid 
          WHERE jenis_dana LIKE '%$search%' 
          OR keterangan LIKE '%$search%' 
          OR total LIKE '%$search%'
          ORDER BY waktu_tanggal DESC 
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $query);

// Hitung total hasil pencarian
$countQuery = "SELECT COUNT(*) as total FROM dana_masjid 
               WHERE jenis_dana LIKE '%$search%' 
               OR keterangan LIKE '%$search%' 
               OR total LIKE '%$search%'";
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
    'search' => $search,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_data' => $totalData,
        'limit' => $limit
    ]
]);
?>