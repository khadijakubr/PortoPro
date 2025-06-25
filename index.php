<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>PORTFOLIO</title>
</head>
<body>
    <div class="navbar">
        <?php 
         include_once 'navigation.php';
        ?>
    </div>
    <div class="main">
        <?php 
        if (!isset($_GET['act'])) {
            include_once 'home.php';
        } elseif ($_GET['act'] == 'ct') {
            include_once 'contact.php';
        } elseif ($_GET['act'] == 'pj') {
            include_once 'project.php';
        } else {
            echo "<h1>404 Not Found</h1>";
            echo "<p>The page you are looking for does not exist.</p>";
        }
        ?>
    </div>
    <div class="footer">
        <?php 
        include_once 'footer.php';
        ?>
    </div>
</body>
</html>