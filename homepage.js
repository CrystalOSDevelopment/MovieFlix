// In case of search related problems, search in this function
function Search(event) {
    //const year = document.getElementById('year').value;
    const searchTerm = document.getElementById('SearchTerm').value;
    //const type = document.getElementById('type').value;

    const year = 0;
    const type = "";

    const movieDataDiv = document.getElementById('movieData');
    movieDataDiv.style.display = 'block';
    movieDataDiv.innerHTML = '<p>Please wait...</p>';

    fetch(`index.php`, {
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
                
                movieDiv.addEventListener('click', function() {
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
    const movieDataDiv = document.getElementById('movieData');
    // Give it style
    movieDataDiv.style.display = 'block';
    movieDataDiv.innerHTML = '<p>Please wait...</p>';
    fetch(`movies.php?wantRecents=1`)
    .then(response => response.json())
    .then(data => {
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
            
            movieDiv.addEventListener('click', function() {
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
    const movieDataDiv = document.getElementById('movieData');
    // Give it style
    movieDataDiv.style.display = 'block';
    movieDataDiv.innerHTML = '<p>Please wait...</p>';
    fetch(`movies.php?wantFavorites=1`)
    .then(response => response.json())
    .then(data => {
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
            
            movieDiv.addEventListener('click', function() {
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