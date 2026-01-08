<?php

include '../notifications/addnotification.php';
function addCar($data, $conn)
{

  // Verplicht
  $brand = $data['brand'] ?? null;
  $model = $data['model'] ?? null;
  $buildyear = $data['buildyear'] ?? null;
  $licensePlateCountry = $data['countryCode'] ?? null;
  $licensePlate = $data['licensePlate'] ?? null;


  // Niet verplicht
  $color = $data['color'] ?? null;
  $fuelType = $data['fuelType'] ?? null;
  $carNickname = $data['carNickname'] ?? null;
  $lastInspection = $data['lastInspection'] ?? null;
  $carimage = $data['carimage'] ?? null;

  $userid = $data['userid'] ?? null;

  if ($carimage) {
    $carimage = mysqli_real_escape_string($conn, $carimage);
  }


  if (
    empty($brand) || empty($model) || empty($buildyear)
    || empty($licensePlateCountry)
    || empty($licensePlate)
  ) {
    echo json_encode([
      "success" => false,
      "message" => "Vul alle verplichte velden in: merk, model, bouwjaar, kentekenland, kenteken"
    ]);
    return;
  }

  $addCarSQL = "INSERT INTO car (userid, carnickname, licenseplatecountry, licenseplate, brand, fueltype, lastinspection, buildyear, model, color, carimage, registered_at) 
                VALUES ('$userid', '$carNickname', '$licensePlateCountry', '$licensePlate', '$brand', '$fuelType', '$lastInspection', '$buildyear', '$model', '$color', '$carimage', NOW())";

  if (mysqli_query($conn, $addCarSQL)) {

    AddNotification([
      "userid" => $userid,
      "preset" => "caradded",
      "carname" => $carNickname
    ], $conn);

    echo json_encode([
      "success" => true,
      "message" => "Auto succesvol toegevoegd"
    ]);
  } else {
    echo json_encode([
      "success" => false,
      "message" => "Fout bij het toevoegen van de auto: " . mysqli_error($conn)
    ]);
  }
}

?>