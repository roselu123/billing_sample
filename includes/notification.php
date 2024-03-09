
<?php
include "../db.php";

// Mark notification as read if it has been viewed
if(isset($_GET['notification_clicked'])) {
    $currentDate = date('Y-m-d');
    // Update notification_read to 1 for notifications with Due_Date equal to current date in the billing table
    $update_query_billing = "UPDATE billing SET notification_read = 1 WHERE Due_Date = ?";
    $stmt = mysqli_prepare($conn, $update_query_billing);
    mysqli_stmt_bind_param($stmt, "s", $currentDate);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

$currentDate = date('Y-m-d');

// Check if the due date has been edited and is not due today, then remove from notifications
$edited_due_date_query = "SELECT id FROM billing WHERE Due_Date != ? AND notification_read = 0";
$stmt = mysqli_prepare($conn, $edited_due_date_query);
mysqli_stmt_bind_param($stmt, "s", $currentDate);
mysqli_stmt_execute($stmt);
$edited_due_date_result = mysqli_stmt_get_result($stmt);
while ($edited_row = mysqli_fetch_assoc($edited_due_date_result)) {
    $edited_id = $edited_row['id'];
    // Remove notifications from notif table where Due_Date has been edited and is not due today
    $remove_query_notif = "DELETE FROM notif WHERE id = ?";
    $stmt = mysqli_prepare($conn, $remove_query_notif);
    mysqli_stmt_bind_param($stmt, "i", $edited_id);
    mysqli_stmt_execute($stmt);
}
mysqli_stmt_close($stmt);

// Calculate two days ahead of the current date
$twoDaysAhead = date('Y-m-d', strtotime($currentDate . '+2 days'));

// Fetch notifications from billing table where the due date is two days ahead of the current date
$query_billing = "SELECT id, Patient_Name, Amount, Due_Date
                FROM billing 
                WHERE Due_Date = ? AND notification_read = 0";
$stmt = mysqli_prepare($conn, $query_billing);
mysqli_stmt_bind_param($stmt, "s", $twoDaysAhead);
mysqli_stmt_execute($stmt);
$result_billing = mysqli_stmt_get_result($stmt);

// Insert retrieved data into the notif table for notifications due two days ahead
while ($row = mysqli_fetch_assoc($result_billing)) {
    $patientName = $row['Patient_Name'];
    $dueDate = $row['Due_Date'];
    
    // Check if a notification for the patient already exists in the notif table
    $existingNotificationQuery = "SELECT id FROM notif WHERE Patient_Name = ? AND Due_Date = ?";
    $stmt = mysqli_prepare($conn, $existingNotificationQuery);
    mysqli_stmt_bind_param($stmt, "ss", $patientName, $dueDate);
    mysqli_stmt_execute($stmt);
    $existingNotificationResult = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($existingNotificationResult) > 0) {
        // Update existing notification instead of inserting a new one
        $updateQuery = "UPDATE notif SET Amount = ?, no_of_notifications = no_of_notifications + 1 WHERE Patient_Name = ? AND Due_Date = ?";
        $stmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmt, "dss", $row['Amount'], $patientName, $dueDate);
        mysqli_stmt_execute($stmt);
    } else {
        // Insert a new notification if it doesn't already exist
        $insertQuery = "INSERT INTO notif (Patient_Name, Amount, Due_Date, no_of_notifications, notification_read) 
                        VALUES (?, ?, ?, 1, 0)";
        $stmt = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($stmt, "sds", $patientName, $row['Amount'], $dueDate);
        mysqli_stmt_execute($stmt);
    }
}
mysqli_stmt_close($stmt);

// Fetch notifications from notif table for display
$query_notif = "SELECT id, Patient_Name, Amount, Due_Date, paid 
                FROM notif 
                WHERE Due_Date = ? AND notification_read = 0";
$stmt = mysqli_prepare($conn, $query_notif);
mysqli_stmt_bind_param($stmt, "s", $twoDaysAhead);
mysqli_stmt_execute($stmt);
$result_notif = mysqli_stmt_get_result($stmt);

// Handle receipt upload for full payment
if (isset($_POST['notification_id']) && isset($_FILES['receipt'])) {
    $notification_id = $_POST['notification_id'];
    $file_name = $_FILES['receipt']['name'];
    $file_tmp = $_FILES['receipt']['tmp_name'];
    $file_type = $_FILES['receipt']['type'];
    $file_ext = strtolower(end(explode('.', $_FILES['receipt']['name'])));
    $extensions = array("jpeg", "jpg", "png");

    if (in_array($file_ext, $extensions) === false) {
        echo "extension not allowed, please choose a JPEG or PNG file.";
    } else {
        move_uploaded_file($file_tmp, "../receipts/" . $file_name);
        // Update the database with the receipt filename
        $update_query = "UPDATE billing SET receipt = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "si", $file_name, $notification_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo "Receipt uploaded successfully.";
    }
}

// Handle receipt upload for partial payment
if (isset($_POST['id']) && isset($_FILES['receipt'])) {
    $notification_id = $_POST['id'];
    $file_name = $_FILES['receipt']['name'];
    $file_tmp = $_FILES['receipt']['tmp_name'];
    $file_type = $_FILES['receipt']['type'];
    $file_ext = strtolower(end(explode('.', $_FILES['receipt']['name'])));
    $extensions = array("jpeg", "jpg", "png");

    if (in_array($file_ext, $extensions) === false) {
        echo "extension not allowed, please choose a JPEG or PNG file.";
    } else {
        move_uploaded_file($file_tmp, "../partial_receipt/" . $file_name);
        // Update the database with the receipt filename
        $update_query = "UPDATE billing SET receipt = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "si", $file_name, $notification_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo "Partial payment receipt uploaded successfully.";
    }
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="notif.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css">
    <title>Notifications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding-top: 10px; /* Adjusted padding */
            padding-bottom: 10px; /* Adjusted padding */
        }
        .section-50 {
            padding: 0 10px; /* Adjusted padding */
        }
        .heading-line {
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px; /* Reduced padding */
            margin-bottom: 20px; /* Reduced margin */
            margin-top: 20px; /* Added margin-top */
            text-align: center;
            font-size: 20px; /* Reduced font size */
        }
        .notification-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px; /* Reduced padding */
            margin-left: 50px;
            margin-bottom: 10px; /* Reduced margin */
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1); /* Reduced shadow */
            width: 600px;
            position: relative; /* Added position relative */
        }
        .notification-card h5 {
            margin-top: 0;
            margin-bottom: 5px; /* Reduced margin */
            color: #007bff;
            font-size: 16px; /* Reduced font size */
        }
        .notification-card p {
            margin-top: 0;
            margin-bottom: 5px; /* Reduced margin */
            font-size: 14px; /* Reduced font size */
            line-height: 1.3; /* Reduced line height */
        }
        .btn-success {
            padding: 5px 10px; /* Reduced padding */
            font-size: 14px; /* Reduced font size */
        }
        .btn-warning {
            float:right;
        }
        .paid-message {
            margin-top: 0;
            margin-bottom: 5px; /* Reduced margin */
            color: green;
            font-size: 14px; /* Reduced font size */
        }
        .close-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <section class="section-50">
        <div class="container">
            <h3 class="m-b-50 heading-line">Notifications</h3>
            <div class="row">
                <div class="col-md-12">
                    <div class="notification-ui_dd-content">
                        <?php
                        if ($result_notif && mysqli_num_rows($result_notif) > 0) {
                            while ($row = mysqli_fetch_assoc($result_notif)) {
                                $id = $row['id'];
                                $name = $row['Patient_Name'];
                                $payment = $row['Amount'];
                                $dueDate = $row['Due_Date'];

                                echo "<div class='notification-card' data-id='{$id}'>";
                                echo "<span class='close-btn' onclick='removeNotification($id)'>&times;</span>"; // Close button
                                echo "<h5>Patient Name: {$name}</h5>";
                                echo "<p>Payment Amount: {$payment}</p>";
                                echo "<p>Due Date: {$dueDate}</p>";
                                echo "<div class='text-center' style='justify-content: space-between; display: flex;'>";
                               // Inside the while loop generating notification cards
                        // Inside the while loop generating notification cards

                                    // Add a Partial Payment modal
                echo "<div class='modal fade' id='partialPaymentModal{$id}' tabindex='-1' role='dialog' aria-labelledby='partialPaymentModalLabel{$id}' aria-hidden='true'>";
                echo "<div class='modal-dialog' role='document'>";
                echo "<div class='modal-content'>";
                echo "<div class='modal-header'>";
                echo "<h5 class='modal-title' id='partialPaymentModalLabel{$id}'>Partial Payment</h5>";
                echo "<button type='button' class='close' data-dismiss='modal' aria-label='Close'>";
                echo "<span aria-hidden='true'>&times;</span>";
                echo "</button>";
                echo "</div>";
                echo "<div class='modal-body'>";
                echo "<p>Amount to be Paid: <span id='amountToBePaid{$id}'>$payment</span></p>";
                echo "<label for='partialAmount'>Enter Partial Amount:</label>";
                echo "<input type='number' id='partialAmount{$id}' class='form-control' placeholder='Enter partial amount'>";
                echo "<p>Remaining Balance: <span id='remainingB{$id}'></span></p>";
                echo "<label for='receiptUpload'>Upload Receipt:</label>";
                echo "<input type='file' id='receiptUpload{$id}' class='form-control-file'>";
                echo "</div>";
                echo "<div class='modal-footer'>";
               
                echo "<button type='button' class='btn btn-primary' onclick='submitPartialPayment($id)'>Submit</button>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
                echo "</div>";

                

                        if ($row['paid'] == 0) {
                            echo "<button type='button' class='btn btn-success' data-id='{$id}' onclick='markAsPaid($id)'>Fully Paid</button>";
                            echo "<button  type='button' class='btn btn-warning' data-id='{$id}' data-payment='{$payment}' onclick='showPartialPaymentModal($id)'>Partial Payment</button>";
                        } else {
                            echo "<p class='paid-message'>Patient has already paid.</p>";
                            // Hide the notification card if the patient has paid
                            echo "<script>document.querySelector(`div[data-id='${id}']`).style.display = 'none';</script>";
                        }

                                echo "</div>";
                                echo "</div>";
                            }
                        } else {
                            echo "<p>No notifications to display.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

 <!-- Modal -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Successfully Paid</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Payment has been successfully processed.
                <form action="upload_receipt.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="receipt">Upload Receipt Image:</label>
                        <input type="file" class="form-control-file" id="receipt" name="reciept">
                    </div>
                    <input type="hidden" name="notification_id" id="notification_id"><br>
                    <button type="submit" class="btn btn-primary" onclick="submitReceipt()">Submit Receipt</button>
                </form>
            </div>
            
        </div>
    </div>
</div>




    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.bundle.min.js"></script>
    <script>
    function markAsPaid(id) {
        id = parseInt(id);
        var button = document.querySelector(`button[data-id='${id}']`);
        if (button) {
            button.disabled = true;
            $('#successModal').modal('show');
            $('.modal-body').attr('data-notification-id', id);
        } else {
            console.error('Button element not found');
        }
    }

    function showPartialPaymentModal(id) {
    $('#partialPaymentModal' + id).modal('show');
}

function calculateRemainingBalance(id) {
    var partialAmount = parseFloat($('#partialAmount' + id).val());
    var totalAmount = parseFloat($('#amountToBePaid' + id).text());
    if (!isNaN(partialAmount) && partialAmount > 0) {
        var remainingBalance = totalAmount - partialAmount;
        $('#remainingB' + id).text(remainingBalance.toFixed(2)); // Update the remaining balance display
    }
}

    function submitPartialPayment(id) {
        var partialAmount = parseFloat($('#partialAmount' + id).val());
        if (!isNaN(partialAmount) && partialAmount > 0) {
            // Send AJAX request to update the partial payment in the database
            $.ajax({
                url: 'notification.php', // Use the same file for handling partial payments
                method: 'POST',
                data: { id: id, partialAmount: partialAmount },
                success: function(response) {
                    console.log('Partial payment submitted successfully.');
                    // Close the modal
                    $('#partialPaymentModal' + id).modal('hide');
                    // Redirect to the view page of the specific patient who paid
                    window.location.href = 'home.php?user_id=' + id; // Pass the ID to view.php
                },
                error: function(xhr, status, error) {
                    console.error('Error submitting partial payment:', error);
                }
            });
        } else {
            alert('Please enter a valid partial amount.');
        }
    }

    function submitReceipt() {
    $('form').submit(); // Submit the form
}

    // Add event listener to calculate remaining balance on input change
    $(document).on('input', 'input[id^="partialAmount"]', function() {
        var id = $(this).attr('id').replace('partialAmount', '');
        calculateRemainingBalance(id);
    });


    function closeModalAndRemoveNotification() {
        $('#successModal').modal('hide');
        var id = parseInt($('.modal-body').data('notification-id'));
        var notificationCard = document.querySelector(`div[data-id='${id}']`);
        if (notificationCard) {
            notificationCard.remove();
            // Send AJAX request to update the database
            $.ajax({
                url: 'action.php', // Replace with the file path to your PHP script for updating the database
                method: 'POST',
                data: { id: id }, // Pass the ID of the notification to be removed
                success: function(response) {
                    console.log('Notification removed from the database.');
                    // Remove the notification card from the UI
                    notificationCard.remove();
                },
                error: function(xhr, status, error) {
                    console.error('Error removing notification from the database:', error);
                }
            });
        }
    }

    function removeNotification(id) {
        var notificationCard = document.querySelector(`div[data-id='${id}']`);
        if (notificationCard) {
            notificationCard.remove();
            // Send AJAX request to update the database
            $.ajax({
                url: 'action.php', // Replace with the file path to your PHP script for updating the database
                method: 'POST',
                data: { id: id }, // Pass the ID of the notification to be removed
                success: function(response) {
                    console.log('Notification removed from the database.');
                },
                error: function(xhr, status, error) {
                    console.error('Error removing notification from the database:', error);
                }
            });
        }
    }
    </script>
    
</body>
</html>