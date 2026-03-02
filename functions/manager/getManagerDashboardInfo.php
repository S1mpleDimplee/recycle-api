<?php

function getManagerDashboardInfo($connection) {
    $dashboardInfo = [];

    // Get total number of lodges
    $result = mysqli_query($connection, "SELECT COUNT(*) AS totalLodges FROM lodges");
    $dashboardInfo['totalLodges'] = mysqli_fetch_assoc($result)['totalLodges'];

    // Get total number of users
    $result = mysqli_query($connection, "SELECT COUNT(*) AS totalUsers FROM users");
    $dashboardInfo['totalUsers'] = mysqli_fetch_assoc($result)['totalUsers'];

    // Get total number of reservations
    $result = mysqli_query($connection, "SELECT COUNT(*) AS totalReservations FROM reservations");
    $dashboardInfo['totalReservations'] = mysqli_fetch_assoc($result)['totalReservations'];

    // Get total revenue from reservations
    $result = mysqli_query($connection, "SELECT SUM(price) AS totalRevenue FROM reservations");
    $dashboardInfo['totalRevenue'] = mysqli_fetch_assoc($result)['totalRevenue'];

    echo json_encode([
        "success" => true,
        "data" => $dashboardInfo
    ]);
}

?>