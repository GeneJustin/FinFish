<?php
session_start();
$currentPage = 'setting';
require_once "../config/db.php";
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$username_session = $_SESSION['username'];
$accountQuery = mysqli_query($conn, "SELECT username, email FROM accounts WHERE username = '$username_session' LIMIT 1");
$accountData = mysqli_fetch_assoc($accountQuery);

$username_db = $accountData ? $accountData['username'] : $_SESSION['username'];
$email_db = $accountData ? $accountData['email'] : 'admin@gmail.com';

$email_parts = explode("@", $email_db);
$username_email = $email_parts[0];
$domain_email = $email_parts[1];

$masked_username_email = substr($username_email, 0, 1) . str_repeat('*', strlen($username_email) - 1);
$masked_email = $masked_username_email . "@" . $domain_email;

$masked_password = "****";

?>



<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FindFins - Setting</title>

<link rel="stylesheet" href="../assets/css/setting.css">
</head>

<body>
    <div class="topbar">
        <div class="logo">
            <a href="../index.php">🐟 FindFins</a>
        </div>
        <div class="topbar-right">
            <button id="darkModeBtn">
                🌙 Dark Mode
            </button>
            
            <?php if (isset($_SESSION['username'])): ?>
                <span class="user-greeting">
                    Halo, <?= htmlspecialchars($_SESSION['username']) ?>
                </span>
            <?php else: ?>
                <a href="../login.php" class="btn-login">
                    Login
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="page-container">
        <aside class="sidebar">
            <a href="search.php" class="<?= $currentPage == 'search' ? 'active' : '' ?>">Cari Ikan & Wilayah</a>
            <a href="filter.php" class="<?= $currentPage == 'filter' ? 'active' : '' ?>">Filter Data</a>
            <a href="peta.php" class="<?= $currentPage == 'peta' ? 'active' : '' ?>">Peta Persebaran</a>
            <a href="detail.php" class="<?= $currentPage == 'detail' ? 'active' : '' ?>">Detail Ikan</a>
            <a href="population.php" class="<?= $currentPage == 'population' ? 'active' : '' ?>">Statistik Populasi</a>
            <a href="collection.php" class="<?= $currentPage == 'collection' ? 'active' : '' ?>">Koleksi Ikan</a>
            <a href="favorite.php" class="<?= $currentPage == 'favorite' ? 'active' : '' ?>">Wilayah Favorit</a>
            <a href="download.php" class="<?= $currentPage == 'download' ? 'active' : '' ?>">Unduh Data</a>
            <a href="notification.php" class="<?= $currentPage == 'notification' ? 'active' : '' ?>">Notifikasi</a>
            <a href="setting.php" class="<?= $currentPage == 'setting' ? 'active' : '' ?>">Pengaturan</a>
        </aside>

        <div class="content">
            <h2 class="title">Pengaturan Akun & Aplikasi</h2>
            
            <div class="settings-container">
                <div class="settings-section">
                    <h3 class="section-title">Account Information</h3>
                    <ul class="account-info-list">
                        <li>
                            <span class="info-label">Username</span>
                            <span class="info-value"><?= htmlspecialchars($username_db) ?></span>
                        </li>
                        <li>
                            <span class="info-label">Password</span>
                            <span class="info-value"><?= $masked_password ?></span>
                        </li>
                        <li>
                            <span class="info-label">Email</span>
                            <span class="info-value"><?= htmlspecialchars($masked_email) ?></span>
                        </li>
                    </ul>
                </div>

                <hr class="settings-divider">

                <div class="settings-section">
                    <h3 class="section-title">Notifications</h3>
                    
                    <div class="setting-row">
                        <span class="setting-label">Turn on Notifications</span>
                        <label class="switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="setting-row">
                        <span class="setting-label">Send me Email updates</span>
                        <label class="switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="setting-row">
                        <span class="setting-label">Haptics and Vibrate</span>
                        <label class="switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <hr class="settings-divider">

                <div class="settings-section">
                    <h3 class="section-title">Location</h3>
                    
                    <div class="setting-row">
                        <span class="setting-label">Turn on Location</span>
                        <label class="switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="setting-row">
                        <span class="setting-label">Share My Location with Nearby Devices</span>
                        <label class="switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="setting-row">
                        <span class="setting-label">Keep my Account Anonymous</span>
                        <label class="switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script>
        const darkModeBtn = document.getElementById('darkModeBtn');
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark');
            darkModeBtn.textContent = '☀️ Light Mode';
        } else {
            document.body.classList.remove('dark');
            darkModeBtn.textContent = '🌙 Dark Mode';
        }

        darkModeBtn.addEventListener('click', () => {
            document.body.classList.toggle('dark');

            if (document.body.classList.contains('dark')) {
                localStorage.setItem('theme', 'dark');
                darkModeBtn.textContent = '☀️ Light Mode';
            } else {
                localStorage.setItem('theme', 'light');
                darkModeBtn.textContent = '🌙 Dark Mode';
            }
        });
    </script>

</body>
</html>