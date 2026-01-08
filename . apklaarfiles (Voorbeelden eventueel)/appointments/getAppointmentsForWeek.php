<?php
function getAppointmentsForWeek(int $year, int $week, string $mechanicId, $conn) {
    header('Content-Type: application/json');

    try {
        // Force ISO Week calculation
        $date = new DateTime();
        // Use 'W' format to ensure we are following ISO-8601
        $date->setISODate($year, $week, 1); // The '1' signifies Monday
        $monday = $date->format('Y-m-d');
        
        $date->modify('+6 days'); // Change to +6 to include the full weekend (Sunday)
        $sunday = $date->format('Y-m-d');

        $stmt = $conn->prepare(
            "SELECT aid, userid, appointmentDate, appointmentTime, repairs, totalLaborTime, status, carModel, licensePlate
             FROM appointments 
             WHERE mechanicid = ? 
               AND appointmentDate BETWEEN ? AND ?
               AND appointmentDate != '0000-00-00'
             ORDER BY appointmentDate ASC, appointmentTime ASC"
        );

        $stmt->bind_param("sss", $mechanicId, $monday, $sunday);
        $stmt->execute();
        $result = $stmt->get_result();
        $appointments = $result->fetch_all(MYSQLI_ASSOC);

        echo json_encode([
            "isSuccess" => true,
            "message" => "Afspraken succesvol opgehaald",
            "data" => $appointments,
            "debug_range" => ["start" => $monday, "end" => $sunday] // Helpful for debugging
        ]);
    } catch (Exception $e) {
        echo json_encode([
            "isSuccess" => false,
            "message" => "Fout: " . $e->getMessage(),
            "data" => []
        ]);
    }
}