<?php


function getAllPatients($conn)
{
    $sql = "SELECT users.*, useradresses.* 
            FROM users 
            JOIN useradresses ON users.userid = useradresses.userid 
            WHERE users.role = 0";
    $result = mysqli_query($conn, $sql);
    $patients = mysqli_fetch_all($result, MYSQLI_ASSOC);


    if (!$patients) {
        echo json_encode([
            "success" => false,
            "message" => "Er staan nog geen patienten geregistreerd",
            "data" => []
        ]);
        return;
    }

    echo json_encode([
        "success" => true,
        "message" => "Alle patienten zin succesvol opgehaald",
        "data" => $patients
    ]);
    return;
}
