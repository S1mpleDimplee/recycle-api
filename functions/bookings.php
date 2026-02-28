<?php
// Bookings functions
// Cancel customer booking
function CancelBooking($data, $conn)
{
    $BookingID = $data['id'] ?? null;

    $cancelBookingSQL = "DELETE FROM booking WHERE id='$BookingID'";
    if (mysqli_query($conn, $cancelBookingSQL)) {
        echo json_encode([
            "success" => true,
            "message" => "Afspraak succesvol geannuleerd."
        ]);

        // AddNotification([
        //     "userid" => $data['userid'] ?? null,
        //     "preset" => "cancelled_appointment",
        //     "appointmenttime" => $data['appointmenttime'] ?? ''
        // ], $conn);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Fout bij het annuleren van de afspraak"
        ]);
        return;
    }
}
// Create customer booking
function CreateBooking($data, $conn)
{
    $id = $data['id'];
    $userid = $data['userid'];
    $carid = $data['carid'];
    $carname = $data['carname'] ?? 'Onbekende auto';
    $appointmentDate = $data['appointmentDate'];
    $appointmentTime = $data['appointmentTime'];
    $repairs = json_encode($data['repairs']);
    $totalNetPrice = $data['totals']['netPrice'];
    $totalGrossPrice = $data['totals']['grossPrice'];
    $totalLaborTime = $data['totals']['totalLaborTime'];

    $sql = "INSERT INTO booking ( id, mechanicid, carid, appointmentDate, appointmentTime, repairs, totalNetPrice, totalGrossPrice, totalLaborTime) 
            VALUES ('$id', '$userid', '$carid', '$appointmentDate', '$appointmentTime', '$repairs', '$totalNetPrice', '$totalGrossPrice', '$totalLaborTime')";


    if ($id && $carid && $appointmentDate && $appointmentTime && $repairs && $totalNetPrice && $totalGrossPrice && $totalLaborTime) {
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Vul alle velden in"
        ]);
        return;
    }


    if (mysqli_query($conn, $sql)) {
        echo json_encode([
            "success" => true,
            "message" => "Afspraak succesvol aangemaakt",
        ]);

        // AddNotification([
        //     "userid" => $userid,
        //     "preset" => "appointmentcreated",
        //     "appointmentcar" => $carname,
        //     "appointmenttime" => $appointmentDate . " " . $appointmentTime
        // ], $conn);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Afspraak kon niet worden aangemaakt: " . mysqli_error($conn)
        ]);
    }
}