<?php
require_once __DIR__ . '/../models/RecipeModel.php';

class ChatbotService
{
    protected $recipeModel;

    public function __construct($recipeModel = null)
    {
        $this->recipeModel = $recipeModel ?: new RecipeModel();
    }

    /**
     * Rute spørring til riktig prosessor basert på innhold.
     *
     * @param string $query
     * @param array|null $saved Tidligere lagrede resultater for valg (f.eks. kategorier eller retter)
     * @return array ['type'=>'text|cards|detail','data'=>mixed,'message'=>string]
     */
    public function routeQuery(string $query, ?array $saved = null): array
    {
        $q = trim((string)$query);
        $lower = mb_strtolower($q, 'UTF-8');

        if ($q === '') {
            return ['type' => 'text', 'data' => null, 'message' => 'Skriv inn et spørsmål eller kommando.'];
        }

        if (preg_match('/\b(kategori|kategorier|categories?)\b/i', $lower)) {
            return $this->processCategories();
        }

        if (preg_match('/\b(tilfeldig|random)\b/i', $lower)) {
            return $this->processRandom();
        }

        if ($this->isAreaQuery($q)) {
            return $this->processArea($q);
        }

        if ($this->isDoneQuery($lower)) {
            return $this->processDone();
        }

        if ($this->isSelectionQuery($q) && is_array($saved) && count($saved) > 0) {
            return $this->processSelection($q, $saved);
        }

        // fallback
        return ['type' => 'text', 'data' => null, 'message' => 'Fant ingenting for "' . $q . '". Prøv f.eks. "kategori", "tilfeldig" eller "fra Italy".'];
    }

    private function isAreaQuery(string $q): bool
    {
        return (bool)preg_match('/\b(?:fra|from|område|area)\b\s*:?[\s]*([\p{L}\s\-]+)/iu', $q);
    }

    private function isSelectionQuery(string $q): bool
    {
        return (bool)preg_match('/^\s*(\d+)\s*$/', $q);
    }

    private function isDoneQuery(string $lower): bool
    {
        return in_array(trim($lower), ['ferdig', 'takk', 'done', 'avslutt', 'quit']);
    }


    /**
     * Behandle kategorier-forespørsel.
     * 
     * @return array ['type'=>'cards','data'=>['items'=>[...]],'message'=>string]
     */
    public function processCategories(): array
    {
        $cats = method_exists($this->recipeModel, 'getAllCategories') ? $this->recipeModel->getAllCategories(true) : [];
        $normalized = array_map(function ($c) {
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
            'message' => empty($normalized) ? 'Ingen kategorier funnet.' : 'Velg en kategori (skriv nummer):'
        ];
    }

    /**
     * Behandle tilfeldig rett-forespørsel.
     * 
     * @return array ['type'=>'detail','data'=>mixed,'message'=>string]
     */
    public function processRandom(): array
    {
        $meal = method_exists($this->recipeModel, 'getRandomMeal') ? $this->recipeModel->getRandomMeal() : null;
        $message = $meal ? ('Forslag: ' . ($meal['name'] ?? $meal['strMeal'] ?? '')) : 'Fant ingen forslag akkurat nå.';
        return ['type' => 'detail', 'data' => $meal ?: null, 'message' => $message];
    }

    /**
     * Behandle område-baserte rett-forespørsel.
     * 
     * @param string $query
     * @return array ['type'=>'cards','data'=>['items'=>[...]],'message'=>string]
     */
    public function processArea(string $query): array
    {
        preg_match('/\b(?:fra|from|område|area)\b\s*:?[\s]*([\p{L}\s\-]+)/iu', $query, $m);
        $rawArea = isset($m[1]) ? trim($m[1]) : '';
        $area = null;
        if (method_exists($this->recipeModel, 'normalizeArea')) {
            $area = $this->recipeModel->normalizeArea($rawArea) ?? $rawArea;
        } else {
            $area = $rawArea;
        }

        $recipes = method_exists($this->recipeModel, 'getRecipesByArea') ? $this->recipeModel->getRecipesByArea($area) : [];
        $items = array_map(function ($r) {
            $r = (array)$r;
            return [
                'id' => $r['id'] ?? $r['idMeal'] ?? null,
                'name' => $r['name'] ?? $r['strMeal'] ?? null,
                'thumbnail' => $r['thumbnail'] ?? $r['strMealThumb'] ?? null,
            ];
        }, $recipes);

        return [
            'type' => 'cards',
            'data' => ['area' => $area, 'items' => array_values($items)],
            'message' => empty($items) ? "Fant ingen retter fra {$rawArea}." : "Her er noen retter fra {$rawArea}:\nSkriv nummeret til en av rettene for å se detaljer."
        ];
    }

    /**
     * Behandle numerisk valg mot en lagret liste.
     * $saved forventes å være en liste med elementer som har minst 'id' eller 'name'.
     * 
     * @param string $query
     * @param array $saved
     * @return array ['type'=>'text|cards|detail','data'=>mixed,'message'=>string]
     */
    public function processSelection(string $query, array $saved): array
    {
        if (!preg_match('/(\d+)/', $query, $m)) {
            return ['type' => 'text', 'data' => null, 'message' => 'Ingen gyldig nummer funnet.'];
        }
        $idx = (int)$m[1] - 1;
        if (!isset($saved[$idx])) {
            return ['type' => 'text', 'data' => null, 'message' => 'Ugyldig nummer.'];
        }

        $entry = $saved[$idx];
        // Hvis oppføringen har en id -> hent full oppskrift
        $id = $entry['id'] ?? null;
        if ($id && method_exists($this->recipeModel, 'getRecipeById')) {
            $selected = $this->recipeModel->getRecipeById($id);
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

        // Hvis oppføringen har et navn, prøv å behandle det som et kategorinavn og hent oppskrifter
        $catName = $entry['name'] ?? null;
        if ($catName && method_exists($this->recipeModel, 'filterByCategory')) {
            $recipes = $this->recipeModel->filterByCategory($catName) ?? [];
            $items = array_map(function ($r) {
                $r = (array)$r;
                return [
                    'id' => $r['id'] ?? $r['idMeal'] ?? null,
                    'name' => $r['name'] ?? $r['strMeal'] ?? null,
                    'thumbnail' => $r['thumbnail'] ?? $r['strMealThumb'] ?? null,
                ];
            }, $recipes);
            return [
                'type' => 'cards',
                'data' => ['category' => $catName, 'items' => array_values($items)],
                'message' => empty($items) ? "Fant ingen retter i kategorien {$catName}." : "Her er retter i kategorien {$catName}:"
            ];
        }

        return ['type' => 'text', 'data' => null, 'message' => 'Kunne ikke finne valgt element.'];
    }

    public function processDone(): array
    {
        return ['type' => 'text', 'data' => null, 'message' => 'Versågod! Håper maten frister! Si ifra hvis du trenger mer hjelp.'];
    }
}
