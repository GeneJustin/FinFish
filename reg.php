<?php
require_once "config/db.php";

$error_message = "";
$success_message = "";

// Validasi Server-Side (PHP) saat Form Registrasi di-submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "Semua field wajib diisi!";
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid!";
    } 
    elseif ($password !== $confirm_password) {
        $error_message = "Konfirmasi password tidak cocok!";
    } 
    elseif (strlen($password) < 6) {
        $error_message = "Password minimal harus memiliki 6 karakter!";
    } else {
        // Query validasi disesuaikan dengan nama tabel baru Anda: accounts
        $check_query = "SELECT id FROM accounts WHERE username = ? OR email = ? LIMIT 1";
        $stmt_check = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt_check, "ss", $username, $email);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $error_message = "Username atau Email sudah terdaftar!";
        } else {
            // Hashing password dengan BCRYPT agar pas di kolom password varchar(255)
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Insert ke tabel accounts. id (AUTO_INCREMENT) dan created_at (TIMESTAMP) akan terisi otomatis
            $insert_query = "INSERT INTO accounts (username, email, password) VALUES (?, ?, ?)";
            $stmt_insert = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt_insert, "sss", $username, $email, $hashed_password);

            if (mysqli_stmt_execute($stmt_insert)) {
                $success_message = "Registrasi Berhasil! Silakan login.";
            } else {
                $error_message = "Terjadi kesalahan sistem, silakan coba lagi.";
            }
            mysqli_stmt_close($stmt_insert);
        }
        mysqli_stmt_close($stmt_check);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - FinFish</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
            transition: background 0.3s ease, color 0.3s ease;
        }

        body {
            background: #dbe7ff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .auth-container {
            background: #ffffff;
            width: 100%;
            max-width: 400px;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(12, 59, 110, 0.15);
        }

        .auth-logo {
            font-size: 28px;
            font-weight: 800;
            color: #0c3b6e;
            text-align: center;
            margin-bottom: 25px;
        }

        .auth-title {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 6px;
            color: #4b5970;
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ced7e6;
            border-radius: 6px;
            font-size: 14px;
            background-color: #fcfdfe;
            outline: none;
        }

        .form-group input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
        }

        .error-box {
            background: #ffe5e5;
            color: #cc0000;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 15px;
            border-left: 4px solid #cc0000;
        }

        .success-box {
            background: #e6f9ed;
            color: #1e7e34;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 15px;
            border-left: 4px solid #1e7e34;
        }

        .btn-auth {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 14px;
            width: 100%;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            font-size: 15px;
            margin-top: 5px;
            box-shadow: 0 4px 10px rgba(0, 123, 255, 0.2);
        }

        .btn-auth:hover {
            background-color: #0056b3;
        }

        .auth-switch {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #5c7599;
        }

        .auth-switch a {
            color: #007bff;
            text-decoration: none;
            font-weight: 700;
        }

        .auth-switch a:hover {
            text-decoration: underline;
        }

        /* --- DARK MODE OPTIONS --- */
        body.dark { background: #121212; }
        body.dark .auth-container { background: #1f1f1f; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4); }
        body.dark .auth-logo { color: #3b82f6; }
        body.dark .auth-title { color: #ffffff; }
        body.dark .form-group label { color: #b0b3b8; }
        body.dark .form-group input { background-color: #2d2d2d; border-color: #444; color: #fff; }
        body.dark .form-group input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2); }
        body.dark .auth-switch { color: #aaa; }
        body.dark .auth-switch a { color: #3b82f6; }
    </style>
</head>
<body>

    <div class="auth-container">
        <div class="auth-logo">🐟 FinFish</div>
        <h3 class="auth-title">Daftar Akun Baru</h3>

        <?php if (!empty($error_message)): ?>
            <div class="error-box"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="success-box"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <div id="jsErrorBox" class="error-box" style="display: none;"></div>

        <form id="registerForm" method="POST" action="reg.php" onsubmit="return validateRegister(event)">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Buat username baru">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" placeholder="Contoh: nama@domain.com">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Minimal 6 karakter">
            </div>

            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password anda">
            </div>

            <button type="submit" class="btn-auth">Register</button>
        </form>

        <div class="auth-switch">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark');
        }

        function validateRegister(e) {
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const errorBox = document.getElementById('jsErrorBox');

            if (!username || !email || !password || !confirmPassword) {
                e.preventDefault();
                errorBox.innerText = "Semua field input wajib diisi!";
                errorBox.style.display = "block";
                return false;
            }

            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                e.preventDefault();
                errorBox.innerText = "Format email tidak valid!";
                errorBox.style.display = "block";
                return false;
            }

            if (password.length < 6) {
                e.preventDefault();
                errorBox.innerText = "Password minimal harus 6 karakter!";
                errorBox.style.display = "block";
                return false;
            }

            if (password !== confirmPassword) {
                e.preventDefault();
                errorBox.innerText = "Konfirmasi password tidak cocok!";
                errorBox.style.display = "block";
                return false;
            }

            return true;
        }
    </script>
</body>
</html>