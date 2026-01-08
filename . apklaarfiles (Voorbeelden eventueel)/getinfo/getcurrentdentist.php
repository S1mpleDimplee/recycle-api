<?php


function getCurrentDentistName($userid, $conn)
{
    $sql = "    ";

    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    json_encode([
        "success" => true,
        "message" => "Huidige tandarts succesvol opgehaald",
        "data" => $user['currentdentistid']
    ]);
}
?>