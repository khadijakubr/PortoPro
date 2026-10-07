<?php
// ============================================================================
// [RENDER-FIX 2] BOOTSTRAP SENTRAL — file BARU
// ----------------------------------------------------------------------------
// SEBELUM  : session_start() tersebar di navigation.php:2 dan
//           admin_logout.php:3 (dipanggil dua kali → warning "session already
//           started" / "headers already sent"), tanpa pengaturan cookie aman.
// SESUDAH : Semua halaman cukup `require_once bootstrap.php` (atau via
//           navigation.php). Session diatur sekali: cookie httponly, SameSite,
//           secure otomatis saat HTTPS (Render selalu HTTPS), + guard
//           session_status() agar tidak double-start.
// ALASAN  : (1) Di balik proxy Render, cookie session default bisa ditolak
//           browser. (2) Satu pintu session = tidak ada lagi warning header.
//           (3) APP_DEBUG mengontrol display_errors: mati di production.
// PENGGUNAAN: ganti `include connection.php` + `include navigation.php` yang
//           butuh session menjadi `require_once bootstrap.php` dulu.
// ============================================================================

if (!defined('PORTOPRO_BOOTSTRAPPED')) {
    define('PORTOPRO_BOOTSTRAPPED', true);

    // Pastikan connection (env loader + $connect) selalu ada.
    require_once __DIR__ . '/connection.php';

    // SEBELUM: error tampil apa adanya (bocor ke pengunjung).
    // SESUDAH: APP_DEBUG=true hanya di lokal. ALASAN: di Render log dibaca
    // via Dashboard Logs, pengunjung cukup lihat pesan umum.
    $is_debug = in_array(strtolower((string) (getenv('APP_DEBUG') ?: 'false')), ['1', 'true', 'yes'], true);
    if ($is_debug) {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
    } else {
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
        ini_set('display_errors', '0');
    }

    // Deteksi HTTPS di balik proxy Render (x-forwarded-proto).
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => $is_https, // true di Render, false di localhost HTTP
            'httponly' => true,      // cegah baca via JavaScript (XSS)
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    // Helper escape HTML — dipakai menggantikan echo mentah (anti-XSS).
    if (!function_exists('e')) {
        function e($str) {
            return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
        }
    }

    // [RENDER-FIX 6 catatan] e_text(): untuk TEKS lama yang tersimpan SUDAH
    // ter-encode (mis. "it&#039;s", "&amp;") karena kode lama memakai
    // htmlspecialchars() saat SIMPAN.
    // SEBELUM (kode lama): echo $description mentah → browser decode sekali
    //   sehingga tampil benar, tapi rawan XSS.
    // e() saja: "&#039;" → "&amp;#039;" → tampil literal "&#039;" (regresi).
    // e_text(): decode dulu lalu escape → tampil benar + tetap anti-XSS.
    // Data BARU (disimpan mentah) juga aman lewat fungsi ini.
    if (!function_exists('e_text')) {
        function e_text($str) {
            $decoded = html_entity_decode((string) $str, ENT_QUOTES, 'UTF-8');
            return htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8');
        }
    }
}
?>
