<?php

function fetchReparations($data, $conn)
{
    $fetchReparationsSQL = "SELECT * FROM reparationtypes where enabled = 1";
    $result = mysqli_query($conn, $fetchReparationsSQL);

    $reparations = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $reparations[] = $row;
    }

    echo json_encode([
        "success" => true,
        "message" => "Reparatietypes succesvol opgehaald",
        "data" => $reparations
    ]);


}