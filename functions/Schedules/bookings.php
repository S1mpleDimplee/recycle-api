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

    $cancelBookingSQL = "UPDATE booking SET status='geannuleerd' WHERE id='$BookingID'";
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

    // Check if lodge already booked in this period
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

    // Calculate price
    $lodge = mysqli_fetch_assoc(mysqli_query($connection, "SELECT price, price_winter FROM lodge WHERE id = '$lodgeId'"));
    $nights = (strtotime($checkOut) - strtotime($checkIn)) / 86400;
    $month = (int) date('m', strtotime($checkIn));
    $isWinter = in_array($month, [11, 12, 1, 2]);
    $totalPrice = $nights * ($isWinter ? $lodge['price_winter'] : $lodge['price']);

    $result = mysqli_query($connection, "
        INSERT INTO booking (user_id, lodge_id, check_in, check_out, total_price, status)
        VALUES ('$userId', '$lodgeId', '$checkIn', '$checkOut', '$totalPrice', 'gepland')
    ");

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Fout bij aanmaken: " . mysqli_error($connection)]);
        return;
    }

    echo json_encode([
        "success" => true,
        "message" => "Boeking succesvol aangemaakt",
        "data" => ["id" => mysqli_insert_id($connection), "total_price" => $totalPrice, "nights" => $nights]
    ]);
}

function ChangeBooking($data, $connection)
{
    $id = $data['id'] ?? '';
    $checkIn = $data['check_in'] ?? '';
    $checkOut = $data['check_out'] ?? '';
    $status = $data['status'] ?? 'bevestigd';

    if (empty($id) || empty($checkIn) || empty($checkOut)) {
        echo json_encode(["success" => false, "message" => "Vul alle velden in"]);
        return;
    }

    $result = mysqli_query($connection, "
        UPDATE booking SET check_in='$checkIn', check_out='$checkOut', status='$status' WHERE id='$id'
    ");

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Fout bij wijzigen: " . mysqli_error($connection)]);
        return;
    }

    echo json_encode(["success" => true, "message" => "Boeking succesvol gewijzigd"]);
}

function GetBookingsByUserId($data, $connection)
{
    $userId = $data['user_id'] ?? '';

    if (empty($userId)) {
        echo json_encode(["success" => false, "message" => "User ID is verplicht"]);
        return;
    }

    $query = "
        SELECT 
            b.id, b.check_in, b.check_out, b.total_price, b.status,
            l.name AS lodge_name, l.image AS lodge_image
        FROM booking b
        LEFT JOIN lodge l ON b.lodge_id = l.id
        WHERE b.user_id = '$userId'
        ORDER BY b.check_in DESC
    ";

    $result = mysqli_query($connection, $query);

    $bookings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row;
    }

    echo json_encode(["success" => true, "data" => $bookings]);
}