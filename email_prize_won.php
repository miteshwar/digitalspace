<?php


function sendPrizeEmail($dealerEmail,$dealerName,$prizeName,$eventName,$prizeAmount,$prizePhoto) {
    // Your email logic here
    session_start();
    $prizeImageUrl = "https://rewards.thinkogic.com/".$prizePhoto;
    
 
    // Prepare JSON data for email
    $emailData = [
    "emailType" => "PrizeWon",
    "emailId" => $dealerEmail,
    "dealerName" => $dealerName,
    "eventName" => $eventName,
    "prizeName" => $prizeName,
    "prizeAmount" => $prizeAmount,
    "prizeImageUrl" => $prizeImageUrl
    ];
    
    // Send email using CURL
    $ch = curl_init("https://rewards.thinkogic.com/send-spin-email.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);
}

?>
