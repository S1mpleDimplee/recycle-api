<?php
//change bookings
function ChangeBooking($data, $conn)
{
    $id = $data['id'];
    $userid = $data['user_id'];
    $lodgeid = $data['lodge_id'];
    $checkinDate = $data['checkinDate'];
    $checkoutDate = $data['checkoutDate'];
    $totalprice = $data['totalPrice'];
    $status = $data['status'] ?? 'Geboekt';

    $sql = "UPDATE booking SET userid='$userid', lodgeid='$lodgeid', checkinDate='$checkinDate', checkoutDate='$checkoutDate', totalprice='$totalprice', status='$status' WHERE id='$id'";

    if ($id && $userid && $lodgeid && $checkinDate && $checkoutDate && $totalprice && $status) {
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Er is iets misgegaan bij het wijzigen van de afspraak, contacteer de beheerder astublieft"
        ]);
        return;
    }

    if (mysqli_query($conn, $sql)) {
        echo json_encode([
            "success" => true,
            "message" => "Afspraak succesvol gewijzigd"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Fout bij het wijzigen van de afspraak: " . mysqli_error($conn)
        ]);
    }
}

//get all repairs for mechanic
function GetAllRepairs($conn)
{
    $sql = "SELECT * FROM repair where status != 'in onderhoud'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        echo json_encode([
            "success" => false,
            "message" => "Fout bij het ophalen van reparaties: " . mysqli_error($conn)
        ]);
        return;
    }

    $repairs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $repairs[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => $repairs
    ]);
}
