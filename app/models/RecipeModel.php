<?php
class RecipeModel
{
    /**
     * Hjelpefunksjon.
     * 
     * Hent JSON fra URL, prøv file_get_contents først, så cURL.
     * Returnerer decoded array eller null ved feil.
     * 
     * @param string $url
     * @param int $timeout
     * @return array|null
     */
    private function fetchJson(string $url, int $timeout = 10): ?array
    {
        // Prøv file_get_contents med timeout context
        $ctx = stream_context_create([
            'http' => ['timeout' => $timeout, 'user_agent' => 'PHP/' . PHP_VERSION],
            'https' => ['timeout' => $timeout, 'user_agent' => 'PHP/' . PHP_VERSION],
        ]);
        $json = @file_get_contents($url, false, $ctx);
        if ($json === false) {
            // fallback til cURL
            if (!function_exists('curl_version')) {
                error_log("fetchJson: unable to fetch {$url} - no cURL and file_get_contents failed");
                return null;
            }
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_USERAGENT => 'PHP/' . PHP_VERSION,
            ]);
            $json = curl_exec($ch);
            $errno = curl_errno($ch);
            $err = curl_error($ch);
            $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($json === false || $errno || ($http >= 400 && $http !== 0)) {
                error_log("fetchJson: cURL error fetching {$url} - err={$errno}, http={$http}, msg={$err}");
                return null;
            }
        }

        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("fetchJson: json decode error for {$url}: " . json_last_error_msg());
            return null;
        }
        return $data;
    }

    /**
     * Hent alle kategorinavn.
     *
     * @return string[] Array av kategorinavn, tom array ved feil eller ingen data.
     */
    public function getAllCategories()
    {
        $url = "https://www.themealdb.com/api/json/v1/1/categories.php";
        $data = $this->fetchJson($url);
        if (empty($data['categories'])) return [];
        return array_map(function ($cat) {
            return $cat['strCategory'];
        }, $data['categories']);
    }

    /**
     * Hent en tilfeldig matrett.
     *
     * @return array|null Array med matrettdata eller null ved feil
     */
    public function getRandomMeal()
    {
        $url = "https://www.themealdb.com/api/json/v1/1/random.php";
        $data = $this->fetchJson($url);
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
        $data = $this->fetchJson($url);
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
        $data = $this->fetchJson($url);
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
        $data = $this->fetchJson($url);
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
