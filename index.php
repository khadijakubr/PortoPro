<?php
// [FIX-HEADERS 2026-10-07]
// SEBELUM  : koneksi DB dimuat di dalam <body> (via navigation.php) — saat
//           koneksi gagal, http_response_code(500) + session cookie tidak bisa
//           dikirim karena HTML sudah terlanjur keluar ("headers already sent"
//           di log + halaman putih).
// SESUDAH : bootstrap (env + DB + session) dimuat SEBELUM output apa pun.
//           Gagal koneksi = pesan rapi + status 500 benar, bukan layar putih.
require_once __DIR__ . '/config/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>PORTFOLIO</title>
</head>
<body>
    <div class="navbar">
        <?php
        include_once __DIR__ . '/includes/navigation.php';
        ?>
    </div>
    <div class="main">
        <?php
        if (!isset($_GET['act'])) {
            include_once __DIR__ . '/pages/home.php';
        } elseif ($_GET['act'] == 'ct') {
            include_once __DIR__ . '/pages/contact.php';
        } elseif ($_GET['act'] == 'pj') {
            include_once __DIR__ . '/pages/project.php';
        } elseif ($_GET['act'] == 'lgn') {
            include_once __DIR__ . '/admin/admin_login.php';
        } else {
            echo "<h1>404 Not Found</h1>";
            echo "<p>The page you are looking for does not exist.</p>";
        }
        ?>
    </div>
    <div class="footer">
        <?php
        include_once __DIR__ . '/includes/footer.php';
        ?>
    </div>
</body>
</html>