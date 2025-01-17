(async () => {
    const fetch = (await import('node-fetch')).default;

    let API_KEY = '';

    const imdbId = process.argv[2];
    const url = `https://api.kinocheck.com/movies?imdb_id=${imdbId}&language=en`;

    let TMDB_ID = "";

    try {
        const response = await fetch(url);
        const data = await response.json();

        // Extract the tmdb_id from the JSON response
        TMDB_ID = data.tmdb_id;

        // console.log('TMDB ID:', TMDB_ID);
    } catch (error) {
        console.error('Error fetching data:', error);
    }

    if (TMDB_ID !== '') {
        // Get the youtube url from the first video
        const tmdbUrl = `https://api.themoviedb.org/3/movie/${TMDB_ID}/videos?language=en-US`;
        const options = {
            method: 'GET',
            headers: {
                accept: 'application/json',
                Authorization: 'Bearer ' + API_KEY
            }
        };

        let YOUTUBE_URL = '';

        try {
            const res = await fetch(tmdbUrl, options);
            const json = await res.json();
            YOUTUBE_URL = "https://www.youtube.com/watch?v=" + json.results[0].key;
            // console.log(json.results[0].key);
            // console.log("Full link: https://www.youtube.com/watch?v=" + json.results[0].key);
            // console.log("YOUTUBE URL: ", YOUTUBE_URL);
        } catch (err) {
            console.error(err);
        }

        // Extract video from the youtube link by using the youtube-dl-exec package
        const youtubeDl = (await import('youtube-dl-exec')).default;

        if (YOUTUBE_URL !== '') {
            try {
                const info = await youtubeDl(YOUTUBE_URL, {
                    format: 'best',
                    dumpSingleJson: true
                });
                // Only write out the highest resolution video
                let MaxRes = 0;
                let Link = '';
                for (let i = 0; i < info.formats.length; i++) {
                    if (info.formats[i].height > MaxRes && info.formats[i].vcodec !== 'none' && info.formats[i].acodec !== 'none') {
                        MaxRes = info.formats[i].height;
                        Link = info.formats[i].url;
                    }
                }
                console.log(Link);
            } catch (err) {
                console.error('Error downloading video:', err);
            }
        } else {
            console.error('No YouTube URL found.');
        }
    }
})();