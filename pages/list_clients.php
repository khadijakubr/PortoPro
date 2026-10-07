<?php
// ============================================================================
// [RENDER-FIX 7 lanjutan] list_clients.php
// SEBELUM  : include connection.php mentah + echo htmlspecialchars per-baris
//           (sudah benar, dipertahankan) tapi tanpa bootstrap (session/cookie
//           tidak aman bila diakses langsung).
// SESUDAH : lewat bootstrap.php; logika tampil tetap sama.
// ============================================================================
require_once __DIR__ . '/../config/bootstrap.php';
// [RESTRUKTUR] navigation/footer pindah ke includes/.
include_once __DIR__ . '/../includes/navigation.php';

$message = "";

$clients = [];

$query = "SELECT * FROM contact_info ORDER BY id DESC";
$result = mysqli_query($connect, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $clients[] = $row;
    }
} else {
    error_log('[PortoPro] fetch clients gagal: ' . mysqli_error($connect));
    $message = "Error fetching clients. Try again.";
}
?>

<?php if (isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true) { ?>
<div class="list-clients-container">
    <h1>Client Information</h1>
    <?php if ($message !== '') { echo "<p class='php-message'>" . e($message) . "</p>"; } ?>
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
// [RESTRUKTUR] footer pindah ke includes/.
include_once __DIR__ . '/../includes/footer.php';
?>
