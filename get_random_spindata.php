<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Include database connection file
include('db_connect.php');

// Set the header to return JSON
header('Content-Type: application/json');

// Directly get the parameters from the query string
$dealerId = intval($_GET['dealerid']); 
$eventId = intval($_GET['eventId']); 
$zoneId = intval($_GET['zoneId']); 

// Check if the dealer exists in DealerMaster
// $dealerCheckQuery = "SELECT DealerId FROM DealerMaster WHERE DealerId = $dealerId";
// $dealerCheckResult = $conn->query($dealerCheckQuery);

// if ($dealerCheckResult->num_rows === 0) {
//     echo json_encode(["status" => "error", "message" => "Invalid user"]);
//     exit;
// }


$sql = "
    SELECT D.DealerRandomPrizePositionId,D.PrizeId, P.PrizeName,D.Position
    FROM DealerRandomPrizePosition D
    INNER JOIN Prizes P ON D.PrizeId = P.PrizeId
    WHERE D.Position = (SELECT SpinCount+1 From DealerRandomPrizes WHERE ZoneId = $zoneId AND EventId = $eventId) 
    AND D.ZoneId = $zoneId AND D.EventId = $eventId
";

// Execute the query
$result = $conn->query($sql);

// Initialize response array
$response = array();

// If query returns rows
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    $dealerRandomPrizePositionId = $row['DealerRandomPrizePositionId'];
    $prizeId = $row['PrizeId'];
    
    $query1 = "UPDATE DealerRandomPrizePosition set IsAllocated =1 WHERE DealerRandomPrizePositionId='$dealerRandomPrizePositionId'";
    $conn->query($query1);
    
    $query2 = "UPDATE `DealerEventDetails` SET SpinTypeId=1,AttemptNo1=(PlayedSpinAttempts+1),PrizeId1='$prizeId' WHERE DealerId ='$dealerId' and EventId ='$eventId'";
    $conn->query($query2);
    
    
    
    // $query3 = "UPDATE `EventZonePrize` SET AllocatedPrizes = AllocatedPrizes+1 where EventId='$eventId' and ZoneId ='$zoneId' and PrizeId ='$prizeId'";
    // $conn->query($query3);
    
    $query4 = "UPDATE `DealerRandomPrizes` SET SpinCount = SpinCount+1 WHERE EventId ='$eventId' and ZoneId ='$zoneId'";
    $conn->query($query4);
    

    
    echo json_encode([
        "status" => "success",
        "data" => [
            "PrizeName" => $row['PrizeName']
        ]
    ]);
} else {
    // Return error if no prize found
    $query4 = "UPDATE `DealerRandomPrizes` SET SpinCount = SpinCount+1 WHERE EventId ='$eventId' and ZoneId ='$zoneId'";
    $conn->query($query4);
    echo json_encode(["status" => "error", "message" => "Prize not found"]);
}

// Close the result set
$result->close();

// Close the database connection
$conn->close();

?>
