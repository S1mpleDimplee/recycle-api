<?php 
//receptionist functions
// get all available lodges for receptionist
function GetAvailableLodges($data, $conn)
{
    $checkinDate = $data['checkinDate'];
    $checkoutDate = $data['checkoutDate'];

    // sql query to find available lodges that are not booked during the specified date range
    $sql = "SELECT * FROM lodge WHERE status = 'available' AND lodgeid NOT IN (
        SELECT lodgeid FROM booking WHERE 
        (checkinDate <= '$checkoutDate' AND checkoutDate >= '$checkinDate')
    )";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        echo json_encode([
            "success" => false,
            "message" => "Fout bij het ophalen van beschikbare lodges: " . mysqli_error($conn)
        ]);
        return;
    }

    $lodges = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $lodges[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => $lodges   ]); 
}
// cleaning schedule
function GetCleaningSchedule($conn)
{
    $sql = "SELECT * FROM cleaning_schedule";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        echo json_encode([
            "success" => false,
            "message" => "Fout bij het ophalen van het schoonmaakschema: " . mysqli_error($conn)
        ]);
        return;
    }

    $schedule = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $schedule[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => $schedule
    ]);
}