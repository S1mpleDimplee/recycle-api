<?php

function GetAllUsers($conn) {
    $sql = "SELECT * FROM user";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $users = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
         echo json_encode([
            "success" => true,
            "message" => "Gebruikers succesvol opgehaald",
            "data" => $users
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Geen gebruikers gevonden"
        ]);
    }
}