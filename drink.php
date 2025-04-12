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
// Initialize variables
$edit_id = "";
$edit_nama = "";
$edit_kategori = "";
$edit_deskripsi = "";
$edit_jenis = "panas";
$edit_status = "1";
$action = "add"; // Default action is add

// Delete drink if requested
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM minuman WHERE id_minuman = $id";
    
    if ($conn->query($sql) === TRUE) {
        $message = "Minuman berhasil dihapus!";
        $alert_class = "success";
    } else {
        $message = "Error: " . $conn->error;
        $alert_class = "danger";
    }
}

// Handle form submission for add/edit
if (isset($_POST['submit'])) {
    $nama = $conn->real_escape_string($_POST['nama_minuman']);
    $kategori = isset($_POST['id_kategori']) ? intval($_POST['id_kategori']) : 'NULL';
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $jenis = $conn->real_escape_string($_POST['jenis']);
    $status = intval($_POST['status_ketersediaan']);
    
    // Check if this is an edit operation
    if (isset($_POST['id_minuman']) && !empty($_POST['id_minuman'])) {
        $id = intval($_POST['id_minuman']);
        $sql = "UPDATE minuman SET 
                nama_minuman = '$nama', 
                id_kategori = " . ($kategori == 'NULL' ? "NULL" : $kategori) . ", 
                deskripsi = '$deskripsi', 
                jenis = '$jenis', 
                status_ketersediaan = $status 
                WHERE id_minuman = $id";
        
        if ($conn->query($sql) === TRUE) {
            $message = "Minuman berhasil diupdate!";
            $alert_class = "success";
        } else {
            $message = "Error: " . $conn->error;
            $alert_class = "danger";
        }
    } else {
        // This is an add operation
        $sql = "INSERT INTO minuman (nama_minuman, id_kategori, deskripsi, jenis, status_ketersediaan) 
                VALUES ('$nama', " . ($kategori == 'NULL' ? "NULL" : $kategori) . ", '$deskripsi', '$jenis', $status)";
        
        if ($conn->query($sql) === TRUE) {
            $message = "Minuman baru berhasil ditambahkan!";
            $alert_class = "success";
        } else {
            $message = "Error: " . $conn->error;
            $alert_class = "danger";
        }
    }
}

// If edit is requested, get the data
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $action = "edit";
    
    $sql = "SELECT * FROM minuman WHERE id_minuman = $edit_id";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $edit_nama = $row['nama_minuman'];
        $edit_kategori = $row['id_kategori'];
        $edit_deskripsi = $row['deskripsi'];
        $edit_jenis = $row['jenis'];
        $edit_status = $row['status_ketersediaan'];
    }
}

// Fetch kategori data for dropdowns
$kategori_query = "SELECT * FROM kategori";
$kategori_result = $conn->query($kategori_query);
$kategoris = [];
if ($kategori_result && $kategori_result->num_rows > 0) {
    while ($row = $kategori_result->fetch_assoc()) {
        $kategoris[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Minuman</title>
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
    </style>
</head>
<body>
    <!-- Navbar -->
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
            <div class="col-lg-2 p-0">
    <div class="sidebar bg-dark text-light h-100 p-3  d-flex flex-column">
        <div class="p-3 border-bottom border-secondary">
            <div class="d-flex align-items-center">
                <span class="fs-5 fw-semibold text-warning">Dashboard Menu</span>
            </div>
        </div>
        
        <ul class="nav nav-pills flex-column p-3 gap-2">
            <li class="nav-item">
                <a href="food.php" class="nav-link text-light d-flex align-items-center">
                    <i class="bi bi-egg-fried me-3"></i>
                    <span>Makanan</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="drink.php" class="nav-link text-light active d-flex align-items-center">
                    <i class="bi bi-cup-straw me-3"></i>
                    <span>Minuman</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="prices.php" class="nav-link text-light d-flex align-items-center">
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
                    <h2><i class="bi bi-cup-straw me-2"></i>Manajemen Minuman</h2>
                    <?php if ($action != "edit"): ?>
                    <a href="drink.php?action=add" class="btn btn-warning">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Minuman
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Display alert messages -->
                <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $alert_class; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <!-- Add or Edit Form -->
                <?php if (isset($_GET['action']) && $_GET['action'] == 'add' || $action == "edit"): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><?php echo $action == "edit" ? "Edit Minuman" : "Tambah Minuman Baru"; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="drink.php">
                            <?php if ($action == "edit"): ?>
                            <input type="hidden" name="id_minuman" value="<?php echo $edit_id; ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="nama_minuman" class="form-label">Nama Minuman</label>
                                <input type="text" class="form-control" id="nama_minuman" name="nama_minuman" value="<?php echo htmlspecialchars($edit_nama); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="id_kategori" class="form-label">Kategori</label>
                                <select class="form-select" id="id_kategori" name="id_kategori">
                                    <option value="">Pilih Kategori</option>
                                    <?php
                                    foreach ($kategoris as $kategori) {
                                        $selected = ($edit_kategori == $kategori['id_kategori']) ? 'selected' : '';
                                        echo "<option value='" . $kategori['id_kategori'] . "' $selected>" . htmlspecialchars($kategori['nama_kategori']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?php echo htmlspecialchars($edit_deskripsi); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="jenis" class="form-label">Jenis</label>
                                <select class="form-select" id="jenis" name="jenis" required>
                                    <option value="panas" <?php echo ($edit_jenis == 'panas') ? 'selected' : ''; ?>>Panas</option>
                                    <option value="dingin" <?php echo ($edit_jenis == 'dingin') ? 'selected' : ''; ?>>Dingin</option>
                                    <option value="keduanya" <?php echo ($edit_jenis == 'keduanya') ? 'selected' : ''; ?>>Keduanya</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status_ketersediaan" class="form-label">Status Ketersediaan</label>
                                <select class="form-select" id="status_ketersediaan" name="status_ketersediaan" required>
                                    <option value="1" <?php echo ($edit_status == '1') ? 'selected' : ''; ?>>Tersedia</option>
                                    <option value="0" <?php echo ($edit_status == '0') ? 'selected' : ''; ?>>Tidak Tersedia</option>
                                </select>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="drink.php" class="btn btn-secondary">Batal</a>
                                <button type="submit" name="submit" class="btn btn-warning"><?php echo $action == "edit" ? "Update" : "Simpan"; ?></button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php else: ?>

                <!-- Search and filter section -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row g-3 align-items-center">
                                <div class="col-auto">
                                    <input type="text" class="form-control" name="search" placeholder="Cari minuman..." 
                                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                </div>
                                <div class="col-auto">
                                    <select name="jenis" class="form-select">
                                        <option value="">Semua Jenis</option>
                                        <option value="panas" <?php echo (isset($_GET['jenis']) && $_GET['jenis'] == 'panas') ? 'selected' : ''; ?>>Panas</option>
                                        <option value="dingin" <?php echo (isset($_GET['jenis']) && $_GET['jenis'] == 'dingin') ? 'selected' : ''; ?>>Dingin</option>
                                        <option value="keduanya" <?php echo (isset($_GET['jenis']) && $_GET['jenis'] == 'keduanya') ? 'selected' : ''; ?>>Keduanya</option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <select name="status" class="form-select">
                                        <option value="">Semua Status</option>
                                        <option value="1" <?php echo (isset($_GET['status']) && $_GET['status'] == '1') ? 'selected' : ''; ?>>Tersedia</option>
                                        <option value="0" <?php echo (isset($_GET['status']) && $_GET['status'] == '0') ? 'selected' : ''; ?>>Tidak Tersedia</option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-warning">Filter</button>
                                </div>
                                <div class="col-auto">
                                    <a href="drink.php" class="btn btn-secondary">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Drinks data table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Minuman</th>
                                        <th>Kategori</th>
                                        <th>Deskripsi</th>
                                        <th>Jenis</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Prepare query with possible filters
                                    $sql = "SELECT m.*, k.nama_kategori 
                                            FROM minuman m 
                                            LEFT JOIN kategori k ON m.id_kategori = k.id_kategori 
                                            WHERE 1=1";
                                    
                                    // Add search filter if provided
                                    if (isset($_GET['search']) && !empty($_GET['search'])) {
                                        $search = $conn->real_escape_string($_GET['search']);
                                        $sql .= " AND (m.nama_minuman LIKE '%$search%' OR m.deskripsi LIKE '%$search%')";
                                    }
                                    
                                    // Add jenis filter if provided
                                    if (isset($_GET['jenis']) && !empty($_GET['jenis'])) {
                                        $jenis = $conn->real_escape_string($_GET['jenis']);
                                        $sql .= " AND m.jenis = '$jenis'";
                                    }
                                    
                                    // Add status filter if provided
                                    if (isset($_GET['status']) && $_GET['status'] !== '') {
                                        $status = intval($_GET['status']);
                                        $sql .= " AND m.status_ketersediaan = $status";
                                    }
                                    
                                    $sql .= " ORDER BY m.id_minuman DESC";
                                    $result = $conn->query($sql);

                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . $row['id_minuman'] . "</td>";
                                            echo "<td>" . htmlspecialchars($row['nama_minuman']) . "</td>";
                                            echo "<td>" . (isset($row['nama_kategori']) ? htmlspecialchars($row['nama_kategori']) : 'Tidak ada kategori') . "</td>";
                                            echo "<td>" . htmlspecialchars($row['deskripsi']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['jenis']) . "</td>";
                                            echo "<td>";
                                            if ($row['status_ketersediaan'] == 1) {
                                                echo '<span class="badge bg-success">Tersedia</span>';
                                            } else {
                                                echo '<span class="badge bg-danger">Tidak Tersedia</span>';
                                            }
                                            echo "</td>";
                                            echo "<td>";
                                            echo "<a href='drink.php?edit=" . $row['id_minuman'] . "' class='btn btn-sm btn-warning me-1'>
                                                    <i class='bi bi-pencil'></i>
                                                  </a>";
                                            echo "<a href='drink.php?delete=" . $row['id_minuman'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Apakah Anda yakin ingin menghapus minuman ini?\")'>
                                                    <i class='bi bi-trash'></i>
                                                  </a>";
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7' class='text-center'>Tidak ada data minuman</td></tr>";
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

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// Close the connection
$conn->close();
?>