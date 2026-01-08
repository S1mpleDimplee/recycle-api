<?php

require "../emailtriggers/newnotification.php";

function getNotificationPresets($preset, $info = "", $appointmenttime = "", $appointmentcar = "")
{
    $presets = [
        "welcome" => [
            "title" => "Welkom bij onze dienst!",
            "message" => "Bedankt voor het registreren. We zijn blij je aan boord te hebben.",
            "type" => "info"
        ],
        "password_change" => [
            "title" => "Wachtwoord gewijzigd",
            "message" => "Je wachtwoord is succesvol gewijzigd. Als jij dit niet was, neem dan onmiddellijk contact op met de ondersteuning.",
            "type" => "warning"
        ],
        "verfication_success" => [
            "title" => "Account geverifieerd",
            "message" => "Je account is succesvol geverifieerd. Je kunt nu inloggen en onze diensten gebruiken.",
            "type" => "success"
        ],
        "caradded" => [
            "title" => "Nieuwe auto toegevoegd",
            "message" => "U heeft een nieuwe auto genaamd " . $info . " aan uw account toegevoegd. U ontvangt nu meldingen voor deze auto.",
            "type" => "info"
        ],
        "cardeleted" => [
            "title" => "Auto verwijderd",
            "message" => "De auto " . $info . " is van uw account verwijderd. Als dit een vergissing is, neem dan contact op met de klantenservice.",
            "type" => "warning"
        ],
        "caredited" => [
            "title" => "Auto aangepast",
            "message" => "De gegevens van uw auto " . $info . " zijn succesvol aangepast.",
            "type" => "success"
        ],
        "invoice_paid" => [
            "title" => "Factuur betaald",
            "message" => "Uw factuur #" . $info . " is succesvol betaald. Bedankt voor uw betaling!",
            "type" => "success"
        ],
        "created_appointment" => [
            "title" => "Afspraak aangemaakt",
            "message" => "Uw afspraak op " . $appointmenttime . " is succesvol aangemaakt. voor de auto: " . $appointmentcar . ".",
            "type" => "info"
        ],
        "cancelled_appointment" => [
            "title" => "Afspraak geannuleerd",
            "message" => "Uw afspraak op " . $appointmenttime . " is succesvol geannuleerd.",
            "type" => "warning"
        ]
    ];

    return $presets[$preset] ?? ["title" => "", "message" => ""];
}

function AddNotification($data, $conn)
{
    global $notifcationpresets;

    $userid = $data['userid'] ?? null;
    $preset = $data['preset'] ?? null;
    $info = $data['info'] ?? "";
    $appointmenttime = $data['appointmenttime'] ?? "";
    $appointmentcar = $data['appointmentcar'] ?? "";

    $title = getNotificationPresets($preset, $info)['title'] ?? "";
    $message = getNotificationPresets($preset, $info)['message'] ?? "";

    if ($title == "" && $message == "") {
        return;
    }
    sendNewNotification([
        "userid" => $userid,
        "title" => $title,
        "message" => $message
    ], $conn);
    $createNotifcationSQL = "INSERT INTO notifications (userid, title, description, date) VALUES ('$userid', '$title', '$message', NOW())";
    mysqli_query($conn, $createNotifcationSQL);
}

function FetchNotifcations($data, $conn)
{
    $userid = $data['userid'] ?? null;

    $fetchNotifcationsSQL = "SELECT * FROM notifications WHERE userid = $userid ORDER BY date DESC";
    $result = mysqli_query($conn, $fetchNotifcationsSQL);

    $notifications = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }

    echo json_encode([
        "success" => true,
        "message" => "Notifications fetched successfully",
        "data" => $notifications
    ]);
}

?>