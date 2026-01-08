<?php


function getAllDentists($conn)
{
    $sql = "SELECT CONCAT(firstname, ' ', lastname) AS name, userid FROM users WHERE role = 1";
    $result = mysqli_query($conn, $sql);
    $dentists = mysqli_fetch_all($result, MYSQLI_ASSOC);


    if (!$dentists) {
        echo json_encode([
            "success" => false,
            "message" => "Geen tandartsen gevonden, maak een tandarts aan",
            "data" => []
        ]);
        return;
    }

    echo json_encode([
        "success" => true,
        "message" => "Alle tandartsen succesvol opgehaald",
        "data" => $dentists
    ]);
    return;
}
