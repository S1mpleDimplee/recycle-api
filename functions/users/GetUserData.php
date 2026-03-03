<?php

function GetUserData($data, $connection)
{
   $userId = $data['userid'] ?? '';

   $query = "
        SELECT u.*, a.street, a.housenumber, a.addition, a.zipcode, a.city
        FROM user u
        LEFT JOIN address a ON a.user_id = u.id
        WHERE u.id = '$userId'
    ";
   $result = mysqli_query($connection, $query);

   if ($result && mysqli_num_rows($result) > 0) {
      $userData = mysqli_fetch_assoc($result);
      unset($userData['password']); // dnt return psw
      echo json_encode(["success" => true, "data" => $userData]);
   } else {
      echo json_encode(["success" => false, "message" => "Gebruiker niet gevonden"]);
   }
}