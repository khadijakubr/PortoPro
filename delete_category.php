<?php
include_once 'connection.php';
include_once 'navigation.php';

$message = "";

// Function to delete a category
function delete_category($category_id) {
    global $connect;

    $check_query = "SELECT * FROM category WHERE id = $category_id";
    $check_result = mysqli_query($connect, $check_query);

    //if the category exists, proceed with deletion
    if (mysqli_num_rows($check_result) > 0) {

        $delete_query = "DELETE FROM category WHERE id = $category_id"; //The cascade automatically deletes all projects associated with this category

        if (mysqli_query($connect, $delete_query)) {
            $message = "<p class='php-message'>Category deleted successfully!</p>";
        } else {
            $message = "<p class='php-message'>Error deleting category: " . mysqli_error($connect) . "</p>";
        }
    } else {
        $message = "<p class='php-message'>Category not found!</p>";
    }    

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
            $category_id = $category['id'];
            $category_name = $category['categoryname'];
        ?>
        <div class='delete-category-container'>
            <p><?php echo $category_name; ?></p>
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