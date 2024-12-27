<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Channel Stream Viewer</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Video.js CSS -->
    <link href="https://vjs.zencdn.net/7.11.4/video-js.css" rel="stylesheet">
    <style>
        .channel-card {
            margin-bottom: 20px;
        }
        .channel-logo {
            max-height: 50px;
            max-width: 50px;
            margin-right: 10px;
        }
        .video-container {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center mb-4">Channel Stream Viewer</h1>
        <div class="row">
            <?php
            $MovieID = isset($_GET['URL']) ? $_GET['URL'] : '';

            $ExpodeContent = [];

            if (!empty($MovieID)) {
                $fileContent = file_get_contents($MovieID);
                if ($fileContent === FALSE) {
                    echo "<div class='alert alert-danger'>Error: Unable to fetch the content.</div>";
                    return;
                } else {
                    $ExpodeContent = explode("#", $fileContent);
                }
            } else {
                echo "<div class='alert alert-warning'>No URL provided.</div>";
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
                }

                // Extract `tvg-logo`
                if (strpos($value, 'tvg-logo') !== false) {
                    preg_match('/tvg-logo="([^"]+)"/', $value, $matches);
                    $TVG_LOGO = htmlspecialchars($matches[1] ?? '', ENT_QUOTES, 'UTF-8');
                }

                // Extract the channel name and stream URL
                $lastCommaPos = strrpos($value, ',');
                if ($lastCommaPos !== false) {
                    $afterComma = substr($value, $lastCommaPos + 1); // Everything after the last comma
                    preg_match('/(.+?)\s(http.*)/', $afterComma, $matches);
                    $Channel_Name = htmlspecialchars(trim($matches[1] ?? $afterComma), ENT_QUOTES, 'UTF-8'); // Text before the URL
                    $Stream_URL = htmlspecialchars(trim($matches[2] ?? ''), ENT_QUOTES, 'UTF-8');           // URL after the name
                }

                // Display the channel
                echo "<div class='col-md-6 channel-card'>
                        <div class='card'>
                            <div class='card-body'>
                                <div class='d-flex align-items-center mb-3'>
                                    " . (!empty($TVG_LOGO) ? "<img src='$TVG_LOGO' class='channel-logo' alt='Logo'>" : "") . "
                                    <h5 class='card-title mb-0'>$Channel_Name</h5>
                                </div>
                                <p class='card-text'>
                                    <strong>TVG ID:</strong> $TVG_ID <br>
                                    <strong>Stream URL:</strong> <a href='$Stream_URL' target='_blank'>$Stream_URL</a>
                                </p>
                                <div class='video-container'>
                                    " . (!empty($Stream_URL) ? "<video-js id='my-video-$index' class='video-js vjs-default-skin embed-responsive-item' controls preload='auto' width='100%' height='300px' data-setup='{}'>
                                        <source src='$Stream_URL' type='application/x-mpegURL'>
                                      </video-js>" : "") . "
                                </div>
                            </div>
                        </div>
                    </div>";
            }
            ?>
        </div>
    </div>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.4.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Video.js JS -->
    <script src="https://vjs.zencdn.net/7.11.4/video.min.js"></script>
</body>
</html>
