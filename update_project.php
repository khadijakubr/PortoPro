<?php
session_start();

include_once 'connection.php';
include_once 'navigation.php';
include_once 'delete_project.php';

$message = "";
$deleted = false;

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

// Handle form submission for updating project
if (isset($_POST['submit'])) {
    $category_id = $_POST['category_id'];
    $title = trim(htmlspecialchars($_POST['title']));
    $description = trim(htmlspecialchars($_POST['description']));
    $link = trim(htmlspecialchars($_POST['link']));

    // Handle file upload for thumbnail
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
        $thumbnail_name = $_FILES['thumbnail']['name'];
        $thumbnail_tmp = $_FILES['thumbnail']['tmp_name'];
        $thumbnail_dest = "media/image/uploads/" . $thumbnail_name;

        if (file_exists($thumbnail_dest)) {
            $message = "File already exists.";
        } else {
            if ($category_id == $project['category_id'] && $title == $project['title'] && 
                $description == $project['description'] && $link == $project['link'] && 
                $thumbnail_name == $project['thumbnail']) {
                $message = "No changes made to the project.";
            } else {
                if (move_uploaded_file($thumbnail_tmp, $thumbnail_dest)) {
                $update = "UPDATE projects SET category_id = '$category_id', title = '$title', description = '$description', 
                            thumbnail = '$thumbnail_name', link = '$link' WHERE id = $project_id";
                if (mysqli_query($connect, $update)) {
                    $message = "Project updated!";
                    $query = "SELECT * FROM projects WHERE id = $project_id";
                    $result = mysqli_query($connect, $query);
                    if (mysqli_num_rows($result) > 0) {
                        $project = mysqli_fetch_assoc($result);
                    }
                } else {
                    $message = "Failed to update project: " . mysqli_error($connect);
                }
            }
        }
    }
    } else {
        // If no new thumbnail is uploaded, update other fields only
        if ($category_id == $project['category_id'] && $title == $project['title'] && 
            $description == $project['description'] && $link == $project['link']) {
            $message = "No changes made to the project.";
        } else {
            $update = "UPDATE projects SET category_id = '$category_id', title = '$title', description = '$description', 
                        link = '$link' WHERE id = $project_id";
            if (mysqli_query($connect, $update)) {
                $message = "Project updated!";
                $query = "SELECT * FROM projects WHERE id = $project_id";
                $result = mysqli_query($connect, $query);
                if (mysqli_num_rows($result) > 0) {
                    $project = mysqli_fetch_assoc($result);
                }
            } else {
                $message = "Failed to update project: " . mysqli_error($connect);
            }
        }
    }
}

// Handle form submission for deleting project
if (isset($_POST['delete'])) {
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
        <?php if (!$deleted) { ?>
            <form action="" method="POST" enctype="multipart/form-data" class="form">
                <select name="category_id" required>
                    <option value=""><?php echo $project['categoryname']; ?></option>
                <?php
                $cat_result = mysqli_query($connect, "SELECT * FROM category");
                while ($cat = mysqli_fetch_assoc($cat_result)) {
                    if ($cat['id'] == $project['category_id']) {
                        $selected = "selected";
                    } else {
                        $selected = "";
                    }
                    echo "<option value='{$cat['id']}' $selected>{$cat['categoryname']}</option>";
                }
                ?>
                </select><br>

                <label>Project Title:</label>
                <input type="text" name="title" id="project-title" value="<?php echo $project['title']; ?>" required>
                <br>

                <label>Project Description:</label>
                <textarea name="description" id="project-description" required><?php echo $project['description']; ?></textarea>
                <br>
                
                <label>Project Thumbnail: (optional, upload to replace current)</label>
                <div id="project-thumbnail-container">
                    <img src="media/image/uploads/<?php echo $project['thumbnail']; ?>" alt="Current Thumbnail">
                </div>
                <input type="file" name="thumbnail" id="project-thumbnail" accept="image/*">
                <br>
                
                <label>Project Link:</label>
                <input type="url" name="link" id="project-link" value="<?php echo $project['link']; ?>">
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

<?php include_once 'footer.php'; ?>