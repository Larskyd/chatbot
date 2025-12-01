<?php
// Start session tidlig
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Basic error mode (kan settes fra config)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// BASE_URL auto
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
define('BASE_URL', $scriptDir);

// Load app bootstrap + dependencies
$baseApp = __DIR__ . '/../app';
$vendorAutoload = __DIR__ . '/../vendor/autoload.php';
    require_once $baseApp . '/config.php';
    require_once $baseApp . '/lib/Database.php';
    require_once $baseApp . '/models/UserModel.php';
    require_once $baseApp . '/controllers/AuthController.php';
    require_once $baseApp . '/controllers/ChatbotController.php';
    require_once $baseApp . '/controllers/HistoryController.php';

// load config (composer autoload may already do this)
$config = $config ?? require $baseApp . '/config.php';

// Create DB + models
try {
    $db = new \Database($config);
} catch (\Throwable $e) {
    http_response_code(500);
    echo 'Database error.';
    error_log('DB connect error: ' . $e->getMessage());
    exit;
}

// Instantiate commonly used models/controllers (dependency injection)
$userModel = new \UserModel($db);
$auth = new \AuthController($userModel);

// Allowlist for pages - sanitise input and avoid arbitrary file include
$allowedPages = [
    'login' => true,
    'register' => true,
    'chatbot' => true,
    'history' => true,
    'logout' => true,
];

$pageRaw = $_GET['page'] ?? 'login';
$page = preg_replace('/[^a-z0-9_]/i', '', (string)$pageRaw);
if (!isset($allowedPages[$page])) {
    $page = 'login';
}

// Helper: redirect to path within BASE_URL
$redirect = function ($path) {
    header('Location: ' . BASE_URL . $path);
    exit;
};

// Handle logout early
if ($page === 'logout') {
    $auth->logout();
    $redirect('/?page=login&loggedout=1');
}

// POST handling - keep PRG and small handlers
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'POST') {
    if ($page === 'register') {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $res = $auth->register($email, $password, $name);
        if ($res['success']) {
            $redirect('/?page=chatbot');
        } else {
            $errors = $res['errors'];
            require $baseApp . '/views/register.php';
            exit;
        }
    }

    if ($page === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $res = $auth->login($email, $password);
        if ($res['success']) {
            $redirect('/?page=chatbot');
        } else {
            $errors = $res['errors'];
            require $baseApp . '/views/login.php';
            exit;
        }
    }

    // If other POST endpoints needed later, add them here (use CSRF check!)
}

// GET / page routing (thin)
switch ($page) {
    case 'chatbot':
        require_once $baseApp . '/controllers/ChatbotController.php';
        $chatCtrl = new \ChatbotController($db);
        $chatCtrl->handleRequest();
        break;

    case 'history':
        require_once $baseApp . '/controllers/HistoryController.php';
        $historyCtrl = new \HistoryController($db);
        $historyCtrl->handleRequest();
        break;

    case 'register':
        require $baseApp . '/views/register.php';
        break;

    case 'login':
    default:
        require $baseApp . '/views/login.php';
        break;
}