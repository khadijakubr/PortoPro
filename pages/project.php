<?php
// ============================================================================
// [RENDER-FIX 6] project.php — prepared + escape output + thumb CDN
// ----------------------------------------------------------------------------
// SEBELUM  : "SELECT * FROM projects WHERE category_id = {$category_id}"
//           (interpolasi) + echo $category['name'] / $project[...] mentah
//           (XSS) + src="media/image/uploads/..." relatif.
// SESUDAH : prepared statement + e() di semua echo + portopro_thumb_src()
//           (mendukung URL Cloudinary & file lokal lama).
// ============================================================================
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../lib/cloudinary.php';
// [RESTRUKTUR] navigation pindah ke includes/.
include_once __DIR__ . '/../includes/navigation.php';

// if there's no subpage specified, show the main projects page
if (!isset($_GET['p'])) {
    $message = "";
    $projects_gallery = [];

    $category_query = "SELECT * FROM category ORDER BY id ASC";
    $category_result = mysqli_query($connect, $category_query);

    // Check if there are any categories in the database
    if (mysqli_num_rows($category_result) == 0) {
        $message = "<p class='php-message'>No categories found.</p>";
    }
    // Fetch categories and their associated projects
    while ($category = mysqli_fetch_assoc($category_result)) {
        $category_id = (int) $category['id'];
        $category_name = $category['categoryname'];

        // SEBELUM: interpolasi langsung. SESUDAH: prepared (anti-SQLi).
        $stmt = $connect->prepare("SELECT * FROM projects WHERE category_id = ? ORDER BY id DESC");
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $project_result = $stmt->get_result();

        $projects = [];
        while ($project = $project_result->fetch_assoc()) {
            $projects[] = $project;
        }
        $stmt->close();

        $projects_gallery[] = [
            'id' => $category_id,
            'name' => $category_name,
            'projects' => $projects
        ]; // Store category and its projects as an array in the projects_gallery array
    }
}

// SUBPAGE HANDLER
// [RESTRUKTUR] sub-halaman admin pindah ke admin/.
elseif ($_GET['p'] == 'add_category') {
    include_once __DIR__ . '/../admin/add_category.php';
} elseif ($_GET['p'] == 'add_project') {
    include_once __DIR__ . '/../admin/add_project.php';
} elseif ($_GET['p'] == 'update_project') {
    if (isset($_GET['id'])) {
        $project_id = $_GET['id'];
        include_once __DIR__ . '/../admin/update_project.php';
    } else {
        echo "<h1 class='php-message'>404 Project Not Found</h1>";
    }
} elseif ($_GET['p'] == 'delete_category') {
    include_once __DIR__ . '/../admin/delete_category.php';
} elseif ($_GET['p'] == 'detail_project'){
    if (isset($_GET['id'])) {
        $project_id = $_GET['id'];
        include_once __DIR__ . '/detail_project.php';
    } else {
        echo "<h1 class='php-message'>404 Project Not Found</h1>";
    }
}else {
    echo "<h1 class='php-message'>404 Subpage Not Found</h1>";
}
?>

<?php if (!isset($_GET['p'])){ ?>
<div class="projects-container">
    <div id="projects-header">
        <h1>PROJECTS</h1>
        <?php if (isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true) { ?>
        <div id="projects-buttons">
            <a href="?act=pj&p=add_project" class="button"><button>Add Project</button></a>
            <a href="?act=pj&p=add_category" class="button"><button>Add Category</button></a>
            <a href="?act=pj&p=delete_category" class="button"><button>Delete Category</button></a>
        </div>
        <?php } ?>
    </div>
    <?php
        if (!empty($message)) { echo $message; }
    ?>
    <?php if (!empty($projects_gallery)){
        // If there are arrays of projects in projects_gallery, loop through them
            foreach ($projects_gallery as $category){ ?>

        <div class="category-container">

            <div class="category-header">
                <div class="category-title"><?php echo e_text($category['name']); //Take the name of category and print it ?></div>
            </div>

            <div class="gallery">

                <?php if (empty($category['projects'])){ ?>
                    <div class="additional-msg" id="no-project">NO PROJECT FOUND</div>
                <?php } // If there are no projects in this category print no project found?>

                <?php // Loop through each project in the category
                foreach ($category['projects'] as $project){ ?>
                    <div class="item">
                        <a href="<?php echo e($project['link']); ?>" target="_blank">
                            <img src="<?php echo e(portopro_thumb_src($project['thumbnail'])); ?>" alt="<?php echo e_text($project['title']); ?>" class="thumbnail" loading="lazy">
                        </a>

                        <div class="project-title">
                            <?php if (isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true) { ?>
                                <a href="?act=pj&p=update_project&id=<?php echo (int)$project['id']; ?>">
                                    <?php echo e_text($project['title']); ?>
                                </a>
                            <?php } else { ?>
                                <a href="?act=pj&p=detail_project&id=<?php echo (int)$project['id']?>">
                                    <?php echo e_text($project['title']); ?>
                                </a>
                            <?php } ?>
                        </div>

                        <div class="project-description"><?php echo e_text($project['description']); ?></div>
                    </div>

                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <?php } ?>
</div>
<?php } ?>

<?php
// [RESTRUKTUR] footer pindah ke includes/.
include_once __DIR__ . '/../includes/footer.php';
?>
