<?php

function fetchCustomerDashboard($data, $connection)
{
    $userid = $data['userid'] ?? null;

    $userid = mysqli_real_escape_string($connection, $userid);

    $sql = "SELECT 
                (SELECT count(*) FROM invoice WHERE (status = 'pending' OR status = 'onbetaald') AND userid = '$userid') AS openInvoices,
                (SELECT concat(model, ' ', brand) FROM car WHERE userid = '$userid' ORDER BY nextinspection ASC LIMIT 1) AS upcomingAPKCarName,
                (SELECT nextinspection FROM car WHERE userid = '$userid' ORDER BY nextinspection ASC LIMIT 1) AS upcomingAPKCarDate,
                (SELECT concat(model, ' ', brand) FROM car WHERE userid = '$userid' ORDER BY lastinspection DESC LIMIT 1) AS lastAPKCarName,
                (SELECT lastinspection FROM car WHERE userid = '$userid' ORDER BY lastinspection DESC LIMIT 1) AS lastAPKCarDate
            ";
    $result = mysqli_query($connection, $sql);
    if (!$result) {
        error_log("Fout bij het ophalen van dashboardgegevens: " . mysqli_error($connection));
        echo json_encode([
            "success" => false,
            "message" => "Fout bij het ophalen van dashboardgegevens"
        ]);
        return;
    }

    $row = mysqli_fetch_assoc($result);

    $appointmentsSql = "SELECT a.*, 
                        (SELECT carnickname FROM car WHERE car.carid = a.carid) AS carNickname,
                        (SELECT model FROM car WHERE car.carid = a.carid) AS model,
                        (SELECT brand FROM car WHERE car.carid = a.carid) AS brand
                        FROM appointments a 
                        WHERE userid = '$userid' AND appointmentDate >= CURDATE() 
                        ORDER BY appointmentdate ASC";
    $appointmentsResult = mysqli_query($connection, $appointmentsSql);
    $appointments = [];
    while ($appointment = mysqli_fetch_assoc($appointmentsResult)) {
        $appointments[] = $appointment;
    }

    $row['nextAppointments'] = $appointments;

    echo json_encode([
        "success" => true,
        "message" => "Customer dashboard info fetched",
        "data" => $row
    ]);
}