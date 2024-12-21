<?PHP
    $MovieID = isset($_GET['URL']) ? $_GET['URL'] : '';

    $ExpodeContent = [];

    if (!empty($MovieID)) {
        $fileContent = file_get_contents($MovieID);
        if ($fileContent === FALSE) {
            echo "Error: Unable to fetch the content.";
        } else {
            $ExpodeContent = explode("#", $fileContent);
        }
    } else {
        echo "No URL provided.";
    }


    foreach ($ExpodeContent as $key => $value) {
        $ExpodeContent[$key] = str_replace('EXTINF:-1 ', '', $value);
    }

    foreach ($ExpodeContent as $value) {
        // Write out the content
        echo $value . "<br>";
    
        // Split the line by spaces to handle individual attributes
        $ExplodeLines = explode(" ", $value);
    
        // Initialize variables for tvg-id and tvg-logo
        $TVG_ID = '';
        $TVG_LOGO = '';
    
        // Iterate over each part of the exploded line
        foreach ($ExplodeLines as $line) {
            // Check if the line contains the tvg-id
            if (strpos($line, "tvg-id") !== false) {
                $TVG_ID = explode("tvg-id=\"", $line)[1];
                $TVG_ID = explode("\"", $TVG_ID)[0];
                echo "TVG ID: " . $TVG_ID . "<br>";
            }
    
            // Check if the line contains the tvg-logo
            if (strpos($line, "tvg-logo") !== false) {
                $TVG_LOGO = explode("tvg-logo=\"", $line)[1];
                $TVG_LOGO = explode("\"", $TVG_LOGO)[0];
                echo "TVG Logo: " . $TVG_LOGO . "<br>";
            }
        }
    
        // Extract the channel name and stream URL (after the comma)
        $parts = explode(',', $value, 2);
        if (isset($parts[1])) {
            $channel_and_url = explode(' ', trim($parts[1]), 2);  // Extract the channel name and URL
            $Channel_Name = isset($channel_and_url[0]) ? $channel_and_url[0] : '';
            $Stream_URL = isset($channel_and_url[1]) ? $channel_and_url[1] : '';
    
            // Output channel name and stream URL
            echo "Channel Name: " . $Channel_Name . "<br>";
            echo "Stream URL: " . $Stream_URL . "<br><br>";
        }
    }
?>
