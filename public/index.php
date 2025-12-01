<?php
// Start session tidlig
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Basic error mode 
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

// load config hvis den ikke er lastet inn tidligere
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

// Auth controller + model (Dependency Injection)
$userModel = new \UserModel($db);
$auth = new \AuthController($userModel);

// Enkel routing basert på "page" parameter
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

// Helper: redirect funksjon
$redirect = function ($path) {
    header('Location: ' . BASE_URL . $path);
    exit;
};

// Handle logout
if ($page === 'logout') {
    $auth->logout();
    $redirect('/?page=login&loggedout=1');
}

// POST handling 
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

}

// GET / page routing
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