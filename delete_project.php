<?php
include_once 'connection.php';

function delete_project($project_id) {
    global $connect;
    $message = "";

    $check_query = "SELECT * FROM projects WHERE id = $project_id";
    $check_result = mysqli_query($connect, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {

        $delete_query = "DELETE FROM projects WHERE id = $project_id";
        
        if (mysqli_query($connect, $delete_query)) {
            $message = "<p class='php-message'>Project deleted successfully!</p>";
        } else {
            $message = "<p class='php-message'>Error deleting project: " . mysqli_error($connect) . "</p>";
        }
    } else {
        $message = "<p class='php-message'>Project not found!</p>";
    }

    return $message;
}
?>