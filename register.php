<?php
// Inisialisasi session
session_start();

// Cek jika user sudah login, redirect ke halaman dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: drik.php");
    exit;
}

// Inisialisasi variabel error dan success
$error = "";
$success = "";

// Proses form registrasi jika ada POST request
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Koneksi ke database
    $conn = new mysqli("localhost", "root", "", "db_restoran");
    
    // Cek koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    
    // Ambil data dari form
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nama_lengkap = $conn->real_escape_string($_POST['nama_lengkap']);
    $email = $conn->real_escape_string($_POST['email']);
    $no_telepon = $conn->real_escape_string($_POST['no_telepon']);
    
    // Validasi data
    if($password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok!";
    } else {
        // Cek apakah username sudah ada
        $check_sql = "SELECT id_user FROM user WHERE username = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if($check_result->num_rows > 0) {
            $error = "Username sudah digunakan, silakan pilih username lain!";
        } else {
            // Cek apakah email sudah ada (jika email diisi)
            if(!empty($email)) {
                $check_email_sql = "SELECT id_user FROM user WHERE email = ?";
                $check_email_stmt = $conn->prepare($check_email_sql);
                $check_email_stmt->bind_param("s", $email);
                $check_email_stmt->execute();
                $check_email_result = $check_email_stmt->get_result();
                
                if($check_email_result->num_rows > 0) {
                    $error = "Email sudah digunakan, silakan gunakan email lain!";
                }
            }
            
            // Jika tidak ada error, lanjutkan registrasi
            if(empty($error)) {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Default role adalah 'kasir', admin akan mengubah role jika diperlukan
                $role = "kasir";
                
                // Insert user baru
                $insert_sql = "INSERT INTO user (username, password, nama_lengkap, email, no_telepon, role) VALUES (?, ?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("ssssss", $username, $hashed_password, $nama_lengkap, $email, $no_telepon, $role);
                
                if($insert_stmt->execute()) {
                    $success = "Registrasi berhasil! Silakan login dengan akun Anda.";
                } else {
                    $error = "Terjadi kesalahan saat mendaftar. Silakan coba lagi!";
                }
            }
        }
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Sistem Restoran</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .register-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .brand {
            text-align: center;
            margin-bottom: 30px;
        }
        .brand h1 {
            color: #d35400;
            font-weight: 700;
        }
        .form-control:focus {
            border-color: #d35400;
            box-shadow: 0 0 0 0.25rem rgba(211, 84, 0, 0.25);
        }
        .btn-primary {
            background-color: #d35400;
            border-color: #d35400;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #b14600;
            border-color: #b14600;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #d35400;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="register-container">
                    <div class="brand">
                        <h1>Restoran App</h1>
                        <p class="text-muted">Daftar akun baru</p>
                    </div>
                    
                    <?php if(!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($success)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $success; ?>
                        <br>
                        <a href="login.php" class="alert-link">Klik di sini untuk login</a>
                    </div>
                    <?php endif; ?>
                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        
                        <div class="mb-3">
                            <label for="no_telepon" class="form-label">No. Telepon</label>
                            <input type="text" class="form-control" id="no_telepon" name="no_telepon">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Daftar</button>
                        </div>
                    </form>
                    
                    <div class="login-link">
                        <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>