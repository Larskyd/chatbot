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
}
