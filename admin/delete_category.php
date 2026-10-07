<?php
// ============================================================================
// [RENDER-FIX 7 lanjutan] delete_category.php
// SEBELUM  : "SELECT/DELETE ... WHERE id = $category_id" ($_POST mentah →
//           SQLi) + error DB dibocorkan.
// SESUDAH : (int) cast + prepared + error masuk log.
// Hapus file fisik: cascade DB menghapus baris projects, tapi file gambar
// (lokal lama / Cloudinary) TIDAK ikut terhapus otomatis → kita bersihkan
// manual di sini agar storage tidak jadi sampah.
// ============================================================================
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../lib/cloudinary.php';
// [RESTRUKTUR] navigation pindah ke includes/.
include_once __DIR__ . '/../includes/navigation.php';

$message = "";

// Function to delete a category
function delete_category($category_id) {
    global $connect;

    $category_id = (int) $category_id;

    $check_stmt = $connect->prepare("SELECT id FROM category WHERE id = ?");
    $check_stmt->bind_param("i", $category_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    //if the category exists, proceed with deletion
    if ($check_result->num_rows > 0) {
        // SEBELUM: file gambar projects dalam kategori dibiarkan (sampah di
        // disk/CDN). SESUDAH: kumpulkan thumbnail dulu, hapus setelah DELETE.
        $thumbs = [];
        $t = $connect->prepare("SELECT thumbnail, thumbnail_public_id FROM projects WHERE category_id = ?");
        if ($t) {
            $t->bind_param("i", $category_id);
            $t->execute();
            $tr = $t->get_result();
            while ($row = $tr->fetch_assoc()) $thumbs[] = $row;
            $t->close();
        } elseif (($t2 = $connect->prepare("SELECT thumbnail FROM projects WHERE category_id = ?"))) {
            // Fallback skema lama tanpa thumbnail_public_id.
            $t2->bind_param("i", $category_id);
            $t2->execute();
            $tr = $t2->get_result();
            while ($row = $tr->fetch_assoc()) $thumbs[] = $row;
            $t2->close();
        }

        $delete_stmt = $connect->prepare("DELETE FROM category WHERE id = ?"); //The cascade automatically deletes all projects associated with this category
        $delete_stmt->bind_param("i", $category_id);

        if ($delete_stmt->execute()) {
            foreach ($thumbs as $th) {
                $thumb = (string) ($th['thumbnail'] ?? '');
                $pid = (string) ($th['thumbnail_public_id'] ?? '');
                if ((strpos($thumb, 'http') === 0) && $pid !== '') {
                    cloudinary_destroy($pid);
                } elseif ($thumb !== '' && strpos($thumb, 'http') !== 0) {
                    // [RESTRUKTUR] file pindah ke admin/ → naik satu level ke root.
                    $p = dirname(__DIR__) . '/media/image/uploads/' . basename($thumb);
                    if (file_exists($p)) unlink($p);
                }
            }
            $message = "<p class='php-message'>Category deleted successfully!</p>";
        } else {
            error_log('[PortoPro] delete category gagal: ' . $connect->error);
            $message = "<p class='php-message'>Error deleting category. Try again.</p>";
        }
        $delete_stmt->close();
    } else {
        $message = "<p class='php-message'>Category not found!</p>";
    }
    $check_stmt->close();

    return $message;
}

// Check if the user has submitted the form to delete a category
if (isset($_POST['delete_category'])) {
    if (isset($_POST['category_id'])) {
        $category_id = $_POST['category_id'];
        $message = delete_category($category_id);
    } else {
        $message = "<p class='php-message'>No category selected for deletion!</p>";
    }
}

$category_list = [];

$query = "SELECT * FROM category ORDER BY id ASC";
$result = mysqli_query($connect, $query);
while ($category = mysqli_fetch_assoc($result)) {
    $category_list[] = $category;
}
?>


<?php
if (isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true) {
?>
<div class="projects-subpage-container">
    <div class="projects-subpage">
        <h1>Delete Category</h1>
        <?php if (!empty($message)) { echo $message; } ?>
        <p class='additional-msg'>Click on the button to delete the category.</p>
        <p class='additional-msg'>Note: Deleting a category will also delete all projects associated with it.</p>
        <br>
        <?php
        foreach ($category_list as $category) {
            $category_id = (int) $category['id'];
            $category_name = $category['categoryname'];
        ?>
        <div class='delete-category-container'>
            <p><?php echo e_text($category_name); ?></p>
            <form action="" method="POST">
                <input type="hidden" name="category_id" value="<?php echo $category_id; ?>"> <!-- Hidden input so php can get the category ID -->
                <button type="submit" name="delete_category" onclick="return confirm('Delete this category?');">Delete</button>
            </form>
        </div>
        <?php }
        } else { ?>
            <p class="php-message decline-message">You are not authorized to view this page.</p>
        <?php } ?>
    </div>
</div>
