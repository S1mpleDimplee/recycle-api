<?php


function getNotifications($data, $conn)
{

    $userid = $data['userid'] ?? null;

    $sql = "SELECT * FROM notifications WHERE userid = '$userid' ORDER BY date DESC";
    $result = mysqli_query($conn, $sql);

    $notifications = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }

    echo json_encode([
        "success" => true,
        "message" => "Notifications retrieved successfully",
        "data" => $notifications
    ]);
}