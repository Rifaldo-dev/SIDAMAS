<?php
require_once '../koneksi.php';

// Cek login
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - DAMAS</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../bootstrap/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <div class="d-inline-block">
                    <div style="font-size: 1.1rem; line-height: 1.2;">Dana Masjid Raya - Admin</div>
                    <div style="font-size: 0.75rem; line-height: 1;">Syekh Ahmad Khatib Al Minangkabawi</div>
                </div>
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    Halo, <?php echo $_SESSION['admin_nama']; ?>
                </span>
                <button class="btn btn-light btn-sm" onclick="logout()">Logout</button>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="card shadow">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Kelola Data Dana Masjid</h4>
                <button class="btn btn-light" onclick="showAddModal()">
                    + Tambah Data
                </button>
            </div>
            <div class="card-body">
                <!-- Search & Filter -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari data...">
                    </div>
                    <div class="col-md-6 text-end">
                        <select class="form-select d-inline-block w-auto" id="limitSelect">
                            <option value="10">10 per halaman</option>
                            <option value="25">25 per halaman</option>
                            <option value="50">50 per halaman</option>
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-success">
                            <tr>
                                <th>No</th>
                                <th>Waktu/Tanggal</th>
                                <th>Jenis</th>
                                <th>Keterangan</th>
                                <th>Dana Masuk</th>
                                <th>Dana Keluar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="dataTable">
                            <tr>
                                <td colspan="7" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav>
                    <ul class="pagination justify-content-center" id="pagination"></ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Modal Add/Edit -->
    <div class="modal fade" id="formModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalTitle">Tambah Data</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="dataForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="dataId">
                        <div class="mb-3">
                            <label class="form-label">Jenis Transaksi</label>
                            <select class="form-select" name="jenis_transaksi" required>
                                <option value="masuk">Dana Masuk</option>
                                <option value="keluar">Dana Keluar</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Waktu/Tanggal</label>
                            <input type="datetime-local" class="form-control" name="waktu_tanggal" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jenis Dana/Keperluan</label>
                            <input type="text" class="form-control" name="jenis_dana" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Total</label>
                            <input type="number" class="form-control" name="total" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" name="keterangan" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentPage = 1;
        let currentLimit = 10;
        let currentSearch = '';
        let formModal;
        let isEditMode = false;

        function loadData(page = 1, limit = 10, search = '') {
            currentPage = page;
            currentLimit = limit;
            currentSearch = search;

            let url = `aksi/readProses.php?page=${page}&limit=${limit}`;
            if (search) url += `&search=${encodeURIComponent(search)}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        displayData(data.data);
                        displayPagination(data.pagination);
                    }
                });
        }

        function displayData(data) {
            const tbody = document.getElementById('dataTable');
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">Tidak ada data</td></tr>';
                return;
            }

            tbody.innerHTML = data.map(item => {
                const isMasuk = item.jenis_transaksi === 'masuk';
                const danaMasuk = isMasuk ? `<span class="text-success fw-bold">Rp ${formatNumber(item.total)}</span>` : '-';
                const danaKeluar = !isMasuk ? `<span class="text-danger fw-bold">Rp ${formatNumber(item.total)}</span>` : '-';
                
                return `
                    <tr>
                        <td>${item.no}</td>
                        <td>${formatDateTime(item.waktu_tanggal)}</td>
                        <td><span class="badge bg-${isMasuk ? 'success' : 'danger'}">${isMasuk ? 'Masuk' : 'Keluar'}</span></td>
                        <td>${item.jenis_dana}${item.keterangan ? '<br><small class="text-muted">' + item.keterangan + '</small>' : ''}</td>
                        <td>${danaMasuk}</td>
                        <td>${danaKeluar}</td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editData(${item.id})">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteData(${item.id})">Hapus</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function displayPagination(pagination) {
            const paginationEl = document.getElementById('pagination');
            let html = '';

            if (pagination.current_page > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadData(${pagination.current_page - 1}, ${currentLimit}, '${currentSearch}'); return false;">Previous</a></li>`;
            }

            for (let i = 1; i <= pagination.total_pages; i++) {
                if (i === pagination.current_page) {
                    html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                } else {
                    html += `<li class="page-item"><a class="page-link" href="#" onclick="loadData(${i}, ${currentLimit}, '${currentSearch}'); return false;">${i}</a></li>`;
                }
            }

            if (pagination.current_page < pagination.total_pages) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadData(${pagination.current_page + 1}, ${currentLimit}, '${currentSearch}'); return false;">Next</a></li>`;
            }

            paginationEl.innerHTML = html;
        }

        function showAddModal() {
            isEditMode = false;
            document.getElementById('modalTitle').textContent = 'Tambah Data';
            document.getElementById('dataForm').reset();
            document.getElementById('dataId').value = '';
            formModal.show();
        }

        function editData(id) {
            isEditMode = true;
            document.getElementById('modalTitle').textContent = 'Edit Data';
            
            fetch(`aksi/editProses.php?id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const item = data.data;
                        document.getElementById('dataId').value = item.id;
                        document.querySelector('[name="jenis_transaksi"]').value = item.jenis_transaksi;
                        document.querySelector('[name="waktu_tanggal"]').value = item.waktu_tanggal.replace(' ', 'T');
                        document.querySelector('[name="jenis_dana"]').value = item.jenis_dana;
                        document.querySelector('[name="total"]').value = item.total;
                        document.querySelector('[name="keterangan"]').value = item.keterangan;
                        formModal.show();
                    }
                });
        }

        function deleteData(id) {
            if (!confirm('Yakin ingin menghapus data ini?')) return;

            const formData = new FormData();
            formData.append('id', id);

            fetch('aksi/deleteProses.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    loadData(currentPage, currentLimit, currentSearch);
                }
            });
        }

        function logout() {
            if (!confirm('Yakin ingin logout?')) return;

            fetch('aksi/logoutProses.php')
                .then(res => res.json())
                .then(data => {
                    window.location.href = '../admin/index.php';
                });
        }

        function formatNumber(num) {
            return parseFloat(num).toLocaleString('id-ID');
        }

        function formatDateTime(datetime) {
            const date = new Date(datetime);
            return date.toLocaleString('id-ID', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Form submit
        document.getElementById('dataForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const url = isEditMode ? 'aksi/editProses.php' : 'aksi/createProses.php';

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    formModal.hide();
                    loadData(currentPage, currentLimit, currentSearch);
                }
            });
        });

        // Search
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            const search = e.target.value;
            loadData(1, currentLimit, search);
        });

        // Limit change
        document.getElementById('limitSelect').addEventListener('change', function(e) {
            const limit = parseInt(e.target.value);
            loadData(1, limit, currentSearch);
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            formModal = new bootstrap.Modal(document.getElementById('formModal'));
            loadData();
        });
    </script>
</body>
</html>