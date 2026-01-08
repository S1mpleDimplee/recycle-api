<?php
function editCar($data, $conn)
{

    // Verplicht
    $brand = $data['brand'] ?? null;
    $model = $data['model'] ?? null;
    $buildyear = $data['buildyear'] ?? null;
    $licensePlateCountry = $data['licenseplatecountry'] ?? null;
    $licensePlate = $data['licenseplate'] ?? null;


    // Niet verplicht
    $color = $data['color'] ?? null;
    $fuelType = $data['fueltype'] ?? null;
    $carNickname = $data['carnickname'] ?? null;
    $lastInspection = $data['lastinspection'] ?? null;
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

    $editCarSql = "UPDATE car SET 
                carnickname = '$carNickname',
                licenseplatecountry = '$licensePlateCountry',
                licenseplate = '$licensePlate',
                brand = '$brand',
                fueltype = '$fuelType',
                lastinspection = '$lastInspection',
                buildyear = '$buildyear',
                model = '$model',
                color = '$color',
                carimage = '$carimage'
                WHERE userid = '$userid' AND carid = '" . $data['carid'] . "'";

    if (mysqli_query($conn, $editCarSql)) {

        AddNotification([
            "userid" => $userid,
            "preset" => "caredited",
            "info" => $carNickname
        ], $conn);

        echo json_encode([
            "success" => true,
            "message" => "Auto succesvol aangepast"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Fout bij het aanpassen van de auto: " . mysqli_error($conn)
        ]);
    }
}

?>