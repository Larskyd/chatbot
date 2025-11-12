<?php
require_once __DIR__ . '/../models/RecipeModel.php';
require_once __DIR__ . '/../models/QueryLogModel.php';

class ChatbotController
{
    protected RecipeModel $recipeModel;
    protected QueryLogModel $logModel;


    public function __construct($db)
    {
        $this->recipeModel = new RecipeModel($db);
        $this->logModel = new QueryLogModel($db);
    }

    /**
     * Sørg for at session er startet.
     */
    private function ensureSessionStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Helperfunksjon
     * Hent ID for nåværende bruker fra session.
     */
    private function getCurrentUserId(): ?int
    {
        $this->ensureSessionStarted();
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    /**
     * Håndter request, sett opp variabler for viewet
     */
    public function handleRequest(): void
    {
        $this->ensureSessionStarted();

        // Auth-guard: redirect to login hvis ikke innlogget
        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            // sett flash-melding og redirect til login
            $_SESSION['flash_error'] = 'Du må være logget inn for å se denne siden.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=login');
            exit;
        }

        $allCategories = [];
        $recipesByArea = [];
        $area = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['showCategories'])) {
                $allCategories = $this->recipeModel->getAllCategories() ?? [];
            }

            if (!empty($_POST['showRecipesByArea'])) {
                $area = trim((string)($_POST['area'] ?? ''));
                if ($area !== '') {
                    $recipesByArea = $this->recipeModel->getRecipesByArea($area) ?? [];

                    $responseText = json_encode(array_map(fn($r) => ($r['title'] ?? ''), $recipesByArea), JSON_UNESCAPED_UNICODE);
                    $metadata = ['count' => count($recipesByArea)];
                    $this->logModel->insertLog($userId, $area, $responseText, $metadata);
                }
            }
        }

        // gjør variablene tilgjengelige for view
        include __DIR__ . '/../views/chatbot.php';
    }
}
