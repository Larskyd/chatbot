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
     * Håndter request, sett opp variabler for viewet
     */
    public function handleRequest(): void
    {
        // defaults
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

                    // logg søket — juster userId hvis du har autentisering
                    $userId = $_SESSION['user_id'] ?? null;
                    $responseText = json_encode(array_map(fn($r)=>($r['title'] ?? ''), $recipesByArea), JSON_UNESCAPED_UNICODE);
                    $metadata = ['count' => count($recipesByArea)];
                    $this->logModel->insertLog($userId, $area, $responseText, $metadata);
                }
            }
        }

        // gjør variablene tilgjengelige for view
        include __DIR__ . '/../views/chatbot.php';
    }
}