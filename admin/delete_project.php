<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../lib/cloudinary.php';

// ============================================================================
// [RENDER-FIX 5 lanjutan] delete_project()
// ----------------------------------------------------------------------------
// SEBELUM  : hanya unlink("media/image/uploads/...") — file Cloudinary tidak
//           ikut terhapus; $project_id interpolasi mentah (SQLi); pesan error
//           DB dibocorkan ke pengunjung.
// SESUDAH : bila thumbnail berupa URL Cloudinary + ada public_id → panggil
//           cloudinary_destroy(); bila file lokal → unlink; semua query
//           prepared; error detail masuk log saja.
// ============================================================================
function delete_project($project_id) {
    global $connect;
    $message = "";
    $project_id = (int) $project_id;

    // Ambil data thumbnail dulu (prepared, anti-SQLi).
    $file_data = null;
    $stmt = $connect->prepare("SELECT thumbnail, thumbnail_public_id FROM projects WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $file_data = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
    if (!$file_data) {
        // Fallback skema lama tanpa kolom thumbnail_public_id.
        $stmt = $connect->prepare("SELECT thumbnail FROM projects WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $project_id);
            $stmt->execute();
            $file_data = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
    }

    if ($file_data) {
        $thumb = (string) ($file_data['thumbnail'] ?? '');
        $pid   = (string) ($file_data['thumbnail_public_id'] ?? '');
        if ((strpos($thumb, 'http://') === 0 || strpos($thumb, 'https://') === 0) && $pid !== '') {
            cloudinary_destroy($pid); // file CDN ikut terhapus
        } else {
            // File lokal (data lama / mode dev): hapus bila ada.
            // [RESTRUKTUR] file pindah ke admin/ → naik satu level ke root.
            $thumbnail_path = dirname(__DIR__) . "/media/image/uploads/" . basename($thumb);
            if ($thumb !== '' && file_exists($thumbnail_path)) {
                unlink($thumbnail_path);
            }
        }
    }

    //delete data from database (prepared)
    $stmt = $connect->prepare("DELETE FROM projects WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $project_id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $message .= "<p class='php-message'>Project deleted successfully!</p>";
        } else {
            $message .= "<p class='php-message'>Project not found!</p>";
        }
        $stmt->close();
    } else {
        error_log('[PortoPro] delete prepare gagal: ' . $connect->error);
        $message .= "<p class='php-message'>Error deleting project. Try again.</p>";
    }

    return $message;
}
?>
