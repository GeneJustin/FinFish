<?php
session_start();
$currentPage = 'favorite';
require_once "../config/db.php";

$islandId = $_GET['island_id'] ?? '';

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

<style>
    .map-wrapper {
        text-align: center;
        background: #ffffff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }

    .indonesia-map {
        max-width: 100%;
        height: auto;
        border-radius: 4px;
    }

    .chart-container {
        background: #ffffff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        width: 100%;
    }

    .chart-title {
        font-size: 1.2rem;
        color: #333333;
        margin-bottom: 20px;
        font-weight: 700;
    }

    .chart-axis-wrapper {
        position: relative;
        padding-left: 120px; 
        border-left: 1px solid #ccc;
        margin-left: 10px;
    }

    .chart-row {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        position: relative;
    }

    .chart-label {
        position: absolute;
        left: -130px;
        width: 110px;
        text-align: right;
        font-size: 14px;
        color: #333333;
        font-weight: 500;
    }

    .chart-bar-wrapper {
        flex: 1;
        background: transparent;
        position: relative;
        display: flex;
        align-items: center;
    }

    .chart-bar {
        height: 32px;
        background-color: #fdd47e;  
        border-radius: 2px;
        transition: width 0.5s ease-in-out;
        width: 0%; 
    }

    .chart-value {
        margin-left: 10px;
        font-size: 13px;
        color: #666666;
        font-weight: bold;
    }

    .chart-gridlines {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        justify-content: space-between;
        pointer-events: none;
        z-index: 0;
    }

    .gridline {
        border-left: 1px solid rgba(0, 0, 0, 0.05);
        height: 100%;
    }

    .chart-row, .chart-title {
        position: relative;
        z-index: 1;
    }

    body.dark .map-wrapper,
    body.dark .chart-container {
        background: #2a2a2a;
        box-shadow: 0 5px 12px rgba(0,0,0,0.3);
    }

    body.dark .chart-title,
    body.dark .chart-label {
        color: #ffffff;
    }

    body.dark .chart-value {
        color: #cccccc;
    }

    body.dark .chart-axis-wrapper {
        border-left-color: #444;
    }

    body.dark .gridline {
        border-left-color: rgba(255, 255, 255, 0.05);
    }
</style>
</head>

<body>

    <div class="topbar">
        <div class="logo">
            <a href="../index.php">🐟 FinFish</a>
        </div>
        <div class="topbar-right">
            <button id="darkModeBtn">🌙 Dark Mode</button>
            <?php if (isset($_SESSION['username'])): ?>
                <span class="user-greeting">Halo, <?= htmlspecialchars($_SESSION['username']) ?></span>
            <?php else: ?>
                <a href="../login.php" class="btn-login">Login</a>
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
                <img src="../assets/img/petaindo.png" alt="Peta Indonesia" class="indonesia-map" onerror="this.src='https://via.placeholder.com/800x400?text=petaindo.png+Belum+Ditemukan'">
            </div>

            <div class="chart-container">
                <h3 class="chart-title">Wilayah Favorit / Populer untuk memancing</h3>
                
                <div class="chart-axis-wrapper" id="barChartWrapper">
                    <div class="chart-gridlines">
                        <div class="gridline"></div>
                        <div class="gridline"></div>
                        <div class="gridline"></div>
                        <div class="gridline"></div>
                        <div class="gridline"></div>
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

        document.addEventListener("DOMContentLoaded", () => {
            const daerahList = ["Jawa Timur", "Jawa Barat", "Jawa Tengah", "DKI Jakarta", "Banten", "Sumatera Utara", "DI Yogyakarta"];
            
            let chartData = daerahList.map(daerah => {
                return {
                    nama: daerah,
                    populasi: Math.floor(Math.random() * (5000 - 500 + 1)) + 500
                };
            });

            chartData.sort((a, b) => b.populasi - a.populasi);

            const maxPopulasi = Math.max(...chartData.map(d => d.populasi));
            
            const wrapper = document.getElementById('barChartWrapper');

            chartData.forEach(item => {
                const barWidth = maxPopulasi > 0 ? (item.populasi / maxPopulasi) * 85 : 0;

                const rowHtml = `
                    <div class="chart-row">
                        <div class="chart-label">${item.nama}</div>
                        <div class="chart-bar-wrapper">
                            <div class="chart-bar" style="width: ${barWidth}%;"></div>
                            <span class="chart-value">${item.populasi.toLocaleString('id-ID')}</span>
                        </div>
                    </div>
                `;
                wrapper.insertAdjacentHTML('beforeend', rowHtml);
            });
        });
    </script>

</body>
</html>