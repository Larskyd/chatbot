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

    private function ensureSessionStarted(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function getCurrentUserId(): ?int
    {
        $this->ensureSessionStarted();
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public function handleRequest(): void
    {
        $this->ensureSessionStarted();

        $userId = $this->getCurrentUserId();
        if ($userId === null) {
            $_SESSION['flash_error'] = 'Du må være logget inn for å se denne siden.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/?page=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['q'])) {
            $this->handleChat();
            return;
        }

        $allCategories = [];
        $recipesByArea = [];
        $randomMeal = null;
        $area = '';

        include __DIR__ . '/../views/chatbot.php';
    }

    public function handleChat()
    {
        $this->ensureSessionStarted();
        $userId = $this->getCurrentUserId();

        $query = trim($_POST['q'] ?? '');
        $response = '';
        $allCategories = $randomMeal = $recipesByArea = $searchResults = [];
        $area = null;
        $selectedRecipe = null;

        if ($query !== '') {
            $lower = mb_strtolower($query, 'UTF-8');

            // Kategorier
            if ($this->matchesCategories($lower)) {
                $res = $this->processCategories();
                $allCategories = $res['allCategories'];
                $response = $res['response'];
            }
            // Tilfeldig forslag
            elseif ($this->matchesRandom($lower)) {
                $res = $this->processRandom();
                $randomMeal = $res['randomMeal'];
                $response = $res['response'];
            }
            // Område-søk
            elseif ($this->isAreaQuery($query)) {
                $res = $this->processArea($query);
                $area = $res['area'];
                $recipesByArea = $res['recipes'];
                $response = $res['response'];

                // lagre siste liste i session
                $_SESSION['last_recipes'] = array_values($recipesByArea);
                $_SESSION['last_recipes_area'] = $area;
            }
            // Velg nummer fra sist listede resultater
            elseif ($this->isSelectionQuery($query) && !empty($_SESSION['last_recipes'])) {
                $res = $this->processSelection($query);
                $selectedRecipe = $res['selectedRecipe'] ?? null;
                $response = $res['response'] ?? '';
            } else {
                $response = 'Søker etter: ' . $query;
            }

            // logg spørringen
            if ($userId !== null && method_exists($this->logModel, 'insertLog')) {
                $metadata = ['area' => $area ?? '', 'response' => $response];
                $this->logModel->insertLog($userId, $query, json_encode($metadata, JSON_UNESCAPED_UNICODE));
            }
        }

        include __DIR__ . '/../views/chatbot.php';
    }

    /* ---------- Extracted helpers ---------- */

    private function matchesCategories(string $lower): bool
    {
        return preg_match('/\b(kategori|kategorier|category|categories)\b/', $lower) === 1;
    }

    private function matchesRandom(string $lower): bool
    {
        return preg_match('/\b(tilfeldig|random|forslag)\b/', $lower) === 1;
    }

    private function isAreaQuery(string $query): bool
    {
        return preg_match('/\b(?:fra|from|område|area)\b\s*:?[\s]*([\p{L}\s\-]+)/iu', $query) === 1;
    }

    private function isSelectionQuery(string $query): bool
    {
        return preg_match('/^\s*(?:vis|show|ja|ok|okei)?\s*#?\s*(\d+)\s*$/iu', $query) === 1;
    }

    private function processCategories(): array
    {
        $cats = $this->recipeModel->getAllCategories() ?? [];
        return [
            'allCategories' => $cats,
            'response' => 'Her er de tilgjengelige kategorier.'
        ];
    }

    private function processRandom(): array
    {
        $rand = $this->recipeModel->getRandomMeal();
        return [
            'randomMeal' => $rand,
            'response' => 'Her er et forslag til en rett:'
        ];
    }

    private function processArea(string $query): array
    {
        preg_match('/\b(?:fra|from|område|area)\b\s*:?[\s]*([\p{L}\s\-]+)/iu', $query, $m);
        $area = isset($m[1]) ? trim($m[1]) : '';
        $recipes = $this->recipeModel->getRecipesByArea($area) ?? [];

        if (empty($recipes)) {
            $response = "Fant ingen retter fra " . htmlspecialchars($area) . ".";
        } else {
            $lines = [];
            foreach ($recipes as $i => $r) {
                $lines[] = ($i + 1) . '. ' . ($r['name'] ?? ($r['title'] ?? 'Ukjent'));
                if ($i >= 9) break;
            }
            $response = "Her er noen retter fra {$area}:\n" . implode("\n", $lines) .
                        "\nVil du se mer på en av dem? Svar f.eks. 'vis 3' eller bare '3'.";
        }

        return [
            'area' => $area,
            'recipes' => $recipes,
            'response' => $response,
        ];
    }

    private function processSelection(string $query): array
    {
        preg_match('/^\s*(?:vis|show|ja|ok|okei)?\s*#?\s*(\d+)\s*$/iu', $query, $m);
        $idx = (int)($m[1] ?? 0) - 1;
        $saved = $_SESSION['last_recipes'] ?? [];

        if (isset($saved[$idx])) {
            $selected = $saved[$idx];
            $response = "Her er detaljene for «" . ($selected['name'] ?? $selected['title'] ?? '') . "».";
            return ['selectedRecipe' => $selected, 'response' => $response];
        } else {
            $response = "Ugyldig nummer. Velg et nummer mellom 1 og " . count($saved) . ".";
            return ['response' => $response];
        }
    }
}
