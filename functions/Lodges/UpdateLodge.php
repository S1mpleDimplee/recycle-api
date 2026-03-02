<?php

function UpdateLodge($data, $connection)
{
   $id = mysqli_real_escape_string($connection, $data['id'] ?? '');
   $name = mysqli_real_escape_string($connection, $data['name'] ?? '');
   $description = mysqli_real_escape_string($connection, $data['description'] ?? '');
   $status = mysqli_real_escape_string($connection, $data['status'] ?? 'beschikbaar');
   $visable = mysqli_real_escape_string($connection, $data['visible'] ?? 1);
   $bedrooms = mysqli_real_escape_string($connection, $data['slaapkamers'] ?? 1);
   $people = mysqli_real_escape_string($connection, $data['aantalPersonen'] ?? 2);
   $price = mysqli_real_escape_string($connection, $data['priceRegular'] ?? '');
   $price_winter = mysqli_real_escape_string($connection, $data['priceWinter'] ?? '');
   $image = mysqli_real_escape_string($connection, $data['image'] ?? '');

   if (empty($id)) {
      echo json_encode(["success" => false, "message" => "Lodge ID is verplicht"]);
      return;
   }

   if (!empty($image)) {
      $query = "UPDATE lodge SET name = '$name', description = '$description', status = '$status',
               visable = '$visable', image = '$image', bedrooms = '$bedrooms',
               people = '$people', price = '$price', price_winter = '$price_winter' WHERE id = '$id'";
   } else {
      $query = "UPDATE lodge SET name = '$name', description = '$description', status = '$status', visable = '$visable',
               bedrooms = '$bedrooms', people = '$people', price = '$price', price_winter = '$price_winter' WHERE id = '$id'";
   }

   $result = mysqli_query($connection, $query);

   if (!$result) {
      echo json_encode(["success" => false, "message" => "Fout bij updaten lodge: " . mysqli_error($connection)]);
      return;
   }

   echo json_encode([
      "success" => true,
      "message" => "Lodge succesvol bijgewerkt"
   ]);
}