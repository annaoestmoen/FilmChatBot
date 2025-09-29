<?php
require_once __DIR__ . '/../config/config.php';

function callApi($endpoint) {
    $url = TMDB_BASE_URL . $endpoint . "&api_key=" . TMDB_API_KEY;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function searchMovie($query) {
    return callApi("/search/movie?query=" . urlencode($query));
}

function discoverMoviesByGenre($genreId) {
    return callApi("/discover/movie?with_genres=$genreId&sort_by=popularity.desc");
}

function searchMovieByKeyword($keyword) {
    return callApi("/search/movie?query=" . urlencode($keyword));
}
