<?php
session_start();
$currentPage = 'peta';
require_once "../config/db.php";

/* FILTER ISLAND */
$islandId = $_GET['island_id'] ?? '';

/* QUERY DATA */
$sql = "
SELECT
    f.fish_name,
    r.region_name,
    f.conservation_status,
    g.population
FROM MsFishDetail f
JOIN MsFishGeo g ON f.fish_id = g.fish_id
JOIN MsRegion r ON g.region_id = r.region_id
WHERE 1=1
";

if ($islandId != '') {
    $sql .= " AND r.island_id = " . intval($islandId);
}

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FinFish - Peta Persebaran</title>

<link rel="stylesheet" href="../assets/css/peta.css">
</head>

<body>

    <div class="topbar">
        <div class="logo">
            <a href="../index.php">🐟 FinFish</a>
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

            <div class="map-wrapper">

                <img src="../assets/img/petaindo.png" alt="Peta Indonesia">

                <a href="?island_id=1" class="island-btn" style="top:20%;left:10%;">Sumatera</a>
                <a href="?island_id=2" class="island-btn" style="top:60%;left:30%;">Jawa</a>
                <a href="?island_id=3" class="island-btn" style="top:30%;left:40%;">Kalimantan</a>
                <a href="?island_id=4" class="island-btn" style="top:45%;left:60%;">Sulawesi</a>
                <a href="?island_id=5" class="island-btn" style="top:50%;left:80%;">Papua</a>

            </div>

            <div class="table-wrapper">

                <table>
                    <thead>
                        <tr>
                            <th>Jenis Ikan</th>
                            <th>Wilayah</th>
                            <th>Status</th>
                            <th>Populasi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $row['fish_name'] ?></td>
                            <td><?= $row['region_name'] ?></td>
                            <td><?= $row['conservation_status'] ?></td>
                            <td><?= number_format($row['population']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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