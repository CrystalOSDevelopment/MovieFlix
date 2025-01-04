<?php
    // Cycle through all directories in the directory D:
    $dir = 'D:\\';
    $files = scandir($dir);
    foreach($files as $file) {
        // Temporarily hide warnings
        $previousErrorReporting = error_reporting(E_ALL & ~E_WARNING);

        if (is_dir($dir . $file) && $file != '.' && $file != '..') {
            // Try entering the new folders and check if they contain a file with the extension .nfo
            $subdir = $dir . $file;
            $subfiles = scandir($subdir);
            foreach ($subfiles as $subfile) {
                $FileName = "";
                if (is_file($subdir . '\\' . $subfile) && pathinfo($subdir . '\\' . $subfile, PATHINFO_EXTENSION) == 'nfo') {
                    $filePath = $dir . $file . '\\' . $subfile;

                    $FileName = str_replace("-",  ".", str_replace(".nfo", ".mkv", $subfile));
                    //echo "<br>";
                    //echo $subfile . "<br>";
                    if (file_exists($filePath)) {
                        // echo "A fájl létezik!<br>";

                        // Read the file
                        $fileContents = file_get_contents($filePath);

                        // Detect encoding and convert to UTF-8
                        $encoding = mb_detect_encoding($fileContents, 'UTF-8, ISO-8859-2, Windows-1252', true);
                        if ($encoding) {
                            $fileContents = mb_convert_encoding($fileContents, 'UTF-8', $encoding);
                        } else {
                            // echo "Unable to detect encoding.";
                            exit;
                        }

                        // Escape special characters for safe display
                        $fileContents = htmlspecialchars($fileContents, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    } else {
                        // echo "A fájl nem található!";
                    }

                    // Split the file into lines
                    $lines = explode("\n", $fileContents);
                    
                    $index = 0;
                    $IMDB_ID = "";
                    foreach ($lines as $line) {
                        // Process each line
                        if(strpos($line, " ") != 0){
                            // Remove until first space
                            $line = substr($line, strpos($line, " "));
                        }
                        $line = preg_replace('/[^\x20-\x7E]/', '', $line);
                        if(str_contains($line, ":")){
                            // Slpilt by ":"
                            $line = explode(":", $line);
                            if(trim(substr($line[0], 0, strpos($line[0], '..'))) > 0 && strlen($line[0]) > 0){
                                $Detail = str_replace(".", " ", strtolower(substr($line[0], 0, strpos($line[0], '..'))));
                                switch(trim($Detail)){
                                    case "hun title":
                                        // echo $Detail . " : " . $line[1] . $line[2] . "<br>";
                                        break;
                                    case "imdb":
                                        // echo $Detail . " : " . $line[1] . $line[2] . "<br>";
                                        $Link = $line[1] . $line[2];
                                        $ID = substr($Link, strrpos($Link, 'title/') + 6);
                                        $ID = str_replace("/", "", $ID);
                                        // echo $ID . "<br>";
                                        $IMDB_ID = $ID;
                                        break;
                                    case "imdb link":
                                        // echo $Detail . " : " . $line[1] . $line[2] . "<br>";
                                        $Link = $line[1] . $line[2];
                                        $ID = substr($Link, strrpos($Link, 'title/') + 6);
                                        $ID = str_replace("/", "", $ID);
                                        // echo $ID . "<br>";
                                        $IMDB_ID = $ID;
                                        break;
                                    case "imdb url":
                                        // echo $Detail . " : " . $line[1] . $line[2] . "<br>";
                                        $Link = $line[1] . $line[2];
                                        $ID = substr($Link, strrpos($Link, 'title/') + 6);
                                        $ID = str_replace("/", "", $ID);
                                        // echo $ID . "<br>";
                                        $IMDB_ID = $ID;
                                        break;
                                    case "imdb rating":
                                        // echo $Detail . " : " . $line[1] . $line[2] . "<br>";
                                        break;
                                }
                                // echo $Detail . " : " . $line[1] . $line[2] . "<br>";
                            }
                            else if (str_contains($subfile, "zsozso")){
                                switch($index){
                                    case 1:
                                        // echo "HU Cím : " . explode("\\", $line[1] . $line[2])[1] . "<br>";
                                        break;
                                }
                                // echo "unknown : " . $line[1] . $line[2] . "<br>";
                                $index++;
                            }
                        }
                    }
                }

                // Check if it's an mkv file without "-" in the name
                if (is_file($subdir . '\\' . $subfile) && pathinfo($subdir . '\\' . $subfile, PATHINFO_EXTENSION) == 'mkv') {
                    $filePath = 'D:\\' . $file . '\\' . $subfile;
                    if(str_contains($subdir, "Video projects")){
                    }
                    else{
                        // Call OMDB API, but only if we have an IMDB ID and a local movie file
                        if($IMDB_ID != ""){
                            $API_Key = "37daa229";
                            $OMDB_URL = "http://www.omdbapi.com/?apikey=" . $API_Key . "&i=" . $IMDB_ID;
                            $OMDB_JSON = file_get_contents($OMDB_URL);
                            $OMDB_Data = json_decode($OMDB_JSON, true);
                            echo "<h2>Movie Details</h2>";
                            echo "<strong>Title:</strong> " . $OMDB_Data['Title'] . "<br>";
                            echo "<strong>Year:</strong> " . $OMDB_Data['Year'] . "<br>";
                            echo "<strong>Rated:</strong> " . $OMDB_Data['Rated'] . "<br>";
                            echo "<strong>Released:</strong> " . $OMDB_Data['Released'] . "<br>";
                            echo "<strong>Runtime:</strong> " . $OMDB_Data['Runtime'] . "<br>";
                            echo "<strong>Genre:</strong> " . $OMDB_Data['Genre'] . "<br>";
                            echo "<strong>Director:</strong> " . $OMDB_Data['Director'] . "<br>";
                            echo "<strong>Writer:</strong> " . $OMDB_Data['Writer'] . "<br>";
                            echo "<strong>Actors:</strong> " . $OMDB_Data['Actors'] . "<br>";
                            echo "<strong>Plot:</strong> " . $OMDB_Data['Plot'] . "<br>";
                            echo "<strong>Language:</strong> " . $OMDB_Data['Language'] . "<br>";
                            echo "<strong>Country:</strong> " . $OMDB_Data['Country'] . "<br>";
                            echo "<strong>Awards:</strong> " . $OMDB_Data['Awards'] . "<br>";
                            echo "<strong>Poster:</strong><br> <img src=\"" . $OMDB_Data['Poster'] . "\" alt=\"Movie Poster\"><br>";
                            echo "<strong>Ratings:</strong><br>";
                            foreach ($OMDB_Data['Ratings'] as $rating) {
                                echo $rating['Source'] . ": " . $rating['Value'] . "<br>";
                            }
                            $IMDB_ID = "";
                        }

                        // Try playing it in the browser
                        echo '<video id="my-video" class="video-js" controls preload="auto" width="640" height="360"
                                    data-setup=\'{}\'>
                                    <source src="https://5xhm2rv4-443.euw.devtunnels.ms/movies/' . $file . '/' . $subfile . '" type="video/webm"> <!-- Replace with the file path -->
                                    <p class="vjs-no-js">
                                        To view this video please enable JavaScript, and consider upgrading to a
                                        web browser that
                                        <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
                                    </p>
                                </video>';
                    }
                }
            }
        }

        // Restore previous error reporting level
        error_reporting($previousErrorReporting);
    }
    echo "";
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://vjs.zencdn.net/7.20.3/video-js.css" rel="stylesheet">
    <style>
        body{
            color: white;
            background-color: black;
        }
    </style>
</head>
<body>
    <script src="https://vjs.zencdn.net/7.11.4/video.min.js"></script>
</body>
</html>