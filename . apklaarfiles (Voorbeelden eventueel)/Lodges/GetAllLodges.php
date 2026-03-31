<?php

function GetAllLodges($connection) {
    $query = "SELECT * FROM lodge";
    $result = mysqli_query($connection, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $lodges = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $lodges[] = $row;
        }
        echo json_encode([
            "success" => true,
            "data" => $lodges
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Geen lodges gevonden"
        ]);
    }
}