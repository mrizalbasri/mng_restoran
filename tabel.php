<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Restoran</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .menu-card {
            cursor: pointer;
        }
        .nav-link {
            border-radius: 5px;
            margin-bottom: 5px;
        }
        .nav-link.active {
            background-color: #0d6efd;
            color: white !important;
        }
        .nav-link:hover:not(.active) {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-cup-hot-fill me-2"></i>
                Restoran Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">
                            <i class="bi bi-cart-fill me-1"></i> Pemesanan
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i> Admin
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-1"></i> Pengaturan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-box-arrow-right me-1"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-2 bg-light sidebar p-3">
                <div class="d-flex flex-column">
                    <div class="p-2 mb-3 text-center">
                        <img src="/api/placeholder/100/100" alt="Logo" class="img-fluid rounded-circle mb-2" style="max-width: 80px;">
                        <h5 class="mb-0">Admin Panel</h5>
                    </div>
                    <hr>
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a href="index.php" class="nav-link text-dark active">
                                <i class="bi bi-house-door me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="food.php" class="nav-link text-dark">
                                <i class="bi bi-egg-fried me-2"></i> Makanan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="beverages.php" class="nav-link text-dark">
                                <i class="bi bi-cup-straw me-2"></i> Minuman
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="prices.php" class="nav-link text-dark">
                                <i class="bi bi-tags me-2"></i> Harga
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="orders.php" class="nav-link text-dark">
                                <i class="bi bi-cart me-2"></i> Pemesanan
                            </a>
                        </li>
                        <li class="nav-item mt-2">
                            <a href="reports.php" class="nav-link text-dark">
                                <i class="bi bi-graph-up me-2"></i> Laporan
                            </a>
                        </li>
                    </ul>
                    <hr>
                    <div class="mt-auto">
                        <a href="#" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-question-circle me-2"></i> Bantuan
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10 py-4 px-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-grid me-2"></i> Dashboard</h2>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>

                <!-- Dashboard Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                            Total Menu Items</div>
                                        <div class="h5 mb-0 fw-bold text-gray-800">25</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-collection fs-2 text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                            Pemesanan Hari Ini</div>
                                        <div class="h5 mb-0 fw-bold text-gray-800">12</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-cart-check fs-2 text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                            Menu Terlaris</div>
                                        <div class="h5 mb-0 fw-bold text-gray-800">Nasi Goreng</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-star fs-2 text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                            Pendapatan Hari Ini</div>
                                        <div class="h5 mb-0 fw-bold text-gray-800">Rp 1,250,000</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-currency-dollar fs-2 text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Menu Categories -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-grid-3x3-gap me-2"></i> Menu Kategori</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <a href="food.php" class="text-decoration-none">
                                            <div class="card menu-card">
                                                <div class="card-body text-center py-4">
                                                    <i class="bi bi-egg-fried text-primary" style="font-size: 3rem;"></i>
                                                    <h5 class="mt-3">Makanan</h5>
                                                    <span class="badge bg-primary">15 items</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <a href="beverages.php" class="text-decoration-none">
                                            <div class="card menu-card">
                                                <div class="card-body text-center py-4">
                                                    <i class="bi bi-cup-straw text-info" style="font-size: 3rem;"></i>
                                                    <h5 class="mt-3">Minuman</h5>
                                                    <span class="badge bg-info">10 items</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <a href="orders.php" class="text-decoration-none">
                                            <div class="card menu-card">
                                                <div class="card-body text-center py-4">
                                                    <i class="bi bi-cart-check text-success" style="font-size: 3rem;"></i>
                                                    <h5 class="mt-3">Pemesanan</h5>
                                                    <span class="badge bg-success">Kelola pesanan</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i> Pesanan Terbaru</h5>
                                <a href="orders.php" class="btn btn-sm btn-light">Lihat Semua</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Pelanggan</th>
                                                <th>Item</th>
                                                <th>Status</th>
                                                <th>Total</th>
                                                <th>Waktu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>#ORD-2023-001</td>
                                                <td>Budi Santoso</td>
                                                <td>3 items</td>
                                                <td><span class="badge bg-success">Selesai</span></td>
                                                <td>Rp 75,000</td>
                                                <td>10:25 AM</td>
                                            </tr>
                                            <tr>
                                                <td>#ORD-2023-002</td>
                                                <td>Ani Wulandari</td>
                                                <td>2 items</td>
                                                <td><span class="badge bg-warning text-dark">Diproses</span></td>
                                                <td>Rp 45,000</td>
                                                <td>11:12 AM</td>
                                            </tr>
                                            <tr>
                                                <td>#ORD-2023-003</td>
                                                <td>Citra Dewi</td>
                                                <td>5 items</td>
                                                <td><span class="badge bg-primary">Dipesan</span></td>
                                                <td>Rp 120,000</td>
                                                <td>11:30 AM</td>
                                            </tr>
                                            <tr>
                                                <td>#ORD-2023-004</td>
                                                <td>Deni Pratama</td>
                                                <td>1 item</td>
                                                <td><span class="badge bg-info">Dikirim</span></td>
                                                <td>Rp 35,000</td>
                                                <td>12:05 PM</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center text-lg-start mt-4">
        <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.05);">
            © 2023 Dashboard Restoran - Dibuat dengan <i class="bi bi-heart-fill text-danger"></i> oleh Anda
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>