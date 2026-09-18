<?php
    session_start();
    $currentPage = 'search';
?>
<?php
    require_once "../config/db.php";

    $keywordFish = $_GET['fish'] ?? '';
    $keywordRegion = $_GET['region'] ?? '';

    $sql = "
    SELECT DISTINCT
        f.fish_id,
        f.fish_name,
        f.fish_image,
        f.conservation_status,
        r.region_name
    FROM MsFishDetail f
    JOIN MsFishGeo g
    ON f.fish_id = g.fish_id
    JOIN MsRegion r
    ON g.region_id = r.region_id
    WHERE 1=1
    ";

    if($keywordFish != '')
    {
        $sql .= " AND f.fish_name LIKE '%".mysqli_real_escape_string($conn,$keywordFish)."%'";
    }

    if($keywordRegion != '')
    {
        $sql .= " AND r.region_name LIKE '%".mysqli_real_escape_string($conn,$keywordRegion)."%'";
    }

    $result = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/search.css">
    <title>FinFish - Cari Ikan</title>
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

    <form method="GET">

        <div class="search-row">

            <input
                type="text"
                name="fish"
                placeholder="Masukkan Nama Ikan..."
                value="<?= htmlspecialchars($keywordFish) ?>">

            <button type="submit">
                Cari
            </button>

        </div>

        <div class="search-row">

            <input
                type="text"
                name="region"
                placeholder="Masukkan Nama Wilayah..."
                value="<?= htmlspecialchars($keywordRegion) ?>">

            <button type="submit">
                Cari
            </button>

        </div>

    </form>

    <h3>Contoh Pencarian Populer</h3>

    <div class="popular-search">

        <a href="?fish=Kerapu">Ikan Kerapu</a>
        <a href="?fish=Tuna">Ikan Tuna</a>
        <a href="?fish=Bandeng">Ikan Bandeng</a>

    </div>

    <hr>

    <h3>Hasil Pencarian</h3>

    <div class="search-results"></div>

    <?php while($row = mysqli_fetch_assoc($result)): ?>

    <div class="fish-card">

        <img
            src="../<?= $row['fish_image'] ?>"
            alt="<?= $row['fish_name'] ?>">
            
        <div class="fish-info">

            <h4><?= $row['fish_name'] ?></h4>

            <p>
                Wilayah :
                <?= $row['region_name'] ?>
            </p>

        </div>

        <div class="fish-status">

            Status :
            <span>
                <?= $row['conservation_status'] ?>
            </span>

        </div>

    </div>

    <?php endwhile; ?>

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