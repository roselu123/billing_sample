<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Billing Management</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto|Varela+Round">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .table-title {
            text-align: center;
            background-color: #007bff;
            color: #fff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .back-button {
            text-align: left;
            margin-top: 20px;
            margin-bottom: 20px;
            margin-left: 20px;
        }

        .table-noborder th,
        .table-noborder td {
            border: none;
            padding: 8px; /* Adjusted padding */
            vertical-align: middle; /* Align vertically in the middle */
        }

     
    </style>
</head>
<body>

<?php
include "../db.php";
?>
<div class="container-xl">
    <div class="table-responsive">
        <div class="table-wrapper">
            <div class="back-button">
                <a href="home.php" class="btn btn-primary"><i class="fa fa-arrow-left"></i></a>
            </div>

            <div class="table-title">
                <h2><b>Patients Information</b></h2>
            </div>
            <table class="table table-noborder">
                <?php
                include "../db.php"; // Make sure this file includes your database connection

                // Process form data when form is submitted
                if (isset($_GET['user_id'])) {
                    $id = $_GET['user_id'];

                    // SQL query to fetch the data where Patient_Name matches $patientName
                    $query = "SELECT * FROM billing WHERE id = '{$id}'";
                    $view_billing = mysqli_query($conn, $query);

                    // Fetch and display the data
                    while ($row = mysqli_fetch_assoc($view_billing)) {

                        $date = $row['Date'];
                        $patient = $row['Patient_Name'];
                        $gaurantor = $row['Name_Gaurantor'];
                        $address = $row['Address'];
                        $contact = $row['Contact'];
                        $amount = $row['Amount'];
                        $due = $row['Due_Date'];
                        $collateral = $row['Collateral_Given'];
                        $c_images = explode(",", $row['Collateral_Image']); // Split the comma-separated list of filenames into an array
                        $orcr_images = explode(",", $row['OR_CR']); 
                        $titles_images = explode(",", $row['Titles']); 
                        $p_note = $row['Promissory_Note'];
                        $s_note = $row['Statement_of_Account'];
                        $r_note = $row['reciept'];
                ?>
                <tr>
                    <th>Date:</th>
                    <td><?php echo $date; ?></td>
                    <th>Due Date:</th>
                    <td><?php echo $due; ?></td>
                    <th>Amount:</th>
                    <td><?php echo $amount; ?></td>
                </tr>
                <tr>
                    <th>Patient Name:</th>
                    <td><?php echo $patient; ?></td>
                    <th>Name of Guarantor:</th>
                    <td><?php echo $gaurantor; ?></td>
                    <th>Contact No:</th>
                    <td><?php echo $contact; ?></td> 
                </tr>

                <tr>
                    <th>Address:</th>
                    <td colspan="5"><?php echo $address; ?></td>
                </tr>

                <tr>
                    <th>ID Image:</th>
                    <td colspan="5">
                        <?php
                        if (!empty($c_images)) {
                            // Display each image filename as a clickable link
                            foreach ($c_images as $c_image) {
                                // Trim the filename to remove leading and trailing spaces
                                $c_image = trim($c_image);
                                $imagePath = "collateral_images/{$c_image}"; // Construct the path to the image
                                echo "<a href='{$imagePath}' target='_blank'>{$c_image}</a><br>"; // Creating a link to view the image
                            }
                        } else {
                            echo "No Images Uploaded"; // Displaying a message if no images are uploaded
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>OR/CR Image:</th>
                    <td colspan="5">
                        <?php
                        if (!empty($orcr_images)) {
                            // Display each image filename as a clickable link
                            foreach ($orcr_images as $orcr_image) {
                                // Trim the filename to remove leading and trailing spaces
                                $orcr_image = trim($orcr_image);
                                $imagePath = "orcr_images/{$orcr_image}"; // Construct the path to the image
                                echo "<a href='{$imagePath}' target='_blank'>{$orcr_image}</a><br>"; // Creating a link to view the image
                            }
                        } else {
                            echo "No Images Uploaded"; // Displaying a message if no images are uploaded
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>TITLES Image:</th>
                    <td colspan="5">
                        <?php
                        if (!empty($titles_images)) {
                            // Display each image filename as a clickable link
                            foreach ($titles_images as $titles_image) {
                                // Trim the filename to remove leading and trailing spaces
                                $titles_image = trim($titles_image);
                                $imagePath = "titles_images/{$titles_image}"; // Construct the path to the image
                                echo "<a href='{$imagePath}' target='_blank'>{$titles_image}</a><br>"; // Creating a link to view the image
                            }
                        } else {
                            echo "No Images Uploaded"; // Displaying a message if no images are uploaded
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Promissory Note:</th>
                    <td colspan="2">
                        <?php
                        if (!empty($p_note)) {
                            $imagePath = "promissory_notes/{$p_note}"; // Constructing the path to the image
                            echo "<a href='{$imagePath}' target='_blank'>View Image</a>"; // Creating a link to view the image
                        } else {
                            echo "No Image Uploaded"; // Displaying a message if no image is uploaded
                        }
                        ?>
                    </td>
                    <th>Statement of Account:</th>
                    <td colspan="2">
                        <?php
                        if (!empty($s_note)) {
                            $imagePath = "statement_of_account/{$s_note}"; // Constructing the path to the image
                            echo "<a href='{$imagePath}' target='_blank'>View Image</a>"; // Creating a link to view the image
                        } else {
                            echo "No Image Uploaded"; // Displaying a message if no image is uploaded
                        }
                        ?>
                    </td>

                    <th>Receipt:</th>
                    <td colspan="2">
                        <?php
                        if (!empty($r_note)) {
                            echo "Filename: $r_note"; // Echoing the filename for debugging purposes
                            $imagePath = "reciepts/{$r_note}"; // Constructing the path to the image
                            echo "<a href='{$imagePath}' target='_blank'>{$r_note}</a>"; // Displaying the filename as a link
                            // Display the image itself
                            echo "<br><img src='{$imagePath}' alt='Receipt' style='max-width: 200px; max-height: 200px;'>"; // Displaying the image itself
                        } else {
                            echo "No Image Uploaded"; // Displaying a message if no image is uploaded
                        }
                        ?>
                    </td>
                </tr>
                <?php
                    }
                }
                ?>
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Financial Summary</h5>
        </div>
        <div class="card-body">
            <p><b>Amount to be Paid: </b><?php echo $amount; ?></p>
            <p><strong>Partially Paid Amount:</strong> <input type="text" name="partially_paid_amount" id="partially_paid_amount"></p>
            <p><strong>Remaining Balance:</strong> </p>
            <p><strong>Interest:</strong> 1%</p>
        </div>

    </div>
</div>

</body>
</html>
