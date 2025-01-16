// In case of search related problems, search in this function
function Search(event) {
    //const year = document.getElementById('year').value;
    const searchTerm = document.getElementById('SearchTerm').value;
    //const type = document.getElementById('type').value;

    const Body = document.getElementsByClassName('SettingsTab');
    // Add a class to the body
    Body[0].classList.add('HideTab');

    const year = 0;
    const type = "";

    const movieDataDiv = document.getElementById('movieData');
    movieDataDiv.style.display = 'block';
    //movieDataDiv.innerHTML = '<div id="loader"><div class="spinner"></div></div>';

    document.getElementById('loader').style.display = 'flex'; // Shows the loader

    fetch(`LinkCatcher.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `year=${year}&search=${searchTerm}&type=${type}`
    })
    .then(response => response.text())
    .then(() => {
        //Get all the Option elements nad find the index of the first selected option
        const selectedOption = Array.from(document.querySelectorAll('.Option')).findIndex(option => option.style.backgroundColor != 'transparent');
        console.log(selectedOption);
        fetch(`movies.php?year=${searchTerm.trim() != "" ? "" : year}&search=${searchTerm.trim()}&orderBy=` + selectedOption)
        .then(response => response.json())
        .then(data => {
            document.getElementById('loader').style.display = 'none'; // Shows the loader

            movieDataDiv.innerHTML = '<h2>Keresési találatok erre: <i style=\"color: gray\">"' + searchTerm.trim() + '"</i></h2>';

            // Create a div
            const UpperDiv = document.createElement('div');
            // Only if the resolut is above 425px
            if(window.innerWidth > 425) {
                UpperDiv.style.display = 'grid';
            }
            UpperDiv.style.maxWidth = '90%';
            // UpperDiv.style.display = 'grid';
            data.forEach(movie => {
                const movieDiv = document.createElement('div');
                movieDiv.classList.add('movie');

                let cleanedTitle = movie.movie_title.replace(/\(\d{4}\)(\s*\(\d+\))*$/, '');
                if (cleanedTitle.length > 40) { 
                    cleanedTitle = cleanedTitle.substring(0, 17) + '...'; 
                }

                movieDiv.style.backgroundImage = `url(${movie.cover})`;
                movieDiv.style.backgroundSize = 'cover';
                movieDiv.style.backgroundPosition = 'center';
                movieDiv.style.overflow = 'hidden';
                
                // Create a div
                const SubmovieDiv = document.createElement('div');
                SubmovieDiv.innerHTML = `
                    <img src="${movie.cover}" alt="${movie.movie_title} Poster">
                    <h2>${cleanedTitle}</h2>
                    <p class="filmhossz">${movie.movie_length} perc</p>
                    <p>${movie.release_date}</p>
                `;
    
                SubmovieDiv.style.backdropFilter = "blur(4px) brightness(0.3)";
                SubmovieDiv.style.height = "100%";
                
                movieDiv.addEventListener('click', function () {
                    const loader = document.getElementById('loader');
                
                    if (loader) {
                        loader.style.display = 'flex'; // Display the loader
                    }
                
                    // Start a timer to hide the loader in the background (after 15 seconds)
                    setTimeout(() => {
                        if (loader) {
                            loader.style.display = 'none'; // Hide the loader
                        }
                    }, 15000); // 15000ms = 15 seconds
                
                    // Navigate to the new page (this happens immediately, so the timer runs in the background)
                    window.location.href = `VideoPlayer.php?id=${movie.id}`;
                });                
    
                SubmovieDiv.style.paddingTop = '40px';
                movieDiv.appendChild(SubmovieDiv);
                
                //movieDataDiv.appendChild(movieDiv);
    
                UpperDiv.appendChild(movieDiv);
    
                UpperDiv.style.id = 'movieData';
                movieDataDiv.style.marginTop = '0px';
                movieDataDiv.appendChild(UpperDiv);
            });
        })
        .catch(error => console.error('Error:', error));
    })
    .catch(error => console.error('Error:', error));
}

// In case there is a problem with the recents, search in this function
function Recents(event){
    // Clear everything from MainBody
    const Body = document.getElementsByClassName('MainBody');
    // Add a class to the body
    Body[0].classList.remove('HideTab');

    const Body2 = document.getElementsByClassName('SettingsTab');
    // Add a class to the body
    Body2[0].classList.add('HideTab');

    const movieDataDiv = document.getElementById('movieData');
    // Give it style
    movieDataDiv.style.display = 'block';
    //movieDataDiv.innerHTML = '<div id="loader"><div class="spinner"></div></div>';

    document.getElementById('loader').style.display = 'flex'; // Shows the loader

    fetch(`movies.php?wantRecents=1`)
    .then(response => response.json())
    .then(data => {
        document.getElementById('loader').style.display = 'none'; // Shows the loader
        
        movieDataDiv.innerHTML = '<h2>Legutóbbi filmek</h2>';
        const UpperDiv = document.createElement('div');
        if(window.innerWidth > 425) {
            UpperDiv.style.display = 'grid';
        }
        UpperDiv.style.maxWidth = '90%';
        data.forEach(movie => {
            const movieDiv = document.createElement('div');
            movieDiv.classList.add('movie');

            let cleanedTitle = movie.movie_title.replace(/\(\d{4}\)(\s*\(\d+\))*$/, '');
            if (cleanedTitle.length > 40) { 
                cleanedTitle = cleanedTitle.substring(0, 17) + '...'; 
            }

            movieDiv.style.backgroundImage = `url(${movie.cover})`;
            movieDiv.style.backgroundSize = 'cover';
            movieDiv.style.backgroundPosition = 'center';
            movieDiv.style.overflow = 'hidden';
            
            // Create a div
            const SubmovieDiv = document.createElement('div');
            SubmovieDiv.innerHTML = `
                <img src="${movie.cover}" alt="${movie.movie_title} Poster">
                <h2>${cleanedTitle}</h2>
                <p class="filmhossz">${movie.movie_length} perc</p>
                <p>${movie.release_date}</p>
            `;

            SubmovieDiv.style.backdropFilter = "blur(4px) brightness(0.3)";
            SubmovieDiv.style.height = "100%";
            
            movieDiv.addEventListener('click', function () {
                const loader = document.getElementById('loader');
            
                if (loader) {
                    loader.style.display = 'flex'; // Display the loader
                }
            
                // Start a timer to hide the loader in the background (after 15 seconds)
                setTimeout(() => {
                    if (loader) {
                        loader.style.display = 'none'; // Hide the loader
                    }
                }, 15000); // 15000ms = 15 seconds
            
                // Navigate to the new page (this happens immediately, so the timer runs in the background)
                window.location.href = `VideoPlayer.php?id=${movie.id}`;
            });    

            SubmovieDiv.style.paddingTop = '40px';
            movieDiv.appendChild(SubmovieDiv);
            
            //movieDataDiv.appendChild(movieDiv);

            UpperDiv.appendChild(movieDiv);

            UpperDiv.style.id = 'movieData';
            movieDataDiv.style.marginTop = '0px';
            movieDataDiv.appendChild(UpperDiv);
        });
    })
    .catch(error => console.error('Error:', error));
}

// In case there is a problem with the favorites, search in this function
function Favorites(event){
    // Clear everything from MainBody
    const Body = document.getElementsByClassName('MainBody');
    // Add a class to the body
    Body[0].classList.remove('HideTab');

    const Body2 = document.getElementsByClassName('SettingsTab');
    // Add a class to the body
    Body2[0].classList.add('HideTab');

    const movieDataDiv = document.getElementById('movieData');
    // Give it style
    movieDataDiv.style.display = 'block';
    //movieDataDiv.innerHTML = '<div id="loader"><div class="spinner"></div></div>';

    document.getElementById('loader').style.display = 'flex'; // Shows the loader

    fetch(`movies.php?wantFavorites=1`)
    .then(response => response.json())
    .then(data => {
        // Reverse the array
        data = data.reverse();

        document.getElementById('loader').style.display = 'none'; // Shows the loader

        movieDataDiv.innerHTML = '<h2>Kedvencek</h2>';
        const UpperDiv = document.createElement('div');
        if(window.innerWidth > 425) {
            UpperDiv.style.display = 'grid';
        }
        UpperDiv.style.maxWidth = '90%';
        data.forEach(movie => {
            const movieDiv = document.createElement('div');
            movieDiv.classList.add('movie');

            let cleanedTitle = movie.movie_title.replace(/\(\d{4}\)(\s*\(\d+\))*$/, '');
            if (cleanedTitle.length > 40) { 
                cleanedTitle = cleanedTitle.substring(0, 17) + '...'; 
            }

            movieDiv.style.backgroundImage = `url(${movie.cover})`;
            movieDiv.style.backgroundSize = 'cover';
            movieDiv.style.backgroundPosition = 'center';
            movieDiv.style.overflow = 'hidden';
            
            // Create a div
            const SubmovieDiv = document.createElement('div');
            SubmovieDiv.innerHTML = `
                <img src="${movie.cover}" alt="${movie.movie_title} Poster">
                <h2>${cleanedTitle}</h2>
                <p class="filmhossz">${movie.movie_length} perc</p>
                <p>${movie.release_date}</p>
            `;

            SubmovieDiv.style.backdropFilter = "blur(4px) brightness(0.3)";
            SubmovieDiv.style.height = "100%";
            
            movieDiv.addEventListener('click', function () {
                const loader = document.getElementById('loader');
            
                if (loader) {
                    loader.style.display = 'flex'; // Display the loader
                }
            
                // Start a timer to hide the loader in the background (after 15 seconds)
                setTimeout(() => {
                    if (loader) {
                        loader.style.display = 'none'; // Hide the loader
                    }
                }, 15000); // 15000ms = 15 seconds
            
                // Navigate to the new page (this happens immediately, so the timer runs in the background)
                window.location.href = `VideoPlayer.php?id=${movie.id}`;
            });

            SubmovieDiv.style.paddingTop = '40px';
            movieDiv.appendChild(SubmovieDiv);
            
            //movieDataDiv.appendChild(movieDiv);

            UpperDiv.appendChild(movieDiv);

            UpperDiv.style.id = 'movieData';
            movieDataDiv.style.marginTop = '0px';
            movieDataDiv.appendChild(UpperDiv);
        });
    })
    .catch(error => console.error('Error:', error));
}

// In case there is a problem with the settings, search in this function
function Settings(event){
    // Clear everything from MainBody
    const Body = document.getElementsByClassName('MainBody');
    // Add a class to the body
    Body[0].classList.add('HideTab');

    // Add the settings page
    const SettingsTab = document.getElementsByClassName('SettingsTab');
    // Remove the class from the settings page
    SettingsTab[0].classList.remove('HideTab');
}

// In case there is a problem with the recommendations, search in this function
function Recommendations(event){
    // Clear everything from MainBody
    const Body = document.getElementsByClassName('MainBody');
    // Add a class to the body
    Body[0].classList.remove('HideTab');

    const Body2 = document.getElementsByClassName('SettingsTab');
    // Add a class to the body
    Body2[0].classList.add('HideTab');

    const movieDataDiv = document.getElementById('movieData');
    // Give it style
    movieDataDiv.style.display = 'block';
    //movieDataDiv.innerHTML = '<div id="loader"><div class="spinner"></div></div>';

    document.getElementById('loader').style.display = 'flex'; // Shows the loader

    fetch(`movies.php?year=${new Date().getFullYear()}`)
    .then(response => response.json())
    .then(data => {
        // Only if the output is not empty
        if(data.length == 0) {
            document.getElementById('loader').style.display = 'none'; // Shows the loader
        }
        else{
    
            document.getElementById('loader').style.display = 'none'; // Shows the loader
    
            movieDataDiv.innerHTML = '<h2>Legújabb filmek (' + new Date().getFullYear() + ')</h2>';
            const UpperDiv = document.createElement('div');
            if(window.innerWidth > 425) {
                UpperDiv.style.display = 'grid';
            }
            UpperDiv.style.maxWidth = '90%';
            data.forEach(movie => {
                const movieDiv = document.createElement('div');
                movieDiv.classList.add('movie');
    
                let cleanedTitle = movie.movie_title.replace(/\(\d{4}\)(\s*\(\d+\))*$/, '');
                if (cleanedTitle.length > 40) { 
                    cleanedTitle = cleanedTitle.substring(0, 17) + '...'; 
                }
    
                movieDiv.style.backgroundImage = `url(${movie.cover})`;
                movieDiv.style.backgroundSize = 'cover';
                movieDiv.style.backgroundPosition = 'center';
                movieDiv.style.overflow = 'hidden';
                
                // Create a div
                const SubmovieDiv = document.createElement('div');
                SubmovieDiv.innerHTML = `
                    <img src="${movie.cover}" alt="${movie.movie_title} Poster">
                    <h2>${cleanedTitle}</h2>
                    <p class="filmhossz">${movie.movie_length} perc</p>
                    <p>${movie.release_date}</p>
                `;
    
                SubmovieDiv.style.backdropFilter = "blur(4px) brightness(0.3)";
                SubmovieDiv.style.height = "100%";
                
                movieDiv.addEventListener('click', function () {
                    const loader = document.getElementById('loader');
                
                    if (loader) {
                        loader.style.display = 'flex'; // Display the loader
                    }
                
                    // Start a timer to hide the loader in the background (after 15 seconds)
                    setTimeout(() => {
                        if (loader) {
                            loader.style.display = 'none'; // Hide the loader
                        }
                    }, 15000); // 15000ms = 15 seconds
                
                    // Navigate to the new page (this happens immediately, so the timer runs in the background)
                    window.location.href = `VideoPlayer.php?id=${movie.id}`;
                });
    
                SubmovieDiv.style.paddingTop = '40px';
                movieDiv.appendChild(SubmovieDiv);
                
                //movieDataDiv.appendChild(movieDiv);
    
                UpperDiv.appendChild(movieDiv);
    
                UpperDiv.style.id = 'movieData';
                movieDataDiv.style.marginTop = '0px';
                movieDataDiv.appendChild(UpperDiv);
            });
        }

        // Get the recommendations
        let FullString = "";
        let SplitString = [];
        fetch(`movies.php?wantRecommendations=1`)
        .then(response => response.json())
        .then(response => {
            response.forEach(movie => {
                FullString += movie.Category + ', ';
                SplitString = FullString.split(', ');
            });
            // Get the most common elements (3)
            const counts = {};
            SplitString.forEach(function(x) { counts[x] = (counts[x] || 0)+1; });
            const sorted = Object.keys(counts).sort(function(a,b){return counts[b]-counts[a]});
            let Top3 = sorted.slice(0, 3);

            // If there is not enough category (3), then fill it up with the most common ones
            const categories = ['Akció', 'Vígjáték', 'Dráma', 'Horror', 'Romantikus', 'Fantasy', 'Sci-fi', 'Thriller', 'Krimi', 'Misztikus', 'Kaland', 'Háborús', 'Western', 'Történelmi', 'Életrajzi', 'Sport', 'Zenés', 'Animációs', 'Családi', 'Mese', 'Dokumentum'];
            for(let i = Top3.length; i < 3;) {
                // Only add if it is not already in the array
                if(!response.includes({Category: categories[Math.floor(Math.random() * categories.length)]})) {
                    response.push({Category: categories[Math.floor(Math.random() * categories.length)]});
                    i++;
                }
            }

            document.getElementById('movieData').innerHTML = '';
            document.getElementById('movieData').style.marginTop = '0px';

            // Write out the 3 movie categories
            Top3.forEach(category => {
                // Fetch the movies from the category
                fetch(`movies.php?genre=${category}&&orderBy=1`)
                    .then(res => res.json())
                    .then(res => {
                        const movieDataDiv = document.getElementById('movieData');
                        
                        // Create a heading for the category
                        const categoryHeading = document.createElement('h2');
                        categoryHeading.textContent = category;
                        movieDataDiv.appendChild(categoryHeading);
                        
                        // Create a container for the movies
                        const UpperDiv = document.createElement('div');
                        UpperDiv.style.maxWidth = '90%';
                        UpperDiv.style.display = window.innerWidth > 425 ? 'grid' : 'block';
            
                        res.forEach(movie => {
                            const movieDiv = document.createElement('div');
                            movieDiv.classList.add('movie');
                            
                            // Clean up the movie title
                            let cleanedTitle = movie.movie_title.replace(/\(\d{4}\)(\s*\(\d+\))*$/, '');
                            if (cleanedTitle.length > 40) { 
                                cleanedTitle = cleanedTitle.substring(0, 17) + '...'; 
                            }
            
                            // Style the movie div
                            movieDiv.style.backgroundImage = `url(${movie.cover})`;
                            movieDiv.style.backgroundSize = 'cover';
                            movieDiv.style.backgroundPosition = 'center';
                            movieDiv.style.overflow = 'hidden';
                            movieDiv.style.cursor = 'pointer';
            
                            // Create a sub-div for the movie details
                            const SubmovieDiv = document.createElement('div');
                            SubmovieDiv.innerHTML = `
                                <img src="${movie.cover}" alt="${movie.movie_title} Poster">
                                <h2>${cleanedTitle}</h2>
                                <p class="filmhossz">${movie.movie_length} perc</p>
                                <p>${movie.release_date}</p>
                            `;
                            SubmovieDiv.style.backdropFilter = "blur(4px) brightness(0.3)";
                            SubmovieDiv.style.height = "100%";
                            SubmovieDiv.style.paddingTop = '40px';
            
                            // Add click event listener to the movie div
                            movieDiv.addEventListener('click', function () {
                                console.log(`Movie ID: ${movie.id}`);
                                
                                // Optional loader handling
                                const loader = document.getElementById('loader');
                                if (loader) {
                                    loader.style.display = 'flex'; // Show the loader
                                    setTimeout(() => loader.style.display = 'none', 15000); // Hide after 15 seconds
                                }
            
                                // Navigate to video player
                                window.location.href = `VideoPlayer.php?id=${movie.id}`;
                            });
            
                            // Append details to the movie div and the movie div to the container
                            movieDiv.appendChild(SubmovieDiv);
                            UpperDiv.appendChild(movieDiv);
                        });
            
                        // Append the category container to the movie data container
                        movieDataDiv.appendChild(UpperDiv);
                    })
                    .catch(error => console.error('Error fetching movies:', error));
            });            
        });
    })
    .catch(error => console.error('Error:', error));
}