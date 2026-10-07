<?php
// ============================================================================
// [RENDER-FIX 7 lanjutan] add_category.php
// SEBELUM  : INSERT "VALUES (UPPER('$name'))" + SELECT interpolasi (SQLi) +
//           htmlspecialchars saat simpan (data tersimpan ter-encode).
// SESUDAH : prepared + simpan mentah + e() saat tampil.
// CATATAN : UPPER() tetap dipakai di SQL agar pencocokan konsisten.
// ============================================================================
require_once __DIR__ . '/../config/bootstrap.php';
// [RESTRUKTUR] navigation/footer pindah ke includes/.
include_once __DIR__ . '/../includes/navigation.php';

$message = "";

if (isset($_POST['submit'])) {
    // Get the new category name from the form
    if (trim($_POST['name'] ?? '') === "") {
        $message = "<p class='php-message'> Please enter a category name!</p>";
    } else {
        $name = trim($_POST['name']);

        // Check if the category already exists (prepared)
        $check_stmt = $connect->prepare("SELECT id FROM category WHERE categoryname = UPPER(?)");
        $check_stmt->bind_param("s", $name);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $message = "<p class='php-message'> Category already exists!</p>";
        } else {
            $ins = $connect->prepare("INSERT INTO category (categoryname) VALUES (UPPER(?))");
            $ins->bind_param("s", $name);
            if ($ins->execute()) {
                $message = "<p class='php-message'> Category added!</p>";
            } else {
                error_log('[PortoPro] add category gagal: ' . $connect->error);
                $message = "<p class='php-message'> Error adding category!</p>";
            }
            $ins->close();
        }
        $check_stmt->close();
    }
}
?>

<?php if (isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true) { ?>
<div class="projects-subpage-container">
    <div class="projects-subpage">
        <h1>Add New Category</h1>
        <form action="" method="POST" class="form">
            <label for="name">Category Name:</label>
            <input type="text" name="name" required>
            <button type="submit" name="submit">Add</button>
            <br>
            <br>
            <?php if (!empty($message)) { echo $message; }?>
        </form>
    </div>
</div>
<?php } else { ?>
    <p class="php-message decline-message">You are not authorized to view this page.</p>
<?php } ?>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
