<?php
include_once 'connection.php';
include_once 'navigation.php';

$message = "";

if (isset($_POST['submit'])) {
    // Get the new category name from the form
    if (trim($_POST['name']) === "") {
        $message = "<p class='php-message'> Please enter a category name!</p>";
    } else {
        $name = trim(htmlspecialchars($_POST['name']));
    }

    // Check if the category already exists
    $insert = "INSERT INTO category (categoryname) VALUES (UPPER('$name'))";
    $check_query = "SELECT * FROM category WHERE categoryname = UPPER('$name')";
    $check_result = mysqli_query($connect, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $message = "<p class='php-message'> Category already exists!</p>";
    } else {
        if (mysqli_query($connect, $insert)) {
            $message = "<p class='php-message'> Category added!</p>";
        } else {
            $message = "<p class='php-message'> Error adding category!</p>";
        }
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

<?php include_once 'footer.php'; ?>