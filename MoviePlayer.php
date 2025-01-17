<?php
    // Check if the user is logged in, if not then redirect him to login page

    $MovieTitle = $_POST['MovieTitle']; // Movie Title
    $MovieID = $_POST['MovieID'];    // Movie IMDB ID
    $Film_Link = $_POST['MovieURL'];  // Movie URL


?>

<!DOCTYPE html>
<html>
    <head>
        <title>Movie Player</title>
        <link rel="stylesheet" type="text/css" href="style.css">
        <link rel="stylesheet" href="Resources/Styles/VideoPlayerExtra.css">
    </head>
    <body>
        <!-- Video player -->
         <?php
            // Check if the link is a m3u8 link
            if (strpos($Film_Link, 'm3u8') !== false) {
                echo "<video-js style=\"width: 100%; height: 100%;\" id=\"my-video\" class=\"video-js vjs-default-skin embed-responsive-item\" controls preload=\"auto\" width=\"80%\" height=\"80%\" data-setup='{}'>
                        <source src=\"{$Film_Link}\" type=\"application/x-mpegURL\">
                    </video-js>";
            }
            // Check if the link is a mkv link or any other video link
            else if(strpos($Film_Link, 'mkv') === true || str_contains($Film_Link, "moviePlaybackRedirect") || str_contains($Film_Link, "https://vk6-7.vkuser.net") || str_contains($Film_Link, ".mp4")){
                // Try playing it in the browser
                echo '<video id="my-video" class="video-js" preload="auto" width="" height="360"
                        data-setup=\'{}\'>
                        <source src="' . $Film_Link. '" type="video/webm"> <!-- Replace with the file path -->
                        <p class="vjs-no-js">
                            To view this video please enable JavaScript, and consider upgrading to a
                            web browser that
                            <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
                        </p>
                    </video>';
            }
            // If the link is leading to a website instead of a video file
            else {
                echo "<iframe src=\"{$Film_Link}\" class=\"embed-responsive-item\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>";
            }
         ?>

        <div class="content">
            <button id="JumpBack" value="a"><img src="Resources/Images/ReverseTen.png" title="Visszaugrás: 10sec"></button>
            <button id="PlayPause" value=""><img src="Resources/Images/Play.png" title="Lejátszás"></button>
            <button id="JumpForward" value=""><img src="Resources/Images/ForwardTen.png" title="Előreugrás: 10sec"></button>
        </div>

        <div class="MovieDetails">
            <!-- Movie title -->
            <h1><?php echo $MovieTitle; ?></h1>
            <!-- Slider to track the movie -->
            <div class="MovieSeek">
                <!-- Current time -->
                <p id="current-time">0:00</p>
                <input type="range" id="seek-bar" value="0">
                <!-- Duration -->
                <p id="duration">0:00</p>
            </div>
        </div>

        <script>
            document.getElementById('JumpBack').addEventListener('click', function(){
                const video = document.getElementById('my-video');
                video.currentTime -= 10;
            });
            document.getElementById('JumpForward').addEventListener('click', function(){
                const video = document.getElementById('my-video');
                video.currentTime += 10;
            });
            document.getElementById('PlayPause').addEventListener('click', function(){
                const video = document.getElementById('my-video');
                if (video.paused) {
                    video.play();
                    document.getElementById('PlayPause').innerHTML = '<img src="Resources/Images/Pause.png" title="Lejátszás">';
                } else {
                    video.pause();
                    document.getElementById('PlayPause').innerHTML = '<img src="Resources/Images/Play.png" title="Lejátszás">';
                }
            });
        </script>
    </body>
</html>