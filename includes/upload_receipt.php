<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    

<?php
include "../db.php";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if a file was uploaded without errors
    if (isset($_FILES["reciept"]) && $_FILES["reciept"]["error"] == UPLOAD_ERR_OK) {
        $notificationId = $_POST["notification_id"];
        $fileName = $_FILES["reciept"]["name"];
        $fileTmpName = $_FILES["reciept"]["tmp_name"];

        // Set the target directory to save uploaded receipts
        $targetDir = "receipts/";
        // Generate a unique filename to avoid overwriting existing files
        $uniqueFilename = uniqid() . '_' . $fileName;
        $targetFilePath = $targetDir . $uniqueFilename;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($fileTmpName, $targetFilePath)) {
            // File upload success, update the database with the filename
            // Assuming you have an active database connection $conn

            $updateQuery = "UPDATE billing SET reciept = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $updateQuery);
            mysqli_stmt_bind_param($stmt, "si", $uniqueFilename, $notificationId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            echo "Receipt uploaded successfully.";
            // Echo out the close button HTML with updated onclick function
            echo '<button id="closeButton" type="button" class="btn btn-secondary">Close</button>';
        } else {
            echo "Error uploading receipt.";
        }
    } else {
        echo "No file uploaded or an error occurred during upload.";
    }
} else {
    echo "Invalid request.";
}
?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var closeButton = document.getElementById('closeButton');
    if (closeButton) {
        closeButton.addEventListener('click', function() {
            // Redirect the user to the notification.php page
            window.location.href = 'notification.php';
        });
    }

    // Disable submit receipt button
    var submitButton = document.getElementById('submitReceiptButton');
    if (submitButton) {
        submitButton.disabled = true;
    }
});
</script>

</body>
</html>
