<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emailid = mysqli_real_escape_string($conn, $_POST['emailid']);

    $query = "SELECT * FROM DealerMaster WHERE EmailId = '$emailid'";
    $result = $conn->query($query);

    if (!$result) {
        die(json_encode(["success" => false, "message" => "Error executing query: " . $conn->error]));
    }

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $otp = rand(1000, 9999); // Generate a 4-digit OTP
        $_SESSION['dealer_otp'] = $otp;
        
         $updateQuery = "UPDATE DealerMaster SET OTP = '$otp' WHERE EmailId = '$emailid'";
        if (!$conn->query($updateQuery)) {
            die(json_encode(["success" => false, "message" => "Failed to update OTP: " . $conn->error]));
        }


        // Prepare JSON data for email
        $emailData = [
            "emailType" => "OTP",
            "otp" => $otp,
            "dealerName" => $user['DealerName'],
            "emailId" => $emailid
        ];

        // Send email using CURL
        $ch = curl_init("https://rewards.thinkogic.com/send-spin-email.php");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        curl_close($ch);

        // Check response
        if ($response) {
            echo json_encode(["success" => true, "message" => "OTP sent successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to send OTP"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "No Dealer found! Contact Administrator"]);
    }
}
?>
