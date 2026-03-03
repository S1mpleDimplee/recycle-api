<?php
//mechanic functions
// update repair status to 'in onderhoud'
function UpdateRepairMaintenance($data, $conn)
{
    $id = $data['id'];
    $sql = "UPDATE repair SET status='in onderhoud' WHERE id='$id'";

    if (!$id) {
        echo json_encode([
            "success" => false,
            "message" => "ID is required"
        ]);
        return;
    }

    if (mysqli_query($conn, $sql)) {
        echo json_encode([
            "success" => true,
            "message" => "Reparatiestatus succesvol bijgewerkt"
        ]); 
    }
}
// update repair status to 'beschikbaar'
function UpdateRepairAvailable($data, $conn)
{
    $id = $data['id'];
    $sql = "UPDATE repair SET status='beschikbaar' WHERE id='$id'";

    if (!$id) {
        echo json_encode([
            "success" => false,
            "message" => "ID is required"
        ]);
        return;
    }

    if (mysqli_query($conn, $sql)) {
        echo json_encode([
            "success" => true,
            "message" => "Reparatiestatus succesvol bijgewerkt"
        ]); 
    }
}