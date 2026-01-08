<?php

require '../emailtriggers/emailcardeleted.php';
function removeCar($data, $conn)
{
    $carId = $data['carid'] ?? null;
    $userid = $data['userid'] ?? null;

    $removeCarSQL = "DELETE FROM car WHERE carid='$carId' AND userid='$userid'";

    if (mysqli_query($conn, $removeCarSQL)) {
        echo json_encode([
            "success" => true,
            "message" => "Auto succesvol verwijderd."
        ]);

        sendCarDeletedEmail([
            "userid" => $userid,
            "carname" => $data['carname'] ?? 'Onbekende auto'
        ], $conn);

        AddNotification([
            "userid" => $userid,
            "preset" => "cardeleted",
            "info" => $data['carname'] ?? 'Onbekende auto'
        ], $conn);

    } else {
        echo json_encode([
            "success" => false,
            "message" => "Fout bij het verwijderen van de auto: " . mysqli_error($conn)
        ]);
        return;
    }

}