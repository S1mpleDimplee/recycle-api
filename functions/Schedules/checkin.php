<?php

function CheckInBooking($data, $connection) {
    $id = $data['id'] ?? '';

    if (empty($id)) {
        echo json_encode(["success" => false, "message" => "Boeking ID is verplicht"]);
        return;
    }

    $time = date('H:i:s');

    $result = mysqli_query($connection, "
        UPDATE booking 
        SET status = 'ingechecked', check_in_time = '$time'
        WHERE id = '$id'
    ");

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Fout bij inchecken: " . mysqli_error($connection)]);
        return;
    }

    echo json_encode(["success" => true, "message" => "Succesvol ingechecked", "time" => $time]);
}

function CheckOutBooking($data, $connection) {
    $id = $data['id'] ?? '';

    if (empty($id)) {
        echo json_encode(["success" => false, "message" => "Boeking ID is verplicht"]);
        return;
    }

    $time = date('H:i:s');

    $result = mysqli_query($connection, "
        UPDATE booking 
        SET status = 'uitgechecked', check_out_time = '$time'
        WHERE id = '$id'
    ");

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Fout bij uitchecken: " . mysqli_error($connection)]);
        return;
    }

    echo json_encode(["success" => true, "message" => "Succesvol uitgechecked", "time" => $time]);
}