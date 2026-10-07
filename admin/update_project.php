<?php
// ============================================================================
// [RENDER-FIX 5 lanjutan] update_project.php
// ----------------------------------------------------------------------------
// SEBELUM  : SELECT/UPDATE "... WHERE id = $project_id" ($_GET mentah → SQLi);
//           thumbnail baru wajib beda nama file (file_exists → "File already
//           exists"); upload selalu lokal; htmlspecialchars saat simpan.
// SESUDAH : (int)$_GET['id'] + prepared statements; thumbnail baru lewat
//           portopro_store_upload() (Cloudinary CDN); file lama di Cloudinary
//           ikut dihapus; simpan mentah + escape saat tampil.
// ============================================================================

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../lib/cloudinary.php';
// [RESTRUKTUR] navigation → includes/, delete_project tetap satu folder.
include_once __DIR__ . '/../includes/navigation.php';
include_once __DIR__ . '/delete_project.php';

$message = "";
$deleted = false;

// Check if the project ID is set in the URL
// SEBELUM: $project_id = $_GET['id'] (string mentah → SQLi "1 OR 1=1").
// SESUDAH: cast (int) + prepared. ALASAN: id selalu angka.
$project = null;
if (isset($_GET['id'])) {
    $project_id = (int) $_GET['id'];
    $stmt = $connect->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $project = $result->fetch_assoc();
    } else {
        $message = "<p class='php-message'>404 Project Not Found</p>";
    }
    $stmt->close();
} else {
    $message = "<p class='php-message'>404 Project Not Found</p>";
    $project_id = 0;
}

// Handle form submission for updating project
if (isset($_POST['submit']) && $project) {
    $category_id = (int) ($_POST['category_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $link = trim($_POST['link'] ?? '');

    $new_thumb_value = null; // null = tidak ganti gambar
    $new_public_id = null;

    // Handle file upload for thumbnail
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
        // SEBELUM: tolak bila nama file sudah ada (file_exists) — di
        // Cloudinary nama selalu unik. SESUDAH: upload dulu, hapus yang lama.
        [$stored, $upErr] = portopro_store_upload($_FILES['thumbnail']);
        if ($upErr !== '') {
            $message = $upErr;
        } else {
            $new_thumb_value = $stored['url'] ?? $stored['local'];
            $new_public_id = $stored['public_id'] ?? null;
        }
    }

    if ($message === "") {
        if ($new_thumb_value !== null) {
            // Hapus file lama agar tidak jadi sampah (biaya storage).
            $old_thumb = (string) ($project['thumbnail'] ?? '');
            $old_pid = (string) ($project['thumbnail_public_id'] ?? '');
            if ((strpos($old_thumb, 'http') === 0) && $old_pid !== '') {
                cloudinary_destroy($old_pid);
            } elseif ($old_thumb !== '' && strpos($old_thumb, 'http') !== 0) {
                // [RESTRUKTUR] file pindah ke admin/ → naik satu level ke root.
                $old_path = dirname(__DIR__) . '/media/image/uploads/' . basename($old_thumb);
                if (file_exists($old_path)) unlink($old_path);
            }
            $stmt = $connect->prepare("UPDATE projects SET category_id = ?, title = ?, description = ?, thumbnail = ?, thumbnail_public_id = ?, link = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("isssssi", $category_id, $title, $description, $new_thumb_value, $new_public_id, $link, $project_id);
                $ok = $stmt->execute();
                $message = $ok ? "Project updated!" : "Failed to update project. Try again.";
                if (!$ok) error_log('[PortoPro] update gagal: ' . $stmt->error);
                $stmt->close();
            } else {
                // Fallback skema lama (tanpa thumbnail_public_id).
                $stmt = $connect->prepare("UPDATE projects SET category_id = ?, title = ?, description = ?, thumbnail = ?, link = ? WHERE id = ?");
                $stmt->bind_param("issssi", $category_id, $title, $description, $new_thumb_value, $link, $project_id);
                $message = $stmt->execute() ? "Project updated!" : "Failed to update project. Try again.";
                $stmt->close();
            }
        } else {
            // If no new thumbnail is uploaded, update other fields only
            $stmt = $connect->prepare("UPDATE projects SET category_id = ?, title = ?, description = ?, link = ? WHERE id = ?");
            $stmt->bind_param("isssi", $category_id, $title, $description, $link, $project_id);
            $message = $stmt->execute() ? "Project updated!" : "Failed to update project. Try again.";
            if ($message !== "Project updated!") error_log('[PortoPro] update gagal: ' . $connect->error);
            $stmt->close();
        }
        // Refresh data project setelah update
        $stmt = $connect->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $r = $stmt->get_result();
        if ($r->num_rows > 0) $project = $r->fetch_assoc();
        $stmt->close();
    }
}

// Handle form submission for deleting project
if (isset($_POST['delete']) && isset($project_id)) {
    $message = delete_project($project_id);
    $deleted = true;
}
?>

<?php
if (isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true) {
?>
<div class="projects-subpage-container">
    <div class="projects-subpage">
        <h1>Update Project Info</h1>
        <?php if (!empty($message)) { echo "<p class='php-message' id='update-project-msg'>$message</p>"; } ?>
        <?php if (!$deleted && $project) { ?>
            <form action="" method="POST" enctype="multipart/form-data" class="form">
                <select name="category_id" required>
                    <option value=""><?php echo e($project['categoryname'] ?? 'Select category'); ?></option>
                <?php
                $cat_result = mysqli_query($connect, "SELECT * FROM category");
                while ($cat = mysqli_fetch_assoc($cat_result)) {
                    if ($cat['id'] == $project['category_id']) {
                        $selected = "selected";
                    } else {
                        $selected = "";
                    }
                    echo "<option value='" . (int)$cat['id'] . "' $selected>" . e_text($cat['categoryname']) . "</option>";
                }
                ?>
                </select><br>

                <label>Project Title:</label>
                <input type="text" name="title" id="project-title" value="<?php echo e_text($project['title']); ?>" required>
                <br>

                <label>Project Description:</label>
                <textarea name="description" id="project-description" required><?php echo e_text($project['description']); ?></textarea>
                <br>

                <label>Project Thumbnail: (optional, upload to replace current)</label>
                <div id="project-thumbnail-container">
                    <img src="<?php echo e(portopro_thumb_src($project['thumbnail'])); ?>" alt="Current Thumbnail">
                </div>
                <input type="file" name="thumbnail" id="project-thumbnail" accept="image/*">
                <br>

                <label>Project Link:</label>
                <input type="url" name="link" id="project-link" value="<?php echo e($project['link']); ?>">
                <br>

                <div id="project-update-btn-container">
                    <button type="submit" name="submit" id="update-project-btn">Update Project</button>
                    <button type="submit" name="delete" id="delete-project-btn" onclick="return confirm('Delete this project?');">Delete Project</button>
                </div>
            </form>
        <?php }
        } else { ?>
            <p class="php-message decline-message">You are not authorized to view this page.</p>
        <?php } ?>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
