<?php
// Database connection
require "database.php";

// Start this at the very top of your file, before anything else
session_start();

// Then check if the session variables exist before using them
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: login.php");
    exit;
}

// Initialize variables for edit functionality
$edit_id_harga = "";
$edit_id_produk = "";
$edit_jenis = "";
$edit_harga = "";
$edit_harga_diskon = "";
$edit_tanggal_mulai = "";
$edit_tanggal_selesai = "";
$action = "view"; // Default action is view

// Initialize message variables
$success_message = "";
$error_message = "";

// Add new price record
if (isset($_POST['submit_price'])) {
    $id_produk = $_POST['id_produk'];
    $jenis = $_POST['jenis'];
    $harga = $_POST['harga'];
    $harga_diskon = !empty($_POST['harga_diskon']) ? $_POST['harga_diskon'] : NULL;
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : NULL;

    $sql = "INSERT INTO harga (id_produk, jenis, harga, harga_diskon, tanggal_mulai, tanggal_selesai) 
            VALUES (?, ?, ?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isddss', $id_produk, $jenis, $harga, $harga_diskon, $tanggal_mulai, $tanggal_selesai);
    
    if ($stmt->execute()) {
        $success_message = "Data harga berhasil ditambahkan!";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Update price record
if (isset($_POST['update_price'])) {
    $id_harga = $_POST['id_harga'];
    $id_produk = $_POST['id_produk'];
    $jenis = $_POST['jenis'];
    $harga = $_POST['harga'];
    $harga_diskon = !empty($_POST['harga_diskon']) ? $_POST['harga_diskon'] : NULL;
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : NULL;

    $sql = "UPDATE harga SET id_produk=?, jenis=?, harga=?, harga_diskon=?, 
            tanggal_mulai=?, tanggal_selesai=? WHERE id_harga=?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isddssi', $id_produk, $jenis, $harga, $harga_diskon, $tanggal_mulai, $tanggal_selesai, $id_harga);
    
    if ($stmt->execute()) {
        $success_message = "Data harga berhasil diperbarui!";
        // Redirect back to view mode after successful update
        header("Location: prices.php?filter=" . (isset($_GET['filter']) ? $_GET['filter'] : 'all'));
        exit;
    } else {
        $error_message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Delete price record
if (isset($_GET['delete'])) {
    $id_harga = $_GET['delete'];
    
    $sql = "DELETE FROM harga WHERE id_harga=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id_harga);
    
    if ($stmt->execute()) {
        $success_message = "Data harga berhasil dihapus!";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Check if edit is requested
if (isset($_GET['edit'])) {
    $edit_id_harga = intval($_GET['edit']);
    $action = "edit";
    
    $sql = "SELECT * FROM harga WHERE id_harga = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $edit_id_harga);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $edit_id_produk = $row['id_produk'];
        $edit_jenis = $row['jenis'];
        $edit_harga = $row['harga'];
        $edit_harga_diskon = $row['harga_diskon'];
        $edit_tanggal_mulai = $row['tanggal_mulai'];
        $edit_tanggal_selesai = $row['tanggal_selesai'];
    }
    $stmt->close();
}

// Check if add action is requested
if (isset($_GET['action']) && $_GET['action'] == 'add') {
    $action = "add";
}

// Get filter from GET parameters, default to 'all'
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Base SQL query
$sql_base = "SELECT h.id_harga, h.id_produk, h.jenis, h.harga, h.harga_diskon, 
        h.tanggal_mulai, h.tanggal_selesai, 
        CASE 
            WHEN h.jenis = 'makanan' THEN m.nama_makanan 
            WHEN h.jenis = 'minuman' THEN d.nama_minuman 
        END AS nama_produk
        FROM harga h
        LEFT JOIN makanan m ON h.id_produk = m.id_makanan AND h.jenis = 'makanan'
        LEFT JOIN minuman d ON h.id_produk = d.id_minuman AND h.jenis = 'minuman'";

// Add filter condition based on selected filter
if ($filter == 'makanan') {
    $sql_base .= " WHERE h.jenis = 'makanan'";
} elseif ($filter == 'minuman') {
    $sql_base .= " WHERE h.jenis = 'minuman'";
}

// Add ORDER BY clause
$sql_base .= " ORDER BY h.tanggal_mulai DESC";

// Execute the query
$result = $conn->query($sql_base);

// Get food items for dropdown
$food_query = "SELECT id_makanan, nama_makanan FROM makanan ORDER BY nama_makanan";
$food_result = $conn->query($food_query);

// Get drink items for dropdown
$drink_query = "SELECT id_minuman, nama_minuman FROM minuman ORDER BY nama_minuman";
$drink_result = $conn->query($drink_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Harga - Restoran Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
   /* Navbar Styles */
.navbar {
    padding: 0.8rem 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.navbar-brand {
    font-size: 1.4rem;
}

.dropdown-menu {
    padding: 0.5rem;
    margin-top: 0.5rem;
    border-radius: 8px;
}

.dropdown-item {
    border-radius: 5px;
    transition: all 0.2s;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}

/* Sidebar Styles */
.sidebar {
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    transition: all 0.3s;
}

.sidebar .nav-link {
    border-radius: 8px;
    padding: 0.8rem 1rem;
    margin-bottom: 0.3rem;
    transition: all 0.3s;
    opacity: 0.8;
}

.sidebar .nav-link:hover {
    opacity: 1;
    background-color: rgba(255,255,255,0.1);
    transform: translateX(5px);
}

.sidebar .nav-link.active {
    background-color: #ffc107;
    color: #212529 !important;
    opacity: 1;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.sidebar .nav-link i {
    font-size: 1.2rem;
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
        .filter-btn.active {
            background-color: #ffc107;
            color: white;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
            <i class="bi bi-cup-hot-fill me-2 text-warning"></i>
            <span>KafeTech Admin</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link px-3 rounded-pill me-2" href="orders.php">
                        <i class="bi bi-cart-fill me-1"></i> Pemesanan
                        <span class="badge rounded-pill bg-danger">5</span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle px-3 d-flex align-items-center" href="#" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-2 fs-5"></i> 
                        <span><?php echo isset($_SESSION['role']) ? $_SESSION['role'] : 'Guest'; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item py-2" href="profile.php">
                            <i class="bi bi-gear me-2 text-secondary"></i> Pengaturan</a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
         <div class="col-lg-2 p-0 ">
            <div class="sidebar bg-dark text-light h-100 p-3  flex-column">
        <div class="p-3 border-bottom border-secondary">
            <div class="d-flex align-items-center">
                <span class="fs-5 fw-semibold text-warning">Dashboard Menu</span>
            </div>
        </div>
        
        <ul class="nav nav-pills flex-column p-3 gap-2">
            
            <li class="nav-item">
                <a href="food.php" class="nav-link text-light  d-flex align-items-center">
                    <i class="bi bi-egg-fried me-3"></i>
                    <span>Makanan</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="drink.php" class="nav-link text-light d-flex align-items-center">
                    <i class="bi bi-cup-straw me-3"></i>
                    <span>Minuman</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="prices.php" class="nav-link text-light active d-flex align-items-center">
                    <i class="bi bi-tags me-3"></i>
                    <span>Harga</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="reports.php" class="nav-link text-light d-flex align-items-center">
                    <i class="bi bi-bar-chart-line me-3"></i>
                    <span>Laporan</span>
                </a>
            </li>
        </ul>
        
    </div>
</div>
            
            <!-- Main Content -->
            <div class="col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-tags"></i> Manajemen Harga</h2>
                    <?php if ($action == "view"): ?>
                    <a href="prices.php?action=add&filter=<?php echo $filter; ?>" class="btn btn-warning">
                        <i class="bi bi-plus-circle"></i> Tambah Harga Baru
                    </a>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <!-- Add or Edit Form -->
                <?php if ($action == "add" || $action == "edit"): ?>
                <div class="card mb-4">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><?php echo ($action == "edit") ? "Edit Data Harga" : "Tambah Harga Baru"; ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="prices.php?filter=<?php echo $filter; ?>" method="post">
                            <?php if ($action == "edit"): ?>
                            <input type="hidden" name="id_harga" value="<?php echo $edit_id_harga; ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="jenis" class="form-label">Jenis</label>
                                <select class="form-select" id="jenis" name="jenis" required>
                                    <option value="" disabled <?php echo empty($edit_jenis) ? 'selected' : ''; ?>>Pilih Jenis</option>
                                    <option value="makanan" <?php echo ($edit_jenis == 'makanan') ? 'selected' : ''; ?>>Makanan</option>
                                    <option value="minuman" <?php echo ($edit_jenis == 'minuman') ? 'selected' : ''; ?>>Minuman</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="id_produk" class="form-label">Produk</label>
                                <select class="form-select" id="id_produk" name="id_produk" required>
                                    <option value="" disabled selected>Pilih Produk</option>
                                    <?php 
                                    // Reset and reuse the food and drink query results
                                    if ($food_result) $food_result->data_seek(0);
                                    if ($drink_result) $drink_result->data_seek(0);
                                    
                                    // Food options
                                    echo '<optgroup label="Makanan">';
                                    if ($food_result) {
                                        while($food = $food_result->fetch_assoc()) {
                                            $selected = ($edit_jenis == 'makanan' && $edit_id_produk == $food['id_makanan']) ? 'selected' : '';
                                            echo '<option value="' . $food['id_makanan'] . '" ' . $selected . '>' . $food['nama_makanan'] . '</option>';
                                        }
                                    }
                                    echo '</optgroup>';
                                    
                                    // Drink options
                                    echo '<optgroup label="Minuman">';
                                    if ($drink_result) {
                                        while($drink = $drink_result->fetch_assoc()) {
                                            $selected = ($edit_jenis == 'minuman' && $edit_id_produk == $drink['id_minuman']) ? 'selected' : '';
                                            echo '<option value="' . $drink['id_minuman'] . '" ' . $selected . '>' . $drink['nama_minuman'] . '</option>';
                                        }
                                    }
                                    echo '</optgroup>';
                                    ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="harga" class="form-label">Harga (Rp)</label>
                                <input type="number" step="0.01" class="form-control" id="harga" name="harga" value="<?php echo $edit_harga; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="harga_diskon" class="form-label">Harga Diskon (Rp)</label>
                                <input type="number" step="0.01" class="form-control" id="harga_diskon" name="harga_diskon" value="<?php echo $edit_harga_diskon; ?>">
                                <div class="form-text">Kosongkan jika tidak ada diskon.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="<?php echo $edit_tanggal_mulai; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" value="<?php echo $edit_tanggal_selesai; ?>">
                                <div class="form-text">Kosongkan jika harga masih berlaku.</div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="prices.php?filter=<?php echo $filter; ?>" class="btn btn-secondary">Batal</a>
                                <button type="submit" name="<?php echo ($action == "edit") ? "update_price" : "submit_price"; ?>" class="btn btn-warning">
                                    <?php echo ($action == "edit") ? "Perbarui" : "Simpan"; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php else: ?>
                <!-- Filter Buttons -->
                <div class="mb-4">
                    <div class="btn-group">
                        <a href="prices.php?filter=all" class="btn btn-outline-warning filter-btn <?php echo ($filter == 'all') ? 'active' : ''; ?>">
                            <i class="bi bi-grid-3x3"></i> Semua
                        </a>
                        <a href="prices.php?filter=makanan" class="btn btn-outline-warning filter-btn <?php echo ($filter == 'makanan') ? 'active' : ''; ?>">
                            <i class="bi bi-egg-fried"></i> Makanan
                        </a>
                        <a href="prices.php?filter=minuman" class="btn btn-outline-warning filter-btn <?php echo ($filter == 'minuman') ? 'active' : ''; ?>">
                            <i class="bi bi-cup-straw"></i> Minuman
                        </a>
                    </div>
                </div>

                <!-- Price List Table -->
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="bi bi-table"></i> Daftar Harga</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Produk</th>
                                        <th>Jenis</th>
                                        <th>Harga</th>
                                        <th>Harga Diskon</th>
                                        <th>Tanggal Mulai</th>
                                        <th>Tanggal Selesai</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if ($result && $result->num_rows > 0) {
                                        $no = 1;
                                        while($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo $row['nama_produk']; ?></td>
                                        <td><?php echo ucfirst($row['jenis']); ?></td>
                                        <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                        <td>
                                            <?php 
                                            if ($row['harga_diskon']) {
                                                echo 'Rp ' . number_format($row['harga_diskon'], 0, ',', '.');
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo date('d-m-Y', strtotime($row['tanggal_mulai'])); ?></td>
                                        <td>
                                            <?php 
                                            if ($row['tanggal_selesai']) {
                                                echo date('d-m-Y', strtotime($row['tanggal_selesai']));
                                            } else {
                                                echo '<span class="badge bg-success">Masih Berlaku</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <a href="prices.php?edit=<?php echo $row['id_harga']; ?>&filter=<?php echo $filter; ?>" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="prices.php?delete=<?php echo $row['id_harga']; ?>&filter=<?php echo $filter; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="8" class="text-center">Tidak ada data harga.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>