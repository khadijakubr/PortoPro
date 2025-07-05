<?php
session_start();
?>

<div class="navbar">
    <div class="navbar-name">
        <a href="index.php">Khadijatul K.</a>
    </div>
    <div>
         <ul class="navbar-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php?act=pj">Projects</a></li>
            <?php if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {?>
                <li><a href="index.php?act=ct">Contact</a></li>
            <?php }; ?>
            <li><a href="index.php?act=lgn">
                <?php echo (isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true) ? 'Logout' : 'Login'; ?>
            </a></li>
        </ul>
    </div>
</div>
