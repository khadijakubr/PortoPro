<?php
// ============================================================================
// [RENDER-FIX 6 lanjutan] detail_project.php
// SEBELUM  : "SELECT * FROM projects WHERE id = $project_id" ($_GET mentah →
//           SQLi, mis. ?id=1 OR 1=1) + echo mentah (XSS).
// SESUDAH : (int) cast + prepared + e() + portopro_thumb_src().
// ============================================================================
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../lib/cloudinary.php';
include_once __DIR__ . '/../includes/navigation.php';

// Check if the project ID is set in the URL
$project = null;
$message = "";
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
}
?>

<?php if (!empty($message)) {
        echo $message;
        } else { ?>
            <div class="projects-subpage-container">
                <div class="detail-project-container">
                    <div id="detail-img">
                        <img src="<?php echo e(portopro_thumb_src($project['thumbnail']))?>" alt="<?php echo e_text($project['title'])?>">
                    </div>
                    <div id="detail-container">
                        <h1 class="project-title"><?php echo e_text($project['title'])?></h1>
                        <p class="project-description" id="detail-project-desc"><?php echo e_text($project['description']) ?></p>
                        <a href="<?php echo e($project['link'])?>" target="_blank">
                            <button>Watch the video</button>
                        </a>
                    </div>
                </div>
            </div>
<?php } ?>


<?php include_once __DIR__ . '/../includes/footer.php'; ?>
