<?php
// bookings functions
//get all bookings
function GetAllBookings($conn)
{
    $query = "SELECT b.*, u.name AS user_name, u.email AS user_email, l.name AS lodge_name, l.image AS lodge_image FROM booking b LEFT JOIN user u ON b.user_id = u.id LEFT JOIN lodge l ON b.lodge_id = l.id ORDER BY b.check_in DESC";

    $result = mysqli_query($conn, $query);

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Fout: " . mysqli_error($conn)]);
        return;
    }

    $bookings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row;
    }

    echo json_encode(["success" => true, "data" => $bookings]);
}
// cancel customer booking
function CancelBooking($data, $conn)
{
    $BookingID = $data['id'] ?? null;

    $cancelBookingSQL = "DELETE FROM booking WHERE id='$BookingID'";
    if (mysqli_query($conn, $cancelBookingSQL)) {
        echo json_encode([
            "success" => true,
            "message" => "Afspraak succesvol geannuleerd."
        ]);

    } else {
        echo json_encode([
            "success" => false,
            "message" => "Fout bij het annuleren van de afspraak"
        ]);
        return;
    }
}
// create customer booking
function CreateBooking($data, $connection)
{
    $userId = $data['user_id'] ?? '';
    $lodgeId = $data['lodge_id'] ?? '';
    $checkIn = $data['check_in'] ?? '';
    $checkOut = $data['check_out'] ?? '';

    if (empty($userId) || empty($lodgeId) || empty($checkIn) || empty($checkOut)) {
        echo json_encode(["success" => false, "message" => "Vul alle velden in"]);
        return;
    }

    $check = mysqli_query($connection, "
        SELECT id FROM booking 
        WHERE lodge_id = '$lodgeId' 
        AND status != 'geannuleerd'
        AND check_in < '$checkOut' 
        AND check_out > '$checkIn'
    ");

    if (mysqli_num_rows($check) > 0) {
        echo json_encode(["success" => false, "message" => "Lodge is al geboekt in deze periode"]);
        return;
    }
}
// change customer booking
function ChangeBooking($data, $conn)
{
    $id = $data['id'];
    $userid = $data['user_id'];
    $lodgeid = $data['lodge_id'];
    $checkinDate = $data['checkinDate'];
    $checkoutDate = $data['checkoutDate'];
    $totalprice = $data['totalPrice'];
    $status = $data['status'] ?? 'geboekt';

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
// get customer bookings by user id
function getBookingsByUserId($data, $conn)
{
    $userId = $data['user_id'] ?? null;

    if (!$userId) {
        echo json_encode([
            "success" => false,
            "message" => "User ID is required"
        ]);
        return;
    }

    $sql = "SELECT * FROM booking WHERE userid='$userId'";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        echo json_encode([
            "success" => false,
            "message" => mysqli_error($conn)
        ]);
        return;
    }

    echo json_encode([
        "success" => true,
        "data" => mysqli_fetch_all($result, MYSQLI_ASSOC)
    ]);
}