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
        include_once __DIR__ . '/includes/navigation.php';
        ?>
    </div>
    <div class="main">
        <?php
        if (!isset($_GET['act'])) {
            include_once __DIR__ . '/pages/home.php';
        } elseif ($_GET['act'] == 'ct') {
            include_once __DIR__ . '/pages/contact.php';
        } elseif ($_GET['act'] == 'pj') {
            include_once __DIR__ . '/pages/project.php';
        } elseif ($_GET['act'] == 'lgn') {
            include_once __DIR__ . '/admin/admin_login.php';
        } else {
            echo "<h1>404 Not Found</h1>";
            echo "<p>The page you are looking for does not exist.</p>";
        }
        ?>
    </div>
    <div class="footer">
        <?php
        include_once __DIR__ . '/includes/footer.php';
        ?>
    </div>
</body>
</html>