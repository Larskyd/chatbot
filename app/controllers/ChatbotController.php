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
        
        include __DIR__ . '/../views/chatbot.php';
    }

    public function handleChat()
    {
        $this->ensureSessionStarted();
        $userId = $this->getCurrentUserId();

        $query = trim($_POST['q'] ?? '');
        $responseType = 'text';
        $responseData = null;
        $responseMessage = '';
        $area = null;

        if ($query !== '') {
            $lower = mb_strtolower($query, 'UTF-8');

            if ($this->matchesCategories($lower)) {
                $res = $this->processCategories();
            } elseif ($this->matchesRandom($lower)) {
                $res = $this->processRandom();
            } elseif ($this->isAreaQuery($query)) {
                $res = $this->processArea($query);
                $area = $res['data']['area'] ?? null;
            } elseif ($this->isSelectionQuery($query) && !empty($_SESSION['last_recipes'])) {
                $res = $this->processSelection($query);
            } elseif ($this->isDoneQuery($lower)) {
                $res = $this->processDone();
            } else {
                $res = [
                    'type' => 'text',
                    'data' => null,
                    'message' => 'Fant ingenting for "' . $query . '". Prøv f.eks. "kategori", "tilfeldig" eller "fra Norge".'
                ];
            }

            // Normaliser resultat
            $responseType = $res['type'] ?? 'text';
            $responseData = $res['data'] ?? null;
            $responseMessage = $res['message'] ?? '';

            // Hvis cards (område) — lagre minimal liste i session for påfølgende selection
            if ($responseType === 'cards' && !empty($responseData['items']) && is_array($responseData['items'])) {
                $_SESSION['last_recipes'] = array_map(function($it){
                    return [
                        'id' => $it['id'] ?? null,
                        'name' => $it['name'] ?? null,
                        'thumbnail' => $it['thumbnail'] ?? null
                    ];
                }, $responseData['items']);
                $_SESSION['last_recipes_area'] = $responseData['area'] ?? null;
            }

            // Logg spørringen
            if ($userId !== null && method_exists($this->logModel, 'insertLog')) {
                $metadata = ['area' => $area ?? '', 'type' => $responseType];
                $this->logModel->insertLog($userId, $query, json_encode($metadata, JSON_UNESCAPED_UNICODE));
            }
        }

        // Gjør variablene tilgjengelige for view (view bruker $responseType, $responseData, $responseMessage)
        include __DIR__ . '/../views/chatbot.php';
    }

    /* ---------- Hjelpefunksjoner ---------- */

    /**
     * Sjekk om spørringen matcher kategorier
     * 
     * @param string $lower
     * @return bool
     */
    private function matchesCategories(string $lower): bool
    {
        return preg_match('/\b(kategori|kategorier|category|categories)\b/', $lower) === 1;
    }

    /**
     * Sjekk om spørringen matcher tilfeldig forslag
     * 
     * @param string $lower
     * @return bool
     */
    private function matchesRandom(string $lower): bool
    {
        return preg_match('/\b(tilfeldig|random|forslag)\b/', $lower) === 1;
    }

    /**
     * Sjekk om spørringen er et område-søk
     * 
     * @param string $query
     * @return bool
     */
    private function isAreaQuery(string $query): bool
    {
        return preg_match('/\b(?:fra|from|område|area)\b\s*:?[\s]*([\p{L}\s\-]+)/iu', $query) === 1;
    }

    /**
     * Sjekk om spørringen er et nummerert valg
     * 
     * @param string $query
     * @return bool
     */
    private function isSelectionQuery(string $query): bool
    {
        return preg_match('/\d+/', $query) === 1;
    }

    /**
     * Sjekk om spørringen indikerer ferdig/spørsmål avsluttet
     * 
     * @param string $query
     * @return bool
     */
    private function isDoneQuery(string $query): bool
    {
        return preg_match('/\b(ferdig|done|avslutt|exit|slutt|takk)\b/', mb_strtolower($query, 'UTF-8')) === 1;
    }

    /**
     * Hent og prosesser kategorier
     *
     * @return array
     */
    private function processCategories(): array
    {
        $cats = $this->recipeModel->getAllCategories() ?? [];
        $data = ['items' => array_values($cats)];
        $message = empty($cats) ? 'Ingen kategorier funnet.' : 'Tilgjengelige kategorier: ' . implode(', ', $cats);

        return [
            'type' => 'list',
            'data' => $data,
            'message' => $message
        ];
    }

    /**
     * Hent og prosesser et tilfeldig måltid
     *
     * @return array
     */
    private function processRandom(): array
    {
        $meal = $this->recipeModel->getRandomMeal();
        $data = $meal ? $meal : null;
        $message = $meal ? ('Forslag: ' . ($meal['name'] ?? '')) : 'Fant ingen forslag akkurat nå.';

        return [
            'type' => 'detail',
            'data' => $data,
            'message' => $message
        ];
    }

    /**
     * Hent og prosesser område-søk
     *
     * @param string $query
     * @return array
     */
    private function processArea(string $query): array
    {
        preg_match('/\b(?:fra|from|område|area)\b\s*:?[\s]*([\p{L}\s\-]+)/iu', $query, $m);
        $rawArea = isset($m[1]) ? trim($m[1]) : '';
        $area = $this->recipeModel->normalizeArea($rawArea) ?? $rawArea;
        $recipes = $this->recipeModel->getRecipesByArea($area) ?? [];

        $normalized = array_map(function($r) {
            $r = (array)$r;
            return [
                'id' => $r['id'] ?? $r['idMeal'] ?? null,
                'name' => $r['name'] ?? $r['strMeal'] ?? null,
                'thumbnail' => $r['thumbnail'] ?? $r['strMealThumb'] ?? null,
            ];
        }, $recipes);

        return [
            'type' => 'cards',
            'data' => ['area' => $area, 'items' => array_values($normalized)],
            'message' => empty($normalized) ? "Fant ingen retter fra {$area}." : "Her er noen retter fra {$area}:"
        ];
    }

    /**
     * Hent og prosesser nummerert valg fra område-søk
     *
     * @param string $query
     * @return array
     */
    private function processSelection(string $query): array
    {
        if (!preg_match('/(\d+)/', $query, $m)) {
            return ['type'=>'text','data'=>null,'message'=>'Ingen gyldig nummer funnet.'];
        }
        $idx = (int)$m[1] - 1;
        $saved = $_SESSION['last_recipes'] ?? [];
        if (!isset($saved[$idx])) {
            return ['type'=>'text','data'=>null,'message'=>'Ugyldig nummer.'];
        }
        $entry = $saved[$idx];
        $id = $entry['id'] ?? null;
        $selected = $id ? $this->recipeModel->getRecipeById($id) : (array)$entry;

        // normaliser felter
        if (is_array($selected)) {
            $selected['thumbnail'] = $selected['thumbnail'] ?? $selected['strMealThumb'] ?? null;
            $selected['name'] = $selected['name'] ?? $selected['strMeal'] ?? null;
            $selected['instructions'] = $selected['instructions'] ?? $selected['strInstructions'] ?? null;
            $selected['category'] = $selected['category'] ?? $selected['strCategory'] ?? null;
            $selected['area'] = $selected['area'] ?? $selected['strArea'] ?? null;
        }

        return [
            'type' => 'detail',
            'data' => $selected,
            'message' => $selected['name'] ?? 'Detaljer'
        ];
    }

    /**
     * Prosesser ferdig/spørsmål avsluttet
     *
     * @return array
     */
    private function processDone(): array
    {
        return [
            'type' => 'text',
            'data' => null,
            'message' => 'Versågod! Håper maten frister! Si ifra hvis du trenger mer hjelp.'
        ];
    }
}
