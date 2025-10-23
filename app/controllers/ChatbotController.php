<?php
require_once __DIR__ . '/../models/RecipeModel.php';

/**
 * ChatbotController
 *
 * Håndterer innkommende forespørsler fra frontend og leverer data til viewet.
 */
class ChatbotController {
    public function handleRequest() {
        $allCategories = [];
        $recipesByArea = [];
        $model = new RecipeModel();

        // Prøv initialisere DB logger hvis database er tilgjengelig
        $logger = null;
        try {
            if (file_exists(__DIR__ . '/../config.php') &&
                file_exists(__DIR__ . '/../lib/Database.php') &&
                file_exists(__DIR__ . '/../models/QueryLogModel.php')) {

                require_once __DIR__ . '/../config.php';
                require_once __DIR__ . '/../lib/Database.php';
                require_once __DIR__ . '/../models/QueryLogModel.php';

                $cfg = require __DIR__ . '/../config.php';
                $db = new Database($cfg);
                $logger = new QueryLogModel($db);
            }
        } catch (\Throwable $e) {
            // Hvis logger ikke fungerer, fortsett uten logging
            $logger = null;
        }

        // Håndter forespørsel om kategorier
        if (isset($_POST['showCategories'])) {
            $allCategories = $model->getAllCategories();

            // Logg forespørselen hvis logger er tilgjengelig
            if ($logger) {
                try {
                    $logger->insertLog(null, 'showCategories', json_encode(['count' => count($allCategories)]));
                } catch (\Throwable $e) {
                    // Ignorer logging-feil
                }
            }
        }

        // Håndter forespørsel om oppskrifter basert på område
        if (isset($_POST['showRecipesByArea']) && !empty($_POST['area'])) {
            $area = $_POST['area'];
            $recipesByArea = $model->getRecipesByArea($area);

            // Logg forespørselen hvis logger er tilgjengelig
            if ($logger) {
                try {
                    $logger->insertLog(null, 'showRecipesByArea: ' . $area, json_encode(['count' => count($recipesByArea)]));
                } catch (\Throwable $e) {
                    // Ignorer logging-feil
                }
            }
        }

        // Render view
        include __DIR__ . '/../views/chatbot.php';
    }
}
?>