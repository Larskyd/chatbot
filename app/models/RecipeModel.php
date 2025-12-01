<?php
require_once __DIR__ . '/../lib/HttpClient.php';

class RecipeModel
{
    protected HttpClient $http;

    public function __construct(?HttpClient $http = null)
    {
        $this->http = $http ?? new HttpClient();
    }

    /**
     * Hent alle kategorinavn.
     *
     * @param bool $detailed Hvis true returnerer hver kategori som array med keys: id, name, thumbnail, description
     * @return array Array av strenger (standard) eller array av associative arrays hvis $detailed=true
     */
    public function getAllCategories(bool $detailed = false): array
    {
        $url = "https://www.themealdb.com/api/json/v1/1/categories.php";
        $data = $this->http->fetchJson($url);
        if (empty($data['categories'])) return [];

        // Returner bare navn
        if (!$detailed) {
            return array_map(function ($cat) {
                return $cat['strCategory'];
            }, $data['categories']);
        }

        // Returner detaljert liste: returner id, navn, thumbnail og beskrivelse
        return array_map(function ($cat) {
            return [
                'id' => $cat['idCategory'] ?? null,
                'name' => $cat['strCategory'] ?? null,
                'thumbnail' => $cat['strCategoryThumb'] ?? null,
                'description' => $cat['strCategoryDescription'] ?? null,
            ];
        }, $data['categories']);
    }

    /**
     * Hent oppskrifter basert på kategori.
     *
     * @param string $category Navn på kategori (f.eks. "Seafood")
     * @return array[] Array av oppskrifter med keys: id, name, thumbnail
     */
    public function filterByCategory(string $category): array
    {
        $url = "https://www.themealdb.com/api/json/v1/1/filter.php?c=" . urlencode($category);
        $data = $this->http->fetchJson($url);
        if (empty($data['meals'])) return [];
        return array_map(function ($meal) {
            return [
                'id' => $meal['idMeal'] ?? null,
                'name' => $meal['strMeal'] ?? null,
                'thumbnail' => $meal['strMealThumb'] ?? null
            ];
        }, $data['meals']);
    }

    /**
     * Hent en tilfeldig matrett.
     *
     * @return array|null Array med matrettdata eller null ved feil
     */
    public function getRandomMeal()
    {
        $url = "https://www.themealdb.com/api/json/v1/1/random.php";
        $data = $this->http->fetchJson($url);
        if (empty($data['meals'][0])) return null;
        $meal = $data['meals'][0];
        return [
            'name' => $meal['strMeal'] ?? null,
            'thumbnail' => $meal['strMealThumb'] ?? null,
            'category' => $meal['strCategory'] ?? null,
            'area' => $meal['strArea'] ?? null,
            'instructions' => $meal['strInstructions'] ?? null
        ];
    }

    /**
     * Hent oppskrifter basert på område (land).
     *
     * @param string $area Navn på område (f.eks. "Italian")
     * @return array[] Array av oppskrifter med keys: id, name, thumbnail
     */
    public function getRecipesByArea($area)
    {
        $url = "https://www.themealdb.com/api/json/v1/1/filter.php?a=" . urlencode($area);
        $data = $this->http->fetchJson($url);
        if (empty($data['meals'])) return [];
        return array_map(function ($meal) {
            return [
                'id' => $meal['idMeal'] ?? null,
                'name' => $meal['strMeal'] ?? null,
                'thumbnail' => $meal['strMealThumb'] ?? null
            ];
        }, $data['meals']);
    }

    /**
     * Hent oppskrift etter ID.
     *
     * @param int|string $id Oppskrifts-ID
     * @return array|null Array med oppskriftsdata eller null ved feil
     */
    public function getRecipeById($id)
    {
        $id = urlencode((string)$id);
        $url = "https://www.themealdb.com/api/json/v1/1/lookup.php?i={$id}";
        $data = $this->http->fetchJson($url);
        if (empty($data['meals'][0])) return null;
        $m = $data['meals'][0];
        return [
            'id' => $m['idMeal'] ?? null,
            'name' => $m['strMeal'] ?? null,
            'thumbnail' => $m['strMealThumb'] ?? null,
            'category' => $m['strCategory'] ?? null,
            'area' => $m['strArea'] ?? null,
            'instructions' => $m['strInstructions'] ?? null,
        ];
    }

    /**
     * Hent alle tilgjengelige "areas" fra API (cache i statisk variabel)
     *
     * @return string[] liste av area navn, eller [] ved feil
     */
    public function getAllAreas(): array
    {
        static $cache = null;
        if ($cache !== null) return $cache;

        $url = "https://www.themealdb.com/api/json/v1/1/list.php?a=list";
        $data = $this->http->fetchJson($url);
        if (empty($data['meals'])) {
            $cache = [];
            return $cache;
        }
        $cache = array_map(fn($m) => $m['strArea'] ?? null, $data['meals']);
        return $cache;
    }

    /**
     * Normaliser brukerinput til en gyldig area-verdi for APIet.
     * Returnerer canonical area (f.eks. "Italian") eller null hvis ingen match.
     *
     * @param string $input
     * @return string|null
     */
    public function normalizeArea(string $input): ?string
    {
        $s = trim(mb_strtolower($input, 'UTF-8'));
        // Fjern eventuelle ledende ord som "fra", "from", "område", "area"
        $s = preg_replace('/^(fra|from|område|area)\b[:\s]*/u', '', $s);

        // Oversett til ASCII for enklere matching
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', $s) ?: $s;
        $ascii = preg_replace('/[^a-z\s]/', '', strtolower($ascii));
        $ascii = trim($ascii);

        if ($ascii === '') return null;

        // Hardkodet mapping for vanlige varianter/feilstavinger
        $map = [
            'italy' => 'Italian', 'italia' => 'Italian', 'italiensk' => 'Italian', 
            'spain' => 'Spanish', 'spania' => 'Spanish', 'spansk' => 'Spanish',
            'france' => 'French', 'fransk' => 'French', 'french' => 'French',
            'norway' => 'Norwegian', 'norge' => 'Norwegian', 'norsk' => 'Norwegian',
            'uk' => 'British', 'england' => 'British', 'britain' => 'British', 'britisk' => 'British',
            'japan' => 'Japanese', 'japansk' => 'Japanese',
            'china' => 'Chinese', 'kina' => 'Chinese', 'kinesisk' => 'Chinese',
            'mexico' => 'Mexican', 'meksiko' => 'Mexican', 'mexikansk' => 'Mexican',
            'india' => 'Indian', 'indisk' => 'Indian',
            'greece' => 'Greek', 'gresk' => 'Greek',
            'thailand' => 'Thai', 'thai' => 'Thai',
            'morocco' => 'Moroccan', 'marokko' => 'Moroccan', 'marokkansk' => 'Moroccan',
            'vietnam' => 'Vietnamese', 'vietnamesisk' => 'Vietnamese',
            'cuba' => 'Cuban', 'kubansk' => 'Cuban',
            'brazil' => 'Brazilian', 'brasiliansk' => 'Brazilian',
            'jamaica' => 'Jamaican', 'jamaicansk' => 'Jamaican',
            'russia' => 'Russian', 'russisk' => 'Russian',
            'germany' => 'German', 'tyskland' => 'German', 'tysk' => 'German',
        ];
        if (isset($map[$ascii])) return $map[$ascii];

        // Sjekk direkte mot listen av gyldige areas
        $areas = $this->getAllAreas();
        $normAreas = array_map(function($a){
            $t = @iconv('UTF-8', 'ASCII//TRANSLIT', mb_strtolower((string)$a, 'UTF-8')) ?: mb_strtolower((string)$a, 'UTF-8');
            return preg_replace('/[^a-z\s]/', '', $t);
        }, $areas);

        // Hvis det er en eksakt match med listen av gyldige areas
        $idx = array_search($ascii, $normAreas, true);
        if ($idx !== false && isset($areas[$idx])) return $areas[$idx];

        // Enkel fuzzy (Levenshtein) søk etter nærmeste match
        $best = null; $bestDist = PHP_INT_MAX;
        foreach ($normAreas as $i => $na) {
            $dist = levenshtein($ascii, $na);
            if ($dist < $bestDist) {
                $bestDist = $dist;
                $best = $areas[$i] ?? null;
            }
        }
        // Terskel for fuzzy matching (3 endringer)
        if ($best !== null && $bestDist <= 3) {
            return $best;
        }

        // Ingen match funnet
        return null;
    }
}
