<?php
require_once __DIR__ . '/../models/RecipeModel.php';

// Kontroller for chatbot-funksjonalitet
class ChatbotController {
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

        include __DIR__ . '/../views/chatbot.php';
    }
}
?>