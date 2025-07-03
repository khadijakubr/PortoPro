<?php
include_once 'connection.php';

// Function to delete a project
// This function deletes a project and its associated thumbnail file if it exists.
function delete_project($project_id) {
    global $connect;
    $message = "";

    //delete file thumbnail if exists
    $file_query = "SELECT thumbnail FROM projects WHERE id = $project_id";
    $file_result = mysqli_query($connect, $file_query);

    if (mysqli_num_rows($file_result) > 0) {
        $file_data = mysqli_fetch_assoc($file_result);
        $thumbnail_path = "media/image/uploads/" . $file_data['thumbnail'];
        
        if (file_exists($thumbnail_path)) {
            unlink($thumbnail_path); // delete the file if exists
            $message .= "";
        } else {
            $message .= "<p class='php-message'>Thumbnail file does not exist.</p><br>";
        }
    }

    //delete data from database
    $check_query = "SELECT * FROM projects WHERE id = $project_id";
    $check_result = mysqli_query($connect, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {

        $delete_query = "DELETE FROM projects WHERE id = $project_id";
        
        if (mysqli_query($connect, $delete_query)) {
            $message .= "<p class='php-message'>Project deleted successfully!</p>";
        } else {
            $message .= "<p class='php-message'>Error deleting project: " . mysqli_error($connect) . "</p>";
        }
    } else {
        $message = "<p class='php-message'>Project not found!</p>";
    }

    return $message;
}
?>
