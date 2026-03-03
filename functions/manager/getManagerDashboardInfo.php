<?php

function GetManagerDashboardInfo($connection)
{

    // Bookings today 
    $result = mysqli_query($connection, "SELECT COUNT(*) AS total FROM booking WHERE check_in = CURDATE()");
    $bookingsToday = mysqli_fetch_assoc($result)['total'];

    // Bookings checkings last month
    $result = mysqli_query($connection, "SELECT COUNT(*) AS total FROM booking WHERE MONTH(check_in) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(check_in) = YEAR(CURDATE() - INTERVAL 1 MONTH)");
    $bookingsLastMonth = mysqli_fetch_assoc($result)['total'];

    // Total accounts
    $result = mysqli_query($connection, "SELECT COUNT(*) AS total FROM user");
    $totalAccounts = mysqli_fetch_assoc($result)['total'];

    // new accounts since 2 months ago
    $result = mysqli_query($connection, "SELECT COUNT(*) AS total FROM user WHERE created_at >= CURDATE() - INTERVAL 2 MONTH");
    $accountsGrowth = mysqli_fetch_assoc($result)['total'];

    // Revenue this month
    $result = mysqli_query($connection, "SELECT SUM(total_price) AS total FROM booking WHERE MONTH(check_in) = MONTH(CURDATE()) AND YEAR(check_in) = YEAR(CURDATE()) AND status IN ('bevestigd')");
    $revenueThisMonth = mysqli_fetch_assoc($result)['total'] ?? 0;

    // Revenue last month
    $result = mysqli_query($connection, "SELECT SUM(total_price) AS total FROM booking WHERE MONTH(check_in) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(check_in) = YEAR(CURDATE() - INTERVAL 1 MONTH)");
    $revenueLastMonth = mysqli_fetch_assoc($result)['total'] ?? 0;

    $result = mysqli_query($connection, "SELECT status, COUNT(*) AS total FROM booking GROUP BY status");
    $bookingStatus = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $bookingStatus[] = $row;
    }

    // Monthly bookings this year
    $result = mysqli_query($connection, "SELECT MONTH(check_in) AS month, COUNT(*) AS total FROM booking WHERE YEAR(check_in) = YEAR(CURDATE()) GROUP BY MONTH(check_in)");
    $monthlyRaw = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $monthlyRaw[$row['month']] = $row['total'];
    }

    $monthLabels = ["Jan", "Feb", "Mrt", "Apr", "Mei", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dec"];
    $monthlyBookings = [];
    for ($i = 1; $i <= 12; $i++) {
        $monthlyBookings[] = [
            "label" => $monthLabels[$i - 1] . " " . date('Y'),
            "value" => (int) ($monthlyRaw[$i] ?? 0),
            "color" => "#3b82f6"
        ];
    }

    // Latest 3 bookings
    $result = mysqli_query($connection, "
        SELECT b.id, b.check_in, b.status, u.name AS guestName
        FROM booking b
        LEFT JOIN user u ON b.user_id = u.id
        ORDER BY b.id DESC
        LIMIT 3
    ");
    $latestBookings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $nameParts = explode(" ", trim($row['guestName'] ?? "Onbekend"));
        $initials = strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1));
        $latestBookings[] = [
            "id" => $row['id'],
            "guestName" => $row['guestName'] ?? "Onbekend",
            "status" => $row['status'],
            "date" => date("d F Y", strtotime($row['check_in'])),
            "initials" => $initials
        ];
    }

    $result = mysqli_query($connection, "SELECT COUNT(*) AS total FROM lodge");
    $totalLodges = mysqli_fetch_assoc($result)['total'];

    $result = mysqli_query($connection, "SELECT COUNT(*) AS total FROM lodge WHERE status = 'bezet'");
    $occupiedLodges = mysqli_fetch_assoc($result)['total'];

    $occupancyPct = $totalLodges > 0 ? round(($occupiedLodges / $totalLodges) * 100) : 0;


    $result = mysqli_query($connection, "SELECT COUNT(*) AS total FROM lodge WHERE status = 'onderhoud'");
    $ongoingRepairs = mysqli_fetch_assoc($result)['total'];

    echo json_encode([
        "success" => true,
        "data" => [
            "bookingsToday" => (int) $bookingsToday,
            "bookingsLastMonth" => (int) $bookingsLastMonth,
            "registeredAccounts" => (int) $totalAccounts,
            "accountsGrowth" => (int) $accountsGrowth,
            "revenue" => (float) $revenueThisMonth,
            "revenueLastMonth" => (float) $revenueLastMonth,
            "Repairs" => (int) $ongoingRepairs,
            "bookingStatus" => $bookingStatus,
            "monthlyBookings" => $monthlyBookings,
            "latestBookings" => $latestBookings,
            "lodgeOccupancy" => $occupancyPct,
            "totalLodges" => (int) $totalLodges,
            "occupiedLodges" => (int) $occupiedLodges,
        ]
    ]);
}