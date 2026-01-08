<?php

function Cancelappointment($data, $conn)
{
    $appointmentID = $data['appointmentId'] ?? null;

    $cancelAppointmentSQL = "DELETE FROM appointments WHERE aid='$appointmentID'";
    if (mysqli_query($conn, $cancelAppointmentSQL)) {
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