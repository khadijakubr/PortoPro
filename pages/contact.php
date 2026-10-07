<?php
// ============================================================================
// [RENDER-FIX 7] contact.php — prepared statement + escape
// ----------------------------------------------------------------------------
// SEBELUM  : "SELECT * FROM contact_info WHERE email = '$email'" dan
//           "INSERT ... VALUES ('$name','$email','$brandname')" — email/nama
//           dari pengunjung masuk mentah ke SQL (SQLi). $success echo $name
//           mentah (XSS). htmlspecialchars dipakai saat SIMPAN ($message ikut
//           hilang karena tidak ada kolom message di DB).
// SESUDAH : prepared statements; simpan mentah + escape saat tampil;
//           pesan error DB masuk log, bukan ke layar.
// ============================================================================
require_once __DIR__ . '/../config/bootstrap.php';

//Save contact form data to the database
$name = $email = $brandname = $message = '';
$error = '';
$success = '';

// Check if the form is submitted
if (isset($_POST['submit-button'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $brandname = trim($_POST['brandname'] ?? '');
    $message = trim($_POST['message'] ?? '');
    // Validate the input
    if ($name === '' || $email === '' || $brandname === '' || $message === '') {
        $error = "All fields are required and cannot be just spaces.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // SEBELUM: query interpolasi. SESUDAH: prepared. ALASAN: $email dari
        // user; pola "' OR '1'='1" sebelumnya bisa membocorkan/memanipulasi DB.
        $stmt = $connect->prepare("SELECT id FROM contact_info WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $checkResult = $stmt->get_result();
        if ($checkResult->num_rows > 0) {
            $success = "Thank you, $name! Your message has been sent.";
            //the mail will be sent but the contact info won't be saved to the database
        } else {
            $ins = $connect->prepare("INSERT INTO contact_info (name, email, brandname) VALUES (?, ?, ?)");
            $ins->bind_param("sss", $name, $email, $brandname);
            if ($ins->execute()) {
                $success = "Thank you, $name! Your message has been sent.";
            } else {
                error_log('[PortoPro] contact insert gagal: ' . $connect->error);
                $error = "Something went wrong. Please try again.";
            }
            $ins->close();
        }
        $stmt->close();
    }
    //Send email to the site owner (I turned it off because I didn't configure the PHP mail function on my local server)
    //$to = "infoheyje@gmail.com"
    //$subject = "New Contact Form Submission from $name";
    //$headers = "From: $email\r\n";
    //$message_body = "Name: $name\n Email: $email\n Brand Name: $brandname\n Message: $message";
    //mail($to, $subject, $message_body, $headers);
    // CATATAN RENDER: fungsi mail() bawaan tidak jalan di Render. Bila ingin
    // email aktif: pakai API (Resend/Brevo) via curl di blok ini.
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>PORTFOLIO</title>
</head>
<div class="contact">
    <div class="contact-info">
        <h1 id="contact-title">Contact Me</h1>
        <p id="contact-description">If you'd like to get in touch, feel free to reach out!</p>
        <div class="contact-buttons">
        <button><a href="mailto:infoheyje@gmail.com">Email Me</a></button>
        <button><a href="https://www.linkedin.com/in/khadijatul-kubro-61b946304/">LinkedIn</a></button>
    </div>
    <div class="contact-form">
        <h4>Or just fill out the form below:</h4>
        <?php
            if (!empty($success)) {
                // SEBELUM: echo $success mentah ($name user ikut → XSS).
                // SESUDAH: e() saat tampil. ALASAN: nama bisa berisi <script>.
                echo "<br><p class='php-message'>" . e($success) . "</p>";
            }
        ?>
        <form action="index.php?act=ct" method="POST" class="form">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
            <br>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <br>
            <label for="brandname"> Brand Name:</label>
            <input type="text" id="brandname" name="brandname" required>
            <br>
            <label for="message">Message:</label>
            <textarea id="message" name="message" rows="4" required></textarea>
            <br>
            <button type="submit" name="submit-button">Send Message</button>
        </form>
        <?php
            if (!empty($error)) {
                echo "<p class='php-message'>" . e($error) . "</p>";
            }
        ?>
    </div>
</div>
