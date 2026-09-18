<?php
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selectedColumns = $_POST['columns'] ?? ['f.fish_name', 'i.island_name', 'r.region_name', 'f.conservation_status', 'g.population'];
    
    $headerMapping = [
        'f.fish_name'           => 'Nama Ikan',
        'i.island_name'         => 'Pulau',
        'r.region_name'         => 'Wilayah (Region)',
        'g.population'          => 'Jumlah Populasi',
        'f.phylum'              => 'Phylum',
        'f.class_name'          => 'Class Name',
        'f.order_name'          => 'Order Name',
        'f.family'              => 'Family',
        'f.genus'               => 'Genus',
        'f.habitat'             => 'Habitat',
        'f.size_info'           => 'Informasi Ukuran',
        'f.food'                => 'Makanan',
        'f.conservation_status' => 'Status Konservasi',
        'f.fish_image'          => 'Nama File Gambar'
    ];

    $selectString = implode(", ", $selectedColumns);

    $fishId = $_POST['fish_id'] ?? 'all';
    $islandId = $_POST['island_id'] ?? 'all';
    $regionId = $_POST['region_id'] ?? 'all';

    $sql = "SELECT $selectString 
            FROM msfishgeo g
            INNER JOIN MsFishDetail f ON g.fish_id = f.fish_id
            INNER JOIN msregion r ON g.region_id = r.region_id
            INNER JOIN msisland i ON g.island_id = i.island_id
            WHERE 1=1";

    if ($fishId !== 'all') {
        $sql .= " AND g.fish_id = " . intval($fishId);
    }
    if ($islandId !== 'all') {
        $sql .= " AND g.island_id = " . intval($islandId);
    }
    if ($regionId !== 'all') {
        $sql .= " AND g.region_id = " . intval($regionId);
    }

    $sql .= " ORDER BY f.fish_name ASC, i.island_name ASC, r.region_name ASC";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Gagal mengeksekusi data ke database: " . mysqli_error($conn));
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=FindFins_Data_Export_' . date('Ymd_His') . '.csv');

    $output = fopen('php://output', 'w');

    $csvHeaders = [];
    foreach ($selectedColumns as $col) {
        $csvHeaders[] = $headerMapping[$col] ?? $col;
    }
    fputcsv($output, $csvHeaders);

    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
}
?>