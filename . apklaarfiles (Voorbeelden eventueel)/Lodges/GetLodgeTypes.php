<?php

function GetLodgeTypes($connection) {
    $query = "SELECT * FROM lodge_type";
    $result = mysqli_query($connection, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $types = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $types[] = $row;
        }
        echo json_encode([
            "success" => true,
            "data" => $types
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Geen lodge types gevonden"
        ]);
    }
}