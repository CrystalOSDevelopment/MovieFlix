<?php
require 'vendor/autoload.php';

use Nesk\Puphpeteer\Puppeteer;
use GuzzleHttp\Client;

$httpClient = new Client([
    'headers' => [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
    ],
]);

$ev = isset($_POST['year']) ? $_POST['year'] : 0;
$page = isset($_POST['page']) ? $_POST['page'] : 0;
$SearchWord = isset($_POST['search']) ? $_POST['search'] : '';

echo "Év: " . $ev . "<br>";
echo "Oldal: " . $page . "<br>";
echo "Keresés: " . $SearchWord . "<br>";
if(trim($SearchWord) != ""){
    $ev = 0;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=links_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if the movie already exists in the database
    $stmt = $pdo->prepare("SELECT * FROM links WHERE movie_title LIKE :search_word");
    $stmt->execute(['search_word' => '%' . $SearchWord . '%']);
    $existingMovies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($existingMovies)) {
        // Display existing movies
        foreach ($existingMovies as $movie) {
            echo "Movie title: " . htmlspecialchars($movie['movie_title']) . "<br>";
            echo "Movie length: " . htmlspecialchars($movie['movie_length']) . " minutes<br>";
            echo "Link: <a href='" . htmlspecialchars($movie['link']) . "'>" . htmlspecialchars($movie['link']) . "</a><br>";
            echo "Release date: " . htmlspecialchars($movie['release_date']) . "<br>";
            echo "<br>";
        }
    } else {
        // Look up the movie on the website
        $baseUrl = 'https://filmezz.club/kereses.php?';
        //'&e=' . $ev
        $response = $httpClient->get($baseUrl . 'p=' . $page . '&q=0&l=0&c=0&t=1&h=&o=abc&s=' . $SearchWord);
        $htmlString = (string) $response->getBody();
        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHTML($htmlString);
        $xpath = new DOMXPath($doc);

        // Simplify the XPath expression for debugging
        $titles = $xpath->evaluate('//ul[@class="row list-unstyled movie-list"]//li/a');
        $covers = $xpath->evaluate('//ul[@class="row list-unstyled movie-list"]//li/a/img');

        // Debug: Print the number of elements found
        echo "Number of titles found: " . $titles->length . "<br>";
        echo "Number of covers found: " . $covers->length . "<br>";

        // Cycles thru the movies
        foreach ($titles as $index => $title) {
            echo "<br>";
            $movieTitle = $title->textContent;
            $filmLink = $title->getAttribute('href');
            $teljesFilmLink = 'https://filmezz.club' . $filmLink;

            // Pulls the cover image by source
            $cover = $covers->item($index);
            $coverUrl = $cover ? $cover->getAttribute('src') : '';
            echo 'Cover: <img src="' . htmlspecialchars($coverUrl) . '" style="max-width: 200px;"><br>';
            echo 'Adatlap: <a href="' . htmlspecialchars($teljesFilmLink) . '">' . htmlspecialchars($teljesFilmLink) . '</a><br>';
            
            // Path to data of the movie
            $movieTitle = $xpath->evaluate('.//span[@class="title"]', $title)->item(0)->textContent ?? 'N/A';

            // Check if the movie already exists in the database
            if($ev == 0){
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM links WHERE movie_title LIKE :movie_title");
                $stmt->execute([
                    'movie_title' => '%' . $movieTitle . '%',
                ]);
            }
            else{
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM links WHERE movie_title LIKE :movie_title AND release_date = :release_date");
                $stmt->execute([
                    'movie_title' => '%' . $movieTitle . '%',
                    'release_date' => $ev,
                ]);
            }
            $count = $stmt->fetchColumn();
            
            // $stmt = $pdo->prepare("SELECT COUNT(*) FROM links WHERE movie_title LIKE :movie_title AND release_date = :release_date");
            // $stmt->execute([
            //     'movie_title' => '%' . $movieTitle . '%',
            //     'release_date' => $ev,
            // ]);
            // $count = $stmt->fetchColumn();
            
            // Beküldött link handling (video links)
            
            // If the movie exists in the database, skip it
            if($count == 0){
                
                $moviePageResponse = $httpClient->get($teljesFilmLink);
                $moviePageHtml = (string) $moviePageResponse->getBody();
                $moviePageDoc = new DOMDocument();
                $moviePageDoc->loadHTML($moviePageHtml);
                $moviePageXpath = new DOMXPath($moviePageDoc);
                
                $bekuldottLinkek = $moviePageXpath->evaluate('//div[@class="container movie-page"]//div[@class="row"]//div[@class="col-md-9 col-sm-12"]//section[@class="content-box"]//div[@class="row"]//div[@class="col-md-6 col-sm-12"]/a/@href');
                
                // Extract data from the movie page, but only if it doesn't exist in the database
                $imdbInfo = $xpath->evaluate('.//span[contains(text(), "IMDB:")]', $title)->item(0)->textContent ?? 'N/A';
                $director = $xpath->evaluate('.//li[span[text()="Rendező:"]]', $title)->item(0)->textContent ?? 'N/A';
                $length = $xpath->evaluate('.//li[span[text="Hossz:"]]', $title)->item(0)->textContent ?? 'N/A';
                $category = $xpath->evaluate('.//li[span[text="Kategória:"]]', $title)->item(0)->textContent ?? 'N/A';
                $rating = $xpath->evaluate('.//span[contains(@class, "imdb")]', $title)->item(0)->textContent ?? 'N/A';
                $description = "";

                $textDivs = $moviePageXpath->evaluate('//div[contains(@class, "text")]');
                foreach ($textDivs as $textDiv) {
                    $description = htmlspecialchars($textDiv->textContent);
                    break;
                }
                
                // Display movie data
                echo "Cím: " . htmlspecialchars(trim($movieTitle)) . "<br>";
                echo htmlspecialchars(trim($imdbInfo)) . "<br>";
                echo htmlspecialchars(trim($director)) . "<br>";
                $lengthWithoutLabel = trim(str_replace("Hossz:", "", $length));
                $totalMinutes = convertToMinutes($lengthWithoutLabel);
                echo "Hossz: " . htmlspecialchars(trim($totalMinutes)) . " perc" . "<br>";
                echo "Értékelés: " . htmlspecialchars(trim($rating)) . "<br><br>";
                
                foreach ($bekuldottLinkek as $bekuldottLink) {
                    
                    // It doesn't enter here yet
                    $detailLink = $bekuldottLink->textContent;
                    echo 'Beküldött: <a href="' . $bekuldottLink->textContent . '">' . $bekuldottLink->textContent . '</a><br>';
                    
                    $detailResponse = $httpClient->get($detailLink);
                    $detailHtml = (string) $detailResponse->getBody();
                    $detailDoc = new DOMDocument();
                    $detailDoc->loadHTML($detailHtml);
                    $detailXpath = new DOMXPath($detailDoc);
                    $videoUrls = $detailXpath->evaluate('//div[@class="col-12 mt-4"]//section[@class="content-box"]//ul[@class="list-unstyled table-horizontal url-list"]//li//div[@class="col-sm-1 col-xs-6"]/a/@href');
                    $secondDivContents = $detailXpath->evaluate('//ul[@class="list-unstyled table-horizontal url-list"]/li/div[2]');
                    
                    $videoUrls = $detailXpath->evaluate('//div[@class="col-12 mt-4"]//section[@class="content-box"]//ul[@class="list-unstyled table-horizontal url-list"]//li//div[@class="col-sm-1 col-xs-6"]/a/@href');
                    $secondDivContents = $detailXpath->evaluate('//ul[@class="list-unstyled table-horizontal url-list"]/li/div[2]');
                    
                    $FoundEZLink = false;
                    $preferredLinks = [];
                    $backupLinks = [];
        
                    // Collect links in a single pass
                    foreach ($videoUrls as $index => $videoUrl) {
                        $url = $videoUrl->nodeValue;
                        $secondDivText = $secondDivContents->item($index + 1)->nodeValue;
                        $teljesTenylegesLink = 'https://filmtarhely.click';
                        $fullLink = $teljesTenylegesLink . $url;
        
                        // Collect preferred and backup links
                        if ($secondDivText == "videa.hu" || $secondDivText == "dood.re") {
                            $preferredLinks[] = $fullLink;
                        } else//if ($secondDivText == "voe.sx" || $secondDivText == "vtbe.to") {
                        {
                            $backupLinks[] = $fullLink;
                        }
                    }
        
                    $ExportLink = "";
                    // Execute preferred link if found
                    if (!empty($preferredLinks)) {
                        $fullLink = $preferredLinks[0];
                        echo 'URL: <a href="' . $fullLink . '">' . htmlspecialchars($secondDivText) . '</a><br>';
                        $ExportLink = $fullLink;
                        $FoundEZLink = true;
                    }
        
                    // If no preferred link found, execute backup link
                    if (!$FoundEZLink && !empty($backupLinks)) {
                        $fullLink = $backupLinks[0];
                        //echo 'URL: ' . exec("node index.js " . escapeshellarg($fullLink)) . "<br>";
                        $ExportLink = exec("node index.js " . escapeshellarg($fullLink));
                        echo 'URL: <a href="' . $ExportLink . '">' . htmlspecialchars($secondDivText) . '</a><br>';
                        $FoundEZLink = true;
                    }
        
                    if ($count == 0) {
                        // If the movie doesn't exist in the database, insert it
                        $imdbCode = null;
                        if (strpos($imdbInfo, ":") !== false) {
                            $parts = explode(":", $imdbInfo);
                            if (isset($parts[1])) {
                                $imdbCode = trim($parts[1]);
                            }
                        }

                        // If the movie link is empty, pull it from another source
                        // if ($ExportLink == "") {
                        //     $response2 = $httpClient->get("https://mozimix.com/?s=" . $SearchWord);
                        //     $htmlContent2 = (string) $response2->getBody();
                        //     libxml_use_internal_errors(true);
                        //     $domDocument2 = new DOMDocument();
                        //     $domDocument2->loadHTML($htmlContent2);
                        //     $xpath2 = new DOMXPath($domDocument2);

                        //     // Debug: Print the HTML string to verify the structure
                        //     echo "<pre>" . htmlspecialchars($htmlContent2) . "</pre>";

                        //     // Find all the movie titles
                        //     $movies = $xpath2->evaluate('//div[@id="dt_contenedor"]//div[@id="contenedor"]//div[@class="module"]//div[@class="content rigth csearch"]//div[@class="search-page"]//div[@class="result-item"]//article//div[@class="details"]//div[@class="title"]/a');

                        //     // Write out every movie link
                        //     foreach ($movies as $index => $movieTitleElement) {
                        //         $movieLink = $movieTitleElement->getAttribute('href');
                        //         // Extract inner html
                        //         $movieName = $movieTitleElement->textContent;
                        //         if(str_contains($movieTitle, $movieName)){
                        //             echo 'Adatlap: <a href="' . htmlspecialchars($movieLink) . '">' . htmlspecialchars($movieLink) . '</a><br>';
                        //             $ExportLink = $movieLink;
                        //             break;
                        //         }
                        //     }

                        //     // Export the video from the new source from the video tag
                        //     if ($ExportLink !== "") {
                        //         $ExportLink = exec("node index.js " . escapeshellarg($movieLink));
                        //         echo 'URL: <a href="' . $ExportLink . '">' . htmlspecialchars($secondDivText) . '</a><br>';
                        //     }
                        // }

                        if ($imdbCode !== null) {
                            $stmt = $pdo->prepare("INSERT INTO links (movie_title, movie_length, link, release_date, cover, imdb_code, description) VALUES (:movie_title, :movie_length, :link, :release_date, :cover, :imdb_code, :description)");
                            $stmt->execute([
                                'movie_title' => $movieTitle,
                                'movie_length' => $totalMinutes,
                                'link' => $ExportLink,
                                'release_date' => substr($movieTitle, strrpos($movieTitle, "(") + 1, 4),
                                'cover' => $coverUrl,
                                'imdb_code' => $imdbCode,
                                'description' => trim($description),
                            ]);
                            echo "Added new movie: " . htmlspecialchars($movieTitle) . "<br>";
                            echo $totalMinutes . " perc<br>";
                        } else {
                            echo "Failed to extract IMDb code from: " . htmlspecialchars($imdbInfo) . "<br>";
                        }
                    }
                }
            }
        }
    }
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

// Convert duration to minutes (helper function)
function convertToMinutes($duration) {
    $parts = explode(" ", $duration);
    $minutes = 0;

    foreach ($parts as $key => $part) {
        if (strpos($part, "óra") !== false) {
            // Convert hours to minutes
            $minutes += (int) $parts[$key - 1] * 60;
        } elseif (strpos($part, "perc") !== false) {
            // Add minutes
            $minutes += (int) $parts[$key - 1];
        }
    }

    return $minutes;
}
?>