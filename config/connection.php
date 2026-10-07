<?php
// ============================================================================
// [RENDER-FIX 1] KONEKSI DATABASE VIA ENVIRONMENT VARIABLE
// ----------------------------------------------------------------------------
// SEBELUM  : $dbhost='localhost'; $dbuser='root'; $dbpass='root';
//           $dbname='PortoPro'; mysqli_connect(...) langsung tanpa cek error.
// SESUDAH : Baca dari getenv('DB_HOST'), DB_PORT, DB_USER, DB_PASS, DB_NAME.
//           Ada fallback localhost agar XAMPP/MAMP lokal tetap jalan tanpa
//           setting apa pun. Ada cek error + charset utf8mb4 + SSL opsional.
// ALASAN  : (1) Render & DB cloud (TiDB/PlanetScale) memberi host/user/pass
//           berbeda per environment — hardcode bikin deploy pasti gagal.
//           (2) Credential tidak boleh masuk Git. (3) DB cloud wajib SSL &
//           butuh pesan error yang jelas saat koneksi gagal (log Render).
// ============================================================================

// Muat file .env lokal bila ada (di Render env diisi via Dashboard, jadi
// blok ini otomatis dilewati). Ini implementasi mini agar tanpa composer.
if (!function_exists('portopro_load_env')) {
    function portopro_load_env($path) {
        if (!is_readable($path)) return;
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            $pos = strpos($line, '=');
            if ($pos === false) continue;
            $key = trim(substr($line, 0, $pos));
            $val = trim(substr($line, $pos + 1), " \t\"'");
            if ($key !== '' && getenv($key) === false) {
                putenv("$key=$val");
                $_ENV[$key] = $val;
            }
        }
    }
    // [RESTRUKTUR] SEBELUM: __DIR__ . '/.env' (saat file di root).
    // SESUDAH: dirname(__DIR__) . '/.env'. ALASAN: file pindah ke config/,
    // .env tetap di root project.
    portopro_load_env(dirname(__DIR__) . '/.env');
}

// Helper kecil: ambil env dengan nilai default.
if (!function_exists('portopro_env')) {
    function portopro_env($key, $default = '') {
        $v = getenv($key);
        return ($v === false || $v === '') ? $default : $v;
    }
}

$dbhost = portopro_env('DB_HOST', 'localhost');
$dbport = (int) portopro_env('DB_PORT', '3306');
$dbuser = portopro_env('DB_USER', 'root');
$dbpass = portopro_env('DB_PASS', 'root');
$dbname = portopro_env('DB_NAME', 'PortoPro');
// DB_SSL=true wajib untuk TiDB Cloud / PlanetScale. Matikan (false) hanya
// untuk MySQL lokal yang tidak support SSL.
$db_ssl  = filter_var(portopro_env('DB_SSL', $dbhost === 'localhost' ? 'false' : 'true'), FILTER_VALIDATE_BOOLEAN);

mysqli_report(MYSQLI_REPORT_OFF); // kita handle error manual agar pesan ramah
$connect = mysqli_init();
if (!$connect) {
    error_log('[PortoPro] mysqli_init() gagal');
    http_response_code(500);
    die('Database tidak tersedia. Coba lagi nanti.');
}

try {
    if ($db_ssl) {
        // SEBELUM: tanpa SSL. SESUDAH: SSL aktif. ALASAN: DB cloud menolak
        // koneksi non-SSL; flag DONT_VERIFY cocok untuk serverless free tier
        // tanpa file CA (kecuali DB_SSL_CA diisi path sertifikat).
        $ca = portopro_env('DB_SSL_CA', '');
        if ($ca !== '' && is_readable($ca)) {
            mysqli_ssl_set($connect, null, null, $ca, null, null);
            $flags = MYSQLI_CLIENT_SSL;
        } else {
            $flags = defined('MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT')
                ? MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT
                : MYSQLI_CLIENT_SSL;
        }
        $ok = @mysqli_real_connect($connect, $dbhost, $dbuser, $dbpass, $dbname, $dbport, null, $flags);
    } else {
        $ok = @mysqli_real_connect($connect, $dbhost, $dbuser, $dbpass, $dbname, $dbport);
    }
    if (!$ok) {
        throw new Exception(mysqli_connect_error() ?: 'koneksi gagal');
    }
    // SEBELUM: charset default (latin1 di banyak server). SESUDAH: utf8mb4.
    // ALASAN: data kamu ada emoji & karakter khusus (matchagato, it’s, ...).
    $connect->set_charset('utf8mb4');
} catch (Throwable $e) {
    // Jangan bocorkan detail kredensial ke pengunjung; tulis ke error log
    // (terlihat di Logs Render), tampilkan pesan umum saja.
    error_log('[PortoPro] DB connect gagal ke ' . $dbhost . ':' . $dbport . ' — ' . $e->getMessage());
    http_response_code(500);
    die('Database tidak tersedia. Periksa Environment Variables (DB_HOST/DB_USER/DB_PASS/DB_NAME) di Render.');
}
?>