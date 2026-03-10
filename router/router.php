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

include_once '../authentication/authentication.php';
include_once '../functions/users/GetAllUsers.php';
include_once '../functions/users/GetUserData.php';
include_once '../functions/users/UpdateUserData.php';
include_once '../functions/users/DeleteUser.php';
include_once '../functions/lodges/AddLodge.php';
include_once '../functions/lodges/GetAllLodges.php';
include_once '../functions/lodges/UpdateLodge.php';
include_once '../functions/lodges/GetLodgeInfo.php';
include_once '../functions/lodges/DeleteLodge.php';
include_once '../functions/manager/getManagerDashboardInfo.php';
// include_once '../functions/receptionist.php';
include_once '../functions/Schedules/manager.php';
include_once '../functions/Schedules/bookings.php';
include_once '../functions/Schedules/mechanic.php';
include_once '../functions/Schedules/receptionist.php';
include_once '../functions/mail/confirmEmailAddress.php';

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
    case 'deleteuser':
        DeleteUser($data, $connection);
        break;
    case 'updateuserdata':
        UpdateUserData($data, $connection);
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
    case 'getallbookings':
        GetAllBookings($connection);
        break;
    case 'cancelbooking':
        CancelBooking($data, $connection);
        break;
    case 'createbooking':
        CreateBooking($data, $connection);
        break;
    case 'changebooking':
        ChangeBooking($data, $connection);
        break;
    case "getbookingsbyuserid":
        GetBookingsByUserId($data, $connection);
        break;
    case "getallrepairs":
        GetAllRepairs($connection);
        break;
    case "updaterepairmaintenance":
        UpdateRepairMaintenance($data, $connection);
        break;
    case "updaterepairavailable":
        UpdateRepairAvailable($data, $connection);
        break;
    case "getavailablelodges":
        GetAvailableLodges($data, $connection);
        break;
    case "getcleaningschedule":
        GetCleaningSchedule($connection);
        break;
    default:
        echo json_encode([
            "success" => false,
            "message" => "Functie niet gevonden"
        ]);
        break;
}
