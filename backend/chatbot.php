<?php
require_once "tmdb.php";
header('Content-Type: application/json');

// Hent brukerinput
$input = json_decode(file_get_contents("php://input"), true);
$message = strtolower(trim($input['message']));

// Map vibe-ord til sjanger eller keyword
$vibeMap = [
    "morsom" => ["genre" => 35],
    "koselig" => ["genre" => 10751],
    "romantisk" => ["genre" => 10749],
    "skummel" => ["genre" => 27],
    "action" => ["genre" => 28],
    "høst" => ["keyword" => "autumn"],
    "feelgood" => ["keyword" => "feel good"]
];

$found = false;
$genreId = null;
$keyword = null;

foreach($vibeMap as $key => $data) {
    if(strpos($message, $key) !== false) {
        $found = true;
        $genreId = $data['genre'] ?? null;
        $keyword = $data['keyword'] ?? null;
        break;
    }
}

// Standard "ligner på [film]" håndtering
if(!$found && strpos($message, "ligner på") !== false) {
    $movieName = str_ireplace(["hva","ligner på","?",".", "gi meg"], "", $message);
    $movieName = trim($movieName);
    $found = true;
    $searchResult = searchMovie($movieName);

    if(!empty($searchResult['results'])) {
        $movieId = $searchResult['results'][0]['id'];
        $recommendations = callApi("/movie/$movieId/recommendations?language=en-US");
        $reply = [];
        foreach(array_slice($recommendations['results'],0,3) as $movie) {
            $reply[] = [
                "title" => $movie['title'],
                "year" => substr($movie['release_date'],0,4) ?? "Ukjent",
                "poster" => $movie['poster_path'] ? "https://image.tmdb.org/t/p/w200" . $movie['poster_path'] : "",
                "overview" => $movie['overview'] ?? ""
            ];
        }
        echo json_encode($reply);
        exit;
    } else {
        echo json_encode(["error"=>"Fant ingen filmer som matcher '$movieName'."]);
        exit;
    }
}

// Hvis det er en vibe-forespørsel
if($found) {
    if($genreId) {
        $movies = discoverMoviesByGenre($genreId);
    } elseif($keyword) {
        $movies = searchMovieByKeyword($keyword);
    }

    $reply = [];
    foreach(array_slice($movies['results'],0,3) as $movie) {
        $reply[] = [
            "title" => $movie['title'],
            "year" => substr($movie['release_date'],0,4) ?? "Ukjent",
            "poster" => $movie['poster_path'] ? "https://image.tmdb.org/t/p/w200" . $movie['poster_path'] : "",
            "overview" => $movie['overview'] ?? ""
        ];
    }
    echo json_encode($reply);
    exit;
}

// Hvis ingenting gjenkjent
echo json_encode(["error"=>"Beklager, jeg forstår ikke hva slags film du vil ha. Prøv f.eks: 'gi meg en morsom film' eller 'gi meg en koselig film'."]);
