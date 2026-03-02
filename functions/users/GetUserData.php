<?php 

function GetUserData($data, $connection) {
    $userId = mysqli_real_escape_string($connection, $data['userid']);

    $query = "SELECT * FROM user WHERE id = '$userId'";
    $result = mysqli_query($connection, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $userData = mysqli_fetch_assoc($result);
        echo json_encode([
            "success" => true,
            "data" => $userData
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Gebruiker niet gevonden"
        ]);
    }
}