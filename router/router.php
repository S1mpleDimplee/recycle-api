<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Database connection
$connection = mysqli_connect("jaylanovanderveen.nl", "jaylanovanderv_sparesortDB", "uYQ7pNDnxz4xvH2KjmBb", "jaylanovanderv_sparesortDB");
if (!$connection) {
    die(json_encode([
        "success" => false,
        "message" => "Connectie met de database is mislukt contacteer ons via sparesortbali@gmail.com"
    ]));
}

// Read the received  data
$request = json_decode(file_get_contents('php://input'), true);
if (!$request) {
    die(json_encode([
        "success" => false,
        "message" => "Er is iets fout gegaan contacteer ons via apklaar@gmail.com"
    ]));
}

// Get function name and data splits them in two variables
$function = strtolower($request['function'] ?? '');
$data = $request['data'] ?? [];

// Uses the function name to use the given function
switch ($function) {
    // User / auth
    case 'loginuser':
        checkLogin($data, $connection);
        break;
    default:
        echo json_encode([
            "success" => false,
            "message" => "Functie niet gevonden"
        ]);
        break;
}
