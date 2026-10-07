<?php
// ============================================================================
// [RENDER-FIX 5] add_project.php — upload Cloudinary + prepared statement
// ----------------------------------------------------------------------------
// SEBELUM  : move_uploaded_file ke media/image/uploads/ + nama file mentah
//           + INSERT "... VALUES ('$category_id','$title',...)" (SQLi).
// SESUDAH : portopro_store_upload() (Cloudinary bila env terisi, fallback
//           lokal aman) + INSERT prepared statement + e() saat echo.
// ALASAN  : (1) Disk Render ephemeral → file lokal hilang tiap redeploy.
//           (2) SQL injection via title/description. (3) Nama file mentah
//           ber-spasi/duplikat → URL rusak.
// Kolom BARU `projects.thumbnail_public_id` (lihat db/migrate_cloudinary.sql):
// kode mencoba simpan public_id; bila kolom belum ada (DB lama) otomatis
// fallback INSERT tanpa kolom itu agar tidak fatal.
// ============================================================================
require_once __DIR__ . '/../config/bootstrap.php';   // pengganti connection.php mentah (env + session aman)
require_once __DIR__ . '/../lib/cloudinary.php';
// [RESTRUKTUR] navigation/footer pindah ke includes/.
include_once __DIR__ . '/../includes/navigation.php';

$message = "";

// Check if the form is submitted
if (isset($_POST['submit'])) {
    $category_id = (int) ($_POST['category_id'] ?? 0);
    // SEBELUM: trim(htmlspecialchars(...)) saat SIMPAN → data tersimpan
    // ter-encode ganda (&amp;). SESUDAH: simpan mentah (trim saja), escape
    // saat TAMPIL via e(). ALASAN: single-source-of-truth di DB.
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $link = trim($_POST['link'] ?? '');

    if ($category_id <= 0 || $title === '' || $description === '') {
        $message = "Please complete category, title and description.";
    } elseif (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
        [$stored, $upErr] = portopro_store_upload($_FILES['thumbnail']);
        if ($upErr !== '') {
            $message = $upErr;
        } else {
            $thumb_value = $stored['url'] ?? $stored['local']; // URL Cloudinary atau nama file lokal
            $public_id   = $stored['public_id'] ?? null;

            // SEBELUM: query string interpolasi (SQLi). SESUDAH: prepared.
            $ok = false;
            $stmt = $connect->prepare("INSERT INTO projects (category_id, title, description, thumbnail, thumbnail_public_id, link) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("isssss", $category_id, $title, $description, $thumb_value, $public_id, $link);
                $ok = $stmt->execute();
                $stmt->close();
            } else {
                // Fallback: DB lama belum punya kolom thumbnail_public_id.
                $stmt = $connect->prepare("INSERT INTO projects (category_id, title, description, thumbnail, link) VALUES (?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("issss", $category_id, $title, $description, $thumb_value, $link);
                    $ok = $stmt->execute();
                    $stmt->close();
                }
            }
            $message = $ok ? "Project added!" : "Failed to add project. Try again.";
            if (!$ok) error_log('[PortoPro] insert project gagal: ' . $connect->error);
        }
    } else {
        $message = "Please choose a thumbnail image.";
    }
}
?>

<?php if (isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true) { ?>
<div class="projects-subpage-container">
    <div class="projects-subpage">
        <h1>Add New Project</h1>
        <br>
        <?php if (!empty($message)) { echo "<p class='php-message'>" . e($message) . "</p>"; } ?>
        <form action="" method="POST" enctype="multipart/form-data" class="form">
            <select name="category_id" required>
                <option value="">Select Category</option>
            <?php
            $cat_result = mysqli_query($connect, "SELECT * FROM category");
            while ($cat = mysqli_fetch_assoc($cat_result)) {
                echo "<option value='" . (int)$cat['id'] . "'>" . e($cat['categoryname']) . "</option>";
            }
            ?>
            </select><br>
            <label>Project Title:</label>
            <input type="text" name="title" id="project-title" required>
            <br>

            <label>Project Description:</label>
            <textarea name="description" id="project-description" required></textarea>
            <br>

            <label>Project Thumbnail: (max 8MB)</label>
            <input type="file" name="thumbnail" id="project-thumbnail" accept="image/*" required>
            <script>
            // Validate file size for thumbnail upload
            document.getElementById('project-thumbnail').addEventListener('change', function() {
                const maxSize = 8 * 1024 * 1024;
                if (this.files[0].size > maxSize) {
                    alert('File size exceeds 8MB limit. Please select a smaller file.');
                    this.value = ''; // clear the selection
                }
            });
            </script>
            <br>

            <label>Project Link:</label>
            <input type="url" name="link" id="project-link">
            <br>

            <button type="submit" name="submit">Add Project</button>
        </form>
    </div>
</div>
<?php } else { ?>
    <p class="php-message decline-message">You are not authorized to view this page.</p>
<?php } ?>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
