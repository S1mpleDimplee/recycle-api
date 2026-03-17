<?php
// bookings functions
//get all bookings
function GetAllBookings($conn)
{
  $query = "SELECT b.*, u.name AS user_name, u.email AS user_email, l.name AS lodge_name, l.image AS lodge_image FROM booking b LEFT JOIN user u ON b.user_id = u.id LEFT JOIN lodge l ON b.lodge_id = l.id ORDER BY b.check_in DESC";

  $result = mysqli_query($conn , $query);

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

function GetBookingById($data, $connection)
{
  $booking_id = $data['id'] ?? null;

  if (empty($booking_id)) {
    echo json_encode(["success" => false, "message" => "Booking ID is verplicht"]);
    return;
  }

  $query = "
    SELECT
      b.id AS booking_id,
      b.check_in,
      b.check_out,
      b.total_price,
      b.status AS booking_status,
      b.check_in_time,
      b.check_out_time,
      u.id AS user_id,
      u.name AS user_name,
      u.email AS user_email,
      u.phonenumber AS user_phone,
      u.created_at AS user_created_at,
      l.id AS lodge_id,
      l.name AS lodge_name,
      l.image AS lodge_image,
      l.bedrooms AS lodge_bedrooms,
      l.people AS lodge_people,
      l.price AS lodge_price,
      l.status AS lodge_status
    FROM booking b
    LEFT JOIN user u ON b.user_id = u.id
    LEFT JOIN lodge l ON b.lodge_id = l.id
    WHERE b.id = '$booking_id'
    LIMIT 1
  ";

  $result = mysqli_query($connection, $query);

  if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode(["success" => false, "message" => "Boeking niet gevonden"]);
    return;
  }

  $booking = mysqli_fetch_assoc($result);

  // Invoice for this booking
  $invoiceResult = mysqli_query($connection, "SELECT * FROM invoice WHERE booking_id = '$booking_id' LIMIT 1");
  $invoice = ($invoiceResult && mysqli_num_rows($invoiceResult) > 0)
    ? mysqli_fetch_assoc($invoiceResult)
    : null;

  // Repairs for the lodge
  $lodge_id = $booking['lodge_id'];
  $repairQuery = "
        SELECT r.*, mt.description AS task_description, mt.status AS task_status
        FROM repair r
        LEFT JOIN maintenance_task mt ON mt.repair_id = r.id
        WHERE r.lodge_id = '$lodge_id'
        ORDER BY r.created_at DESC
    ";
  $repairResult = mysqli_query($connection, $repairQuery);
  $repairs = [];
  if ($repairResult) {
    while ($row = mysqli_fetch_assoc($repairResult)) {
      $repairs[] = $row;
    }
  }

  echo json_encode([
    "success" => true,
    "data" => [
      "booking" => $booking,
      "invoice" => $invoice,
      "repairs" => $repairs,
    ]
  ]);
}

// Update booking status (approve / cancel / terugbetaling)
function UpdateBookingStatus($data, $connection)
{
  $booking_id = $data['id'] ?? null;
  $status = $data['status'] ?? null;

  $allowed = ['bevestigd', 'gepland', 'geannuleerd', 'terugbetaling'];

  if (empty($booking_id) || empty($status)) {
    echo json_encode(["success" => false, "message" => "Booking ID en status zijn verplicht"]);
    return;
  }

  if (!in_array($status, $allowed)) {
    echo json_encode(["success" => false, "message" => "Ongeldige status: $status"]);
    return;
  }

  $result = mysqli_query($connection, "UPDATE booking SET status = '$status' WHERE id = '$booking_id'");

  if (!$result) {
    echo json_encode(["success" => false, "message" => "Fout: " . mysqli_error($connection)]);
    return;
  }

  if (mysqli_affected_rows($connection) === 0) {
    echo json_encode(["success" => false, "message" => "Boeking niet gevonden of status ongewijzigd"]);
    return;
  }

  echo json_encode(["success" => true, "message" => "Status bijgewerkt naar '$status'"]);
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