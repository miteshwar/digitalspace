<?php
include('db_connect.php');

if (isset($_GET['dealerId'])) {
    $dealerId = intval($_GET['dealerId']);
    
    // Fetch Zone Name
    $zoneQuery = "SELECT ZoneId,ZoneName FROM ZoneMaster WHERE ZoneId = (Select ZoneID from DealerMaster where DealerId=$dealerId) ";
    $zoneResult = $conn->query($zoneQuery);
    
    $zoneName =  "";
    $zoneid = 0;
    
    if ($zoneResult && $zoneResult->num_rows > 0) {
        while ($zone = $zoneResult->fetch_assoc()) {
            $zoneName =  $zone['ZoneName'];
            $zoneid =  $zone['ZoneId'];
        }
    }
    

    // // Fetch Prizes
    $prizesQuery = "SELECT * FROM Prizes WHERE PrizeId IN (
        SELECT PrizeId FROM EventZonePrize 
        WHERE ZoneID = (SELECT ZoneID FROM DealerMaster WHERE DealerId = $dealerId)
    )";
    $prizesResult = $conn->query($prizesQuery);

    $prizesOptions = "<option value='0'>Select Prize</option>";
    
    if ($prizesResult && $prizesResult->num_rows > 0) {
        while ($prize = $prizesResult->fetch_assoc()) {
            $prizesOptions .= "<option value='" . $prize['PrizeId'] . "'>" . $prize['PrizeName'] . "</option>";
        }
    } else {
        $prizesOptions = "<option value='0'>No Prizes Available</option>";
    }

    // Return as JSON
    echo json_encode([
        'zoneId' => $zoneid,
        'zoneName' => $zoneName,
        'prizesOptions' => $prizesOptions
    ]);
}
?>
