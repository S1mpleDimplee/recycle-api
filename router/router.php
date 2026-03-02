<?php
include_once '../authentication/authentication.php';
include_once '../functions/mail/confirmEmailAddress.php';
include_once '../functions/users/GetAllUsers.php';
include_once '../functions/users/GetUserData.php';
include_once '../functions/lodges/AddLodge.php';
include_once '../functions/lodges/GetAllLodges.php';
include_once '../functions/lodges/UpdateLodge.php';
include_once '../functions/lodges/GetLodgeInfo.php';
include_once '../functions/lodges/DeleteLodge.php';
include_once '../functions/manager/getManagerDashboardInfo.php';

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
session_start();

// Database connection
$connection = mysqli_connect("jaylanovanderveen.nl", "jaylanovanderv_sparesortDB", "uYQ7pNDnxz4xvH2KjmBb", "jaylanovanderv_sparesortDB");
if (!$connection) {
    die(json_encode([
        "success" => false,
        "message" => "Connectie met de database is mislukt contacteer ons via sparesortbali@gmail.com"
    ]));
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
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
    case 'adduser':
        addUser($data, $connection);
        break;
    case 'loginuser':
        checkLogin($data, $connection);
        break;
    case 'getalllodges':
        GetAllLodges($connection);
        break;
    case 'sendverificationmail':
        SendVerificationEmail($data, $connection);
        break;
    case 'confirmemailwithlink':
        confirmEmailWithLink($data, $connection);
        break;
    case 'getallusers':
        GetAllUsers($connection);
        break;
    case 'getuserdata':
        GetUserData($data, $connection);
        break;
    case 'addlodge':
        addLodge($data, $connection);
        break;
    case 'updatelodge':
        UpdateLodge($data, $connection);
        break;
    case 'getlodgeinfo':
        GetLodgeInfo($data, $connection);
        break;
    case 'deletelodge':
        DeleteLodge($data, $connection);
        break;
    case 'getmanagerdashboardinfo':
        GetManagerDashboardInfo($connection);
        break;
    default:
        echo json_encode([
            "success" => false,
            "message" => "Functie niet gevonden"
        ]);
        break;
}
