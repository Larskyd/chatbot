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

    // Hente oppskrifter basert på land 
    public function getRecipesByArea()
    {
        $url = "https://www.themealdb.com/api/json/v1/1/filter.php?a=Canadian";
        $json = file_get_contents($url);
        if ($json === false) return [];
        $data = json_decode($json, true);

        //returner array av oppskrifter fra ulike land
        if (!empty($data['land'])) {
            return array_map(function($area) {
                return $area['strArea'];
            }, $data['land']);
        }
        return [];
    }
}


?>