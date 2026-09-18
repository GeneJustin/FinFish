<?php
session_start();
require_once "config/db.php";


$fishQuery = mysqli_query($conn,"
SELECT COUNT(DISTINCT fish_id) total
FROM MsFishGeo
");

if(isset($_GET['ajax']))
{
    header('Content-Type: application/json');

    $islandId = intval($_GET['island_id']);

    $fish = mysqli_query($conn,"
        SELECT COUNT(DISTINCT fish_id) total
        FROM MsFishGeo
        WHERE island_id = $islandId
    ");

    $region = mysqli_query($conn,"
        SELECT COUNT(DISTINCT region_id) total
        FROM MsFishGeo
        WHERE island_id = $islandId
    ");

    $population = mysqli_query($conn,"
        SELECT SUM(population) total
        FROM MsFishGeo
        WHERE island_id = $islandId
    ");

    echo json_encode([
        "fish" => mysqli_fetch_assoc($fish)['total'] ?? 0,
        "region" => mysqli_fetch_assoc($region)['total'] ?? 0,
        "population" => mysqli_fetch_assoc($population)['total'] ?? 0
    ]);

    exit;
}

$totalFish = mysqli_fetch_assoc($fishQuery)['total'];

$regionQuery = mysqli_query($conn,"
SELECT COUNT(*) total
FROM MsRegion
");

$totalRegion = mysqli_fetch_assoc($regionQuery)['total'];

$populationQuery = mysqli_query($conn,"
SELECT SUM(population) total
FROM MsFishGeo
");

$totalPopulation = mysqli_fetch_assoc($populationQuery)['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FinFish</title>

<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="navbar">

    <div class="logo">
        🐟 FinFish
    </div>

    <div class="search-container">
        <input type="text" placeholder="Cari ikan atau wilayah">
        <button>Cari</button>
    </div>
    <div class="navbar-right">
        <button id="darkModeBtn">
            🌙 Dark Mode
        </button>
        <?php if (isset($_SESSION['username'])): ?>
            <span class="user-greeting" style="color: #0c3b6e; font-weight: 700; margin-left: 15px;">
                Halo, <?= htmlspecialchars($_SESSION['username']) ?>
            </span>
            <?php else: ?>
            <a href="login.php" class="btn-login">
                Login
            </a>
        <?php endif; ?>
    </div>

</header>

<section class="hero">

    <div class="hero-left">

        <h2>Selamat Datang di</h2>
        <h1>FinFish</h1>

        <div id="selectedIsland" class="selected-island">
            Indonesia
        </div>

        <div class="stats">

            <div class="stat-card">
                <h3 id="fishCount">
                    <?= $totalFish ?>
                </h3>
                <p>Jenis Ikan</p>
            </div>

            <div class="stat-card">
                <h3 id="regionCount">
                    <?= $totalRegion ?>
                </h3>
                <p>Wilayah</p>
            </div>

            <div class="stat-card">
                <h3 id="populationCount">
                    <?= number_format($totalPopulation) ?>
                </h3>
                <p>Data Populasi</p>
            </div>

        </div>

    </div>

    <div class="hero-right">

        <div class="map-wrapper">

            <img
                src="assets/img/petaindo.png"
                class="indo-map"
                alt="Peta Indonesia">

            <button
                class="island-btn sumatera"
                data-id="1"
                data-name="Sumatera">
                Sumatera
            </button>

            <button
                class="island-btn jawa"
                data-id="2"
                data-name="Jawa">
                Jawa
            </button>

            <button
                class="island-btn kalimantan"
                data-id="3"
                data-name="Kalimantan">
                Kalimantan
            </button>

            <button
                class="island-btn sulawesi"
                data-id="4"
                data-name="Sulawesi">
                Sulawesi
            </button>

            <button
                class="island-btn papua"
                data-id="5"
                data-name="Papua">
                Papua
            </button>

            <button id="allBtn">
                Indonesia
            </button>

        </div>

    </div>

</section>

<section class="menu-grid">

    <a href="pages/search.php" class="menu-card">
        <div class="menu-icon">🔍</div>
        <h3>Cari Ikan & Wilayah</h3>
        <span>Buka →</span>
    </a>

    <a href="pages/filter.php" class="menu-card">
        <div class="menu-icon">⚙️</div>
        <h3>Filter Data</h3>
        <span>Buka →</span>
    </a>

    <a href="pages/peta.php" class="menu-card">
        <div class="menu-icon">🗺️</div>
        <h3>Peta Persebaran</h3>
        <span>Buka →</span>
    </a>

    <a href="pages/detail.php" class="menu-card">
        <div class="menu-icon">📄</div>
        <h3>Detail Ikan</h3>
        <span>Buka →</span>
    </a>

    <a href="pages/population.php" class="menu-card">
        <div class="menu-icon">📊</div>
        <h3>Statistik Populasi</h3>
        <span>Buka →</span>
    </a>

    <a href="pages/collection.php" class="menu-card">
        <div class="menu-icon">📚</div>
        <h3>Koleksi Ikan</h3>
        <span>Buka →</span>
    </a>

    <a href="pages/favorite.php" class="menu-card">
        <div class="menu-icon">❤️</div>
        <h3>Wilayah Favorit</h3>
        <span>Buka →</span>
    </a>

    <a href="pages/download.php" class="menu-card">
        <div class="menu-icon">⬇️</div>
        <h3>Unduh Data</h3>
        <span>Buka →</span>
    </a>

    <a href="pages/notification.php" class="menu-card">
        <div class="menu-icon">🔔</div>
        <h3>Notifikasi</h3>
        <span>Buka →</span>
    </a>

    <a href="pages/setting.php" class="menu-card">
        <div class="menu-icon">⚙️</div>
        <h3>Pengaturan</h3>
        <span>Buka →</span>
    </a>

</section>

<script src="assets/js/script.js"></script>

</body>
</html>