<?php

function addLodge($data, $connection)
{
	$name = mysqli_real_escape_string($connection, $data['name'] ?? '');
	$description = mysqli_real_escape_string($connection, $data['description'] ?? '');
	$status = mysqli_real_escape_string($connection, $data['status'] ?? 'beschikbaar');
	$visable = mysqli_real_escape_string($connection, $data['visible'] ?? 1);
	$bedrooms = mysqli_real_escape_string($connection, $data['slaapkamers'] ?? 1);
	$people = mysqli_real_escape_string($connection, $data['aantalPersonen'] ?? 2);
	$price = mysqli_real_escape_string($connection, $data['priceRegular'] ?? '');
	$price_winter = mysqli_real_escape_string($connection, $data['priceWinter'] ?? '');
	$lodge_type_id = mysqli_real_escape_string($connection, $data['lodge_type_id'] ?? null);
	$image = mysqli_real_escape_string($connection, $data['image'] ?? '');

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