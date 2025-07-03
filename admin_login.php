<?php
session_start();

include_once 'connection.php';
include_once 'navigation.php';
include_once 'admin_logout.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($connect, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM admin WHERE username='$username'";
    $result = mysqli_query($connect, $query);
    $admin = mysqli_fetch_assoc($result);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_loggedin'] = true;
        header("Location: index.php");
    } else {
        $message = "Invalid username or password.";
    }
}

if (isset($_POST['logout'])) {
    logout();
}
?>

<?php if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) { ?>

<div class="admin-login-container">
    <div class="admin-login-form">
        <h1>Admin Login</h1>
        <?php if (!empty($message)) { echo "<p class='additional-msg'>$message</p>"; } ?>
        <form action="" method="POST" class="form">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>
            <br>

            <label for="password">Password:</label>
            <div id="password-container" style="position: relative; display: inline-block;">
                <input type="password" name="password" id="password" required>
                <button type="button" id="toggle-password">Show</button>
            </div>

            <button type="submit" name="login">Login</button>
        </form>
    </div>
</div>
 <?php } else { ?>
    <div class="admin-login-container">
        <div id="after-login-container">
            <h1>Welcome, Admin!</h1>
            <p class="additional-msg">You are already logged in.</p>
            <form action="" method="POST" class="form">
                <button type="submit" name="logout">Logout</button>
            </form>
        </div>
    </div>
<?php } ?>

<script> // Toggle password visibility
document.getElementById("toggle-password").addEventListener("click", function () {
    const passwordInput = document.getElementById("password");
    const toggleBtn = this;
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleBtn.textContent = "Hide";
    } else {
        passwordInput.type = "password";
        toggleBtn.textContent = "Show";
    }
});
</script>

<?php include_once 'footer.php'; ?>