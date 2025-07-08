<?php 
include_once 'connection.php';
include_once 'navigation.php';

// Check if the project ID is set in the URL
if (isset($_GET['id'])) {
    $project_id = $_GET['id'];
    $query = "SELECT * FROM projects WHERE id = $project_id";
    $result = mysqli_query($connect, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $project = mysqli_fetch_assoc($result);
    } else {
        $message = "<p class='php-message'>404 Project Not Found</p>";
    }
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
                        <img src="media/image/uploads/<?php echo $project['thumbnail']?>" alt="<?php echo $project['title']?>">
                    </div>
                    <div id="detail-container">
                        <h1 class="project-title"><?php echo $project['title']?></h1>
                        <p class="project-description" id="detail-project-desc"><?php echo $project['description'] ?></p>
                        <a href="<?php echo $project['link']?>" target="_blank">
                            <button>Watch the video</button>
                        </a>
                    </div>
                </div>
            </div>
<?php } ?>


<?php include_once 'footer.php'; ?>