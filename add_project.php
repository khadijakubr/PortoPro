<?php
include_once 'connection.php';
include_once 'navigation.php';

$message = "";
// Check if the form is submitted
if (isset($_POST['submit'])) {
    $category_id = $_POST['category_id'];
    $title = trim(htmlspecialchars($_POST['title']));
    $description = trim(htmlspecialchars($_POST['description']));
    $link = trim(htmlspecialchars($_POST['link']));

    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
        $thumbnail_name = $_FILES['thumbnail']['name'];
        $thumbnail_tmp = $_FILES['thumbnail']['tmp_name'];
        $thumbnail_dest = "media/image/uploads/" . $thumbnail_name;

        if (file_exists($thumbnail_dest)) {
            $message = "File already exists.";
        } else {
            // If the file uploaded, insert the project info into the database
            if (move_uploaded_file($thumbnail_tmp, $thumbnail_dest)) {
                $insert = "INSERT INTO projects (category_id, title, description, thumbnail, link) 
                           VALUES ('$category_id', '$title', '$description', '$thumbnail_name', '$link')";
                if (mysqli_query($connect, $insert)) {
                    $message = "Project added!";
                } else {
                    $message = "Failed to add project: " . mysqli_error($connect);
                }
            } else {
                $message = "Failed to upload image.";
            }
        }
    }
}
?>

<div class="projects-subpage-container">
    <div class="projects-subpage">
        <h1>Add New Project</h1>
        <br>
        <?php if (!empty($message)) { echo "<p class='php-message'>$message</p>"; } ?>
        <form action="" method="POST" enctype="multipart/form-data" class="form">
            <select name="category_id" required>
                <option value="">Select Category</option>
            <?php
            $cat_result = mysqli_query($connect, "SELECT * FROM category");
            while ($cat = mysqli_fetch_assoc($cat_result)) {
                echo "<option value='{$cat['id']}'>{$cat['categoryname']}</option>";
            }
            ?>
            </select><br>
            <label>Project Title:</label>
            <input type="text" name="title" id="project-title" required>
            <br>

            <label>Project Description:</label>
            <textarea name="description" id="project-description" required></textarea>
            <br>

            <label>Project Thumbnail:</label>
            <input type="file" name="thumbnail" id="project-thumbnail" accept="image/*" required>
            <br>

            <label>Project Link:</label>
            <input type="url" name="link" id="project-link">
            <br>

            <button type="submit" name="submit">Add Project</button>
        </form>
    </div>
</div>
<?php include_once 'footer.php'; ?>