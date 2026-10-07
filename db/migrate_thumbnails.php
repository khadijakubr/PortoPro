<?php
// ============================================================================
// [MIGRASI-CLOUDINARY] db/migrate_thumbnails.php — skrip SEKALI PAKAI
// ----------------------------------------------------------------------------
// TUJUAN : 13 project lama thumbnail-nya masih nama file lokal
//          ("IMG_7081.jpg") yang tidak ikut ke image Docker (akibat
//          .dockerignore mengecualikan media/image/uploads/*) → 404.
//          Skrip ini mengupload tiap file lokal ke Cloudinary lalu UPDATE
//          thumbnail = URL CDN + thumbnail_public_id.
// CARA PAKAI (dari root project):
//   1. cp .env.example .env  → isi DB_* (TiDB) + CLOUDINARY_*
//   2. php db/migrate_thumbnails.php            → DRY-RUN (hanya lapor)
//   3. php db/migrate_thumbnails.php --live     → EKSEKUSI (tulis DB)
// KEAMANAN: baris di bawah mematikan akses via browser (403). Skrip ini
//          hanya jalan dari terminal (php_sapi_name() === 'cli').
// ============================================================================
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Forbidden: hanya via terminal.');
}

$ROOT = dirname(__DIR__);
require $ROOT . '/config/connection.php'; // $connect + env loader
require $ROOT . '/lib/cloudinary.php';

$live = in_array('--live', $argv ?? [], true);
echo $live ? "MODE: LIVE (menulis DB)\n" : "MODE: DRY-RUN (tidak menulis, tambah --live untuk eksekusi)\n";

if (!cloudinary_configured()) {
    fwrite(STDERR, "ERROR: CLOUDINARY_CLOUD_NAME/KEY/SECRET kosong di .env\n");
    exit(1);
}

// Cek kolom thumbnail_public_id (tahan bila migrate_cloudinary.sql lupa jalan)
$has_pid = false;
if ($r = @$connect->query("SHOW COLUMNS FROM projects LIKE 'thumbnail_public_id'")) {
    $has_pid = $r->num_rows > 0;
    $r->free();
}
if (!$has_pid) {
    fwrite(STDERR, "ERROR: kolom thumbnail_public_id belum ada. Jalankan db/migrate_cloudinary.sql dulu.\n");
    exit(1);
}

$res = $connect->query("SELECT id, title, thumbnail FROM projects ORDER BY id ASC");
if (!$res) {
    fwrite(STDERR, 'ERROR query: ' . $connect->error . "\n");
    exit(1);
}

$ok = $skip = $fail = 0;
while ($row = $res->fetch_assoc()) {
    $id = (int) $row['id'];
    $thumb = (string) ($row['thumbnail'] ?? '');
    // Sudah URL Cloudinary → lewati (idempoten: aman dijalankan ulang)
    if (strpos($thumb, 'http://') === 0 || strpos($thumb, 'https://') === 0) {
        echo "[SKIP] #$id {$row['title']} (sudah URL)\n";
        $skip++;
        continue;
    }
    $local = $ROOT . '/media/image/uploads/' . basename($thumb);
    if (!is_file($local)) {
        echo "[HILANG] #$id {$row['title']} — file tidak ada: " . basename($thumb) . "\n";
        $fail++;
        continue;
    }
    if (!$live) {
        echo "[AKAN-UPLOAD] #$id {$row['title']} <- " . basename($thumb) . "\n";
        $ok++;
        continue;
    }
    [$data, $err] = cloudinary_upload($local, basename($thumb));
    if ($err !== '') {
        echo "[GAGAL] #$id {$row['title']}: $err\n";
        $fail++;
        continue;
    }
    $stmt = $connect->prepare("UPDATE projects SET thumbnail = ?, thumbnail_public_id = ? WHERE id = ?");
    $stmt->bind_param("ssi", $data['url'], $data['public_id'], $id);
    if ($stmt->execute()) {
        echo "[OK] #$id {$row['title']} -> {$data['url']}\n";
        $ok++;
    } else {
        echo "[GAGAL-DB] #$id {$row['title']}: " . $stmt->error . "\n";
        $fail++;
    }
    $stmt->close();
}
$res->free();

echo "----\nSelesai: $ok sukses, $skip dilewati, $fail gagal/hilang.\n";
exit($fail > 0 ? 2 : 0);
