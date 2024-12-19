<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;

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

            // Trace back the IMDB data
            $IMDB_BaseLink = "https://www.imdb.com";
            $imdbLink = $IMDB_BaseLink . "/title/" . str_replace(" ", "+", $movie['imdb_code']);
            //echo "<br><a href=\"" . $imdbLink . "\">IMDB Link</a>";

            $IMDBCode = $movie['imdb_code'];

            // Check if the database has a record for the trailer link
            $stmt2 = $pdo->prepare("SELECT Count(*) FROM trailers WHERE imdb_id = :id");
            $stmt2->execute(['id' => $IMDBCode]);
            $movie2 = $stmt2->fetch(PDO::FETCH_ASSOC);

            if ($movie2['Count(*)'] > 0) {
                $stmt2 = $pdo->prepare("SELECT * FROM trailers WHERE imdb_id = :id");
                $stmt2->execute(['id' => $IMDBCode]);
                $movie2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                $IMDB_Elozetes = $movie2['trailer_link'];
                //echo "<br><a href=\"" . $movie2['trailer_link'] . "\">Direct IMDB link</a>" . "<br>";
                //echo "<video src=\"" . htmlspecialchars($movie2['trailer_link']) . "\" width=\"320\" height=\"240\" controls>" . PHP_EOL;
            } else {
                // If no trailer is found in the database, fetch the trailer link
                $httpClient = new Client([
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3'
                    ]
                ]);

                $response = $httpClient->get($IMDB_BaseLink . "/title/" . str_replace(" ", "+", $movie['imdb_code']));
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
                $returnVar = 0;

                // Execute the Puppeteer script to extract video tags
                exec("node $script " . escapeshellarg($IMDBVideoURL), $output, $returnVar);

                if ($returnVar === 0) {
                    $videoTags = json_decode(implode('', $output), true);
                    if (!empty($videoTags)) {
                        foreach ($videoTags as $videoTag) {
                            //echo "<video src=\"" . htmlspecialchars($videoTag) . "\" width=\"320\" height=\"240\" controls autoplay>" . PHP_EOL;

                            // Insert the trailer link into the trailers table
                            $stmt2 = $pdo->prepare("INSERT INTO trailers (imdb_id, trailer_link) VALUES (:imdb_id, :trailer_link)");
                            $stmt2->execute([
                                'imdb_id' => $movie['imdb_code'],  // Using movie IMDB code
                                'trailer_link' =>  $videoTag       // The trailer URL extracted
                            ]);
                            $IMDB_Elozetes = $videoTag;

                            // Check if the record was inserted successfully
                            if ($stmt2->rowCount() > 0) {
                                //echo "Trailer link successfully inserted for IMDB code " . $movie['imdb_code'];
                            } else {
                                //echo "Error inserting trailer link for IMDB code " . $movie['imdb_code'];
                            }

                            break;  // Insert only one record per movie
                        }
                    } else {
                        echo "No video tags found.";
                    }
                } else {
                    echo "Error executing Puppeteer script.";
                }
            }

        } else {
            //echo 'No movie found with the given ID.';
        }
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
    }
} else {
    echo 'No ID provided.';
}

// The html code for the entire page
echo "<!DOCTYPE html>
<html lang=\"en\">
    <head>
        <meta charset=\"UTF-8\">
        <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <title>{$Film_Cim}</title>
        <link href=\"https://vjs.zencdn.net/7.11.4/video-js.css\" rel=\"stylesheet\" />
        <style>
        #Mobile, #Desktop {
            display: none;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: rgba(30, 30, 30);
        }
        .Header {
            display: flex;
        }
        .HeaderMain{
                margin-top: 25%;
                margin-left: 20px;
                margin-right: 20px;
                position: relative;
                background: linear-gradient(to bottom, rgba(0, 0, 0, 0.8) 10%, rgba(0, 0, 0, 0.9) 15%, rgba(0, 0, 0, 0.95) 40%, rgba(0, 0, 0, 1) 100%);
                padding: 20px; /* Optional */
                border-radius: 5px; /* Optional */
        }
        .Details {
            margin-left: 40px;
            color: white;
        }
        h1 {
            margin-bottom: 30px;
            color: white;
        }
        video{
            margin-left: auto;
            margin-right: auto;
            position: fixed;
            top: -20%;
            min-width: 100%;
            max-height: 100%;
            background-size: cover;
            transition: opacity 2s;
        }
        .Hossz {
            font-size: 20px;
            margin-top: 10px;
            margin-left: 10%;
        }
        .Megtekintes {
            margin-top: 60px;
            text-align: center;
            color: white;
        }
        button{
            background-color: #f44336;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            cursor: pointer;
            width: 100%;
        }
        img{
            margin-top: -60px;
            min-width: 225px;
        }
        .Description{
            margin-top: 15px;
            margin-left: auto;
            color: white;
            max-width: 40%;
        }
        .Description p{
            color: lightgray;
            font-style: italic;
            margin-left: 5%
        }
        @media (min-width: 768px) {
            #Desktop {
                display: block;
            }
        }
        @media (max-width: 767px){
            #Mobile {
                display: block;
            }
            .Header{
                display: block;
                text-align: center;
                margin: 0px;
                color: white;
                
            }
            .HeaderMain{
                margin-top: 45%;
            }
            video{
                top: 0px;
                max-width: 100%;
            }
        }

        ::-webkit-scrollbar {
            width: 0;
        }

        body{
            cursor: url(cursor.png), auto;
        }
        </style>
    </head>
    <body>
        <div id=\"Desktop\">
            <video autoplay muted loop playsinline src=\"{$IMDB_Elozetes}\" style=\"max-width: 100%\"></video>
            <div class=\"HeaderMain\">
                <div class=\"Header\">
                    <img src=\"{$Film_Boritokep}\" alt=\"{$Film_Cim}\" style=\"width: 200px; max-height: 320px; border: 2px solid white;\">
                    <div class=\"Details\">
                        <h1>{$Film_Cim}</h1>
                        <p class=\"Hossz\">Megjelenés: {$Film_Megjelenes}</p>
                        <p class=\"Hossz\">Hossz: {$Film_Hossz} perc</p>
                        <button onclick=\"window.location.href='{$IMDB_Elozetes}'\">Előzetes megtekintése</button>
                    </div>
                    <div class=\"Description\">
                        <h3>Leírás:</h3>
                        <p>{$Film_leiras}</p>
                    </div>
                </div>
                <div class=\"Megtekintes\">
                    <h1>Film megtekintése</h1>
                    <div style=\"width: 100%\">
                    ";
                    if (strpos($Film_Link, 'm3u8') !== false) {
                        echo "<video-js style=\"width: 100%; height: 100%;\" id=\"my-video\" class=\"video-js vjs-default-skin\" controls preload=\"auto\" width=\"80%\" height=\"80%\" data-setup='{}'>
                                <source src=\"{$Film_Link}\" type=\"application/x-mpegURL\">
                            </video-js>";
                    } else {
                        echo "<iframe width=\"90%\" height=\"90%\" src=\"{$Film_Link}\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>";
                    }
echo "          </div>
                </div>
            </div>
        </div>
        <div id=\"Mobile\">
            <video autoplay muted loop playsinline src=\"{$IMDB_Elozetes}\"></video>
            <div class=\"HeaderMain\">
                <div class=\"Header\">
                    <img src=\"{$Film_Boritokep}\" alt=\"{$Film_Cim}\" style=\"width: 200px; max-height: 320px; border: 2px solid white;\">
                    <h1>{$Film_Cim}</h1>
                    <p class=\"Hossz\">Megjelenés: {$Film_Megjelenes}</p>
                    <p class=\"Hossz\">Hossz: {$Film_Hossz} perc</p>
                    <button onclick=\"window.location.href='{$IMDB_Elozetes}'\">Előzetes megtekintése</button>
                    </div>
                    <div class=\"Megtekintes\">
                    <h1>Film megtekintése</h1>
                    <div style=\"width: 80%; height: 80%; padding-left: 10%;\">
                    ";
                    if (strpos($Film_Link, 'm3u8') !== false) {
                        echo "<video-js style=\"width: 100%; height: 100%;\" id=\"my-video\" class=\"video-js vjs-default-skin\" controls preload=\"auto\" width=\"80%\" height=\"80%\" data-setup='{}'>
                        <source src=\"{$Film_Link}\" type=\"application/x-mpegURL\">
                        </video-js>";
                    } else {
                        echo "<iframe width=\"100%\" src=\"{$Film_Link}\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>";
                    }
                    echo "          </div>
                    <h3>Leírás:</h3>
                    <p>{$Film_leiras}</p>
                </div>
            </div>
        </div>
        <script src=\"https://vjs.zencdn.net/7.11.4/video.min.js\"></script>
    </body>
</html>";
?>