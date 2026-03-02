<?php

function GetLodgeInfo($data, $connection)
{
   $id = mysqli_real_escape_string($connection, $data['id'] ?? '');

   $query = "SELECT * FROM lodge WHERE id = '$id'";
   $result = mysqli_query($connection, $query);

   if ($result && mysqli_num_rows($result) > 0) {
      $lodge = mysqli_fetch_assoc($result);
      echo json_encode(["success" => true, "data" => $lodge]);
   } else {
      echo json_encode(["success" => false, "message" => "Lodge niet gevonden"]);
   }
}