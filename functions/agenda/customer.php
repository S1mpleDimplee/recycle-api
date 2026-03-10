<?php

function getBookingByCustomer($customerId, $connection)
{
    $query = "SELECT b.id, b.user_id, b.lodge_id, b.check_in, b.check_out, b.total_price, b.status, b.check_in_time, b.check_out_time, 
                     l.name AS lodge_name,
                     u.name AS customer_name
              FROM booking b
              join lodge l ON b.lodge_id = l.id
              join user u ON b.user_id = u.id
              WHERE b.user_id = '$customerId'";

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