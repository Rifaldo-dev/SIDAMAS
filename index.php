<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAMAS - Data Dana Masjid</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="bootstrap/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Video Background -->
    <video autoplay muted loop playsinline class="video-background">
        <source src="assets/video/background.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <div class="d-inline-block">
                    <div style="font-size: 1.1rem; line-height: 1.2;">Dana Masjid Raya</div>
                    <div style="font-size: 0.75rem; line-height: 1;">Syekh Ahmad Khatib Al Minangkabawi</div>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin/index.php">Login Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Data Dana Masjid</h4>
            </div>
            <div class="card-body">
                <!-- Search & Filter -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Filter Bulan</label>
                        <select class="form-select" id="monthFilter">
                            <option value="">Semua Bulan</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cari Data</label>
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari jenis dana...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tampilkan</label>
                        <select class="form-select" id="limitSelect">
                            <option value="10">10 per halaman</option>
                            <option value="25">25 per halaman</option>
                            <option value="50">50 per halaman</option>
                        </select>
                    </div>
                </div>

                <!-- Summary Card -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h6>Dana Masuk</h6>
                                <h4 id="danaMasuk">Rp 0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h6>Dana Keluar</h6>
                                <h4 id="danaKeluar">Rp 0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h6>Saldo</h6>
                                <h4 id="saldo">Rp 0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h6>Total Transaksi</h6>
                                <h4 id="totalTransaksi">0</h4>
                            </div>
                        </div>
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
                            </tr>
                        </thead>
                        <tbody id="dataTable">
                            <tr>
                                <td colspan="6" class="text-center">Loading...</td>
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

    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentPage = 1;
        let currentLimit = 10;
        let currentSearch = '';
        let currentMonth = '';

        // Generate month options
        function generateMonthOptions() {
            const monthSelect = document.getElementById('monthFilter');
            const months = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            
            const currentDate = new Date();
            const currentYear = currentDate.getFullYear();
            
            // Generate untuk 12 bulan terakhir
            for (let i = 0; i < 12; i++) {
                const date = new Date(currentYear, currentDate.getMonth() - i, 1);
                const year = date.getFullYear();
                const month = date.getMonth() + 1;
                const monthName = months[date.getMonth()];
                
                const option = document.createElement('option');
                option.value = `${year}-${String(month).padStart(2, '0')}`;
                option.textContent = `${monthName} ${year}`;
                monthSelect.appendChild(option);
            }
        }

        function loadData(page = 1, limit = 10, search = '', month = '') {
            currentPage = page;
            currentLimit = limit;
            currentSearch = search;
            currentMonth = month;

            let url = `aksi/readProses.php?page=${page}&limit=${limit}`;
            if (search) url += `&search=${encodeURIComponent(search)}`;
            if (month) url += `&month=${month}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        displayData(data.data);
                        displayPagination(data.pagination);
                        updateSummary(data.summary);
                    }
                });
        }

        function displayData(data) {
            const tbody = document.getElementById('dataTable');
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>';
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
                    </tr>
                `;
            }).join('');
        }

        function updateSummary(summary) {
            if (summary) {
                document.getElementById('danaMasuk').textContent = 'Rp ' + formatNumber(summary.dana_masuk || 0);
                document.getElementById('danaKeluar').textContent = 'Rp ' + formatNumber(summary.dana_keluar || 0);
                document.getElementById('saldo').textContent = 'Rp ' + formatNumber(summary.saldo || 0);
                document.getElementById('totalTransaksi').textContent = summary.total_transaksi || 0;
            }
        }

        function displayPagination(pagination) {
            const paginationEl = document.getElementById('pagination');
            
            if (!pagination || !pagination.total_pages || pagination.total_pages <= 0) {
                paginationEl.innerHTML = '';
                return;
            }
            
            let html = '';
            const currentPage = parseInt(pagination.current_page) || 1;
            const totalPages = parseInt(pagination.total_pages) || 1;

            // Jika cuma 1 halaman, tidak perlu pagination
            if (totalPages === 1) {
                paginationEl.innerHTML = '';
                return;
            }

            // Previous button
            if (currentPage > 1) {
                html += `<li class="page-item">
                    <a class="page-link" href="#" onclick="loadData(${currentPage - 1}, ${currentLimit}, '${currentSearch}', '${currentMonth}'); return false;">
                        &laquo;
                    </a>
                </li>`;
            }

            // Page numbers - tampilkan semua jika tidak terlalu banyak
            if (totalPages <= 7) {
                for (let i = 1; i <= totalPages; i++) {
                    if (i === currentPage) {
                        html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                    } else {
                        html += `<li class="page-item">
                            <a class="page-link" href="#" onclick="loadData(${i}, ${currentLimit}, '${currentSearch}', '${currentMonth}'); return false;">${i}</a>
                        </li>`;
                    }
                }
            } else {
                // Jika banyak halaman, tampilkan dengan ellipsis
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, currentPage + 2);

                if (startPage > 1) {
                    html += `<li class="page-item">
                        <a class="page-link" href="#" onclick="loadData(1, ${currentLimit}, '${currentSearch}', '${currentMonth}'); return false;">1</a>
                    </li>`;
                    if (startPage > 2) {
                        html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    }
                }

                for (let i = startPage; i <= endPage; i++) {
                    if (i === currentPage) {
                        html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                    } else {
                        html += `<li class="page-item">
                            <a class="page-link" href="#" onclick="loadData(${i}, ${currentLimit}, '${currentSearch}', '${currentMonth}'); return false;">${i}</a>
                        </li>`;
                    }
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    }
                    html += `<li class="page-item">
                        <a class="page-link" href="#" onclick="loadData(${totalPages}, ${currentLimit}, '${currentSearch}', '${currentMonth}'); return false;">${totalPages}</a>
                    </li>`;
                }
            }

            // Next button
            if (currentPage < totalPages) {
                html += `<li class="page-item">
                    <a class="page-link" href="#" onclick="loadData(${currentPage + 1}, ${currentLimit}, '${currentSearch}', '${currentMonth}'); return false;">
                        &raquo;
                    </a>
                </li>`;
            }

            paginationEl.innerHTML = html;
        }

        function showDetail(id) {
            fetch(`aksi/editProses.php?id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const item = data.data;
                        document.getElementById('detailContent').innerHTML = `
                            <table class="table">
                                <tr><th>Waktu/Tanggal</th><td>${formatDateTime(item.waktu_tanggal)}</td></tr>
                                <tr><th>Jenis Dana</th><td>${item.jenis_dana}</td></tr>
                                <tr><th>Total</th><td>Rp ${formatNumber(item.total)}</td></tr>
                                <tr><th>Keterangan</th><td>${item.keterangan || '-'}</td></tr>
                            </table>
                        `;
                        new bootstrap.Modal(document.getElementById('detailModal')).show();
                    }
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

        // Search
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            const search = e.target.value;
            loadData(1, currentLimit, search, currentMonth);
        });

        // Month filter
        document.getElementById('monthFilter').addEventListener('change', function(e) {
            const month = e.target.value;
            loadData(1, currentLimit, currentSearch, month);
        });

        // Limit change
        document.getElementById('limitSelect').addEventListener('change', function(e) {
            const limit = parseInt(e.target.value);
            loadData(1, limit, currentSearch, currentMonth);
        });

        // Load initial data
        generateMonthOptions();
        loadData();
    </script>
</body>
</html>
