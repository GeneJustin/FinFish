<?php
session_start();
$currentPage = 'filter';

require_once "../config/db.php";

/* INPUT FILTER */
$fish = $_GET['fish'] ?? '';
$region = $_GET['region'] ?? '';
$status = $_GET['status'] ?? '';
$minPop = $_GET['min_pop'] ?? '';
$maxPop = $_GET['max_pop'] ?? '';

/* QUERY UTAMA */
$sql = "
SELECT DISTINCT
    f.fish_id,
    f.fish_name,
    f.fish_image,
    f.conservation_status,
    r.region_name,
    g.population
FROM MsFishDetail f
JOIN MsFishGeo g ON f.fish_id = g.fish_id
JOIN MsRegion r ON g.region_id = r.region_id
WHERE 1=1
";

if ($fish != '') {
    $sql .= " AND f.fish_id = '".mysqli_real_escape_string($conn,$fish)."'";
}

if ($region != '') {
    $sql .= " AND r.region_id = '".mysqli_real_escape_string($conn,$region)."'";
}

if ($status != '') {
    $sql .= " AND f.conservation_status = '".mysqli_real_escape_string($conn,$status)."'";
}

if ($minPop != '') {
    $sql .= " AND g.population >= ".intval($minPop);
}

if ($maxPop != '') {
    $sql .= " AND g.population <= ".intval($maxPop);
}

$result = mysqli_query($conn,$sql);

$fishList = mysqli_query($conn, "SELECT fish_id, fish_name FROM MsFishDetail");
$regionList = mysqli_query($conn, "SELECT region_id, region_name FROM MsRegion");
$statusList = mysqli_query($conn, "SELECT DISTINCT conservation_status FROM MsFishDetail");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/filter.css">
    <title>FinFish - Filter Data</title>
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

            <form method="GET" class="filter-form">
                <div class="search-row">
                    <label>Jenis Ikan</label>
                    <select name="fish">
                        <option value="">Jenis Ikan</option>
                        <?php mysqli_data_seek($fishList, 0); ?>
                        <?php while($f = mysqli_fetch_assoc($fishList)): ?>
                            <option value="<?= $f['fish_id'] ?>" <?= $fish == $f['fish_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['fish_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="search-row">
                    <label>Wilayah</label>
                    <select name="region">
                        <option value="">Pilih Jenis Ikan</option>
                        <?php mysqli_data_seek($regionList, 0); ?>
                        <?php while($r = mysqli_fetch_assoc($regionList)): ?>
                            <option value="<?= $r['region_id'] ?>" <?= $region == $r['region_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($r['region_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="search-row">
                    <label>Status Konservasi</label>
                    <select name="status">
                        <option value="">Pilih Status</option>
                        <?php mysqli_data_seek($statusList, 0); ?>
                        <?php while($s = mysqli_fetch_assoc($statusList)): ?>
                            <option value="<?= htmlspecialchars($s['conservation_status']) ?>" <?= $status == $s['conservation_status'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['conservation_status']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="search-row">
                    <label>Rentang Populasi</label>
                    <div class="range-inputs">
                        <input type="number" name="min_pop" placeholder="MIN" value="<?= htmlspecialchars($minPop) ?>">
                        <input type="number" name="max_pop" placeholder="MAX" value="<?= htmlspecialchars($maxPop) ?>">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-apply">Terapkan Filter</button>
                    <button type="button" class="btn-reset" onclick="window.location='filter.php'">Reset Filter</button>
                </div>
            </form>

            <div class="results">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <div class="fish-card">
                            <div class="card-image-wrapper">
                                <img src="../<?= htmlspecialchars($row['fish_image']) ?>" alt="<?= htmlspecialchars($row['fish_name']) ?>">
                            </div>
                            <div class="fish-info">
                                <h4><?= htmlspecialchars($row['fish_name']) ?></h4>
                                <p><?= htmlspecialchars($row['region_name']) ?></p>
                                <?php
                                    $status = $row['conservation_status'];
                                    if ($status == 'Aman') {
                                        $statusClass = 'safe';
                                    } elseif ($status == 'Rentan') {
                                        $statusClass = 'warning';
                                    } else {
                                        $statusClass = 'danger';
                                    }
                                ?>
                                <div class="fish-status">
                                    Status : <span class="<?= $statusClass ?>"><?= htmlspecialchars($row['conservation_status']) ?></span>
                                </div>
                                <p>Estimasi Populasi : <?= number_format($row['population']) ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="grid-column: span 2;">Data tidak ditemukan.</p>
                <?php endif; ?>
            </div>

        </div> </div>
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