<?php
session_start();
include_once 'connection.php';
include_once 'navigation.php';

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
        $category_id = $category['id'];
        $category_name = $category['categoryname'];

        $project_query = "SELECT * FROM projects WHERE category_id = {$category_id}";
        $project_result = mysqli_query($connect, $project_query);

        $projects = [];
        while ($project = mysqli_fetch_assoc($project_result)) {
            $projects[] = $project; 
        }

        $projects_gallery[] = [
            'id' => $category_id,
            'name' => $category_name,
            'projects' => $projects
        ]; // Store category and its projects as an array in the projects_gallery array
    }
}

// SUBPAGE HANDLER
elseif ($_GET['p'] == 'add_category') {
    include_once 'add_category.php';
}
elseif ($_GET['p'] == 'add_project') {
    include_once 'add_project.php';
} elseif ($_GET['p'] == 'update_project') {
    if (isset($_GET['id'])) {
        $project_id = $_GET['id'];
        include_once 'update_project.php';
    } else {
        echo "<h1 class='php-message'>404 Project Not Found</h1>";
    }
} elseif ($_GET['p'] == 'delete_category') {
        include_once 'delete_category.php';
} else {
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
                <div class="category-title"><?php echo $category['name']; //Take the name of category and print it ?></div>
            </div>

            <div class="gallery">

                <?php if (empty($category['projects'])){ ?>
                    <div class="additional-msg" id="no-project">NO PROJECT FOUND</div>
                <?php } // If there are no projects in this category print no project found?>

                <?php // Loop through each project in the category
                foreach ($category['projects'] as $project){ ?>
                    <div class="item">
                        <a href="<?php echo $project['link']; ?>" target="_blank">
                            <img src="media/image/uploads/<?php echo $project['thumbnail']; ?>" alt="<?php echo $project['title']; ?>" class="thumbnail">
                        </a>

                        <div class="project-title">
                            <?php if (isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true) { ?>
                                <a href="?act=pj&p=update_project&id=<?php echo $project['id']; ?>">
                                    <?php echo $project['title']; ?>
                                </a>
                            <?php } else { ?>
                                <?php echo $project['title']; ?>
                            <?php } ?>
                        </div>

                        <div class="project-description"><?php echo $project['description']; ?></div>
                    </div>

                <?php } ?>
            </div>
        </div>
    <?php } ?>
    <?php } ?>
</div>
<?php } ?>

<?php
include_once 'footer.php';
?>