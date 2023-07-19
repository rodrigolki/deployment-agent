<?php
namespace App\Controllers\web;

use App\Traits\View;

class Controller {
    use View;

    public function createSlug($string) {
        $pattern = '/[^a-z0-9]+/i'; // Matches any non-alphanumeric characters
        $replacement = '-'; // Replaces non-alphanumeric characters with a dash (-)
        
        $slug = preg_replace($pattern, $replacement, $string);
        $slug = trim($slug, '-'); // Removes leading and trailing dashes (-)
        $slug = strtolower($slug); // Converts the slug to lowercase
        
        return $slug;
    }
}

?>