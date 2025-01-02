<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;

$httpClient = new Client([
    'headers' => [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
    ],
]);

// Requesting record ID from the table
$MovieID = isset($_GET['id']) ? $_GET['id'] : '';

// Link region
$IMDB_Elozetes = "";
$Film_Boritokep = "";
$Film_Cim = "";
$Film_Hossz = "";
$Film_Megjelenes = "";
$Film_Link = "";
$Film_leiras = "";
$Film_Kedvenc = false;
$Film_Korhatar = "";
$IMDBCode = "";

if ($MovieID) {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=links_db', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT * FROM links WHERE id = :id");
        $stmt->execute(['id' => $MovieID]);
        $movie = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($movie) {
            // Set the title of the page
            echo "<title>" . htmlspecialchars($movie['movie_title']) . "</title>";
            $Film_Cim = $movie['movie_title'];
            $Film_Boritokep = $movie['cover'];
            $Film_Hossz = $movie['movie_length'];
            $Film_Megjelenes = $movie['release_date'];
            $Film_Link = $movie['link'];
            $Film_leiras = $movie['description'];
            $IMDBCode = $movie['imdb_code'];

            // Check if the movie is in the favorites table
            $stmt = $pdo->prepare("SELECT Count(*) FROM favorites WHERE movieID = :id");
            $stmt->execute(['id' => $MovieID]);
            $movie = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($movie['Count(*)'] > 0) {
                $Film_Kedvenc = true;
            }

            // Trace back the IMDB data
            $IMDB_BaseLink = "https://www.imdb.com";
            $imdbLink = $IMDB_BaseLink . "/title/" . str_replace(" ", "+", $IMDBCode);
            //echo "<br><a href=\"" . $imdbLink . "\">IMDB Link</a>";

            $NeedsUpdate = false;
            // Check if the database has a record for the trailer link
            $stmt2 = $pdo->prepare("SELECT Count(*) FROM trailers WHERE imdb_id = :id");
            $stmt2->execute(['id' => $IMDBCode]);
            $movie2 = $stmt2->fetch(PDO::FETCH_ASSOC);

            // If the record exists, fetch the trailer link
            if ($movie2['Count(*)'] > 0) {
                // Check if the record is over 12 hours old
                $stmt2 = $pdo->prepare("SELECT RecordTime FROM trailers WHERE imdb_id = :id");
                $stmt2->execute(['id' => $IMDBCode]);
                $movie2 = $stmt2->fetch(PDO::FETCH_ASSOC);

                $RecordTime = $movie2['RecordTime'];
                $RecordTime = strtotime($RecordTime);
                $CurrentTime = time();
                $Difference = $CurrentTime - $RecordTime;

                // If the record is over 2 hours old, update it
                if ($Difference > 7200) {
                    $NeedsUpdate = true;
                }
                else{
                    $stmt2 = $pdo->prepare("SELECT * FROM trailers WHERE imdb_id = :id");
                    $stmt2->execute(['id' => $IMDBCode]);
                    $movie2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                    $IMDB_Elozetes = $movie2['trailer_link'];
                    $Film_Link = $movie2['link'];
                }
            } else {
                // If no trailer is found in the database, fetch the trailer link
                $httpClient = new Client([
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3'
                    ]
                ]);

                $response = $httpClient->get($IMDB_BaseLink . "/title/" . str_replace(" ", "+", $IMDBCode));
                $htmlString = (string) $response->getBody();
                libxml_use_internal_errors(true);
                $doc = new DOMDocument();
                $doc->loadHTML($htmlString);
                $xpath = new DOMXPath($doc);

                $trailerDiv = $xpath->evaluate('//div[@id="__next"]')->item(0);
                $IMDBVideoURL = "";
                if ($trailerDiv) {
                    $links = $trailerDiv->getElementsByTagName('a');
                    foreach ($links as $link) {
                        $href = $link->getAttribute('href');
                        if (strpos($href, '/video') !== false) {
                            if (strpos($link->textContent, 'Trailer') !== false) {
                                $IMDBVideoURL = $IMDB_BaseLink . $href;
                                break;
                            }
                        }
                    }
                } else {
                    //echo 'Trailer not found.';
                }

                $script = 'fetchPage.js';

                $output = [];
                $returnVar = -1;

                // Execute the Puppeteer script to extract video tags
                while($returnVar !== 0 || count($output) === 0){
                    exec("node $script " . escapeshellarg($IMDBVideoURL), $output, $returnVar);
                }
                // exec("node $script " . escapeshellarg($IMDBVideoURL), $output, $returnVar);

                if ($returnVar === 0) {
                    $IMDB_Elozetes = $output[0];
                    $stmt2 = $pdo->prepare("INSERT INTO trailers (imdb_id, trailer_link, RecordTime) VALUES (:imdb_id, :trailer_link, CURRENT_TIMESTAMP)");
                    $stmt2->execute([
                        'imdb_id' => $IMDBCode,  // Using movie IMDB code
                        'trailer_link' =>  $IMDB_Elozetes       // The trailer URL extracted
                    ]);
                    $NeedsUpdate = true;
                } else {
                    echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var footer = document.createElement('div');
                        footer.style.position = 'fixed';
                        footer.style.bottom = '0';
                        footer.style.left = '0';
                        footer.style.width = '100%';
                        footer.style.backgroundColor = '#121212';
                        footer.style.color = 'red';
                        footer.style.padding = '10px';
                        footer.style.textAlign = 'center';
                        footer.innerText = 'Hiba történt a Puppeteer script futtatásakor.';
                        document.body.appendChild(footer);
                    });
                </script>";
                }

                $NeedsUpdate = true;
            }

        }

        // If no movies found, pull it from mozimix.com
        if ($Film_Link == "" || $NeedsUpdate) {
            // Remove every date from the movie title
            $Film_Cim = preg_replace('/\(\d{4}\)(\s*\(\d+\))*$/', '', $Film_Cim);
            // Remove dots from the end of the movie title
            $Film_Cim = preg_replace('/\.*$/', '', trim($Film_Cim));

            $response2 = $httpClient->get("https://mozimix.com/?s=" . $Film_Cim);
            $htmlContent2 = (string) $response2->getBody();
            libxml_use_internal_errors(true);
            $domDocument2 = new DOMDocument();
            $domDocument2->loadHTML($htmlContent2);
            $xpath2 = new DOMXPath($domDocument2);

            // Find all the movie titles
            $movies = $xpath2->evaluate('//div[@id="dt_contenedor"]//div[@id="contenedor"]//div[@class="module"]//div[@class="content rigth csearch"]//div[@class="search-page"]//div[@class="result-item"]//article//div[@class="details"]//div[@class="title"]/a');

            $ExportLink = "";
            $movieLink = "";
            // Write out every movie link
            foreach ($movies as $index => $movieTitleElement) {
                $movieLink = $movieTitleElement->getAttribute('href');
                // Extract inner html
                $movieName = trim($movieTitleElement->textContent);
            
                // Clean the movie title
                $cleanedFilmCim = preg_replace('/\(\d{4}\)(\s*\(\d+\))*$/', '', $Film_Cim);
                $cleanedFilmCim = preg_replace('/\.*$/', '', $cleanedFilmCim);
                $cleanedFilmCim = trim($cleanedFilmCim);
                
                // Case-insensitive comparison
                if (strcasecmp($cleanedFilmCim, $movieName) == 0) {
                    $ExportLink = $movieLink;
                    $BestLink = $movieLink;
                    break;
                }
            }

            // Export the video from the new source from the video tag
            if ($ExportLink !== "") {
                while($Film_Link === ""){
                    $ExportLink = exec("node index.js " . escapeshellarg($ExportLink));
                    $Film_Link = $ExportLink;
                }
                // $ExportLink = exec("node index.js " . escapeshellarg($BestLink));
                // $Film_Link = $ExportLink;
            }

            // Update the trailer link in the database with the new link
            $stmt2 = $pdo->prepare("UPDATE trailers SET link = :link, RecordTime = CURRENT_TIMESTAMP WHERE imdb_id = :id");
            $stmt2->execute([
                'link' => $Film_Link,
                'id' => $IMDBCode
            ]);
        }

    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
    }
} else {
    echo 'No ID provided.';
}

// Finally add the id of the film to recents table
// First check if the record already exists
$stmt = $pdo->prepare("SELECT Count(*) FROM recents WHERE movieID = :id");
$stmt->execute(['id' => $MovieID]);
$movie = $stmt->fetch(PDO::FETCH_ASSOC);
if($movie['Count(*)'] == 0) {
    $stmt = $pdo->prepare("INSERT INTO recents (movieID) VALUES (:id)");
    $stmt->execute(['id' => $MovieID]);
}
else{
    // Move the record to the top by deleting and re-inserting it
    $stmt = $pdo->prepare("DELETE FROM recents WHERE movieID = :id");
    $stmt->execute(['id' => $MovieID]);

    $stmt = $pdo->prepare("INSERT INTO recents (movieID) VALUES (:id)");
    $stmt->execute(['id' => $MovieID]);
}

// Age limit from OMDB. Can be placed in index.php
$apikey = ""; // Your OMDB API key here
$FetchedOMDBData = file_get_contents("http://www.omdbapi.com/?i=" . $IMDBCode . "&apikey=" . $apikey);
$OMDBData = json_decode($FetchedOMDBData, true);
$Film_Korhatar = $OMDBData['Rated'];
$Film_besorolas_szoveg = '';

switch ($Film_Korhatar) {
    case 'G':
        $Film_Korhatar_Magyar = '0';
        $Film_besorolas_szoveg = 'Minden korosztály számára megtekinthető';
        break;
    case 'PG':
        $Film_Korhatar_Magyar = '6';
        $Film_besorolas_szoveg = 'A műsorszám megtekíntése 6 éven aluliak számára nem ajánlott';
        break;
    case 'PG-13':
        $Film_Korhatar_Magyar = '12';
        $Film_besorolas_szoveg = 'A műsorszám megtekíntése 12 éven aluliak számára nem ajánlott';
        break;
    case 'R':
        $Film_Korhatar_Magyar = '16';
        $Film_besorolas_szoveg = 'A műsorszám megtekíntése 16 éven aluliak számára nem ajánlott';
        break;
    case 'NC-17':
        $Film_Korhatar_Magyar = '18';
        $Film_besorolas_szoveg = 'A műsorszám megtekíntése 18 éven aluliak számára nem ajánlott';
        break;
    case 'X':
        $Film_Korhatar_Magyar = 'X';
        $Film_besorolas_szoveg = "Csak felnőtteknek! Durva tartalom!";
        break;
    default:
        $Film_Korhatar_Magyar = '1';
        $Film_besorolas_szoveg = 'Korhatárra való tekintet nélkül megtekinthető';
        break;
}

// Remove every date from the movie title
$Film_Cim = preg_replace('/\(\d{4}\)(\s*\(\d+\))*$/', '', $Film_Cim);

// The html code for the entire page
echo "<!DOCTYPE html>
<!DOCTYPE html>
<html lang=\"en\">
    <head>
        <meta charset=\"UTF-8\">
        <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <title>{$Film_Cim}</title>
        <link href=\"https://vjs.zencdn.net/7.11.4/video-js.css\" rel=\"stylesheet\" />
        <style>
            body {
                margin: 0;
                font-family: 'Inter', sans-serif;
                background: #121212;
                background-image: url('https://images.unsplash.com/photo-1665652475985-37e285aeff53?q=80&w=2662&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D');
                background-size: cover;
                background-repeat: no-repeat;
                color: #fff;
                overflow: hidden;
            }
            .page-content {
                position: relative;
                width: auto;
                height: 100vh;
            }
            .player-feature-badge {
                border: 1px solid #ffffff;
                border-radius: 3px;
                color: hsla(0, 0%, 100%, .9);
                font-size: .7em;
                padding: 0 .5em;
                white-space: nowrap;
                position: relative; /* A belső span pozícionálásához */
                display: inline-block;
                transition: padding 0.3s ease; /* Sima átmenet a badge méretnövekedésére */
            }

            .player-feature-badge:hover {
                padding: 0 1.5em; /* Kinagyítja a badge-t */
            }

            .hover-text {
                position: absolute;
                left: 50%;
                bottom: -1.5em; /* Az elem alatt jelenik meg */
                transform: translateX(-50%);
                background-color: #333;
                color: #fff;
                font-size: 0.7em;
                padding: 5px 10px;
                border-radius: 5px;
                opacity: 0; /* Kezdetben láthatatlan */
                visibility: hidden;
                white-space: nowrap;
                transition: opacity 0.3s ease, transform 0.3s ease; /* Animáció simítása */
                z-index: 10;
            }

            .player-feature-badge:hover .hover-text {
                opacity: 1; /* Láthatóvá válik */
                visibility: visible;
                transform: translateX(-50%) translateY(-5px); /* Finom mozgás felfelé */
            }

            .background {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-size: cover;
                background-repeat: no-repeat;
                background-position: center;
                filter: blur(10px);
                z-index: -1;
            }
            .overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.7);
                backdrop-filter: blur(5px);
            }
            .header {
                z-index: 999;
                position: sticky;
                top: 0;
                left: 0;
                width: 100%;
                height: 70px;
                background-color: rgba(16, 16, 16, 0.75);
                color: white;
                border-bottom: 1px solid #747474;
                backdrop-filter: blur(5px);
                display: flex;
                align-items: center;
                padding: 0 20px;
                box-sizing: border-box;
            }
            .logo {
                height: 50px;
                margin-right: 30px;
            }
            .menu {
                display: flex;
                gap: 20px;
            }
            .menu-item {
                color: #fff;
                font-size: 16px;
                cursor: pointer;
                transition: color 0.3s;
            }
            .menu-item:hover {
                color: #1e90ff;
            }
            .video-player {
                width: 100%;
                height: 76rem;
            }

            .video-player video {
                z-index: 0;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: #000;
            }
            .bottom-bar {
                position: absolute;
                bottom: -500px; /* Alapértelmezett helyzet az oldal alján kívül */
                left: 0;
                width: 100%;
                background: rgb(2,0,36);
                background: linear-gradient(0deg, rgb(0, 0, 0) 47%, rgba(0, 0, 0, 0.5) 100%);
                backdrop-filter: blur(5px);
                border-radius: 20px 20px 0 0;
                padding: 20px;
                box-sizing: border-box;
                display: flex;
                flex-direction: row;
                color: #fff;
                flex-wrap: nowrap;
                align-content: stretch;
                justify-content: flex-start;
                align-items: stretch;
                transition: bottom 0.3s ease-in-out; /* Simább mozgás */
            }

            .bottom-bar.scrolled {
                bottom: 10px; /* Görgetéskor megjelenik az oldal alján */
            }
            .bottom-bar .content {
                display: flex;
                margin-left: 20px;
                padding: 20px;
                flex-direction: column;
                align-items: flex-start;
                border-radius: 1rem;
                border: 2px solid rgba(255, 255, 255, 0.04);
                background: hsla(0, 0.00%, 100.00%, 0.10);
                backdrop-filter: blur(16px);
                flex-wrap: nowrap;
                align-content: flex-start;
                justify-content: center;
                width: 70%;
                height: fit-content;
            }
            .film-title {
                font-size: 36px;
                font-weight: 700;
                margin-bottom: 10px;
            }
            .film-details {
                font-size: 18px;
                margin-bottom: 20px;
            }
            .film-description {
                font-size: 20px;
            }
            .buttons {
                display: flex;
                gap: 20px;
                margin-bottom: 30px;
            }
            .button {
                padding: 10px 20px;
                font-size: 20px;
                font-weight: 700;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                transition: background 0.3s;
            }
            .button-watch {
                background: #1e90ff;
                color: #fff;
            }
            .button-watch:hover {
                background: #1c86ee;
            }
            .button-add {
                background: rgba(217, 217, 217, 0.3);
                color: #d9d9d9;
                border: 1px solid rgba(217, 217, 217, 1);
            }
            .button-add:hover {
                background: rgba(217, 217, 217, 0.5);
            }
            .poster-frame {
                width: 300px;
                height: 450px;
                margin-bottom: 30px;
                border: 1px solid #fff;
                border-radius: 10px;
                overflow: hidden;
            }
            .poster-frame img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .korhatar {
                width: 30px;
                height: 30px;
                padding: 10px;
            }
            .adatlap {
                font-size: 20px;
                margin-bottom: 30px;
                display: flex;
            }
            .footer {
                font-size: 16px;
                color: #d9d9d9;
            }
            .videoMetadata--second-line {
                font-size: 20px;
                align-items: center;
                color: #bcbcbc;
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                vertical-align: middle;
            }

            .modal {
                display: none;
                position: fixed;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                z-index: 1050;
                width: 100%;
                overflow: hidden;
                outline: 0;
            }

            .modal-content {
                position: relative;
                -ms-flex-direction: column;
                flex-direction: column;
                width: 100%;
                pointer-events: auto;
                background-color: #fff;
                background-clip: padding-box;
                border: 1px solid rgba(0, 0, 0, .2);
                border-radius: .3rem;
                outline: 0  ;
                height: 30rem;
            }

            .modal-dialog {
                max-width: 800px;
                margin: 30px auto;
            }

            .modal-body {
                position: relative;
                padding: 0px;
            }

            .fade {
            background-color: rgba(0, 0, 0, 0.72);
                transition: opacity .15s linear;
                height: 100%;
            }

            button.close {
                padding: 0;
                background-color: transparent;
                border: 0;
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
            }


            button.approve {
                padding: 0;
                background-color: transparent;
                border: 0;
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
            }

            .embed-responsive-item {
                width: 100%;
                height: 100%;
                border-color: transparent;
            }	

            .close {
                position: absolute;
                right: -30px;
                top: 0;
                z-index: 999;
                font-size: 2rem;
                font-weight: normal;
                color: #fff;
                opacity: 1;
            }

            .Age_Restr {
                margin-bottom: 10px;
            }

            /* Loader teljes képernyőn */
            #loader {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: #1a1a1a75;
                backdrop-filter: blur(20px);
                
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }

            /* Spinner animáció betöltés előtt */
            .spinner {
                width: 50px;
                height: 50px;
                border: 6px solid #555555;
                border-top: 6px solid #ff0000;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin-left: auto;
                margin-right: auto;
            }

            /* Forgó animáció */
            @keyframes spin {
                0% {
                    transform: rotate(0deg);
                }
                100% {
                    transform: rotate(360deg);
                }
            }

            /* Mobile resolution 425px wide, but still waiting for confirmation! */
            @media (max-width: 715px){
                .video-player {
                    height: 50vh;
                }
                .bottom-bar {
                    height: 50vh;
                }
                .poster-frame {
                    width: 150px;
                    height: 225px;
                }
                .film-title {
                    font-size: 24px;
                }
                .film-details {
                    font-size: 14px;
                }
                .film-description {
                    font-size: 14px;
                    font-weight: bold;
                }
                .buttons {
                    gap: 10px;
                }
                .button {
                    padding: 5px 10px;
                    font-size: 14px;
                }
                .korhatar {
                    width: 20px;
                    height: 20px;
                    margin-bottom: 0px;
                    padding: 5px;
                }
                .adatlap {
                    font-size: 13px;
                }
                .footer {
                    font-size: 12px;
                }
                
                .logo{
                    //display: none;
                    width: 30px;
                    margin-right: 15px;
                }
                .poster-frame{
                    display: none;
                }
                .video-player video{
                    height: 33%;
                }
                .bottom-bar{
                    top: 23%;
                    height: auto;
                }
                .buttons{
                    display: flex;
                    flex-direction: column;
                    margin-left: auto;
                    margin-right: auto;
                    width: 100%;
                }
                .ltr-bjn8wh{
                    display: none;
                }
                .videoMetadata--second-line{
                    padding-bottom: 20px;
                    font-size: 16px;
                }
                .film-description{
                    margin-bottom: 0px;
                }
                .Age_Restr{
                    margin-bottom: 20px;
                }
                .bottom-bar .content{
                    width: auto;
                    margin-left: 0px;
                    height: auto;
                }
                .menu{
                    margin-left: auto;
                    margin-right: auto;
                    gap: 15px;
                }
                .header{
                    padding: 0 15px;
                    height: 55px;
                }
                .bottom-bar .content {
                    justify-content: start;
                }
            }

            /* Tablet view 820px wide */
            @media (max-width: 820px){
                .buttons{
                    display: block;
                }
                .button{
                    margin-bottom: 10px;
                    width: 100%;
                }
            }
        </style>
    </head>
    <body>
    <div id=\"loader\"><div class=\"spinner\"></div></div>
     <div class=\"modal fade show\" id=\"myModal\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"exampleModalLabel\" aria-modal=\"true\">
        <div class=\"modal-dialog\" role=\"document\">
            <div class=\"modal-content\">
                <div class=\"modal-body\">
                    <button type=\"button\" class=\"close\" id=\"closeModalBtn\" data-dismiss=\"modal\" aria-label=\"Close\">
                        <span aria-hidden=\"true\">×</span>
                    </button>

                    <!-- Kérdés: Elindult? Igen/Nem -->
                    <div class=\"Question\">
                        <p style=\"font-size: 18px; color: #333; text-align: center;\">Elindult a film?</p>
                        <div style=\"display: flex; justify-content: center; gap: 20px;\">
                            <button type=\"button\" class=\"approve\" id=\"yesBtn\" data-dismiss=\"modal\" aria-label=\"Close\" style=\"background-color: #28a745; color: white; padding: 10px 20px; font-size: 16px; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s;\">
                                Igen
                            </button>
                            <button type=\"button\" class=\"approve\" id=\"noBtn\" data-dismiss=\"modal\" aria-label=\"Close\" style=\"background-color: #dc3545; color: white; padding: 10px 20px; font-size: 16px; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s;\">
                                Nem
                            </button>
                        </div>
                    </div>

                    <!-- 16:9 aspect ratio -->
                    <div class=\"videoiframe\">
                        ";
                        if (strpos($Film_Link, 'm3u8') !== false) {
                            echo "<video-js style=\"width: 100%; height: 100%;\" id=\"my-video\" class=\"video-js vjs-default-skin embed-responsive-item\" controls preload=\"auto\" width=\"80%\" height=\"80%\" data-setup='{}'>
                            <source src=\"{$Film_Link}\" type=\"application/x-mpegURL\">
                            </video-js>";
                        } else {
                            echo "<iframe src=\"{$Film_Link}\" class=\"embed-responsive-item\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>";
                        }
                        echo "
                    </div>
                </div>
            </div>
        </div>
    </div>
        <div class=\"page-content\">
            <div class=\"background\"></div>
            <div class=\"header\">
                <img src=\"logo.svg\" alt=\"MovieFlix Logo\" class=\"logo\">
                <div class=\"menu\">
                    <div class=\"menu-item\">
                        <a href=\"index.html\" style=\"text-decoration: none; color: inherit;\">Főoldal</a>
                    </div>
                    <div class=\"menu-item\">
                        <a href=\"index.html?Page=Recents\" style=\"text-decoration: none; color: inherit;\">Legutóbbiak</a>
                    </div>
                    <div class=\"menu-item\">
                        <a href=\"index.html?Page=Favorites\" style=\"text-decoration: none; color: inherit;\">Kedvencek</a>
                    </div>
                    <!-- <div class=\"menu-item\">Profilom</div>
                    <div class=\"menu-item\">Beállítások</div> -->
                </div>
            </div>
            <div class=\"video-player\">";
                if ($IMDB_Elozetes == "") {
                    echo "<div id=\"no-trailer-message\" style=\"display: block; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: rgba(0, 0, 0, 0.8); color: white; padding: 20px; font-size: 35px; border-radius: 10px; text-align: center;\">Nincs elérhető előzetes</div>";
                }
                else{
                    echo "<video autoplay muted loop src=\"{$IMDB_Elozetes}\" style=\"max-width: 100%;\"></video>";
                }
                echo "
            </div>
            <div class=\"bottom-bar\">
                <div class=\"poster-frame\">
                    <img src=\"{$Film_Boritokep}\" alt=\"{$Film_Cim}\">
                </div>
                <div class=\"content\">
                    <div class=\"film-title\">{$Film_Cim}</div>
                    <div class=\"videoMetadata--second-line\">
                        <div class=\"year\">{$Film_Megjelenes}</div>
                        <span class=\"duration\">{$Film_Hossz} perc</span>
                        <span class=\"player-feature-badge\">
                            SD
                            <span class=\"hover-text\">Közepes felbontás (480p)</span>
                        </span>
                        <div class=\"ltr-bjn8wh\">
                            <div class=\"ltr-x1hvkl\" style=\"display: flex;flex-direction: row;justify-content: center;align-content: flex-end;flex-wrap: nowrap;align-items: center;gap: 10px;\">
                                <div aria-labelledby=\"standaloneAudioDescriptionAvailable\" data-tooltip=\"Audio description is available\">
                                    <svg aria-hidden=\"true\" focusable=\"false\" data-prefix=\"fas\" data-icon<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" role=\"img\" viewBox=\"0 0 24 24\" width=\"34\" height=\"34\" data-icon=\"AudioDescriptionStandard\" aria-hidden=\"true\" class=\"ltr-18tpq4v\">
                                        <path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M21.9782 7.52002H22.2621C23.37 8.81831 24.0001 10.4801 24.0001 12.2077C24.0001 13.7414 23.505 15.2301 22.6221 16.4453H22.3348H21.8501H21.5662C22.5598 15.2613 23.1207 13.7691 23.1207 12.2077C23.1207 10.449 22.404 8.75599 21.1611 7.52002H21.445H21.9782ZM6.91381 16.4796H8.87336V7.52661H6.42566L0 16.4796H2.87701L3.63174 15.2956H6.91381V16.4796ZM4.8625 13.4299H6.92592V10.224L4.8625 13.4299ZM12.3019 9.62283C13.621 9.62283 14.6839 10.6926 14.6839 12.0048C14.6839 13.3203 13.621 14.3901 12.3019 14.3901H11.6787V9.62283H12.3019ZM12.5443 16.4743C15.0128 16.4743 17.0208 14.4698 17.0208 12.0048C17.0208 9.52935 15.0335 7.52826 12.565 7.52826H12.5373H9.79883V16.4778H12.5443V16.4743ZM20.0103 7.52002H19.7264H19.1932H18.9093C20.1522 8.75599 20.8689 10.449 20.8689 12.2077C20.8689 13.7691 20.308 15.2613 19.3144 16.4453H19.5983H20.083H20.3634C21.2531 15.2301 21.7482 13.7414 21.7482 12.2077C21.7482 10.4801 21.1181 8.81831 20.0103 7.52002ZM17.4745 7.52002H17.7584C18.8663 8.81831 19.4895 10.4801 19.4895 12.2077C19.4895 13.7414 19.0013 15.2301 18.1116 16.4453H17.8277H17.3464H17.0625C18.0492 15.2613 18.6101 13.7691 18.6101 12.2077C18.6101 10.449 17.9004 8.75599 16.6575 7.52002H16.9344H17.4745Z\" fill=\"currentColor\"></path>
                                    </svg>
                                </div>  
                                <div aria-labelledby=\"standaloneTextClosedCaptionsAvailable\" data-tooltip=\"Subtitles for the deaf and hard of hearing are available\">
                                    <svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" role=\"img\" viewBox=\"0 0 16 16\" width=\"20\" height=\"20\" data-icon=\"SubtitlesSmall\" aria-hidden=\"true\">
                                        <path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M0 1.75C0 1.33579 0.335786 1 0.75 1H15.25C15.6642 1 16 1.33579 16 1.75V12.25C16 12.6642 15.6642 13 15.25 13H12.75V15C12.75 15.2652 12.61 15.5106 12.3817 15.6456C12.1535 15.7806 11.8709 15.785 11.6386 15.6572L6.80736 13H0.75C0.335786 13 0 12.6642 0 12.25V1.75ZM1.5 2.5V11.5H7H7.19264L7.36144 11.5928L11.25 13.7315V12.25V11.5H12H14.5V2.5H1.5ZM6 6.5L3 6.5V5L6 5V6.5ZM13 7.5H10V9H13V7.5ZM3 9V7.5L9 7.5V9L3 9ZM13 5H7V6.5H13V5Z\" fill=\"currentColor\"></path>
                                    </svg>
                                </div>
                                </div>
                            </div>
                        </div>  
                    
                    <div class=\"Age_Restr\" style=\"display: flex; justify-content: flex-start; align-items: center;\">
                        <img src=\"{$Film_Korhatar_Magyar}.png\"
                            alt=\"Korhatár\" class=\"korhatar\">
                        <div class=\"film-description\">{$Film_besorolas_szoveg}</div>
                        
                    </div>
                    <div class=\"buttons\">
                        <button class=\"button button-watch\" id=\"openModalBtn\">Megtekintem</button>
                        <button class=\"button button\" onclick=\"window.location.href='upcNEW.php?MovieID={$IMDBCode}'\">Előzetes megtekintése</button>
                        <button class=\"button button-add\" id=\"AddtoFavorites\">"; echo $Film_Kedvenc? "Hozzáadva kedvencekhez" : "Kedvencekhez adás"; echo "</button>
                    </div>
                    <script>
                    // Modal elem és gombok referenciája
                    var modal = document.getElementById('myModal');
                    var openModalBtn = document.getElementById('openModalBtn');
                    var closeModalBtn = document.getElementById('closeModalBtn');

                    // Gomb kattintás eseménykezelő - modal megnyitása
                    openModalBtn.addEventListener('click', function() {
                        modal.style.display = 'block'; // Modal megjelenítése
                    });

                    // Close gomb kattintás eseménykezelő - modal eltüntetése
                    closeModalBtn.addEventListener('click', function() {
                        modal.style.display = 'none'; // Modal elrejtése
                    });
                    </script>
                    
                        
                    <div class=\"adatlap\">
                        <div class=\"default-ltr-cache-kiz1b3 em9qa8x3\">
                            <div class=\"default-ltr-cache-18fxwnx em9qa8x0\">
                                <div class=\"default-ltr-cache-1y7pnva em9qa8x1\">
                                
                                <span class=\" default-ltr-cache-v92n84 euy28770\">{$Film_leiras}</span></div>
                                        
                                <div class=\" default-ltr-cache-1mulv68 eebk2mu2\" role=\"separator\">
                                    <div class=\"default-ltr-cache-1k8qwhc eebk2mu0\"></div>  
                                </div>
                                <div class=\"default-ltr-cache-1wmy9hl ehsrwgm0\">
                                    <div class=\"default-ltr-cache-eywhmi ehsrwgm1\">
                                        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </body>    
        <script src=\"https://vjs.zencdn.net/7.11.4/video.min.js\"></script>
        <script>
            // If the viewport is more than 320px wide, don't do this animation
            if (window.innerWidth > 512) {
                const bottomBar = document.querySelector(\".bottom-bar\");

                // Alapértelmezett helyzet: kicsit fentebb
                bottomBar.style.position = \"fixed\";
                bottomBar.style.bottom = \"-330px\"; // Alapértelmezett távolság az aljától
                bottomBar.style.transition = \"bottom 0.3s ease-in-out\"; // Simább mozgás

                // Amikor az egér rámegy a bottom-bar-ra, teljesen az aljára csúszik
                bottomBar.addEventListener(\"mouseenter\", function () {
                    bottomBar.style.bottom = \"0px\"; // Teljesen az oldal aljára kerül
                    bottomBar.classList.add(\"scrolled\");
                });

                // Amikor az egér elhagyja a bottom-bar-t, visszaáll az alap helyzetbe
                bottomBar.addEventListener(\"mouseleave\", function () {
                    bottomBar.style.bottom = \"-330px\"; // Alapértelmezett távolság az aljától
                    bottomBar.classList.remove(\"scrolled\");
                });

                // Ellenőrizze az előzetes állapotát
                var video = document.getElementById(\"trailer\");
                var noTrailerMessage = document.getElementById(\"no-trailer-message\");

                if (video.readyState !== 4) {
                    // Ha az előzetes nem található
                    noTrailerMessage.style.display = \"block\";
                    video.style.display = \"none\";
                }
            }
            else{
                const bottomBar = document.querySelector(\".bottom-bar\");
                bottomBar.style.bottom = \"0px\";
                console.log('Mobile resolution');
            }
        </script>

        <script>
            document.getElementById('AddtoFavorites').addEventListener('click', function() {
                var movieID = {$MovieID};
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'movies.php?addtoFavorites=' + movieID, true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        const response = xhr.responseText.trim();
                        const button = document.getElementById('AddtoFavorites');
                        if (response === 'true') {
                            button.innerHTML = 'Hozzáadva kedvencekhez';
                        } else {
                            button.innerHTML = 'Kedvencekhez adás';
                        }
                    }
                };
                xhr.send();
            });
        </script>

        <script>
            // Action on yes button click
            document.getElementById('yesBtn').addEventListener('click', function() {
                const que = document.getElementsByClassName('Question')[0];
                que.style.display = 'none';
            });
            // Action on no button click
            document.getElementById('noBtn').addEventListener('click', function() {
                // Delete every record from every table related to the movie id
                var movieID = {$MovieID}; // Make sure `$MovieID` is properly embedded
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'movies.php?deleteMovie=' + movieID, true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        const response = xhr.responseText.trim();
                        if (response === 'true') {
                            window.location.href = 'index.html';
                        }
                    }
                };
                xhr.send(); // This is crucial to send the request
            });
        </script>
        <script>
            document.onreadystatechange = function () {
                if (document.readyState !== \"complete\") {
                    document.querySelector(
                        \"body\").style.visibility = \"visible\";
                    document.querySelector(
                        \"#loader\").style.visibility = \"visible\";
                } else {
                    document.querySelector(
                        \"#loader\").style.display = \"none\";
                    document.querySelector(
                        \"body\").style.visibility = \"visible\";
                }
            };


    </script>
    </body>
</html>";
?>