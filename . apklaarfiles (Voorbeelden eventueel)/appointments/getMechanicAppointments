<?php
function getMechanicAppointments($data, $conn)
{
    header('Content-Type: application/json');

    if (empty($data['mechanicId'])) {
        echo json_encode([
            "isSuccess" => false,
            "message" => "mechanicId ontbreekt",
            "data" => []
        ]);
        return;
    }

    $mechanicId = $data['mechanicId'];

    // Determine if it's a week request or a single day request
    if (!empty($data['startDate']) && !empty($data['endDate'])) {
        // WEEK request
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        $stmt = $conn->prepare(
            "SELECT aid, userid, appointmentDate, appointmentTime, repairs, totalLaborTime, status
             FROM appointments
             WHERE mechanicid = ?
               AND appointmentDate BETWEEN ? AND ?
               AND appointmentDate != '0000-00-00'
             ORDER BY appointmentDate, appointmentTime ASC"
        );

        $stmt->bind_param("sss", $mechanicId, $startDate, $endDate);

    } else {
        // SINGLE DAY request
        if (empty($data['date'])) {
            echo json_encode([
                "isSuccess" => false,
                "message" => "date ontbreekt",
                "data" => []
            ]);
            return;
        }

        $date = $data['date'];

        $stmt = $conn->prepare(
            "SELECT aid, userid, appointmentDate, appointmentTime, repairs, totalLaborTime, status
             FROM appointments
             WHERE mechanicid = ?
               AND appointmentDate = ?
               AND appointmentDate != '0000-00-00'
             ORDER BY appointmentTime ASC"
        );

        $stmt->bind_param("ss", $mechanicId, $date);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $appointments = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        "isSuccess" => true,
        "message" => "Afspraken succesvol opgehaald",
        "data" => $appointments
    ]);
}
