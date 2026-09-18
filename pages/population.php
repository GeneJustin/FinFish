<?php
session_start();
$currentPage = 'population';
require_once "../config/db.php";

$fish = $_GET['fish'] ?? '';
$island = $_GET['island'] ?? '';

$fishList = mysqli_query($conn, "
    SELECT DISTINCT f.fish_id, d.fish_name
    FROM Fishpopulationhistory f
    JOIN Msfishdetail d ON d.fish_id = f.fish_id
    ORDER BY fish_name ASC");

$islandList = null;
if ($fish != '') {
    $islandList = mysqli_query($conn, "
        SELECT DISTINCT i.island_id, i.island_name
        FROM Fishpopulationhistory h
        JOIN MsIsland i ON i.island_id = h.island_id
        WHERE h.fish_id = '".mysqli_real_escape_string($conn,$fish)."'
        ORDER BY i.island_name ASC
    ");
}
$sql = "
SELECT h.year_record, h.population
FROM Fishpopulationhistory h
WHERE 1=1
";

if ($fish != '') {
    $sql .= " AND h.fish_id = '".mysqli_real_escape_string($conn,$fish)."'";
}

if ($island != '') {
    $sql .= " AND h.island_id = '".mysqli_real_escape_string($conn,$island)."'";
}

$sql .= " ORDER BY h.year_record ASC";

$result = mysqli_query($conn,$sql);

$years = [];
$pops = [];

while ($row = mysqli_fetch_assoc($result)) {
    $years[] = $row['year_record'];
    $pops[] = $row['population'];
}

/* growth rate per 10-year step */
$growth = [];
$growthYears = [];

for ($i = 1; $i < count($pops); $i++) {
    $prev = $pops[$i-1];
    $curr = $pops[$i];

    $growth[] = ($prev == 0) ? 0 : (($curr - $prev) / $prev) * 100;
    $growthYears[] = $years[$i];
}

$info = null;
if ($fish && $island) {
    $info = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT f.fish_name, f.fish_image, f.conservation_status, i.island_name
        FROM MsFishDetail f
        JOIN Fishpopulationhistory h ON f.fish_id = h.fish_id
        JOIN MsIsland i ON h.island_id = i.island_id
        WHERE f.fish_id = '$fish'
        AND i.island_id = '$island'
        LIMIT 1
    "));
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FindFins - Data Analytic</title>

<link rel="stylesheet" href="../assets/css/pop.css">
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
        <main class="main-content">
        <form method="GET">
            <select name="fish" onchange="this.form.submit()">
                <option value="">Pilih Ikan</option>

                <?php while($f = mysqli_fetch_assoc($fishList)): ?>
                    <option value="<?= $f['fish_id'] ?>"
                        <?= ($fish == $f['fish_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($f['fish_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <select name="island" <?= ($fish == '') ? 'disabled' : '' ?>>
                <option value="">Pilih Wilayah</option>

                <?php if ($islandList): ?>
                    <?php while($i = mysqli_fetch_assoc($islandList)): ?>
                        <option value="<?= $i['island_id'] ?>"
                            <?= ($island == $i['island_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($i['island_name']) ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>

                <button type="submit">Apply</button>
            </form>

            <?php if ($info): ?>
                <div class="info-card">
                    <img src="../<?= $info['fish_image'] ?>" width="120">
                    <div>
                        <h2><?= $info['fish_name'] ?></h2>
                        <p>Wilayah: <?= $info['island_name'] ?></p>
                        <p>Status: <?= $info['conservation_status'] ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="charts-container">
                <canvas id="popChart"></canvas>
                <canvas id="growthChart"></canvas>
            </div>
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    const years = <?= json_encode($years) ?>;
    const pop = <?= json_encode($pops) ?>;

    const growthYears = <?= json_encode($growthYears) ?>;
    const growth = <?= json_encode($growth) ?>;

    new Chart(document.getElementById('popChart'), {
        type: 'bar',
        data: {
            labels: years,
            datasets: [{
                label: 'Population',
                data: pop,
                backgroundColor: '#1e88e5'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true
                }
            }
        }
    });

    new Chart(document.getElementById('growthChart'), {
        type: 'line',
        data: {
            labels: growthYears,
            datasets: [{
                label: 'Growth Rate (%)',
                data: growth,
                borderColor: '#ff9800',
                backgroundColor: 'rgba(255,152,0,0.2)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });
    </script>

</body>
</html>