<?php
include_once 'connection.php';
include_once 'navigation.php';

if (!isset($_GET['p'])) {
    $message = "";
    $categories = [];

    $category_query = "SELECT * FROM category ORDER BY id ASC";
    $category_result = mysqli_query($connect, $category_query);

    if (mysqli_num_rows($category_result) == 0) {
        $message = "<p class='php-message'>No categories found.</p>";
    }

    while ($category = mysqli_fetch_assoc($category_result)) {
        $category_id = $category['id'];
        $category_name = $category['categoryname'];

        $project_query = "SELECT * FROM projects WHERE category_id = {$category_id}";
        $project_result = mysqli_query($connect, $project_query);

        $projects = [];
        while ($project = mysqli_fetch_assoc($project_result)) {
            $projects[] = $project; 
        }

        $categories[] = [
            'id' => $category_id,
            'name' => $category_name,
            'projects' => $projects
        ];
    }
}

// SUBPAGE HANDLER
elseif ($_GET['p'] == 'add_category') {
    include_once 'add_category.php';
}
elseif ($_GET['p'] == 'add_project') {
    include_once 'add_project.php';
}
else {
    echo "<h1>404 Subpage Not Found</h1>";
}
?>

<?php if (!isset($_GET['p'])): ?>
<div class="projects-container">
    <div id="projects-header">
        <h1>PROJECTS</h1>
        <div id="projects-buttons">
            <a href="?act=pj&p=add_project" class="button"><button>Add Project</button></a>
            <a href="?act=pj&p=add_category" class="button"><button>Add Category</button></a>
        </div>
    </div>
    <?php 
        if (!empty($message)) { echo $message; }
    ?>
    <?php if (!empty($categories)):
            foreach ($categories as $category): ?>
        <div class="category-container">
            <div class="category-header">
                <div class="category-title"><?php echo $category['name']; ?></div>
            </div>
            <div class="gallery">
                <?php if (empty($category['projects'])): ?>
                    <div class="additional-msg" id="no-project">NO PROJECT FOUND</div>
                <?php endif; ?>
                <?php foreach ($category['projects'] as $project): ?>
                    <div class="item">
                        <a href="<?php echo $project['link']; ?>" target="_blank">
                            <img src="media/image/uploads/<?php echo $project['thumbnail']; ?>" alt="<?php echo $project['title']; ?>" class="thumbnail">
                        </a>
                        <div class="project-title"><a href="update_project.php?id=<?php echo $project['id']; ?>"><?php echo $project['title']; ?></a></div>
                        <div class="project-description"><?php echo $project['description']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php
include_once 'footer.php';
?>