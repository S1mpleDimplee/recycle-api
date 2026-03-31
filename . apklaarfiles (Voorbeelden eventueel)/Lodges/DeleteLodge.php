<?php


function DeleteLodge($data, $connection)
{
   $id = $data['id'] ?? '';

   if (empty($id)) {
      echo json_encode(["success" => false, "message" => "Lodge ID is verplicht"]);
      return;
   }

   $query = "DELETE FROM lodge WHERE id = '$id'";
   $result = mysqli_query($connection, $query);

   if (!$result) {
      echo json_encode(["success" => false, "message" => "Fout bij verwijderen lodge: " . mysqli_error($connection)]);
      return;
   }

   echo json_encode([
      "success" => true,
      "message" => "Lodge succesvol verwijderd!"
   ]);
}