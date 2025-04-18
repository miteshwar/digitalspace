<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Include database connection file
include('db_connect.php');

// Set the header to return JSON
header('Content-Type: application/json');

// Check if dealerid is passed as a GET parameter
if (isset($_GET['dealerid'])) {
    $dealerid = intval($_GET['dealerid']); // Sanitize input to avoid SQL injection
    $addSpincount = intval($_GET['addSpincount']); // Sanitize input to avoid SQL injection

    // Increment PlayedSpinAttempts
    $updateAttemptsQuery = "UPDATE DealerEventDetails SET PlayedSpinAttempts = PlayedSpinAttempts + 1 WHERE DealerId = ? and PlayedSpinAttempts < NoOfCoupons";
    $attemptsStmt = $conn->prepare($updateAttemptsQuery);
    $attemptsStmt->bind_param("i", $dealerid);

    if ($attemptsStmt->execute()) {
        // Add additional insert statement
        $fetchEventQuery = "SELECT EventId, PlayedSpinAttempts, AttemptNo1, DefaultAllocated,ZoneId,SpinTypeId,
                            (CASE WHEN (AttemptNo1 = PlayedSpinAttempts) THEN PrizeId1 ELSE 0 END) as PrizeId 
                            FROM DealerEventDetails 
                            WHERE DealerId = ?";
        $fetchEventStmt = $conn->prepare($fetchEventQuery);
        $fetchEventStmt->bind_param("i", $dealerid);
        $fetchEventStmt->execute();
        $eventResult = $fetchEventStmt->get_result();

        if ($eventResult->num_rows > 0) {
            $eventData = $eventResult->fetch_assoc();
            $eventId = $eventData['EventId'];
            $playedSpinAttempts = $eventData['PlayedSpinAttempts'];
            $prizeId  = $eventData['PrizeId'];
            $defaultAllocated  = $eventData['DefaultAllocated'];
            $zoneId  = $eventData['ZoneId'];
            $spinTypeId  = $eventData['SpinTypeId'];

            // Insert data into DealerAttemptDetails table
            $insertAttemptQuery = "INSERT INTO DealerAttemptDetails (DealerId, EventId, AttemptNo, PrizeId, AttemptDateTime) 
                                   VALUES (?, ?, ?, ?, NOW())";
            $insertStmt = $conn->prepare($insertAttemptQuery);
            $insertStmt->bind_param("iiii", $dealerid, $eventId, $playedSpinAttempts, $prizeId);
            $insertStmt->execute();
            $insertStmt->close();
            
            if($defaultAllocated==0 && $spinTypeId==1)
            {
                if($addSpincount==1)
                {
                    $query4 = "UPDATE `DealerRandomPrizes` SET SpinCount = SpinCount+1 WHERE EventId ='$eventId' and ZoneId ='$zoneId'";
                    $conn->query($query4);
                    
                    $query7 = "SELECT * FROM `DealerRandomPrizePosition` Where ZoneId = '$zoneId' and EventId ='$eventId' and Position = (
                                SELECT SpinCount FROM `DealerRandomPrizes` Where ZoneId = '$zoneId' and EventId ='$eventId' );";
                    $result7 = $conn->query($query7);
                    
                    
                    
                    // If query returns rows
                    if ($result7->num_rows > 0) {
                        $query8 = "UPDATE `DealerRandomPrizes` SET SpinCount = SpinCount-1 WHERE EventId ='$eventId' and ZoneId ='$zoneId'";
                            $conn->query($query8);
                        }

                }
            }
            
            
        }
        $fetchEventStmt->close();

        // Check if PlayedSpinAttempts matches AttemptNo1 and increment WinCount
        $updateWinCountQuery = "UPDATE DealerEventDetails 
                                SET WinCount = WinCount + 1 
                                WHERE DealerId = ? AND PlayedSpinAttempts = AttemptNo1";
        $winCountStmt = $conn->prepare($updateWinCountQuery);
        $winCountStmt->bind_param("i", $dealerid);

        if ($winCountStmt->execute()) {
            // Fetch event and dealer details to insert into EventWinners table
             if ($winCountStmt->affected_rows > 0) {
                    $query = "SELECT `DealerEventDetailsId`, DE.`DealerId`,D.DealerName,D.EmailId, DE.`EventId`,E.EventName,
                    (SELECT ZoneName FROM ZoneMaster WHERE ZoneId = (Select ZoneID from DealerMaster where DealerId=DE.DealerId) ) as ZoneName,`NoOfCoupons`, `PlayedSpinAttempts`, `WinCount`, `SpinTypeId`, `AttemptNo1`, `PrizeId1`, `AttemptNo2`, `PrizeId2`, `DefaultAllocated` , (Select UnitQuantityPrice from EventZonePrize WHERE ZoneId =(Select ZoneID from DealerMaster where DealerId=DE.DealerId) and PrizeId = DE.PrizeId1  AND PlayedSpinAttempts = AttemptNo1) as PrizeAmount,
                    (Select PrizeName from Prizes where PrizeId = DE.PrizeId1 AND PlayedSpinAttempts = AttemptNo1) as PrizeName,
                    (Select ZoneID from DealerMaster where DealerId=DE.DealerId) as ZoneId
                    ,(Select PrizePhoto from Prizes where PrizeId = DE.PrizeId1) as PrizePhoto 
                    FROM `DealerEventDetails` DE 
                    INNER JOIN EventMaster E ON DE.EventId = E.EventId 
                    INNER JOIN DealerMaster D ON DE.DealerId = D.DealerId 
                    WHERE DE.DealerId =  $dealerid and DE.EventId = $eventId";
                    $result = $conn->query($query);
                    $dealerEventDetails = $result->fetch_assoc();
                    $dealerName = $dealerEventDetails['DealerName'];
                    $dealerEmail = $dealerEventDetails['EmailId'];
                    $eventName = $dealerEventDetails['EventName'];
                    $zoneId = $dealerEventDetails['ZoneId'];
                    $zoneName = $dealerEventDetails['ZoneName'];
                    $prizeName = $dealerEventDetails['PrizeName'];
                    $prizeAmount = $dealerEventDetails['PrizeAmount'];
                    $prizePhoto = $dealerEventDetails['PrizePhoto'];
                    
                    
                    
                    
                    $insertWinnerQuery = "INSERT INTO EventWinners (DealerId, EventId, PrizeId,ZoneId,DealerName,EventName,ZoneName,PrizeName,PrizeAmount,PrizePhoto) 
                    VALUES (?, ?, ?, ?,?, ?,?, ?, ?,?)";
                    $insertWinnerStmt = $conn->prepare($insertWinnerQuery);
                    $insertWinnerStmt->bind_param("iiiissssss", $dealerid, $eventId, $prizeId,$zoneId,$dealerName,$eventName,$zoneName,$prizeName,$prizeAmount,$prizePhoto);
                    $insertWinnerStmt->execute();
                    $insertWinnerStmt->close();
                    
                    $query3 = "UPDATE `EventZonePrize` SET AllocatedPrizes = AllocatedPrizes+1 where EventId='$eventId' and ZoneId ='$zoneId' and PrizeId ='$prizeId'";
                    $conn->query($query3);
                    
                     require_once 'email_prize_won.php'; // Ensure the function is available
                     sendPrizeEmail($dealerEmail,$dealerName,$prizeName,$eventName,$prizeAmount,$prizePhoto);

             }

            echo json_encode(["status" => "success", "message" => "PlayedSpinAttempts and WinCount updated successfully"]);
        } else {
            echo json_encode(["status" => "partial_success", "message" => "PlayedSpinAttempts updated, but failed to update WinCount"]);
        }

        $winCountStmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update PlayedSpinAttempts"]);
    }

    $attemptsStmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "dealerid parameter is required"]);
}

// Close the database connection
$conn->close();

?>
