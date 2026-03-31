<?php

function addLodge($data, $connection)
{
	$name =  $data['name'] ?? '';
	$description =  $data['description'] ?? '';
	$status =  $data['status'] ?? 'beschikbaar';
	$visable =  $data['visible'] ?? 1;
	$bedrooms =  $data['slaapkamers'] ?? 1;
	$people =  $data['aantalPersonen'] ?? 2;
	$price =  $data['priceRegular'] ?? '';
	$price_winter =  $data['priceWinter'] ?? '';
	$lodge_type_id =  $data['lodge_type_id'] ?? null;
	$image =  $data['image'] ?? '';

	if (empty($name)) {
		echo json_encode(["success" => false, "message" => "Naam is verplicht"]);
		return;
	}

	$query = "INSERT INTO lodge (lodge_type_id, name, description, status, visable, image, bedrooms, people, price, price_winter)
              VALUES ('$lodge_type_id', '$name', '$description', '$status', '$visable', '$image', '$bedrooms', '$people', '$price', '$price_winter')";

	$result = mysqli_query($connection, $query);

	if (!$result) {
		echo json_encode(["success" => false, "message" => "Fout bij aanmaken lodge: " . mysqli_error($connection)]);
		return;
	}

	echo json_encode([
		"success" => true,
		"message" => "Lodge succesvol aangemaakt",
		"data" => ["id" => mysqli_insert_id($connection)]
	]);
}