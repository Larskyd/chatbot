<?php
require_once __DIR__ . '/../models/RecipeModel.php';

// Kontroller for chatbot-funksjonalitet
// Håndterer getAllCategories-forespørsler

class ChatbotController {
    public function handleRequest() {
        $allCategories = [];
        $model = new RecipeModel();

        if (isset($_POST['showCategories'])) {
            $allCategories = $model->getAllCategories();
        }

        include __DIR__ . '/../views/chatbot.php';
    }
}
?>