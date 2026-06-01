<?php
require_once __DIR__ . '/../vendor/autoload.php';

$allowedOrigins = ['http://localhost:3000', 'http://localhost:5173'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

set_exception_handler(function (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Er is een serverfout opgetreden.",
        "error"   => $e->getMessage(),
    ]);
    exit();
});

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

session_start();

include_once '../functions/helpers.php';
include_once '../functions/authentication/authentication.php';
include_once '../functions/authentication/ForgotPassword.php';
include_once '../functions/authentication/ResetPassword.php';
include_once '../functions/authentication/Verify2FA.php';
include_once '../functions/authentication/ResendVerification.php';
include_once '../functions/mail/confirmEmailAddress.php';
include_once '../functions/users/DeleteOwnAccount.php';
include_once '../functions/users/GetAllUsers.php';
include_once '../functions/users/GetProfile.php';
include_once '../functions/users/UpdateProfile.php';
include_once '../functions/users/GetUserData.php';
include_once '../functions/users/UpdateUserData.php';
include_once '../functions/users/DeleteUser.php';
include_once '../functions/products/GetAllProducts.php';
include_once '../functions/products/GetUserProducts.php';
include_once '../functions/products/GetProduct.php';
include_once '../functions/products/AddProduct.php';
include_once '../functions/products/UpdateProduct.php';
include_once '../functions/products/DeleteProduct.php';
include_once '../functions/products/BuyNow.php';
include_once '../functions/credits/GetCredits.php';
include_once '../functions/credits/UpdateCredits.php';
include_once '../functions/dashboard/GetUserDashboard.php';
include_once '../functions/dashboard/GetAdminDashboard.php';

// Bids
include_once '../functions/bids/PlaceBid.php';
include_once '../functions/bids/CancelBid.php';
include_once '../functions/bids/GetUserBids.php';
include_once '../functions/bids/GetProductBids.php';
include_once '../functions/bids/AcceptBid.php';
include_once '../functions/bids/RejectBid.php';
include_once '../functions/bids/GetAllProductBids.php';
include_once '../functions/bids/GetAllBids.php';

// Purchases
include_once '../functions/purchases/GetUserPurchases.php';
include_once '../functions/purchases/GetUserSales.php';
include_once '../functions/purchases/GetAllTransactions.php';

// Load .env
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $val] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($val);
    }
}

// Database connection
$connection = mysqli_connect(
    $_ENV['DB_HOST'] ?? '',
    $_ENV['DB_USER'] ?? '',
    $_ENV['DB_PASS'] ?? '',
    $_ENV['DB_NAME'] ?? ''
);
if (!$connection) {
    die(json_encode([
        "success" => false,
        "message" => "Connectie met de database is mislukt."
    ]));
}
mysqli_set_charset($connection, 'utf8mb4');

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

    case 'loginuser':
        checkLogin($data, $connection);
        break;
    case 'registeruser':
        registerUser($data, $connection);
        break;

    case 'sendverificationmail':
        SendVerificationEmail($data, $connection);
        break;
    case 'confirmemailwithlink':
        confirmEmailWithLink($data, $connection);
        break;
    case 'resendverification':
        ResendVerification($data, $connection);
        break;
    case 'forgotpassword':
        ForgotPassword($data, $connection);
        break;
    case 'resetpassword':
        ResetPassword($data, $connection);
        break;
    case 'verify2fa':
        Verify2FA($data, $connection);
        break;
    case 'deleteownaccount':
        DeleteOwnAccount($data, $connection);
        break;

    case 'getuserprofile':
        GetProfile($data, $connection);
        break;
    case 'updateuserprofile':
        UpdateProfile($data, $connection);
        break;

    case 'getuserdashboard':
        GetUserDashboard($data, $connection);
        break;

    case 'getusercredits':
        GetCredits($data, $connection);
        break;

    case 'getuserproducts':
        GetUserProducts($data, $connection);
        break;
    case 'addproduct':
        AddProduct($data, $connection);
        break;
    case 'updateproduct':
        UpdateProduct($data, $connection);
        break;
    case 'deleteproduct':
        DeleteProduct($data, $connection);
        break;
    case 'buynow':
        BuyNow($data, $connection);
        break;

    case 'getallproducts':
        GetAllProducts($connection);
        break;
    case 'getproduct':
        GetProduct($data, $connection);
        break;

    case 'getallusers':
        GetAllUsers($data, $connection);
        break;
    case 'getuserdata':
        GetUserData($data, $connection);
        break;
    case 'updateuserdata':
        UpdateUserData($data, $connection);
        break;
    case 'deleteuser':
        DeleteUser($data, $connection);
        break;

    case 'updateusercredits':
        UpdateCredits($data, $connection);
        break;

    case 'getadmindashboard':
        GetAdminDashboard($data, $connection);
        break;

    // ── Bids ──────────────────────────────────────────────
    case 'placebid':
        PlaceBid($data, $connection);
        break;
    case 'cancelbid':
        CancelBid($data, $connection);
        break;
    case 'getuserbids':
        GetUserBids($data, $connection);
        break;
    case 'getproductbids':
        GetProductBids($data, $connection);
        break;
    case 'acceptbid':
        AcceptBid($data, $connection);
        break;
    case 'rejectbid':
        RejectBid($data, $connection);
        break;
    case 'getallproductbids':
        GetAllProductBids($data, $connection);
        break;
    case 'getallbids':
        GetAllBids($data, $connection);
        break;

    // ── Purchases / history ───────────────────────────────
    case 'getuserpurchases':
        GetUserPurchases($data, $connection);
        break;
    case 'getusersales':
        GetUserSales($data, $connection);
        break;
    case 'getalltransactions':
        GetAllTransactions($data, $connection);
        break;

    default:
        echo json_encode([
            "success" => false,
            "message" => "Functie niet gevonden"
        ]);
        break;
}
