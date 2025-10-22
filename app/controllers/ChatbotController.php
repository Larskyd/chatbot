<?php
require_once __DIR__ . '/../models/RecipeModel.php';

/**
 * ChatbotController
 *
 * Håndterer innkommende forespørsler fra frontend og leverer 
 * data til viewet.
 */
class ChatbotController {
    /**
     * Behandler request og render view eller returnerer data.
     *
     * Forventede POST-felter:
     * - showCategories (on/true)  => returnerer alle kategorier
     * - showRecipesByArea (on/true) + area => returnerer oppskrifter for område
     *
     * @return void
     */
    public function handleRequest() {
        $allCategories = [];
        $recipesByArea = [];
        $model = new RecipeModel();

        // Håndter forespørsel om kategorier
        if (isset($_POST['showCategories'])) {
            $allCategories = $model->getAllCategories();
        }

        // Håndter forespørsel om oppskrifter basert på område
        if (isset($_POST['showRecipesByArea']) && !empty($_POST['area'])) {
            $area = $_POST['area'];
            $recipesByArea = $model->getRecipesByArea($area);
        }

        // Render view (passer data via variabler)
        include __DIR__ . '/../views/chatbot.php';
    }
}
?>