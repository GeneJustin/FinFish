<?php
require_once "config/db.php";
session_start();


$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error_message = "Semua field wajib diisi!";
    } else {
        $query = "SELECT id, username, password FROM accounts WHERE username = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                header("Location: index.php");
                exit();
            } else {
                $error_message = "Username atau password salah!";
            }
        } else {
            $error_message = "Username atau password salah!";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FindFins</title>
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
        <div class="auth-logo">🐟 FindFins</div>
        <h3 class="auth-title">Login ke Akun Anda</h3>

        <?php if (!empty($error_message)): ?>
            <div class="error-box"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div id="jsErrorBox" class="error-box" style="display: none;"></div>

        <form id="loginForm" method="POST" action="login.php" onsubmit="return validateLogin(event)">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username anda">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password anda">
            </div>

            <button type="submit" class="btn-auth">Login</button>
        </form>

        <div class="auth-switch">
            Don't have an account? <a href="reg.php">Register here</a>
        </div>
    </div>

    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark');
        }

        function validateLogin(e) {
            const usernameInput = document.getElementById('username').value.trim();
            const passwordInput = document.getElementById('password').value;
            const errorBox = document.getElementById('jsErrorBox');

            if (usernameInput === "" || passwordInput === "") {
                e.preventDefault();
                errorBox.innerText = "Username dan Password tidak boleh kosong!";
                errorBox.style.display = "block";
                return false;
            }
            return true;
        }
    </script>
</body>
</html>