<?php
session_start();
$currentPage = 'detail';
require_once "../config/db.php";

$listFishQuery = mysqli_query($conn, "SELECT fish_id, fish_name FROM msfishdetail ORDER BY fish_name ASC");

$fish_id = $_GET['fish_id'] ?? ''; 
if (empty($fish_id)) {
    $fish_id = '1'; 
}

$fishQuery = mysqli_query($conn, "
    SELECT 
        fish_name, phylum, class_name, order_name, family, 
        genus, habitat, size_info, food, conservation_status, fish_image 
    FROM msfishdetail 
    WHERE fish_id = '".mysqli_real_escape_string($conn, $fish_id)."'
    LIMIT 1
");
$fishData = mysqli_fetch_assoc($fishQuery);
$geoQuery = mysqli_query($conn, "
    SELECT r.region_name, g.population
    FROM msfishgeo g
    JOIN msregion r ON g.region_id = r.region_id
    WHERE g.fish_id = '".mysqli_real_escape_string($conn, $fish_id)."'
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinFish - Fish Details</title>
    <link rel="stylesheet" href="../assets/css/detail.css">
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
        
        <main class="content">
            <div class="filter-wrapper">
                <form method="GET" action="">
                    <div class="form-group">
                        <label for="fish_id">Pilih Jenis Ikan :</label>
                        <select name="fish_id" id="fish_id">
                            <option value="">-- Pilih Ikan --</option>
                            <?php while($row = mysqli_fetch_assoc($listFishQuery)): ?>
                                <option value="<?= $row['fish_id'] ?>" <?= $fish_id == $row['fish_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['fish_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-submit">Cari Detail</button>
                </form>
            </div>

            <?php if ($fishData): ?>
                <div class="detail-grid">
                    
                    <div class="card image-card">
                        <img src="../<?= htmlspecialchars($fishData['fish_image']) ?>" alt="<?= htmlspecialchars($fishData['fish_name']) ?>">
                    </div>

                    <div class="card classification-card">
                        <h3>Klasifikasi Ilmiah</h3>
                        <ul>
                            <li><strong>Filum:</strong> <?= htmlspecialchars($fishData['phylum'] ?? '-') ?></li>
                            <li><strong>Kelas:</strong> <?= htmlspecialchars($fishData['class_name'] ?? '-') ?></li>
                            <li><strong>Ordo:</strong> <?= htmlspecialchars($fishData['order_name'] ?? '-') ?></li>
                            <li><strong>Famili:</strong> <?= htmlspecialchars($fishData['family'] ?? '-') ?></li>
                            <li><strong>Genus:</strong> <?= htmlspecialchars($fishData['genus'] ?? '-') ?></li>
                        </ul>
                    </div>

                    <div class="card info-card">
                        <h3>Informasi Detail :</h3>
                        <ul>
                            <li><strong>Habitat :</strong> <?= htmlspecialchars($fishData['habitat'] ?? '-') ?></li>
                            <li><strong>Ukuran :</strong> <?= htmlspecialchars($fishData['size_info'] ?? '-') ?></li>
                            <li><strong>Makanan :</strong> <?= htmlspecialchars($fishData['food'] ?? '-') ?></li>
                            <li><strong>Status Konservasi :</strong> <?= htmlspecialchars($fishData['conservation_status'] ?? '-') ?></li>
                        </ul>
                    </div>

                    <div class="card population-card">
                        <h3>Populasi Per-Wilayah</h3>
                        <ul>
                            <?php if (mysqli_num_rows($geoQuery) > 0): ?>
                                <?php while($geo = mysqli_fetch_assoc($geoQuery)): ?>
                                    <li><strong><?= htmlspecialchars($geo['region_name']) ?>:</strong> <?= number_format($geo['population']) ?> ekor</li>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <li>Data populasi wilayah belum tersedia.</li>
                            <?php endif; ?>
                        </ul>
                    </div>

                </div>
            <?php else: ?>
                <div class="card">
                    <p style="color: #222;">Data ikan dengan ID (<?= htmlspecialchars($fish_id) ?>) tidak ditemukan dalam database.</p>
                </div>
            <?php endif; ?>
        </main>
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