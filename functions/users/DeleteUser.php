<?php

function DeleteUser($data, $connection)
{
    $userId = $data['userid'] ?? '';

    if (empty($userId)) {
        echo json_encode(["success" => false, "message" => "Gebruiker niet gevonden"]);
        return;
    }
    // Check if user has any bookings still active or in the future
    $check = mysqli_query($connection, "SELECT id FROM booking WHERE user_id = '$userId' AND check_out >= CURDATE()");

    if (mysqli_num_rows($check) > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Gebruiker kan niet worden verwijderd, er zijn nog actieve of toekomstige boekingen"
        ]);
        return;
    }

    mysqli_query($connection, "DELETE FROM address WHERE user_id = '$userId'");
    $result = mysqli_query($connection, "DELETE FROM user WHERE id = '$userId'");

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Fout bij verwijderen: " . mysqli_error($connection)]);
        return;
    }

    echo json_encode(["success" => true, "message" => "Gebruiker succesvol verwijderd"]);
}