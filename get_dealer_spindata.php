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

    // Prepare SQL query to get dealer data
    $query_dealer = "SELECT 
                        dm.DealerId, dm.DealerName, dm.DealerPhoto, ded.ZoneId,ded.SpinTypeId,
                        (SELECT ZoneName FROM ZoneMaster WHERE ZoneId = dm.ZoneId) as ZoneName,
                        ded.NoOfCoupons, ded.DefaultAllocated, ded.PlayedSpinAttempts, ded.WinCount,
                        ded.AttemptNo1, ded.AttemptNo2, ded.PrizeId1, ded.PrizeId2, ded.SpinTypeId, 
                        em.*, 
                        CASE WHEN ded.SpinTypeId = 1 THEN p.PrizeName ELSE NULL END AS PrizeName1,
                        CASE WHEN ded.SpinTypeId = 1 THEN p.PrizeDescription ELSE NULL END AS PrizeDescription1,
                        CASE WHEN ded.SpinTypeId = 1 THEN p.PrizePhoto ELSE NULL END AS PrizePhoto1,
                        CASE WHEN ded.SpinTypeId = 1 THEN p2.PrizeName ELSE NULL END AS PrizeName2,
                        CASE WHEN ded.SpinTypeId = 1 THEN p2.PrizeDescription ELSE NULL END AS PrizeDescription2,
                        CASE WHEN ded.SpinTypeId = 1 THEN p2.PrizePhoto ELSE NULL END AS PrizePhoto2
                    FROM 
                        DealerEventDetails AS ded
                    INNER JOIN 
                        DealerMaster AS dm ON ded.DealerId = dm.DealerId
                    INNER JOIN 
                        EventMaster AS em ON ded.EventId = em.EventId
                    LEFT JOIN 
                        Prizes AS p ON ded.SpinTypeId = 1 AND ded.PrizeId1 = p.PrizeId
                    LEFT JOIN 
                        Prizes AS p2 ON ded.SpinTypeId = 1 AND ded.PrizeId2 = p2.PrizeId
                    WHERE 
                        ded.dealerid = ?;";

    $stmt_dealer = $conn->prepare($query_dealer);
    $stmt_dealer->bind_param("i", $dealerid);

    // Execute query
    $stmt_dealer->execute();
    $result_dealer = $stmt_dealer->get_result();

    // Initialize sections array to store prize data
    $sections = [];

    // Fetch result for dealer data
    if ($result_dealer->num_rows > 0) {
        $dealer_data = $result_dealer->fetch_assoc();

        // Prepare prize data using your original SQL query
        $query_prizes = "SELECT 
                            W.WheelConfigDetailsId, W.EventId, W.ZoneId, W.PrizeId, W.WheelOrder, 
                            P.PrizeName, P.PrizePhoto, P.PrizeDescription
                        FROM 
                            WheelConfigDetails W
                        INNER JOIN 
                            Prizes P ON P.PrizeId = W.PrizeId
                        WHERE 
                            W.ZoneId = (SELECT ZoneId FROM DealerMaster WHERE DealerId = ?)
                        ORDER BY 
                            W.WheelOrder;";

        $stmt_prizes = $conn->prepare($query_prizes);
        $stmt_prizes->bind_param("i", $dealerid);

        // Execute query for prize data
        $stmt_prizes->execute();
        $result_prizes = $stmt_prizes->get_result();

        // Fetch prize data and prepare sections
        while ($row = $result_prizes->fetch_assoc()) {
            $sections[] = [
                'label' => $row['PrizeName'],
                'desc' => $row['PrizeDescription'],
                'image' => $row['PrizePhoto']
            ];
        }

        // Return both dealer and prize data as JSON, including sections
        echo json_encode([
            "status" => "success",
            "data" => [
                "DealerId" => $dealer_data['DealerId'],
                "DealerName" => $dealer_data['DealerName'],
                "DealerPhoto" => $dealer_data['DealerPhoto'],
                "ZoneId" => $dealer_data['ZoneId'],
                "ZoneName" => $dealer_data['ZoneName'],
                "SpinTypeId" => $dealer_data['SpinTypeId'],
                "NoOfCoupons" => $dealer_data['NoOfCoupons'],
                "DefaultAllocated" => $dealer_data['DefaultAllocated'],
                "PlayedSpinAttempts" => $dealer_data['PlayedSpinAttempts'],
                "WinCount" => $dealer_data['WinCount'],
                "AttemptNo1" => $dealer_data['AttemptNo1'],
                "AttemptNo2" => $dealer_data['AttemptNo2'],
                "PrizeId1" => $dealer_data['PrizeId1'],
                "PrizeId2" => $dealer_data['PrizeId2'],
                "SpinTypeId" => $dealer_data['SpinTypeId'],
                "EventId" => $dealer_data['EventId'],
                "EventName" => $dealer_data['EventName'],
                "EventStartDate" => $dealer_data['EventStartDate'],
                "EventStartTime" => $dealer_data['EventStartTime'],
                "EventEndDate" => $dealer_data['EventEndDate'],
                "EventEndTime" => $dealer_data['EventEndTime'],
                "EventStatus" => $dealer_data['EventStatus'],
                "EventDescription" => $dealer_data['EventDescription'],
                "PrizeName1" => $dealer_data['PrizeName1'],
                "PrizeDescription1" => $dealer_data['PrizeDescription1'],
                "PrizePhoto1" => $dealer_data['PrizePhoto1'],
                "PrizeName2" => $dealer_data['PrizeName2'],
                "PrizeDescription2" => $dealer_data['PrizeDescription2'],
                "PrizePhoto2" => $dealer_data['PrizePhoto2'],
            ],
            "sections" => $sections
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Dealer not found"]);
    }

    // Close the prepared statements
    $stmt_dealer->close();
    $stmt_prizes->close();
} else {
    echo json_encode(["status" => "error", "message" => "dealerid parameter is required"]);
}

// Close the database connection
$conn->close();

?>
