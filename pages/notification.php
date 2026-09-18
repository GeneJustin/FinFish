<?php
session_start();
$currentPage = 'notification';
require_once "../config/db.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FinFish - Notification</title>

<link rel="stylesheet" href="../assets/css/notif.css">
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
            <div id="notification-container"></div>
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
        const notificationData = [
            { title: "New Fish Found!", desc: "Ikan sapu-sapu ditemukan di area kamu nih!" },
            { title: "The Tuna Journey in Winter", desc: "Hundreds of tuna spotted swimming upstream!" },
            { title: "Are you fishing in endangered waters?", desc: "Periksa kembali peta zonasi laut lokal sebelum melempar pancingan." },
            { title: "Is the Clownfish Population Reduced Due to Overfishing?", desc: "Area kamu baru terdaftar dalam area konservasi! Geserrr" },
            { title: "Peringatan Gelombang Tinggi", desc: "Dihimbau bagi nelayan kecil untuk tidak melaut di perairan utara malam ini." },
            { title: "Hutan Bakau Baru Diresmikan", desc: "Sistem pelindung pantai alami seluas 2 hektar baru saja selesai ditanam." },
            { title: "Whale Sightings Near the Coast", desc: "A family of blue whales was spotted just 2 miles off the local bay." },
            { title: "Edukasi Alat Tangkap Ikan", desc: "Gunakan jaring ramah lingkungan demi masa depan terumbu karang kita." },
            { title: "Coral Reef Restoration Project", desc: "Komunitas selam lokal berhasil menanam 200 bibit fragmen karang baru." },
            { title: "Update Batas Kuota Tangkapan", desc: "Kementerian Kelautan merilis regulasi batas berat berkala minggu ini." },
            { title: "Rare Seahorse Discovered", desc: "Kuda laut kerdil langka kembali terlihat berkembang biak di area estuari." },
            { title: "Waspada Fenomena Pasang Maksimum", desc: "Air laut diperkirakan naik ke permukaan dermaga mulai pukul 21:00 WIB." },
            { title: "Pemeliharaan Jalur Labuh Kapal", desc: "Sektor timur kolam bandar ditutup sementara karena adanya pengerukan pasir." },
            { title: "Penertiban Wilayah Konservasi", desc: "Petugas patroli mengamankan area terlarang dari aktivitas jangkar ilegal." },
            { title: "Festival Edukasi Bahari Akhir Pekan", desc: "Bawa anak-anak untuk mengenal biota laut nusantara gratis di balai kota." }
        ];

        const container = document.getElementById('notification-container');
        const MAX_NOTIFICATIONS = 4;
        const INTERVAL_TIME = 60000;
        function getRandomNotification() {
            const randomIndex = Math.floor(Math.random() * notificationData.length);
            return notificationData[randomIndex];
        }

        function createNotificationCard(data) {
            const card = document.createElement('div');
            card.className = 'notification-card';
            
            const badge = document.createElement('div');
            badge.className = 'unread-badge';
            
            const title = document.createElement('div');
            title.className = 'notification-title';
            title.innerText = data.title;
            
            const desc = document.createElement('div');
            desc.className = 'notification-desc';
            desc.innerText = data.desc;

            card.appendChild(badge);
            card.appendChild(title);
            card.appendChild(desc);

            card.addEventListener('click', function() {
                card.classList.add('read');
            });

            return card;
        }

        function triggerNewNotification() {
            const currentCards = container.getElementsByClassName('notification-card');
            
            if (currentCards.length >= MAX_NOTIFICATIONS) {
                const lastCard = currentCards[currentCards.length - 1];
                
                lastCard.classList.add('fade-out');
                
                setTimeout(() => {
                    lastCard.remove();
                    insertNewCard(); 
                }, 400);
            } else {
                insertNewCard();
            }
        }

        function insertNewCard() {
            const data = getRandomNotification();
            const newCard = createNotificationCard(data);
            
            container.insertBefore(newCard, container.firstChild);
            
            setTimeout(() => {
                newCard.classList.add('show');
            }, 10);
        }

        function initNotifications() {
            for (let i = 0; i < MAX_NOTIFICATIONS; i++) {
                setTimeout(() => {
                    insertNewCard();
                }, i * 150);
            }

            setInterval(triggerNewNotification, INTERVAL_TIME);
        }

        initNotifications();

    </script>

</body>
</html>

