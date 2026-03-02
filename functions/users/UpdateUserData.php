<?php

function UpdateUserData($data, $connection)
{
   $userId = $data['id'] ?? '';
   $name = $data['name'] ?? '';
   $email = $data['email'] ?? '';
   $phonenumber = $data['phonenumber'] ?? '';
   $role = $data['role'] ?? 0;
   $verified = $data['email_verified'] ?? 0;

   // Address fields
   $street = $data['street'] ?? '';
   $housenumber = $data['housenumber'] ?? '';
   $addition = $data['addition'] ?? '';
   $zipcode = $data['zipcode'] ?? '';
   $city = $data['city'] ?? '';

   if (empty($userId)) {
      echo json_encode(["success" => false, "message" => "Gebruiker ID is verplicht"]);
      return;
   }

   $query = "UPDATE user SET name='$name', email='$email', phonenumber='$phonenumber', role='$role', email_verified='$verified' WHERE id='$userId'";
   $result = mysqli_query($connection, $query);

   if (!$result) {
      echo json_encode(["success" => false, "message" => "Fout bij updaten gebruiker: " . mysqli_error($connection)]);
      return;
   }

   // Check if address exists
   $check = mysqli_query($connection, "SELECT id FROM address WHERE user_id = '$userId'");

   if (mysqli_num_rows($check) > 0) {
      $query = "UPDATE address SET street='$street', housenumber='$housenumber', addition='$addition', zipcode='$zipcode', city='$city' WHERE user_id='$userId'";
   } else {
      $query = "INSERT INTO address (user_id, street, housenumber, addition, zipcode, city) VALUES ('$userId', '$street', '$housenumber', '$addition', '$zipcode', '$city')";
   }

   $result = mysqli_query($connection, $query);

   if (!$result) {
      echo json_encode(["success" => false, "message" => "Fout bij updaten adres: " . mysqli_error($connection)]);
      return;
   }

   echo json_encode(["success" => true, "message" => "Gebruiker succesvol bijgewerkt"]);
}