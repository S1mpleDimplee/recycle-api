<?php

function getAllAppointments($conn)
{
  header('Content-Type: application/json');

  $sql = "
    SELECT 
      a.aid,
      a.date,
      a.time,
      a.duration,
      a.status,
      a.apk,
      a.note,
      u.firstname,
      u.lastname,
      m.name AS mechanic
    FROM appointments a
    LEFT JOIN users u ON a.userid = u.userid
    LEFT JOIN mechanics m ON a.moid = m.moid
    ORDER BY a.date, a.time
  ";

  $result = mysqli_query($conn, $sql);

   if (!$result) {
        http_response_code(500); // Set proper HTTP status
        echo json_encode([
            "success" => false,
            "message" => mysqli_error($conn),
            "data" => []
        ]);
        exit;
    }

    $appointments = mysqli_fetch_all($result, MYSQLI_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $appointments
    ]);
    exit; // Ensure nothing else is output
}
