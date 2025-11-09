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

// POST: håndter login eller registrering
// Hvis suksessfull, redirect til chatbot siden
if ($page === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';
    if ($action === 'register') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $res = $auth->register($email, $password);
        if ($res['success']) {
            header('Location: ' . BASE_URL . '/?page=chatbot');
            exit;
        } else {
            $errors = $res['errors'];
            require __DIR__ . '/../app/views/login.php';
            exit;
        }
    } else { // login
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

// GET: vis view
// vis chatbot eller login basert på page
$page = $_GET['page'] ?? 'login';

if ($page === 'chatbot') {
    require_once __DIR__ . '/../app/controllers/ChatbotController.php';
    $chatCtrl = new ChatbotController($db);
    $chatCtrl->handleRequest();
    exit;
} else {
    require __DIR__ . '/../app/views/login.php';
}