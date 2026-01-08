<?php

function fetchMechanics($conn)
{
    $fetchMechanicsSQL = "SELECT * FROM user WHERE role = 2";
    $result = mysqli_query($conn, $fetchMechanicsSQL);

    $mechanics = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $mechanics[] = $row;
    }

    echo json_encode([
        "success" => true,
        "message" => "Mnteurs succesvol opgehaald",
        "data" => $mechanics
    ]);

}