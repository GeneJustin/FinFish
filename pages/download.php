<?php
session_start();
$currentPage = 'download';
require_once "../config/db.php";

$fishMasterQuery = mysqli_query($conn, "SELECT DISTINCT fish_id, fish_name FROM MsFishDetail ORDER BY fish_name ASC");

$islandMasterQuery = mysqli_query($conn, "SELECT island_id, island_name FROM msisland ORDER BY island_name ASC");

$regionMasterQuery = mysqli_query($conn, "SELECT region_id, island_id, region_name FROM msregion ORDER BY region_name ASC");
$allRegions = [];
while ($reg = mysqli_fetch_assoc($regionMasterQuery)) {
    $allRegions[] = $reg;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinFish - Filter Data</title>
    <link rel="stylesheet" href="../assets/css/peta.css"> <style>
        .filter-container {
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            max-width: 950px;
            width: 100%;
            margin-top: 20px;
        }

        .filter-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }

        .filter-group select {
            padding: 10px;
            border: 1px solid #b8c3d6;
            border-radius: 4px;
            background-color: #f7f9fc;
            font-size: 14px;
            color: #222;
        }

        .filter-divider {
            border: 0;
            height: 1px;
            background: #e0e0e0;
            margin: 25px 0;
        }

        .column-selection h4 {
            margin-bottom: 15px;
            color: #333;
            font-size: 16px;
        }

        .checkbox-grid {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 14px;
            color: #444;
            background: #f0f4fd;
            padding: 8px 16px;
            border-radius: 4px;
            border: 1px solid #d0daf0;
            transition: background 0.2s, transform 0.1s;
        }

        .checkbox-label:hover {
            background: #e2ebfc;
            transform: translateY(-1px);
        }

        .checkbox-label input {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .action-row {
            margin-top: 30px;
            text-align: right;
        }

        .btn-export {
            background-color: #0c5132;
            color: white;
            border: none;
            padding: 12px 28px;
            font-size: 15px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(12, 81, 50, 0.2);
            transition: background 0.2s;
        }

        .btn-export:hover {
            background-color: #083622;
        }

        /* TEMA DARK MODE */
        body.dark .filter-container {
            background: #2a2a2a;
            box-shadow: 0 5px 12px rgba(0,0,0,0.3);
        }

        body.dark .filter-group label,
        body.dark .column-selection h4 {
            color: #ffffff;
        }

        body.dark .filter-group select {
            background-color: #1c1c1c;
            border-color: #444;
            color: #ddd;
        }

        body.dark .filter-divider {
            background: #444;
        }

        body.dark .checkbox-label {
            background: #1c1c1c;
            border-color: #444;
            color: #ddd;
        }

        body.dark .checkbox-label:hover {
            background: #333;
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
            <h2 class="title" style="color: #0c3b6e; font-size: 28px; font-weight: 800;">Filter & Ekspor Data FinFish</h2>

            <div class="filter-container">
                <form id="filterForm" action="export_csv.php" method="POST">
                    
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="fishSelect">Jenis Ikan</label>
                            <select name="fish_id" id="fishSelect">
                                <option value="all">Semua Jenis Ikan (All)</option>
                                <?php while($fish = mysqli_fetch_assoc($fishMasterQuery)): ?>
                                    <option value="<?= $fish['fish_id'] ?>"><?= htmlspecialchars($fish['fish_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="islandSelect">Pulau</label>
                            <select name="island_id" id="islandSelect" onchange="filterRegionsByIsland()">
                                <option value="all">Semua Pulau (All)</option>
                                <?php while($island = mysqli_fetch_assoc($islandMasterQuery)): ?>
                                    <option value="<?= $island['island_id'] ?>"><?= htmlspecialchars($island['island_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="regionSelect">Wilayah (Region)</label>
                            <select name="region_id" id="regionSelect">
                                <option value="all">Semua Wilayah (All)</option>
                                </select>
                        </div>
                    </div>

                    <hr class="filter-divider">

                <div class="column-selection">
                        <h4 style="margin-bottom: 5px; color: #333;">Pilih Kolom Data yang Ingin Diambil:</h4>
                        
                        <h5 style="margin: 15px 0 5px 0; color: #0c3b6e; font-weight: bold;">📍 Informasi Geografis & Populasi</h5>
                        <div class="checkbox-grid" style="margin-bottom: 15px;">
                            <label class="checkbox-label"><input type="checkbox" name="columns[]" value="f.fish_name" checked> Nama Ikan</label>
                            <label class="checkbox-label"><input type="checkbox" name="columns[]" value="i.island_name" checked> Pulau</label>
                            <label class="checkbox-label"><input type="checkbox" name="columns[]" value="r.region_name" checked> Wilayah (Region)</label>
                            <label class="checkbox-label"><input type="checkbox" name="columns[]" value="g.population" checked> Jumlah Populasi</label>
                        </div>

                        <h5 style="margin: 15px 0 5px 0; color: #0c3b6e; font-weight: bold;">🧬 Klasifikasi Taksonomi (MsFishDetail)</h5>
                        <div class="checkbox-grid" style="margin-bottom: 15px;">
                            <label class="checkbox-label"><input type="checkbox" name="columns[]" value="f.phylum"> Phylum</label>
                            <label class="checkbox-label"><input type="checkbox" name="columns[]" value="f.class_name"> Class Name</label>
                            <label class="checkbox-label"><input type="checkbox" name="columns[]" value="f.order_name"> Order Name</label>
                            <label class="checkbox-label"><input type="checkbox" name="columns[]" value="f.family" checked> Family Ikan</label>
                            <label class="checkbox-label"><input type="checkbox" name="columns[]" value="f.genus"> Genus</label>
                        </div>

                        <h5 style="margin: 15px 0 5px 0; color: #0c3b6e; font-weight: bold;">🌊 Karakteristik & Status Biologi (MsFishDetail)</h5>
                        <div class="checkbox-grid">
                            <label class="checkbox-label"><input type="checkbox" name="columns[]" value="f.habitat" checked> Habitat</label>
                            <label class="checkbox-label"><input type="checkbox" name="columns[]" value="f.size_info"> Informasi Ukuran (Size Info)</label>
                            <label class="checkbox-label"><input type="checkbox" name="columns[]" value="f.food"> Makanan (Food)</label>
                            <label class="checkbox-label"><input type="checkbox" name="columns[]" value="f.conservation_status" checked> Status Konservasi</label>
                        </div>
                    </div>

                    <div class="action-row">
                        <button type="submit" class="btn-export">Export to CSV</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const darkModeBtn = document.getElementById('darkModeBtn');
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark');
            darkModeBtn.textContent = '☀️ Light Mode';
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

        const masterRegions = <?= json_encode($allRegions); ?>;

        function filterRegionsByIsland() {
            const selectedIslandId = document.getElementById('islandSelect').value;
            const regionSelect = document.getElementById('regionSelect');
            
            regionSelect.innerHTML = '<option value="all">Semua Wilayah (All)</option>';
            
            const filtered = masterRegions.filter(r => {
                if (selectedIslandId === 'all') return false; 
                return r.island_id == selectedIslandId;
            });
            
            if (selectedIslandId !== 'all') {
                filtered.forEach(reg => {
                    const opt = document.createElement('option');
                    opt.value = reg.region_id;
                    opt.textContent = reg.region_name;
                    regionSelect.appendChild(opt);
                });
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
            filterRegionsByIsland();
        });
    </script>
</body>
</html>