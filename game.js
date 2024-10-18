document.addEventListener('DOMContentLoaded', function () {
    let currentIndex = 0;
    const songs = document.querySelectorAll('.song');
    
    if (songs.length > 0) {
        // Marca la primera cançó com a seleccionada
        songs[currentIndex].classList.add('selected');

        // Funció per actualitzar la selecció de la cançó
        function updateSelection() {
            songs.forEach((song, index) => {
                song.classList.toggle('selected', index === currentIndex);
            });
        }

 // Escoltar els esdeveniments del teclat
document.addEventListener('keydown', function (event) {
    if (event.key === 'ArrowDown') {
        // Moure cap avall en la llista
        currentIndex = (currentIndex + 1) % songs.length;
        updateSelection();
    } else if (event.key === 'ArrowUp') {
        // Moure cap amunt en la llista
        currentIndex = (currentIndex - 1 + songs.length) % songs.length;
        updateSelection();
    } else if (event.key === 'Enter') {
        // Seleccionar la cançó amb Enter
        const selectedSong = songs[currentIndex];
        const downloadLink = selectedSong.querySelector('a[download]');
        if (downloadLink) {
            // Redirigir o obrir el fitxer del joc
            window.location.href = downloadLink.href;
        }
    } else if (event.key === 'Escape') {
        // Redirigir a index.html quan es premi Escape
        window.location.href = 'index.html';
    }
});

        
    }
});