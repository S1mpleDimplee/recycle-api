<?php

function UpdateLodge($data, $connection)
{
   $id = $data['id'] ?? '';
   $name = $data['name'] ?? '';
   $description = $data['description'] ?? '';
   $status = $data['status'] ?? 'beschikbaar';
   $visable = $data['visible'] ?? 1;
   $bedrooms = $data['slaapkamers'] ?? 1;
   $people = $data['aantalPersonen'] ?? 2;
   $price = $data['priceRegular'] ?? '';
   $price_winter = $data['priceWinter'] ?? '';
   $image = $data['image'] ?? '';

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