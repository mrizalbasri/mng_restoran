<?php
// Konfigurasi koneksi database
require "database.php";


// Start this at the very top of your file, before anything else
session_start();

// Then check if the session variables exist before using them
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: login.php");
    exit;
}


// Inisialisasi variabel
$id_makanan = "";
$nama_makanan = "";
$id_kategori = "";
$deskripsi = "";
$bahan_utama = "";
$waktu_persiapan = "";
$status_ketersediaan = 1;
$harga = "";
$harga_diskon = "";
$pesan = "";
$pesan_error = "";
$mode = "list"; // Default tampilan daftar

// Fungsi untuk membersihkan input
function bersihkan_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Tentukan mode tampilan
if (isset($_GET['mode'])) {
    $mode = $_GET['mode'];
    
    if ($mode == 'tambah') {
        // Mode tambah data
        $action_mode = 'tambah';
    } elseif ($mode == 'edit' && isset($_GET['id'])) {
        // Mode edit data
        $action_mode = 'edit';
        $id_makanan = bersihkan_input($_GET['id']);
        
        // Ambil data makanan
        $sql = "SELECT m.*, h.harga, h.harga_diskon 
                FROM makanan m 
                LEFT JOIN harga h ON h.jenis='makanan' AND h.id_produk=m.id_makanan
                AND (h.tanggal_selesai IS NULL OR h.tanggal_selesai >= CURDATE())
                WHERE m.id_makanan = ? 
                ORDER BY h.tanggal_mulai DESC 
                LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_makanan);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $nama_makanan = $row['nama_makanan'];
            $id_kategori = $row['id_kategori'];
            $deskripsi = $row['deskripsi'];
            $bahan_utama = $row['bahan_utama'];
            $waktu_persiapan = $row['waktu_persiapan'];
            $status_ketersediaan = $row['status_ketersediaan'];
            $harga = $row['harga'];
            $harga_diskon = $row['harga_diskon'];
        }
    } else {
        // Default ke mode list
        $mode = 'list';
    }
} else {
    $mode = 'list';
}

// Proses form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['aksi'])) {
        // Menangani aksi tambah data
        if ($_POST['aksi'] == 'tambah') {
            $nama_makanan = bersihkan_input($_POST['nama_makanan']);
            $id_kategori = bersihkan_input($_POST['id_kategori']);
            $deskripsi = bersihkan_input($_POST['deskripsi']);
            $bahan_utama = bersihkan_input($_POST['bahan_utama']);
            $waktu_persiapan = bersihkan_input($_POST['waktu_persiapan']);
            $status_ketersediaan = isset($_POST['status_ketersediaan']) ? 1 : 0;
            $harga = bersihkan_input($_POST['harga']);
            $harga_diskon = !empty($_POST['harga_diskon']) ? bersihkan_input($_POST['harga_diskon']) : NULL;
            
            // Validasi input
            if (empty($nama_makanan) || empty($id_kategori) || empty($harga)) {
                $pesan_error = "Nama Makanan, Kategori, dan Harga harus diisi!";
                $mode = isset($_POST['source_mode']) ? $_POST['source_mode'] : 'tambah'; // Tetap di mode yang sama
            } else {
                // Mulai transaksi
                $conn->begin_transaction();
                try {
                    // Tambah data ke tabel makanan
                    $stmt = $conn->prepare("INSERT INTO makanan (nama_makanan, id_kategori, deskripsi, bahan_utama, waktu_persiapan, status_ketersediaan) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sissii", $nama_makanan, $id_kategori, $deskripsi, $bahan_utama, $waktu_persiapan, $status_ketersediaan);
                    $stmt->execute();
                    $id_makanan_baru = $conn->insert_id;
                    
                    // Tambah data ke tabel harga
                    $stmt = $conn->prepare("INSERT INTO harga (jenis, id_produk, harga, harga_diskon, tanggal_mulai) VALUES ('makanan', ?, ?, ?, CURDATE())");
                    $stmt->bind_param("idd", $id_makanan_baru, $harga, $harga_diskon);
                    $stmt->execute();
                    
                    // Commit transaksi
                    $conn->commit();
                    $pesan = "Data makanan berhasil ditambahkan!";
                    
                    // Redirect ke mode list setelah berhasil
                    header("Location: food.php?pesan=" . urlencode($pesan));
                    exit;
                } catch (Exception $e) {
                    // Rollback jika terjadi kesalahan
                    $conn->rollback();
                    $pesan_error = "Error: " . $e->getMessage();
                    $mode = isset($_POST['source_mode']) ? $_POST['source_mode'] : 'tambah'; // Tetap di mode yang sama
                }
            }
        }
        
        // Menangani aksi edit data
        elseif ($_POST['aksi'] == 'edit') {
            $id_makanan = bersihkan_input($_POST['id_makanan']);
            $nama_makanan = bersihkan_input($_POST['nama_makanan']);
            $id_kategori = bersihkan_input($_POST['id_kategori']);
            $deskripsi = bersihkan_input($_POST['deskripsi']);
            $bahan_utama = bersihkan_input($_POST['bahan_utama']);
            $waktu_persiapan = bersihkan_input($_POST['waktu_persiapan']);
            $status_ketersediaan = isset($_POST['status_ketersediaan']) ? 1 : 0;
            $harga = bersihkan_input($_POST['harga']);
            $harga_diskon = !empty($_POST['harga_diskon']) ? bersihkan_input($_POST['harga_diskon']) : NULL;
            
            // Validasi input
            if (empty($nama_makanan) || empty($id_kategori) || empty($harga)) {
                $pesan_error = "Nama Makanan, Kategori, dan Harga harus diisi!";
                $mode = 'edit'; // Tetap di mode edit
            } else {
                // Mulai transaksi
                $conn->begin_transaction();
                try {
                    // Update data di tabel makanan
                    $stmt = $conn->prepare("UPDATE makanan SET nama_makanan=?, id_kategori=?, deskripsi=?, bahan_utama=?, waktu_persiapan=?, status_ketersediaan=? WHERE id_makanan=?");
                    $stmt->bind_param("sissiii", $nama_makanan, $id_kategori, $deskripsi, $bahan_utama, $waktu_persiapan, $status_ketersediaan, $id_makanan);
                    $stmt->execute();
                    
                    // Cek apakah ada perubahan harga
                    $sql = "SELECT id_harga FROM harga WHERE jenis='makanan' AND id_produk=? AND (tanggal_selesai IS NULL OR tanggal_selesai >= CURDATE()) ORDER BY tanggal_mulai DESC LIMIT 1";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id_makanan);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $id_harga = $row['id_harga'];
                        
                        // Update harga yang sudah ada
                        $stmt = $conn->prepare("UPDATE harga SET harga=?, harga_diskon=? WHERE id_harga=?");
                        $stmt->bind_param("ddi", $harga, $harga_diskon, $id_harga);
                        $stmt->execute();
                    } else {
                        // Tambah data harga baru jika tidak ada yang aktif
                        $stmt = $conn->prepare("INSERT INTO harga (jenis, id_produk, harga, harga_diskon, tanggal_mulai) VALUES ('makanan', ?, ?, ?, CURDATE())");
                        $stmt->bind_param("idd", $id_makanan, $harga, $harga_diskon);
                        $stmt->execute();
                    }
                    
                    // Commit transaksi
                    $conn->commit();
                    $pesan = "Data makanan berhasil diperbarui!";
                    
                    // Redirect ke mode list setelah berhasil
                    header("Location: food.php?pesan=" . urlencode($pesan));
                    exit;
                } catch (Exception $e) {
                    // Rollback jika terjadi kesalahan
                    $conn->rollback();
                    $pesan_error = "Error: " . $e->getMessage();
                    $mode = 'edit'; // Tetap di mode edit
                }
            }
        }
    }
}

// Proses aksi hapus
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id'])) {
    $id_makanan = bersihkan_input($_GET['id']);
    
    // Mulai transaksi
    $conn->begin_transaction();
    try {
        // Set harga saat ini berakhir
        $stmt = $conn->prepare("UPDATE harga SET tanggal_selesai = CURDATE() WHERE jenis='makanan' AND id_produk=? AND tanggal_selesai IS NULL");
        $stmt->bind_param("i", $id_makanan);
        $stmt->execute();
        
        // Hapus data makanan
        $stmt = $conn->prepare("DELETE FROM makanan WHERE id_makanan=?");
        $stmt->bind_param("i", $id_makanan);
        $stmt->execute();
        
        // Commit transaksi
        $conn->commit();
        $pesan = "Data makanan berhasil dihapus!";
        
        // Redirect kembali ke halaman utama
        header("Location: food.php?pesan=" . urlencode($pesan));
        exit;
    } catch (Exception $e) {
        // Rollback jika terjadi kesalahan
        $conn->rollback();
        $pesan_error = "Error: " . $e->getMessage();
    }
}

// Ambil pesan dari URL jika ada
if (isset($_GET['pesan'])) {
    $pesan = $_GET['pesan'];
}

// Pagination (hanya untuk mode list)
if ($mode == 'list') {
    $batas = 10;
    $halaman = isset($_GET["halaman"]) ? (int)$_GET["halaman"] : 1;
    $halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

    $previous = $halaman - 1;
    $next = $halaman + 1;

    $sql = "SELECT COUNT(*) as total FROM makanan";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $jumlah_data = $row['total'];
    $total_halaman = ceil($jumlah_data / $batas);

    // Mengambil data untuk tabel dengan pagination
    $sql = "SELECT m.id_makanan, m.nama_makanan, k.nama_kategori, m.bahan_utama, 
            m.deskripsi, m.waktu_persiapan, m.status_ketersediaan, h.harga, h.harga_diskon
            FROM makanan m
            JOIN kategori k ON m.id_kategori = k.id_kategori
            LEFT JOIN harga h ON h.jenis='makanan' AND h.id_produk=m.id_makanan
            AND (h.tanggal_selesai IS NULL OR h.tanggal_selesai >= CURDATE())
            ORDER BY m.id_makanan DESC
            LIMIT $halaman_awal, $batas";
    $data_makanan = $conn->query($sql);
}

// Ambil data kategori untuk dropdown (untuk mode tambah dan edit)
if ($mode == 'tambah' || $mode == 'edit') {
    $sql = "SELECT id_kategori, nama_kategori FROM kategori WHERE jenis='makanan' ORDER BY nama_kategori";
    $kategori = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Manajemen Makanan</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
       .content-section {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .badge-available {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .badge-unavailable {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .price-discount {
            text-decoration: line-through;
            color: #6c757d;
        }
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .form-section {
            max-width: 800px;
            margin: 0 auto;
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
                <a href="food.php" class="nav-link text-light active d-flex align-items-center">
                    <i class="bi bi-egg-fried me-3"></i>
                    <span>Makanan</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="drink.php" class="nav-link text-light  d-flex align-items-center">
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
        
        <div class="mt-auto p-3 border-top border-secondary">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle me-2 text-warning"></i>
                <small>Admin Panel v1.0</small>
            </div>
        </div>
    </div>
</div>
            <!-- Main Content -->
            <div class="col-lg-10 px-4 py-3">

                <!-- Alert Messages -->
                <?php if(!empty($pesan)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $pesan; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($pesan_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $pesan_error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if($mode == 'list'): ?>
                <!-- LIST MODE -->
                <div class="content-header">
                        <h2><i class="bi bi-egg-fried me-2"></i> Daftar Menu Makanan</h2>
                        <a href="?mode=tambah" class="btn btn-warning">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Makanan Baru
                        </a>
                    </div>
                <div class="content-section">
                   
                    
                    <div class="row mb-3">
                        <div class="col-md-4 ms-auto">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Cari makanan..." id="searchInput">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Makanan</th>
                                    <th>Kategori</th>
                                    <th>Bahan Utama</th>
                                    <th>Waktu</th>
                                    <th>Harga</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = $halaman_awal + 1;
                                if($data_makanan->num_rows > 0):
                                    while($row = $data_makanan->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <strong><?php echo $row['nama_makanan']; ?></strong>
                                        <?php if(!empty($row['deskripsi'])): ?>
                                        <br><small class="text-muted"><?php echo (strlen($row['deskripsi']) > 50) ? substr($row['deskripsi'], 0, 50).'...' : $row['deskripsi']; ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $row['nama_kategori']; ?></td>
                                    <td><?php echo $row['bahan_utama']; ?></td>
                                    <td><?php echo $row['waktu_persiapan']; ?> menit</td>
                                    <td>
                                        <?php if($row['harga_diskon']): ?>
                                            <span class="price-discount">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></span><br>
                                            <strong>Rp <?php echo number_format($row['harga_diskon'], 0, ',', '.'); ?></strong>
                                        <?php else: ?>
                                            <strong>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></strong>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($row['status_ketersediaan']): ?>
                                            <span class="badge badge-available">Tersedia</span>
                                        <?php else: ?>
                                            <span class="badge badge-unavailable">Tidak Tersedia</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="?mode=edit&id=<?php echo $row['id_makanan']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="?aksi=hapus&id=<?php echo $row['id_makanan']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Yakin ingin menghapus <?php echo $row['nama_makanan']; ?>?')"
                                               data-bs-toggle="tooltip" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile; 
                                else: 
                                ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-info-circle me-2 fs-4"></i>
                                            <p>Tidak ada data makanan tersedia</p>
                                            <a href="?mode=tambah" class="btn btn-primary btn-sm">Tambah Makanan Sekarang</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if($total_halaman > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php if($halaman <= 1) echo 'disabled'; ?>">
                                <a class="page-link" href="?halaman=<?php echo $previous; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php for($x = 1; $x <= $total_halaman; $x++): ?>
                            <li class="page-item <?php if($halaman == $x) echo 'active'; ?>">
                                <a class="page-link" href="?halaman=<?php echo $x; ?>"><?php echo $x; ?></a>
                            </li>
                            <?php endfor; ?>
                            <li class="page-item <?php if($halaman >= $total_halaman) echo 'disabled'; ?>">
                                <a class="page-link" href="?halaman=<?php echo $next; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
                
                <?php elseif($mode == 'tambah' || $mode == 'edit'): ?>
                <!-- FORM MODE (TAMBAH/EDIT) -->
                <div class="content-section">
                    <div class="content-header mb-4">
                        <h2><?php echo ($mode == 'edit') ? 'Edit Menu Makanan' : 'Tambah Menu Makanan Baru'; ?></h2>
                        <a href="food.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                        </a>
                    </div>
                    
                    <div class="form-section">
                        <form method="post" action="">
                            <input type="hidden" name="aksi" value="<?php echo ($mode == 'edit') ? 'edit' : 'tambah'; ?>">
                            <input type="hidden" name="source_mode" value="<?php echo $mode; ?>">
                            
                            <?php if($mode == 'edit'): ?>
                            <input type="hidden" name="id_makanan" value="<?php echo $id_makanan; ?>">
                            <?php endif; ?>
                            
                            <div class="row mb-4">
                                <div class="col-12 mb-4">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-warning text-white">
                                            <h5 class="mb-0">Informasi Dasar</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="nama_makanan" class="form-label fw-bold">Nama Makanan*</label>
                                                    <input type="text" class="form-control" id="nama_makanan" name="nama_makanan" value="<?php echo $nama_makanan; ?>" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="id_kategori" class="form-label fw-bold">Kategori*</label>
                                                    <select class="form-select" id="id_kategori" name="id_kategori" required>
                                                        <option value="">Pilih Kategori</option>
                                                        <?php while($row = $kategori->fetch_assoc()): ?>
                                                        <option value="<?php echo $row['id_kategori']; ?>" <?php if($id_kategori == $row['id_kategori']) echo 'selected'; ?>>
                                                            <?php echo $row['nama_kategori']; ?>
                                                        </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="deskripsi" class="form-label fw-bold">Deskripsi</label>
                                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Tuliskan deskripsi singkat tentang makanan ini"><?php echo $deskripsi; ?></textarea>
                                                        </div><div class="row">
    <div class="col-md-6 mb-3">
        <label for="bahan_utama" class="form-label fw-bold">Bahan Utama</label>
        <input type="text" class="form-control" id="bahan_utama" name="bahan_utama" value="<?php echo $bahan_utama; ?>">
    </div>
    <div class="col-md-6 mb-3">
        <label for="waktu_persiapan" class="form-label fw-bold">Waktu Persiapan (menit)</label>
        <input type="number" class="form-control" id="waktu_persiapan" name="waktu_persiapan" value="<?php echo $waktu_persiapan; ?>">
    </div>
</div>

<div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="status_ketersediaan" name="status_ketersediaan" <?php if($status_ketersediaan) echo 'checked'; ?>>
    <label class="form-check-label" for="status_ketersediaan">
        Tersedia
    </label>
</div>
</div>
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-warning text-white">
        <h5 class="mb-0">Informasi Harga</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="harga" class="form-label fw-bold">Harga Reguler*</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" class="form-control" id="harga" name="harga" value="<?php echo $harga; ?>" required>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="harga_diskon" class="form-label fw-bold">Harga Diskon <small class="text-muted">(opsional)</small></label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" class="form-control" id="harga_diskon" name="harga_diskon" value="<?php echo $harga_diskon; ?>">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between mt-4">
    <a href="food.php" class="btn btn-outline-secondary">Batal</a>
    <button type="submit" class="btn btn-success">
        <i class="bi bi-save me-1"></i> <?php echo ($mode == 'edit') ? 'Simpan Perubahan' : 'Simpan Data Baru'; ?>
    </button>
</div>
</form>
</div>
</div>
<?php endif; ?>
</div>
</div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>