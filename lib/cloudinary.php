<?php
// ============================================================================
// [RENDER-FIX 4] HELPER CLOUDINARY — file BARU (pengganti move_uploaded_file)
// ----------------------------------------------------------------------------
// SEBELUM  : $thumbnail_dest = "media/image/uploads/" . $_FILES[...]['name'];
//           move_uploaded_file(...) — file menempel di disk container.
// SESUDAH : cloudinary_upload($tmp, $origName) → ['url','public_id'] bila
//           env CLOUDINARY_* terisi; bila kosong → fallback lokal (dev).
// ALASAN  : (1) Disk Render EPHEMERAL: tiap redeploy/restart, file upload
//           hilang. (2) Nama file asli ("IMG_6545 (1).jpg", spasi & duplikat)
//           rawan tabrakan & URL rusak — Cloudinary memberi URL CDN unik.
//           (3) Tanpa composer: cukup curl ke REST API (signed upload aman,
//           secret tidak keluar ke browser).
// CARA KERJA: signature = sha1("folder=..&timestamp=.." . api_secret).
// DOKUMEN ENV: lihat .env.example (CLOUDINARY_CLOUD_NAME/KEY/SECRET/FOLDER).
// ============================================================================

function cloudinary_configured() {
    return (bool) (getenv('CLOUDINARY_CLOUD_NAME') && getenv('CLOUDINARY_API_KEY') && getenv('CLOUDINARY_API_SECRET'));
}

// Amankan nama file: buang karakter aneh, tambah uniqid agar tak tabrakan.
// SEBELUM: pakai $_FILES['thumbnail']['name'] mentah ("Cold Whisk Matcha.JPG"
// dengan spasi → URL "media/image/uploads/Cold Whisk..." rusak di <img>).
function portopro_safe_filename($original) {
    $base = pathinfo((string) $original, PATHINFO_FILENAME);
    $ext  = strtolower(pathinfo((string) $original, PATHINFO_EXTENSION));
    $base = preg_replace('/[^a-zA-Z0-9_-]+/', '-', trim($base));
    $base = trim($base, '-') ?: 'upload';
    $allow = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $allow, true)) $ext = 'jpg';
    return $base . '-' . uniqid() . '.' . $ext;
}

// Validasi server-side (SEBELUM: hanya JS di browser, mudah di-bypass).
// Mengembalikan '' bila lolos, atau pesan error bila gagal.
function portopro_validate_image($file) {
    if (!isset($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return 'Tidak ada file yang diupload.';
    }
    if ($file['size'] > 8 * 1024 * 1024) {
        return 'Ukuran file melebihi 8MB.';
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (strpos((string) $mime, 'image/') !== 0) {
        return 'File harus berupa gambar.';
    }
    return '';
}

function cloudinary_upload($tmp_path, $original_name) {
    $cloud  = (string) getenv('CLOUDINARY_CLOUD_NAME');
    $key    = (string) getenv('CLOUDINARY_API_KEY');
    $secret = (string) getenv('CLOUDINARY_API_SECRET');
    $folder = (string) (getenv('CLOUDINARY_FOLDER') ?: 'portopro/uploads');
    $timestamp = time();
    // Signature untuk params yang dikirim (urut abjad): folder & timestamp.
    $signature = sha1("folder={$folder}&timestamp={$timestamp}{$secret}");

    $post = [
        'file'       => new CURLFile($tmp_path),
        'api_key'    => $key,
        'timestamp'  => $timestamp,
        'signature'  => $signature,
        'folder'     => $folder,
        'public_id'  => pathinfo(portopro_safe_filename($original_name), PATHINFO_FILENAME),
    ];
    $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloud}/image/upload");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $post,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
    ]);
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false || $code < 200 || $code >= 300) {
        error_log('[PortoPro] Cloudinary upload gagal: ' . ($err ?: $raw));
        return [null, 'Upload ke Cloudinary gagal. Coba lagi.'];
    }
    $res = json_decode($raw, true);
    if (empty($res['secure_url'])) {
        error_log('[PortoPro] Cloudinary respons tak terduga: ' . $raw);
        return [null, 'Upload ke Cloudinary gagal. Coba lagi.'];
    }
    return [['url' => $res['secure_url'], 'public_id' => $res['public_id'] ?? ''], ''];
}

function cloudinary_destroy($public_id) {
    if ($public_id === '' || $public_id === null || !cloudinary_configured()) return;
    $cloud  = (string) getenv('CLOUDINARY_CLOUD_NAME');
    $key    = (string) getenv('CLOUDINARY_API_KEY');
    $secret = (string) getenv('CLOUDINARY_API_SECRET');
    $timestamp = time();
    $signature = sha1("public_id={$public_id}&timestamp={$timestamp}{$secret}");
    $ch = curl_init("https://api.cloudinary.com/v1_1/{$cloud}/image/destroy");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'public_id' => $public_id,
            'api_key'   => $key,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);
    if ($raw) error_log('[PortoPro] Cloudinary destroy: ' . $raw);
}

// Simpan file dengan strategi ganda:
// - Cloudinary terisi → return ['url'=>..., 'public_id'=>..., 'local'=>null]
// - Cloudinary kosong (dev lokal) → simpan ke media/image/uploads/ dengan
//   nama aman unik, return ['url'=>null,'public_id'=>null,'local'=>namaFile]
// SEBELUM: selalu lokal + nama mentah + cek file_exists (balapan & gagal
// di Render). SESUDAH: cabang ini. ALASAN: dev lokal tetap jalan tanpa
// internet/kredensial, production permanen di CDN.
function portopro_store_upload($file) {
    $err = portopro_validate_image($file);
    if ($err !== '') return [null, $err];
    if (cloudinary_configured()) {
        [$data, $upErr] = cloudinary_upload($file['tmp_name'], $file['name']);
        if ($upErr !== '') return [null, $upErr];
        return [['url' => $data['url'], 'public_id' => $data['public_id'], 'local' => null], ''];
    }
    $safe = portopro_safe_filename($file['name']);
    $dest = __DIR__ . '/../media/image/uploads/' . $safe;
    if (!@move_uploaded_file($file['tmp_name'], $dest)) {
        return [null, 'Gagal menyimpan file lokal.'];
    }
    return [['url' => null, 'public_id' => null, 'local' => $safe], ''];
}

// Tampilkan thumbnail: bila baris DB berisi URL Cloudinary (kolom thumbnail
// berisi "https://...") pakai URL itu; bila nama file lama → path lokal.
// SEBELUM: src selalu "media/image/uploads/" + nama file (relatif, pecah bila
// di-include dari subpath). SESUDAH: helper ini (mendukung keduanya).
function portopro_thumb_src($thumbnail) {
    $t = (string) $thumbnail;
    if (strpos($t, 'http://') === 0 || strpos($t, 'https://') === 0) return $t;
    return 'media/image/uploads/' . rawurlencode($t);
}
?>
