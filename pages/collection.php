<?php
session_start();
$currentPage = 'collection';
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_collection') {
    $account_id = isset($_SESSION['account_id']) ? $_SESSION['account_id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1);
    
    $fish_id = mysqli_real_escape_string($conn, $_POST['fish_id']);
    $location = mysqli_real_escape_string($conn, $_POST['island_id']); // Pulau disimpan ke kolom location sesuai request
    $specific_area = !empty($_POST['specific_area']) ? "'" . mysqli_real_escape_string($conn, $_POST['specific_area']) . "'" : "NULL";
    $weight = !empty($_POST['weight']) ? "'" . mysqli_real_escape_string($conn, $_POST['weight']) . "'" : "NULL";
    $description = !empty($_POST['description']) ? "'" . mysqli_real_escape_string($conn, $_POST['description']) . "'" : "NULL";

    $insertQuery = "INSERT INTO fish_collections (account_id, fish_id, location, specific_area, weight, description) 
                    VALUES ('$account_id', '$fish_id', '$location', $specific_area, $weight, $description)";
    
    if (mysqli_query($conn, $insertQuery)) {
        header("Location: collection.php?status=success");
        exit();
    } else {
        header("Location: collection.php?status=error");
        exit();
    }
}

$fishMasterQuery = mysqli_query($conn, "SELECT DISTINCT fish_id, fish_name, fish_image FROM MsFishDetail ORDER BY fish_name ASC");
$islandMasterQuery = mysqli_query($conn, "SELECT DISTINCT island_id, island_name FROM MsIsland ORDER BY island_name ASC");

$historyQuery = mysqli_query($conn, "
    SELECT fc.*, f.fish_name, f.fish_image, i.island_name 
    FROM fish_collections fc
    JOIN MsFishDetail f ON fc.fish_id = f.fish_id
    JOIN MsIsland i ON fc.location = i.island_id
    ORDER BY fc.id DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FinFish - Collection</title>

<link rel="stylesheet" href="../assets/css/collection.css">

<style>
    body.dark .detail-desc-box {
        background-color: #2d2d2d !important;
        color: #e4e6eb !important;
        border-left-color: #3b82f6 !important;
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
                <span class="user-greeting">👋 Halo, <?= htmlspecialchars($_SESSION['username']) ?></span>
            <?php else: ?>
                <a href="../login.php" class="btn-login">Login</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_SESSION['username'])): ?>
        
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
                <h2 class="title">Koleksi Ikan yang kamu pernah temui!</h2>
                
                <div class="fish-grid" id="fishGrid">
                    <div class="card card-add-more" onclick="openAddModal()">
                        <div class="add-icon">+</div>
                        <div class="add-text">add more</div>
                    </div>

                    <?php while($row = mysqli_fetch_assoc($historyQuery)): ?>
                        <div class="card">
                            <div class="card-img-wrapper">
                                <img src="../<?= htmlspecialchars($row['fish_image']) ?>" alt="<?= htmlspecialchars($row['fish_name']) ?>">
                            </div>
                            <div class="card-footer">
                                <span class="fish-name"><?= htmlspecialchars($row['fish_name']) ?></span>
                                <button class="btn-showmore" onclick="viewDetail(<?= htmlspecialchars(json_encode($row)) ?>)">show more ▼</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div id="addModal" class="modal">
                <div class="modal-content">
                    <span class="close-modal" onclick="closeAddModal()">&times;</span>
                    <h3>Tambah Koleksi Ikan</h3>
                    
                    <form id="addFishForm" method="POST" action="collection.php">
                        <input type="hidden" name="action" value="save_collection">

                        <div class="form-group">
                            <label>Pilih Jenis Ikan Master</label>
                            <select name="fish_id" id="fishSelect" required>
                                <option value="">Pilih Jenis Ikan</option>
                                <?php while($fish = mysqli_fetch_assoc($fishMasterQuery)): ?>
                                    <option value="<?= $fish['fish_id'] ?>"><?= htmlspecialchars($fish['fish_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group" style="flex: 1;">
                                <label>Pilih Pulau</label>
                                <select name="island_id" id="islandSelect" required>
                                    <option value="">Pilih Pulau</option>
                                    <?php while($island = mysqli_fetch_assoc($islandMasterQuery)): ?>
                                        <option value="<?= $island['island_id'] ?>"><?= htmlspecialchars($island['island_name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group" style="flex: 1;">
                                <label>Daerah Spesifik</label>
                                <input type="text" name="specific_area" id="specificArea" placeholder="Contoh: Karang Dangkal">
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label>Berat (kg)</label>
                                <input type="number" step="0.01" name="weight" id="weight" placeholder="0.00">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Deskripsi Singkat Anda</label>
                            <textarea name="description" id="description" rows="3" placeholder="Tulis catatan atau deskripsi singkat saat menangkap ikan ini..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn-submit">Simpan ke Koleksi</button>
                    </form>
                </div>
            </div>

            <div id="detailModal" class="modal">
                <div class="modal-content">
                    <span class="close-modal" onclick="closeDetailModal()">&times;</span>
                    <h3 id="detailFishName">Nama Ikan</h3>
                    <img id="detailFishImg" src="" alt="Fish Image" class="detail-img">
                    <div class="detail-info">
                        <p><strong>Pulau:</strong> <span id="detailIsland"></span></p>
                        <p><strong>Daerah Spesifik:</strong> <span id="detailSpecific"></span></p>
                        <p><strong>Berat:</strong> <span id="detailWeight"></span> kg</p>
                        <p><strong>Deskripsi Anda:</strong></p>
                        <p id="detailDesc" class="detail-desc-box" style="background: #f5f5f5; padding: 10px; border-left: 3px solid #2b5876; margin-top: 5px; font-style: italic;"></p>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="restricted-container">
            <div class="restricted-box">
                <h2>Akses Terbatas 🔒</h2>
                <p>Anda harus login terlebih dahulu untuk dapat melihat, mencatat, dan mengelola koleksi ikan pribadi Anda.</p>
                <a href="../login.php" class="btn-primary-login">Login Sekarang</a>
            </div>
        </div>
    <?php endif; ?>

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

        <?php if (isset($_SESSION['username'])): ?>
        function openAddModal() { 
            document.getElementById('addModal').style.display = 'flex'; 
        }
        
        function closeAddModal() { 
            document.getElementById('addModal').style.display = 'none'; 
            document.getElementById('addFishForm').reset(); 
        }
        
        function closeDetailModal() { 
            document.getElementById('detailModal').style.display = 'none'; 
        }

        function viewDetail(data) {
            if(data) {
                document.getElementById('detailFishName').innerText = data.fish_name;
                document.getElementById('detailFishImg').src = "../" + data.fish_image;
                document.getElementById('detailIsland').innerText = data.island_name; 
                document.getElementById('detailSpecific').innerText = data.specific_area || '-';
                document.getElementById('detailWeight').innerText = data.weight || '0';
                document.getElementById('detailDesc').innerText = data.description || 'Tidak ada deskripsi.';
                
                document.getElementById('detailModal').style.display = 'flex';
            }
        }

        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = "none";
            }
        }
        <?php endif; ?>
    </script>
</body>
</html>