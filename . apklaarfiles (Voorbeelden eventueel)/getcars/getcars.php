<?php
function getcars($data, $conn)
{

  $userid = $data['userid'] ?? null;

  $sql = "SELECT * FROM car WHERE userid = '$userid' ORDER BY registered_at DESC";
  $result = mysqli_query($conn, $sql);
  $cars = [];
  while ($row = mysqli_fetch_assoc($result)) {
    $cars[] = $row;
  }
  if (!$result) {
    echo json_encode([
      "success" => false,
      "message" => "Database fout: " . mysqli_error($conn)
    ]);
    return;
  } else {
    echo json_encode([  
      "success" => true,
      "message" => "Auto's succesvol opgehaald",
      "data" => $cars
    ]);
  }
}

?>