<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
session_start();

include_once '../functions/authentication/authentication.php';
include_once '../functions/users/GetAllUsers.php';
include_once '../functions/users/GetUserData.php';
include_once '../functions/users/UpdateUserData.php';
include_once '../functions/users/DeleteUser.php';
include_once '../functions/mail/confirmEmailAddress.php';

// Database connection
$connection = mysqli_connect("jaylanovanderveen.nl", "jaylanovanderv_recycle", "hawktuah", "jaylanovanderv_recycle");
if (!$connection) {
    die(json_encode([
        "success" => false,
        "message" => "Connectie met de database is mislukt."
    ]));
}

// Read the received data
$request = json_decode(file_get_contents('php://input'), true);
if (!$request) {
    die(json_encode([
        "success" => false,
        "message" => "Er is iets fout gegaan."
    ]));
}

// Get function name and data
$function = strtolower($request['function'] ?? '');
$data = $request['data'] ?? [];

switch ($function) {
    // Auth
    case 'loginuser':
        checkLogin($data, $connection);
        break;
    case 'registeruser':
        registerUser($data, $connection);
        break;

    // Mail / verification
    case 'sendverificationmail':
        SendVerificationEmail($data, $connection);
        break;
    case 'confirmemailwithlink':
        confirmEmailWithLink($data, $connection);
        break;

    default:
        echo json_encode([
            "success" => false,
            "message" => "Functie niet gevonden"
        ]);
        break;
}
