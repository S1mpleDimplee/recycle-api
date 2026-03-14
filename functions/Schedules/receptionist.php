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
        "data" => $lodges
    ]);
}
// cleaning schedule
function GetCleaningSchedule($data, $conn)
{
    // 1. Get the week and year sent from React
    $week = $data['week'];
    $year = $data['year'];

    // 2. Calculate the Start (Monday) and End (Friday) of that specific week
    $dto = new DateTime();
    $dto->setISODate($year, $week);
    $startDate = $dto->format('Y-m-d'); // Monday
    $dto->modify('+4 days');
    $endDate = $dto->format('Y-m-d');   // Friday

    // 3. SQL query with JOIN to get lodge names
    $sql = "SELECT cs.*, l.name as lodge_name 
            FROM cleaning_schedule cs
            JOIN lodge l ON cs.lodge_id = l.lodgeid
            WHERE cs.cleaning_date BETWEEN '$startDate' AND '$endDate'";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        echo json_encode([
            "success" => false,
            "message" => "SQL Error: " . mysqli_error($conn)
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