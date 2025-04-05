<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_restoran";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
    $stmt->bind_param('isddssj', $id_produk, $jenis, $harga, $harga_diskon, $tanggal_mulai, $tanggal_selesai, $id_harga);
    
    if ($stmt->execute()) {
        $success_message = "Data harga berhasil diperbarui!";
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

// Get all price records
$sql = "SELECT h.id_harga, h.id_produk, h.jenis, h.harga, h.harga_diskon, 
        h.tanggal_mulai, h.tanggal_selesai, 
        CASE 
            WHEN h.jenis = 'makanan' THEN m.nama_makanan 
            WHEN h.jenis = 'minuman' THEN d.nama_minuman 
        END AS nama_produk
        FROM harga h
        LEFT JOIN makanan m ON h.id_produk = m.id_makanan AND h.jenis = 'makanan'
        LEFT JOIN minuman d ON h.id_produk = d.id_minuman AND h.jenis = 'minuman'
        ORDER BY h.tanggal_mulai DESC";
        
$result = $conn->query($sql);

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
                        <a class="nav-link" href="index.php">
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
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link text-dark">
                                <i class="bi bi-house-door me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="food.php" class="nav-link text-dark">
                                <i class="bi bi-egg-fried me-2"></i> Makanan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="drink.php" class="nav-link text-dark">
                                <i class="bi bi-cup-straw me-2"></i> Minuman
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="prices.php" class="nav-link text-dark active">
                                <i class="bi bi-tags me-2"></i> Harga
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="orders.php" class="nav-link text-dark">
                                <i class="bi bi-cart me-2"></i> Pemesanan
                            </a>
                        </li>
                    </ul>
                    <hr>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-tags"></i> Manajemen Harga</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPriceModal">
                        <i class="bi bi-plus-circle"></i> Tambah Harga Baru
                    </button>
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

                <!-- Price List Table -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
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
                                    if ($result->num_rows > 0) {
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
                                            <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editPriceModal<?php echo $row['id_harga']; ?>">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="prices.php?delete=<?php echo $row['id_harga']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    
                                    <!-- Edit Price Modal -->
                                    <div class="modal fade" id="editPriceModal<?php echo $row['id_harga']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-warning text-dark">
                                                    <h5 class="modal-title">Edit Data Harga</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="prices.php" method="post">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id_harga" value="<?php echo $row['id_harga']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label for="jenis" class="form-label">Jenis</label>
                                                            <select class="form-select" id="jenis" name="jenis" required>
                                                                <option value="makanan" <?php echo ($row['jenis'] == 'makanan') ? 'selected' : ''; ?>>Makanan</option>
                                                                <option value="minuman" <?php echo ($row['jenis'] == 'minuman') ? 'selected' : ''; ?>>Minuman</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="id_produk" class="form-label">Produk</label>
                                                            <select class="form-select" id="id_produk" name="id_produk" required>
                                                                <?php 
                                                                // Reset and reuse the food and drink query results
                                                                $food_result->data_seek(0);
                                                                $drink_result->data_seek(0);
                                                                
                                                                // Food options
                                                                echo '<optgroup label="Makanan">';
                                                                while($food = $food_result->fetch_assoc()) {
                                                                    $selected = ($row['jenis'] == 'makanan' && $row['id_produk'] == $food['id_makanan']) ? 'selected' : '';
                                                                    echo '<option value="' . $food['id_makanan'] . '" ' . $selected . '>' . $food['nama'] . '</option>';
                                                                }
                                                                echo '</optgroup>';
                                                                
                                                                // Drink options
                                                                echo '<optgroup label="Minuman">';
                                                                while($drink = $drink_result->fetch_assoc()) {
                                                                    $selected = ($row['jenis'] == 'minuman' && $row['id_produk'] == $drink['id_minuman']) ? 'selected' : '';
                                                                    echo '<option value="' . $drink['id_minuman'] . '" ' . $selected . '>' . $drink['nama'] . '</option>';
                                                                }
                                                                echo '</optgroup>';
                                                                ?>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="harga" class="form-label">Harga (Rp)</label>
                                                            <input type="number" step="0.01" class="form-control" id="harga" name="harga" value="<?php echo $row['harga']; ?>" required>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="harga_diskon" class="form-label">Harga Diskon (Rp)</label>
                                                            <input type="number" step="0.01" class="form-control" id="harga_diskon" name="harga_diskon" value="<?php echo $row['harga_diskon']; ?>">
                                                            <div class="form-text">Kosongkan jika tidak ada diskon.</div>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                                            <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="<?php echo $row['tanggal_mulai']; ?>" required>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                                                            <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" value="<?php echo $row['tanggal_selesai']; ?>">
                                                            <div class="form-text">Kosongkan jika harga masih berlaku.</div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" name="update_price" class="btn btn-warning">Perbarui</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
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
            </div>
        </div>
    </div>

    <!-- Add Price Modal -->
    <div class="modal fade" id="addPriceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Harga Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="prices.php" method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="add_jenis" class="form-label">Jenis</label>
                            <select class="form-select" id="add_jenis" name="jenis" required>
                                <option value="" selected disabled>Pilih Jenis</option>
                                <option value="makanan">Makanan</option>
                                <option value="minuman">Minuman</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="add_id_produk" class="form-label">Produk</label>
                            <select class="form-select" id="add_id_produk" name="id_produk" required>
                                <option value="" selected disabled>Pilih Produk</option>
                                <?php 
                                // Reset and reuse the food and drink query results
                                $food_result->data_seek(0);
                                $drink_result->data_seek(0);
                                
                                // Food options
                                echo '<optgroup label="Makanan">';
                                while($food = $food_result->fetch_assoc()) {
                                    echo '<option value="' . $food['id_makanan'] . '">' . $food['nama'] . '</option>';
                                }
                                echo '</optgroup>';
                                
                                // Drink options
                                echo '<optgroup label="Minuman">';
                                while($drink = $drink_result->fetch_assoc()) {
                                    echo '<option value="' . $drink['id_minuman'] . '">' . $drink['nama'] . '</option>';
                                }
                                echo '</optgroup>';
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="add_harga" class="form-label">Harga (Rp)</label>
                            <input type="number" step="0.01" class="form-control" id="add_harga" name="harga" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="add_harga_diskon" class="form-label">Harga Diskon (Rp)</label>
                            <input type="number" step="0.01" class="form-control" id="add_harga_diskon" name="harga_diskon">
                            <div class="form-text">Kosongkan jika tidak ada diskon.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="add_tanggal_mulai" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="add_tanggal_mulai" name="tanggal_mulai" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="add_tanggal_selesai" class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" id="add_tanggal_selesai" name="tanggal_selesai">
                            <div class="form-text">Kosongkan jika harga masih berlaku.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="submit_price" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>