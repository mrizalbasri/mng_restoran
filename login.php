<?php
// Inisialisasi session
session_start();

// Cek jika user sudah login, redirect ke halaman dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: drink.php");
    exit;
}

// Inisialisasi variabel error
$error = "";

// Proses form login jika ada POST request
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
    
    // Query untuk mencari user
    $sql = "SELECT id_user, username, password, nama_lengkap, role FROM user WHERE username = ? AND status_aktif = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verifikasi password
        if(password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            
            // Update waktu login terakhir
            $update_sql = "UPDATE user SET terakhir_login = NOW() WHERE id_user = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $user['id_user']);
            $update_stmt->execute();
            
            // Redirect ke dashboard sesuai role
            header("Location: food.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan atau akun tidak aktif!";
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Restoran</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            max-width: 450px;
            margin: 100px auto;
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
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        .register-link a {
            color: #d35400;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="login-container">
                    <div class="brand">
                        <h1>Restoran App</h1>
                        <p class="text-muted">Masuk ke sistem manajemen restoran</p>
                    </div>
                    
                    <?php if(!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Masuk</button>
                        </div>
                    </form>
                    
                    <div class="register-link">
                        <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>