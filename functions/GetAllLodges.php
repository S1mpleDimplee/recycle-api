<?php
function GetAllLodges($conn)
{
  $sql = "
    SELECT
      lodgeid,
      name,
      capacity,
      base_price,
      description
    FROM lodge
    ORDER BY created_at DESC
  ";

  $result = mysqli_query($conn, $sql);

  if (!$result) {
    echo json_encode([
      "success" => false,
      "message" => mysqli_error($conn)
    ]);
    return;
  }

  echo json_encode([
    "success" => true,
    "data" => mysqli_fetch_all($result, MYSQLI_ASSOC)
  ]);
}
