<?php
// Konfigurasi koneksi database
$host = "localhost";
$username = "root";
$password = "";
$database = "db_restoran";

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Mengecek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
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
$mode = "tambah";

// Fungsi untuk membersihkan input
function bersihkan_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
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
                    
                    // Reset form
                    $nama_makanan = "";
                    $id_kategori = "";
                    $deskripsi = "";
                    $bahan_utama = "";
                    $waktu_persiapan = "";
                    $status_ketersediaan = 1;
                    $harga = "";
                    $harga_diskon = "";
                } catch (Exception $e) {
                    // Rollback jika terjadi kesalahan
                    $conn->rollback();
                    $pesan_error = "Error: " . $e->getMessage();
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
                    $mode = "tambah"; // Kembali ke mode tambah
                    
                    // Reset form
                    $id_makanan = "";
                    $nama_makanan = "";
                    $id_kategori = "";
                    $deskripsi = "";
                    $bahan_utama = "";
                    $waktu_persiapan = "";
                    $status_ketersediaan = 1;
                    $harga = "";
                    $harga_diskon = "";
                } catch (Exception $e) {
                    // Rollback jika terjadi kesalahan
                    $conn->rollback();
                    $pesan_error = "Error: " . $e->getMessage();
                }
            }
        }
    }
}

// Proses aksi dari tabel (edit/hapus)
if (isset($_GET['aksi'])) {
    if ($_GET['aksi'] == 'edit' && isset($_GET['id'])) {
        $id_makanan = bersihkan_input($_GET['id']);
        $mode = "edit";
        
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
    } elseif ($_GET['aksi'] == 'hapus' && isset($_GET['id'])) {
        $id_makanan = bersihkan_input($_GET['id']);
        
        // Mulai transaksi
        $conn->begin_transaction();
        try {
            // Set harga saat ini berakhir
            $stmt = $conn->prepare("UPDATE harga SET tanggal_selesai = CURDATE() WHERE jenis='makanan' AND id_produk=? AND tanggal_selesai IS NULL");
            $stmt->bind_param("i", $id_makanan);
            $stmt->execute();
            
            // Hapus data makanan (atau bisa juga hanya mengupdate status, tidak benar-benar menghapus)
            $stmt = $conn->prepare("DELETE FROM makanan WHERE id_makanan=?");
            $stmt->bind_param("i", $id_makanan);
            $stmt->execute();
            
            // Commit transaksi
            $conn->commit();
            $pesan = "Data makanan berhasil dihapus!";
        } catch (Exception $e) {
            // Rollback jika terjadi kesalahan
            $conn->rollback();
            $pesan_error = "Error: " . $e->getMessage();
        }
    }
}

// Pagination
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

// Ambil data kategori untuk dropdown
$sql = "SELECT id_kategori, nama_kategori FROM kategori WHERE jenis='makanan' ORDER BY nama_kategori";
$kategori = $conn->query($sql);
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
        .sidebar {
            min-height: calc(100vh - 56px);
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .sidebar .nav-link {
            padding: 10px 15px;
            font-size: 16px;
            color: #333;
        }
        .sidebar .nav-link:hover {
            background-color: #f8f9fa;
            color: #0d6efd;
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
        .form-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .table-section {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .badge-available {
            background-color: #28a745;
            color: white;
        }
        .badge-unavailable {
            background-color: #dc3545;
            color: white;
        }
        .price-discount {
            text-decoration: line-through;
            color: #6c757d;
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
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link text-dark">
                                <i class="bi bi-house-door me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="food.php" class="nav-link text-dark active">
                                <i class="bi bi-egg-fried me-2"></i> Makanan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="drink.php" class="nav-link text-dark">
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
                    </ul>
                    <hr>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-10 px-4 py-3">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Manajemen Menu Makanan</h2>
                </div>
                
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
                
                <!-- Form Section -->
                <div class="form-section">
                    <h4><?php echo ($mode == 'edit') ? 'Edit' : 'Tambah'; ?> Menu Makanan</h4>
                    <form method="post" action="">
                        <input type="hidden" name="aksi" value="<?php echo $mode; ?>">
                        <?php if($mode == 'edit'): ?>
                        <input type="hidden" name="id_makanan" value="<?php echo $id_makanan; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nama_makanan" class="form-label">Nama Makanan*</label>
                                <input type="text" class="form-control" id="nama_makanan" name="nama_makanan" value="<?php echo $nama_makanan; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="id_kategori" class="form-label">Kategori*</label>
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
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="bahan_utama" class="form-label">Bahan Utama</label>
                                <input type="text" class="form-control" id="bahan_utama" name="bahan_utama" value="<?php echo $bahan_utama; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="waktu_persiapan" class="form-label">Waktu Persiapan (menit)</label>
                                <input type="number" class="form-control" id="waktu_persiapan" name="waktu_persiapan" value="<?php echo $waktu_persiapan; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="harga" class="form-label">Harga*</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="harga" name="harga" value="<?php echo $harga; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="harga_diskon" class="form-label">Harga Diskon</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="harga_diskon" name="harga_diskon" value="<?php echo $harga_diskon; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?php echo $deskripsi; ?></textarea>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="status_ketersediaan" name="status_ketersediaan" <?php if($status_ketersediaan) echo 'checked'; ?>>
                            <label class="form-check-label" for="status_ketersediaan">Tersedia</label>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo ($mode == 'edit') ? 'Update' : 'Simpan'; ?>
                            </button>
                            <?php if($mode == 'edit'): ?>
                            <a href="?aksi=batal" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <!-- Table Section -->
                <div class="table-section">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4>Daftar Menu Makanan</h4>
                        <div class="input-group" style="width: 300px;">
                            <input type="text" class="form-control" placeholder="Cari..." id="searchInput">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Makanan</th>
                                    <th>Kategori</th>
                                    <th>Bahan Utama</th>
                                    <th>Deskripsi</th>
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
                                    <td><?php echo $row['nama_makanan']; ?></td>
                                    <td><?php echo $row['nama_kategori']; ?></td>
                                    <td><?php echo $row['bahan_utama']; ?></td>
                                    <td><?php echo !empty($row['deskripsi']) ? (strlen($row['deskripsi']) > 50 ? substr($row['deskripsi'], 0, 50).'...' : $row['deskripsi']) : '-'; ?></td>
                                    <td><?php echo $row['waktu_persiapan']; ?> menit</td>
                                    <td>
                                        <?php if($row['harga_diskon']): ?>
                                            <span class="price-discount">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></span><br>
                                            Rp <?php echo number_format($row['harga_diskon'], 0, ',', '.'); ?>
                                        <?php else: ?>
                                            Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?>
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
                                        <a href="?aksi=edit&id=<?php echo $row['id_makanan']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?aksi=hapus&id=<?php echo $row['id_makanan']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile; 
                                else: 
                                ?>
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
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
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS dan Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    
    <!-- Script untuk pencarian -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const tableRows = document.querySelectorAll('tbody tr');
        
        searchInput.addEventListener('keyup', function() {
            const searchText = searchInput.value.toLowerCase();
            
            tableRows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
    </script>
</body>
</html>