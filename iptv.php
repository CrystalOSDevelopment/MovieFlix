<?PHP
$MovieID = isset($_GET['URL']) ? $_GET['URL'] : '';

$ExpodeContent = [];

if (!empty($MovieID)) {
    $fileContent = file_get_contents($MovieID);
    if ($fileContent === FALSE) {
        echo "Error: Unable to fetch the content.";
        return;
    } else {
        $ExpodeContent = explode("#", $fileContent);
    }
} else {
    echo "No URL provided.";
    return;
}

foreach ($ExpodeContent as $key => $value) {
    $ExpodeContent[$key] = str_replace('EXTINF:-1 ', '', $value);
}

foreach ($ExpodeContent as $index => $value) {
    // Skip empty lines
    if (trim($value) === '') {
        continue;
    }

    // Initialize variables
    $TVG_ID = '';
    $TVG_LOGO = '';
    $Channel_Name = '';
    $Stream_URL = '';

    // Extract `tvg-id`
    if (strpos($value, 'tvg-id') !== false) {
        preg_match('/tvg-id="([^"]+)"/', $value, $matches);
        $TVG_ID = htmlspecialchars($matches[1] ?? '', ENT_QUOTES, 'UTF-8');
        echo "TVG ID: " . $TVG_ID . "<br>";
    }

    // Extract `tvg-logo`
    if (strpos($value, 'tvg-logo') !== false) {
        preg_match('/tvg-logo="([^"]+)"/', $value, $matches);
        $TVG_LOGO = htmlspecialchars($matches[1] ?? '', ENT_QUOTES, 'UTF-8');
        echo "TVG Logo: <img src=\"" . $TVG_LOGO . "\" alt=\"Logo\" style=\"max-height: 50px; max-width: 50px;\"><br>";
    }

    // Extract the channel name and stream URL
    $lastCommaPos = strrpos($value, ',');
    if ($lastCommaPos !== false) {
        $afterComma = substr($value, $lastCommaPos + 1); // Everything after the last comma
        preg_match('/(.+?)\s(http.*)/', $afterComma, $matches);
        $Channel_Name = htmlspecialchars(trim($matches[1] ?? $afterComma), ENT_QUOTES, 'UTF-8'); // Text before the URL
        $Stream_URL = htmlspecialchars(trim($matches[2] ?? ''), ENT_QUOTES, 'UTF-8');           // URL after the name
    }

    // Output channel name and stream URL
    echo "Channel Name: " . $Channel_Name . "<br>";
    echo "Stream URL: " . $Stream_URL . "<br>";

    // Add a video player only if the Stream_URL is valid
    if (!empty($Stream_URL)) {
        echo "<video-js id=\"my-video-$index\" class=\"video-js vjs-default-skin embed-responsive-item\" controls preload=\"auto\" width=\"80%\" height=\"80%\" data-setup='{}'>
                <source src=\"$Stream_URL\" type=\"application/x-mpegURL\">
              </video-js><br>";
    }
}

echo "<script src=\"https://vjs.zencdn.net/7.11.4/video.min.js\"></script>";
?>