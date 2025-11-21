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
            $_SESSION['flash_error'] = 'Du må være logget inn for å se denne siden.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=login');
            exit;
        }

        // NEW: if chat input submitted, delegate to handleChat
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['q'])) {
            // handleChat expects to include the view itself
            $this->handleChat();
            return;
        }

        $allCategories = [];
        $recipesByArea = [];
        $randomMeal = null;
        $area = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // existing button-based handling...
            // ...existing code...
        }

        include __DIR__ . '/../views/chatbot.php';
    }

    /**
     * Håndter chat-innsending
     * 
     * @return void
     */
    public function handleChat()
    {
        $this->ensureSessionStarted();
        $userId = $this->getCurrentUserId();

        $query = trim($_POST['q'] ?? '');
        $response = '';
        $allCategories = $randomMeal = $recipesByArea = $searchResults = [];
        $area = null;

        if ($query !== '') {
            $lower = mb_strtolower($query, 'UTF-8');

            if (preg_match('/\b(kategori|kategorier|category|categories)\b/', $lower)) {
                $allCategories = $this->recipeModel->getAllCategories() ?? [];
                $response = 'Her er tilgjengelige kategorier.';
            } elseif (preg_match('/\b(tilfeldig|random|forslag)\b/', $lower)) {
                $randomMeal = $this->recipeModel->getRandomMeal();
                $response = 'Forslag til en rett:';
            } elseif (preg_match('/\b(?:fra|from|område|area)\b\s*:?[\s]*([\p{L}\s\-]+)/iu', $query, $m)) {
                $area = trim($m[1]);
                $recipesByArea = $this->recipeModel->getRecipesByArea($area) ?? [];
                $response = 'Oppskrifter fra: ' . $area;
            } elseif (preg_match('/\b(historikk|historie|logg|history)\b/', $lower)) {
                if (method_exists($this->logModel, 'getRecent')) {
                    $searchResults = $this->logModel->getRecent();
                }
                $response = 'Viser siste søk.';
            } else {
                $response = 'Søker etter: ' . $query;
            }

            // logg spørringen med riktig modell og metode (tilpass parametre etter insertLog-signaturen)
            if ($userId !== null && method_exists($this->logModel, 'insertLog')) {
                $metadata = ['area' => $area ?? '', 'response' => $response];
                $this->logModel->insertLog($userId, $query, json_encode($metadata, JSON_UNESCAPED_UNICODE));
            }
        }

        // gjør variablene tilgjengelige for view
        include __DIR__ . '/../views/chatbot.php';
    }

}
