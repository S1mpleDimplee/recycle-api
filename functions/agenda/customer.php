<?php

function getBookingByCustomer($customerId, $connection)
{
    $query = "SELECT b.id, b.start_date, b.end_date, l.name AS lodge_name
              FROM booking b
              JOIN lodge l ON b.lodge_id = l.id
              WHERE b.customer_id = '$customerId'";

    $result = mysqli_query($connection, $query);

    if (!$result) {
        echo json_encode(["success" => false, "message" => "Fout bij ophalen boekingen: " . mysqli_error($connection)]);
        return;
    }

    $bookings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row;
    }

    echo json_encode(["success" => true, "data" => $bookings]);
}
?>