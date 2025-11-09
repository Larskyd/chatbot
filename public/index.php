<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// BASE_URL auto (peker til /.../public)
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
define('BASE_URL', $scriptDir);

// last config og delte klasser
$config = require __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/lib/Database.php';
require_once __DIR__ . '/../app/models/UserModel.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

$db = new Database($config);
$userModel = new UserModel($db);
$auth = new AuthController($userModel);

// enkel routing: ?page=login (default)
$page = $_GET['page'] ?? 'login';

// POST: håndter registrering eller login basert på page
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($page === 'register') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $res = $auth->register($email, $password);
        if ($res['success']) {
            header('Location: ' . BASE_URL . '/?page=chatbot');
            exit;
        } else {
            $errors = $res['errors'];
            require __DIR__ . '/../app/views/register.php';
            exit;
        }
    }

    if ($page === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $res = $auth->login($email, $password);
        if ($res['success']) {
            header('Location: ' . BASE_URL . '/?page=chatbot');
            exit;
        } else {
            $errors = $res['errors'];
            require __DIR__ . '/../app/views/login.php';
            exit;
        }
    }
}

// GET: vis riktig view eller chatbot via controller
if ($page === 'chatbot') {
    require_once __DIR__ . '/../app/controllers/ChatbotController.php';
    $chatCtrl = new ChatbotController($db);
    $chatCtrl->handleRequest();
    exit;
} elseif ($page === 'register') {
    require __DIR__ . '/../app/views/register.php';
    exit;
} elseif ($page === 'history') {
    require __DIR__ . '/../app/controllers/HistoryController.php';
    $historyCtrl = new HistoryController($db);
    $historyCtrl->handleRequest();
    exit;
} else { // default login
    require __DIR__ . '/../app/views/login.php';
    exit;
}