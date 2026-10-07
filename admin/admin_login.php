<?php
// ============================================================================
// [RENDER-FIX 7 lanjutan] admin_login.php
// ----------------------------------------------------------------------------
// SEBELUM  : "SELECT * FROM admin WHERE username='$username'" (SQLi) +
//           header("Location: index.php") tanpa exit + footer.php di-include
//           dua kali (sekali di sini baris 84, sekali di index.php:33).
// SESUDAH : prepared + exit setelah header + tanpa footer ganda (index.php
//           yang menampilkan footer) + pesan error di-escape.
// ALASAN  : halaman ini di-include DI DALAM index.php yang sudah punya
//           <div class="footer">, jadi include footer di sini bikin footer
//           dobel di production.
// ============================================================================
require_once __DIR__ . '/../config/bootstrap.php';
// [RESTRUKTUR] navigation → includes/, admin_logout tetap satu folder.
include_once __DIR__ . '/../includes/navigation.php';
include_once __DIR__ . '/admin_logout.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // SEBELUM: mysqli_real_escape_string + interpolasi (lolos untuk pola
    // tertentu). SESUDAH: prepared statement penuh.
    $stmt = $connect->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();

    if ($admin && password_verify($password, $admin['password'])) {
        // Anti session-fixation: ID session baru setelah login sukses.
        session_regenerate_id(true);
        $_SESSION['admin_loggedin'] = true;
        header("Location: index.php");
        exit; // SEBELUM: tanpa exit → sisa halaman login tetap dirender setelah redirect.
    } else {
        $message = "Invalid username or password.";
    }
}

if (isset($_POST['logout'])) {
    logout(); // logout() sudah exit() setelah header
}
?>

<?php if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) { ?>

<div class="admin-login-container">
    <div class="admin-login-form">
        <h1>Admin Login</h1>
        <?php if (!empty($message)) { echo "<p class='additional-msg'>" . e($message) . "</p>"; } ?>
        <form action="" method="POST" class="form">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required autocomplete="username">
            <br>

            <label for="password">Password:</label>
            <div id="password-container" style="position: relative; display: inline-block;">
                <input type="password" name="password" id="password" required autocomplete="current-password">
                <button type="button" id="toggle-password">Show</button>
            </div>

            <button type="submit" name="login">Login</button>
        </form>
    </div>
</div>
<?php } else {
            if (isset($_GET['p']) && $_GET['p'] == 'list_clients') {
                // [RESTRUKTUR] list_clients pindah ke pages/.
                include_once __DIR__ . '/../pages/list_clients.php';
            } else {
?>
    <div class="admin-login-container">
        <div id="after-login-container">
            <h1>Welcome, Admin!</h1>
            <p class="additional-msg">You are already logged in.</p>
            <div id="after-login-btn-container">
                <a href="?act=lgn&p=list_clients" class="button"><button>List Clients</button></a>
                <form action="" method="POST" class="form">
                    <button type="submit" name="logout">Logout</button>
                </form>
            </div>
        </div>
    </div>
<?php }
} ?>

<script> // Toggle password visibility
document.getElementById("toggle-password") && document.getElementById("toggle-password").addEventListener("click", function () {
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

<?php
// SEBELUM: include_once 'footer.php'; di sini. SESUDAH: dihapus.
// ALASAN: index.php sudah merender footer; include di sini = footer dobel.
?>
