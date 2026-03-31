<?php
// manager functions
//get all repairs for mechanic
function GetAllRepairs($conn)
{
    $sql = "SELECT * FROM repair where status != 'in onderhoud'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        echo json_encode([
            "success" => false,
            "message" => "Fout bij het ophalen van reparaties: " . mysqli_error($conn)
        ]);
        return;
    }

    $repairs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $repairs[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => $repairs
    ]);
}
