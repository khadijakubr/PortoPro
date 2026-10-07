<?php
// [RENDER-FIX 2 lanjutan]
// SEBELUM  : function logout() { session_start(); ... } — session_start di
//           dalam fungsi + navigasi juga start → warning + cookie tidak aman.
// SESUDAH : session sudah dijamin bootstrap.php; logout cukup
//           bersihkan session + cookie bila session aktif.
// ALASAN  : hindari double-start; pastikan cookie session ikut terhapus.
function logout() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'] ?? '', $p['secure'] ?? false, $p['httponly'] ?? true);
    }
    session_unset();
    session_destroy();
    header("Location: index.php?act=lgn");
    exit; // SEBELUM: tanpa exit → kode setelah header tetap jalan. SESUDAH: exit. ALASAN: cegah eksekusi lanjutan setelah redirect.
}
?>