<?php
require_once __DIR__ . '/../models/RecipeModel.php';
require_once __DIR__ . '/../models/QueryLogModel.php';

/**
 * ChatbotController
 * Håndterer chatbot-forespørsler og interaksjoner.
 * 
 * Funksjonalitet:
 * - handleRequest(): Håndterer innkommende forespørsler
 * - handleChat(): Behandler chat-spørringer
 * 
 * - Hjelpefunksjoner
 * - processDone(): Behandler "ferdig"-kommandoen
 * - processCategories(): Hent og prosesser kategorier
 * - processRandom(): Hent og prosesser et tilfeldig måltid
 * - processArea(): Hent og prosesser område-søk
 * - processSelection(): Hent og prosesser valg av oppskrift
 * 
 */
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

    /**
     * Behandle chat-spørring
     */
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
                // Hvis forrige liste var selve kategorilisten, velg en kategori.
                // Hvis forrige liste var en liste med oppskrifter (area eller valgt kategori),
                // vis valgt oppskrift.
                $prevType = $_SESSION['last_list_type'] ?? '';
                if ($prevType === 'categories') {
                    $res = $this->processCategoriesSelection($query);
                } else {
                    // default: behandle som valg av oppskrift fra siste kortliste
                    $res = $this->processSelection($query);
                }
            } elseif ($this->isDoneQuery($lower)) {
                $res = $this->processDone();
            } else {
                $res = [
                    'type' => 'text',
                    'data' => null,
                    'message' => 'Fant ingenting for "' . $query . '". Prøv f.eks. "kategori", "tilfeldig" eller "fra Norge".'
                ];
            }

            // Normaliser resultat for å unngå udefinerte variabler i view
            $responseType = $res['type'] ?? 'text';
            $responseData = $res['data'] ?? null;
            $responseMessage = $res['message'] ?? '';

            // Hvis cards (område eller kategori) — lagre minimal liste i session for påfølgende selection
            if ($responseType === 'cards' && !empty($responseData['items']) && is_array($responseData['items'])) {
                $_SESSION['last_recipes'] = array_map(function($it){
                    return [
                        'id' => $it['id'] ?? null,
                        'name' => $it['name'] ?? null,
                        'thumbnail' => $it['thumbnail'] ?? null
                    ];
                }, $responseData['items']);
                // lagre kontekst: område / category / categories
                $_SESSION['last_recipes_area'] = $responseData['area'] ?? null;
                if (isset($responseData['area'])) {
                    $_SESSION['last_list_type'] = 'area'; // oppskrifter fra område
                } elseif (isset($responseData['category'])) {
                    $_SESSION['last_list_type'] = 'recipes'; // oppskrifter fra valgt kategori
                } else {
                    $_SESSION['last_list_type'] = 'categories'; // liste over kategorier
                }
            }

            // Bygg en kort summary av svaret for historikk (f.eks. navn på retter eller tittel)
            $responseSummary = '';
            if ($responseType === 'cards' && !empty($responseData['items']) && is_array($responseData['items'])) {
                $names = array_map(fn($it) => $it['name'] ?? '', array_slice($responseData['items'], 0, 5));
                $responseSummary = implode(', ', array_filter($names));
            } elseif ($responseType === 'detail' && !empty($responseData['name'])) {
                $responseSummary = $responseData['name'];
            } else {
                $responseSummary = mb_substr(strip_tags((string)$responseMessage), 0, 240);
            }

            // Metadata struktur (hold det kort)
            $metadata = ['area' => $area ?? '', 'type' => $responseType];

            // Logg spørringen med summary og type
            if ($userId !== null && method_exists($this->logModel, 'insertLog')) {
                $this->logModel->insertLog($userId, $query, (string)$responseMessage, $responseType, $responseSummary, $metadata);
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
        return preg_match('/\b(kategori|kategorier|category|categories|kategoriene|type|typene|typer)\b/', $lower) === 1;
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
     * @return array (type = 'cards', data = items[], message)
     */
    private function processCategories(): array
    {
        // Ber om detaljert info fra modellen (true)
        $cats = $this->recipeModel->getAllCategories(true) ?? [];

        $normalized = array_map(function($c) {
            $c = (array)$c;
            return [
                'id' => $c['id'] ?? null,
                'name' => $c['name'] ?? $c['strCategory'] ?? null,
                'thumbnail' => $c['thumbnail'] ?? $c['strCategoryThumb'] ?? null,
                'description' => $c['description'] ?? $c['strCategoryDescription'] ?? null,
            ];
        }, $cats);

        
        return [
            'type' => 'cards',
            'data' => ['items' => array_values($normalized)],
            'message' => empty($normalized) ? 'Ingen kategorier funnet.' : "Her er de tilgjenglige karegoriene: \nVelg en kategori for å få retter fra den kategorien."
        ];
    }

    /**
     * Hent og prosesser nummerert valg fra kategoriliste
     *
     * @param string $query
     * @return array (type = 'cards', data = items[], message)
     */
    private function processCategoriesSelection(string $query): array
    {
        if (!preg_match('/(\d+)/', $query, $m)) {
            return ['type' => 'text', 'data' => null, 'message' => 'Ingen gyldig nummer funnet.'];
        }
        $idx = (int)$m[1] - 1;
        $saved = $_SESSION['last_recipes'] ?? [];
        if (!isset($saved[$idx])) {
            return ['type' => 'text', 'data' => null, 'message' => 'Ugyldig nummer.'];
        }

        $entry = $saved[$idx];
        $categoryName = $entry['name'] ?? null;
        if (empty($categoryName)) {
            return ['type' => 'text', 'data' => null, 'message' => 'Kunne ikke finne kategorinavnet for valget.'];
        }

        // Hent oppskrifter i valgt kategori fra modellen
        $recipes = $this->recipeModel->filterByCategory($categoryName) ?? [];

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
            'data' => ['category' => $categoryName, 'items' => array_values($normalized)],
            'message' => empty($normalized) ? "Fant ingen retter i kategorien {$categoryName}." : "Her er retter i kategorien {$categoryName}: \nSkriv nummeret til en av rettene for å se detaljer."
        ];
    }

    /**
     * Hent og prosesser et tilfeldig måltid
     *
     * @return array (type = 'detail', data, message)
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
     * @return array (type = 'cards', data = items[], message)
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
            'message' => empty($normalized)
                ? "Fant ingen retter fra {$area}."
                : "Her er noen retter fra {$rawArea}:\nSkriv nummeret til en av rettene for å se detaljer."
        ];
    }

    /**
     * Hent og prosesser nummerert valg fra område-søk
     *
     * @param string $query
     * @return array (type = 'detail', data, message)
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
     * @return array (type = 'text', data = null, message)
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
