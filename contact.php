<?php
include_once 'connection.php';

//Save contact form data to the database
$name = $email = $brandname = $message = '';
$error = '';
$success = '';

if (isset($_POST['submit-button'])) {
    $name = trim(htmlspecialchars($_POST['name']));
    $email = trim(htmlspecialchars($_POST['email']));
    $brandname = trim(htmlspecialchars($_POST['brandname']));
    $message = trim(htmlspecialchars($_POST['message']));

    if ($name === '' || $email === '' || $brandname === '' || $message === '') {
        $error = "All fields are required and cannot be just spaces.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $checkQuery = "SELECT * FROM contact_info WHERE email = '$email'";
        $checkResult = mysqli_query($connect, $checkQuery);
        if (mysqli_num_rows($checkResult) > 0) {
            $success = "Thank you, $name! Your message has been sent.";
            //the mail will be sent but the contact info won't be saved to the database
        } else {
            $query = "INSERT INTO contact_info (name, email, brandname) VALUES ('$name', '$email', '$brandname')";
            mysqli_query($connect, $query);
            $success = "Thank you, $name! Your message has been sent.";
        }
    }
    //Send email to the site owner (I turned it off because I didn't configure the PHP mail function on my local server)
    //$to = "infoheyje@gmail.com"
    //$subject = "New Contact Form Submission from $name";
    //$headers = "From: $email\r\n";
    //$message_body = "Name: $name\n Email: $email\n Brand Name: $brandname\n Message: $message";
    //mail($to, $subject, $message_body, $headers);
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
                echo "<br><p class='php-message'>$success</p>";
            }
        ?>
        <form action="index.php?act=ct" method="POST">
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
                echo "<p class='php-message'>$error</p>";
            }
        ?>
    </div>
</div>