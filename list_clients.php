<?php
include_once 'connection.php';
include_once 'navigation.php';

$message = "";

$clients = [];

$query = "SELECT * FROM contact_info ORDER BY id DESC";
$result = mysqli_query($connect, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $clients[] = $row;
    }
} else {
    $message = "Error fetching clients: " . mysqli_error($connect);
}
?>

<?php if (isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true) { ?>
<div class="list-clients-container">
    <h1>Client Information</h1>
    <table>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Brand</th>
            </tr>
            <?php foreach ($clients as $client){ 
                $client_name = htmlspecialchars($client['name']);
                $client_email = htmlspecialchars($client['email']);
                $client_brand = htmlspecialchars($client['brandname']);
            ?>
            <tr>
                <td><?php echo $client_name; ?></td>
                <td><?php echo $client_email; ?></td>
                <td><?php echo $client_brand; ?></td>
            </tr>
    <?php } ?>
        </table>
    </div>
<?php } else { ?>
    <p class="php-message decline-message">You are not authorized to view this page.</p>
<?php } ?>

<?php
include_once 'footer.php';
?>