<?php
require_once 'config.php';

if (isLoggedIn('guru')) {
    redirect(BASE_URL . 'guru/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM guru WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $guru = $result->fetch_assoc();
        
        if (password_verify($password, $guru['password'])) {
            $_SESSION['guru_id'] = $guru['id_guru'];
            $_SESSION['guru_nama'] = $guru['nama_guru'];
            $_SESSION['guru_mapel'] = $guru['mapel'];
            
            redirect(BASE_URL . 'guru/dashboard.php');
        } else {
            $error = 'Username atau password salah!';
        }
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Guru - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-yellow: #FFD700;
            --dark-yellow: #FFC700;
            --primary-black: #1a1a1a;
            --secondary-black: #2d2d2d;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-black) 0%, var(--secondary-black) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid var(--primary-yellow);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
        }
        
        .login-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-yellow);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .login-icon i {
            font-size: 40px;
            color: var(--primary-black);
        }
        
        .login-title {
            color: var(--primary-yellow);
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .login-subtitle {
            color: #bbb;
            font-size: 0.9rem;
            text-align: center;
        }
        
        .form-label {
            color: var(--primary-yellow);
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 215, 0, 0.3);
            color: white;
            padding: 12px 15px;
            border-radius: 10px;
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-yellow);
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
        }
        
        .form-control::placeholder {
            color: #888;
        }
        
        .input-group-text {
            background: rgba(255, 215, 0, 0.2);
            border: 1px solid rgba(255, 215, 0, 0.3);
            color: var(--primary-yellow);
        }
        
        .btn-login {
            background: var(--primary-yellow);
            color: var(--primary-black);
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: bold;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background: var(--dark-yellow);
            color: var(--primary-black);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
        }
        
        .btn-back {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 10px;
            border-radius: 10px;
            width: 100%;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-back:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .password-toggle {
            cursor: pointer;
            user-select: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-icon">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <h2 class="login-title">Login Guru</h2>
            <p class="login-subtitle">Monitoring & Kelola Absensi Siswa</p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger mt-3">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="mt-4">
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" required>
                        <span class="input-group-text password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-login mb-3">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                
                <a href="<?php echo BASE_URL; ?>" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                </a>
            </form>
            
            
        </div>
    </div>

    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>