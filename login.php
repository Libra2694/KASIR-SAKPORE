<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/paths.php';
require_once 'includes/functions.php';

// Jika sudah login, redirect ke dashboard sesuai role
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: kasir/dashboard.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = sanitize($_POST['password'] ?? '');
    
    if (!empty($username) && !empty($password)) {
        $user = validateLogin($username, $password);
        
        if ($user) {
            $_SESSION['user_id'] = $user['userID'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['jabatan'] = $user['jabatan'];
            $_SESSION['id'] = $user['id'];
            
            // Redirect berdasarkan role
            if ($user['jabatan'] === 'Admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: kasir/dashboard.php');
            }
            exit();
        } else {
            $error = 'Username atau password salah!';
        }
    } else {
        $error = 'Username dan password harus diisi!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kasir SAKPORE</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-left">
                <div class="login-logo">S</div>
                <h2 style="margin-top: 20px;">Welcome To SAKPORE</h2>
            </div>
            <div class="login-right">
                <h1 class="login-title">LOGIN</h1>
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <span>⚠️</span>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>
                <form method="POST" action="" id="loginForm">
                    <div class="form-group">
                        <label for="username">USERNAME :</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               placeholder="Masukkan username" required autofocus autocomplete="username">
                    </div>
                    <div class="form-group">
                        <label for="password">PASSWORD :</label>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Masukkan password" required autocomplete="current-password">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <span>LOGIN</span>
                    </button>
                </form>
                <div style="margin-top: 30px; text-align: center; color: #666; font-size: 13px;">
                    <p>Default: admin/admin atau kasir/kasir</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

