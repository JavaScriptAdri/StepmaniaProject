  // Crear un nuevo elemento de audio para el efecto de sonido
  function playSound(soundFile) {
    var audioPlayer = document.getElementById('audio-player');
    audioPlayer.src = soundFile;
    audioPlayer.play();
}
  window.addEventListener('load', function() {
    setTimeout(function() {
      document.getElementById('intro').style.display = 'none';
    }, 2000);
  });

  // Agregar el evento de mouseover a cada enlace del menú
  const menuLinks = document.querySelectorAll('.menu a, .menu button, .menu li');
  menuLinks.forEach(link => {
    link.addEventListener('mouseover', function() {
      hoverSound.currentTime = 0; // Reiniciar el sonido
      hoverSound.play(); // Reproducir el sonido al pasar el ratón
    });
  });

  const menuItems = document.querySelectorAll('.menu a, .menu button, .menu li');
  let currentIndex = 0;

  function updateActive() {
    menuItems.forEach((item, index) => {
      item.classList.toggle('active', index === currentIndex);
    });
    menuItems[currentIndex].focus(); // Focalizar el elemento activo
    hoverSound.currentTime = 0; // Reiniciar el sonido
    hoverSound.play(); // Reproducir el sonido al cambiar de elemento
  }

  document.querySelector('.menu').addEventListener('keydown', (event) => {
    if (event.key === 'ArrowDown') {
      currentIndex = (currentIndex + 1) % menuItems.length; // Mover hacia abajo
      updateActive();
      event.preventDefault(); // Prevenir el desplazamiento de la página
    } else if (event.key === 'ArrowUp') {
      currentIndex = (currentIndex - 1 + menuItems.length) % menuItems.length; // Mover hacia arriba
      updateActive();
      event.preventDefault(); // Prevenir el desplazamiento de la página
    } else if (event.key === 'Enter') {
      menuItems[currentIndex].click(); // Simular un clic en el elemento activo
    }
  });

  // Focalizar el menú al cargar la página
  document.querySelector('.menu').focus();
  updateActive(); // Inicializa el estado activo
