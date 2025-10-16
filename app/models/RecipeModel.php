<?php
class RecipeModel
{
    // Hent alle kategorier fra TheMealDB API
    public function getAllCategories()
    {
        $url = "https://www.themealdb.com/api/json/v1/1/categories.php";
        $json = file_get_contents($url);
        if ($json === false) return [];
        $data = json_decode($json, true);
        // Returner array av kategorinavn
        if (!empty($data['categories'])) {
            return array_map(function($cat) {
                return $cat['strCategory'];
            }, $data['categories']);
        }
        return [];
    }

    // Hent oppskrifter basert på område (land)
    public function getRecipesByArea($area)
    {
        // Bygg URL dynamisk basert på området
        $url = "https://www.themealdb.com/api/json/v1/1/filter.php?a=" . urlencode($area);
        $json = file_get_contents($url);
        if ($json === false) return [];
        $data = json_decode($json, true);

        // Returner array av oppskrifter
        if (!empty($data['meals'])) {
            return array_map(function($meal) {
                return [
                    'id' => $meal['idMeal'],
                    'name' => $meal['strMeal'],
                    'thumbnail' => $meal['strMealThumb']
                ];
            }, $data['meals']);
        }
        return [];
    }
}
?>