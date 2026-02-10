<?php
// Function to cancel a booking
// notes to add: 
// - check if the booking exists
// - check if the user has permission to cancel the booking (check if already checked in, the status is not active)
function Cancelbooking($data, $conn)
{
    $id = $data['id'] ?? null;
    
    $cancelbookingSQL = "DELETE FROM booking WHERE id='$id'";
    if (mysqli_query($conn, $cancelbookingSQL)) {
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