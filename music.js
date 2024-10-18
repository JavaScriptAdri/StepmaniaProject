document.getElementById('playButton').addEventListener('click', function() {
    var select = document.getElementById('songSelect');
    var audioPlayer = document.getElementById('audioPlayer');
    audioPlayer.src = select.value;
    audioPlayer.play();
});

// Manejar el botón de reproducción
playButton.onclick = () => {
    const selectedSong = songSelect.value;
    if (selectedSong) {
        audioPlayer.src = selectedSong; // Asigna la fuente de audio
        audioPlayer.play(); // Reproduce la canción seleccionada
    } else {
        alert("Selecciona una canción primero!"); // Mensaje de alerta
    }
};

// Manejar el botón de Home
homeButton.onclick = () => {
    window.location.href = 'index.html'; // Redirige a index.html
};

document.getElementById('editButton').addEventListener('click', function() {
    var select = document.getElementById('songSelect');
    var songIndex = select.selectedIndex - 1; // Restamos 1 por el primer option vacío
    
    if (songIndex >= 0) {
        // Mostrar el formulario de edición
        document.getElementById('editForm').style.display = 'block';
        
        // Precargar el nombre de la canción seleccionada en el formulario de edición
        var selectedOption = select.options[select.selectedIndex];
        document.getElementById('editSongName').value = selectedOption.text;
        document.getElementById('songIndex').value = songIndex; // Guardar el índice de la canción
    } else {
        alert("Selecciona una canción para editar.");
    }
});

    // Obtenir tots els botons d'edició
    const editButtons = document.querySelectorAll('.edit-btn');
    
    // Afegir event listener a cada botó d'edició
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const songId = this.getAttribute('data-id');
            const songItem = this.parentElement;

            // Obtenir dades de la cançó
            const songName = songItem.querySelector('h3').textContent;
            const description = songItem.querySelector('p').textContent;
            const cover = songItem.querySelector('img').src;

            // Precargar dades al formulari d'edició
            document.getElementById('editSongId').value = songId;
            document.getElementById('editSongName').value = songName;
            document.getElementById('editDescription').value = description;

            // Si és necessari, pots precarregar la caràtula
            // Nota: No podem mostrar la caràtula precàrrega en un input de tipus 'file'
            // així que podem mostrar-la en una imatge auxiliar, si cal.
            const coverInput = document.createElement('img');
            coverInput.src = cover;
            coverInput.style.width = '100px';
            coverInput.alt = 'Caràtula actual';
            coverInput.id = 'currentCoverImage';
            document.getElementById('editFormContainer').insertBefore(coverInput, document.getElementById('editCover'));

            // Mostrar el formulari d'edició
            document.getElementById('editFormContainer').style.display = 'block';
        });
    });

    // Cancel·lar l'edició
    document.getElementById('cancelEdit').addEventListener('click', function() {
        document.getElementById('editFormContainer').style.display = 'none';
        const currentCover = document.getElementById('currentCoverImage');
        if (currentCover) {
            currentCover.remove(); // Eliminar la imatge de caràtula si existeix
        }
    });

    function calculateDuration(input) {
        const file = input.files[0];
        const audio = new Audio(URL.createObjectURL(file));
        audio.addEventListener('loadedmetadata', function() {
            const duration = audio.duration; // Obtiene la duración en segundos
            const minutes = Math.floor(duration / 60);
            const seconds = Math.floor(duration % 60);
            document.getElementById('durationInput').value = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`; // Formato mm:ss
            document.getElementById('durationDisplay').textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`; // Muestra la duración
        });
    }
    