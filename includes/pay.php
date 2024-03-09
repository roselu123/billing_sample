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
        .card {
            max-width: 600px;
            margin: 0 auto;
            margin-top: 50px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="back-button mt-5">
        <a href="home.php" class="btn btn-primary"><i class="fa fa-arrow-left"></i></a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Financial Summary</h5>
                </div>
                <div class="card-body">
                    <?php
                    include "../db.php";

                    if (isset($_GET['user_id'])) {
                        $id = $_GET['user_id'];

                        $query = "SELECT * FROM billing WHERE id = '{$id}'";
                        $view_billing = mysqli_query($conn, $query);

                        while ($row = mysqli_fetch_assoc($view_billing)) {
                            $amount = $row['Amount'];
                            // You can calculate the remaining balance here based on the partially paid amount
                            $partially_paid_amount = isset($_POST['partially_paid_amount']) ? $_POST['partially_paid_amount'] : 0;
                            $remaining_balance = $amount - $partially_paid_amount;
                    ?>
                    <form method="post" action="">
                        <h4><b>Amount to be Paid: </b><?php echo $amount; ?></h4><br>
                        <p><strong>Partially Paid Amount:</strong> <input type="text" name="partially_paid_amount" id="partially_paid_amount" value="<?php echo isset($_POST['partially_paid_amount']) ? $_POST['partially_paid_amount'] : ''; ?>" <?php if ($remaining_balance == 0) echo 'disabled'; ?>></p>
                        <p><strong>Remaining Balance:</strong> <span id="remaining_balance"><?php echo $remaining_balance; ?></span></p>
                        <p><strong>Interest:</strong> 1%</p>
                        <?php if ($remaining_balance != 0 && $partially_paid_amount != 0) { ?>
                            <p><strong>Pay Remaining Balance:</strong> <input type="text" name="remaining_paid_amount" id="remaining_paid_amount" oninput="updateRemainingBalance()"></p>
                        <?php } ?>
                        <?php if ($remaining_balance != 0) { ?>
                            <input type="submit" value="Save Remaining Balance" class="btn btn-primary" id="save_remaining_button">
                        <?php } ?>
                    </form>

                    <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Payment History</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Display partially paid amount inputted here
                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                        $partially_paid_amount = isset($_POST['partially_paid_amount']) ? $_POST['partially_paid_amount'] : 0;
                        echo "<p>Partially Paid Amount: $partially_paid_amount</p>";
                    }
                    ?>
                    <!-- You can add more payment history information here if needed -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Function to hide the button after the form submission
    document.addEventListener('DOMContentLoaded', function () {
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST") { ?>
            document.getElementById('save_remaining_button').style.display = 'none';
            document.getElementById('partially_paid_amount').disabled = true;
        <?php } ?>
        
        document.getElementById('save_remaining_button').addEventListener('click', function() {
            this.style.display = 'none';
        });
    });
    
    // Function to update the remaining balance when the "Pay Remaining Balance" input changes
    function updateRemainingBalance() {
        // Get the amount to be paid
        var amountToBePaid = <?php echo $amount; ?>;
        
        // Get the partially paid amount entered by the user
        var partiallyPaidAmount = parseFloat(document.getElementById("partially_paid_amount").value);
        
        // Get the pay remaining balance input
        var remainingPaidAmount = parseFloat(document.getElementById("remaining_paid_amount").value);
        
        // Calculate the remaining balance
        var remainingBalance = amountToBePaid - partiallyPaidAmount - remainingPaidAmount;
        
        // Update the remaining balance displayed on the page
        document.getElementById("remaining_balance").textContent = remainingBalance;
    }
</script>

</body>
</html>
