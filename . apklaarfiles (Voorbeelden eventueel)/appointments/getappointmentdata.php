<?php

function getAppointmentData($data, $conn)
{
  header('Content-Type: application/json');

  // Validate input
  if (!isset($data['aid']) || empty($data['aid'])) {
    echo json_encode([
      "success" => false,
      "message" => "Ongeldige invoer (aid ontbreekt)",
      "data" => []
    ]);
    return;
  }

   $stmt = $conn->prepare(
        "SELECT aid, userid, repid, moid, apk, note, time, date, duration
         FROM appointments
         WHERE aid = ?"
    );

    $stmt->bind_param("i", $data['aid']);
    $stmt->execute();

    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();
  if (!$result) {
    echo json_encode([
      "success" => false,
      "message" => "Databasefout: " . mysqli_error($conn),
      "data" => []
    ]);
    return;
  }

  $appointment = mysqli_fetch_all($result, MYSQLI_ASSOC);

  // Check if appointment was found
  if (empty($appointment)) {
    echo json_encode([
      "success" => false,
      "message" => "Er is geen afspraak gevonden met ID: $aid",
      "data" => []
    ]);
    return;
  }

  // Return the found appointment
  echo json_encode([
    "success" => true,
    "message" => "Afspraakgegevens succesvol opgehaald",
    "data" => $appointment
  ]);
}
