// Esperar a que el DOM esté completamente cargado
document.addEventListener("DOMContentLoaded", function() {
    // Inicializar la lluvia Matrix
    initMatrixRain();
    
    // Configurar el video de fondo
    setupBackgroundVideo();
    
    // Configurar la navegación móvil
    setupMobileNav();
    
    // Configurar filtros de música
    setupMusicFilters();
    
    // Configurar reproductor de música
    setupMusicPlayer();
    
    // Animación de fade-in para elementos
    setupFadeInElements();
});

// Inicializar la lluvia Matrix
function initMatrixRain() {
    const canvas = document.getElementById('matrix-rain');
    const ctx = canvas.getContext('2d');

    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const fontSize = 14;
    const columns = Math.floor(canvas.width / fontSize);
    
    const drops = [];
    for (let i = 0; i < columns; i++) {
        drops[i] = Math.floor(Math.random() * canvas.height / fontSize) * -1;
    }

    // Caracteres Matrix
    const matrix = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789@#$%^&*()*&^%$#@!~';
    
    function draw() {
        ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        ctx.fillStyle = '#00ff41';
        ctx.font = fontSize + 'px monospace';
        
        for (let i = 0; i < drops.length; i++) {
            const text = matrix[Math.floor(Math.random() * matrix.length)];
            ctx.fillText(text, i * fontSize, drops[i] * fontSize);
            
            if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
                drops[i] = 0;
            }
            
            drops[i]++;
        }
    }

    window.addEventListener('resize', function() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        
        const newColumns = Math.floor(canvas.width / fontSize);
        
        if (newColumns > columns) {
            for (let i = columns; i < newColumns; i++) {
                drops[i] = Math.floor(Math.random() * canvas.height / fontSize) * -1;
            }
        }
    });

    setInterval(draw, 35);
}

// Configurar el video de fondo
function setupBackgroundVideo() {
    const video = document.getElementById('background-video');
    
    if (video) {
        if (video.canPlayType) {
            window.addEventListener('resize', function() {
                adjustVideoSize(video);
            });
            
            adjustVideoSize(video);
            
            video.addEventListener('error', function() {
                document.getElementById('matrix-rain').style.opacity = '1';
            });
        } else {
            document.getElementById('matrix-rain').style.opacity = '1';
        }
    }
}

// Ajustar tamaño del video
function adjustVideoSize(videoElement) {
    const windowWidth = window.innerWidth;
    const windowHeight = window.innerHeight;
    const videoRatio = 16/9;
    
    let newWidth, newHeight;
    
    if (windowWidth / windowHeight > videoRatio) {
        newWidth = windowWidth;
        newHeight = windowWidth / videoRatio;
    } else {
        newWidth = windowHeight * videoRatio;
        newHeight = windowHeight;
    }
    
    videoElement.style.width = newWidth + 'px';
    videoElement.style.height = newHeight + 'px';
}

// Configurar la navegación móvil
function setupMobileNav() {
    const menuToggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('nav');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            nav.classList.toggle('active');
            menuToggle.querySelector('i').classList.toggle('fa-bars');
            menuToggle.querySelector('i').classList.toggle('fa-times');
        });
    }
    
    const navLinks = document.querySelectorAll('nav ul li a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (nav.classList.contains('active')) {
                nav.classList.remove('active');
                menuToggle.querySelector('i').classList.add('fa-bars');
                menuToggle.querySelector('i').classList.remove('fa-times');
            }
        });
    });
}

// Configurar filtros de música
function setupMusicFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const trackCards = document.querySelectorAll('.track-card');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remover clase 'active' de todos los botones
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // Añadir clase 'active' al botón actual
            this.classList.add('active');
            
            // Obtener el filtro
            const filter = this.getAttribute('data-filter');
            
            // Mostrar u ocultar las tarjetas según el filtro
            trackCards.forEach(card => {
                if (filter === 'all' || card.getAttribute('data-category') === filter) {
                    card.style.display = 'block';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 500);
                }
            });
        });
    });
}

// Configurar reproductor de música
function setupMusicPlayer() {
    const playButtons = document.querySelectorAll('.play-btn');
    const featuredPlayButton = document.querySelector('.featured-play-btn');
    
    // Audio actual reproduciendo
    let currentAudio = null;
    let currentButton = null;
    let currentCard = null;
    let isFeaturedPlaying = false;
    
    // Variables para el seguimiento del progreso
    let progressInterval = null;
    
    // Reproducir canción normal
    playButtons.forEach(button => {
        button.addEventListener('click', function() {
            const audioSrc = this.getAttribute('data-audio');
            const card = this.closest('.track-card');
            
            // Si el botón clickeado es el mismo que ya estaba reproduciendo, pausar/reproducir
            if (currentButton === this && currentAudio) {
                if (currentAudio.paused) {
                    // Si está pausado, reanudar
                    currentAudio.play();
                    this.innerHTML = '<i class="fas fa-pause"></i>';
                    if (currentCard) {
                        currentCard.classList.add('playing');
                    }
                    
                    // Reiniciar intervalo de progreso
                    startProgressUpdate(currentAudio, card.querySelector('.track-progress-bar'), card.querySelector('.track-current'));
                } else {
                    // Si está reproduciendo, pausar
                    currentAudio.pause();
                    this.innerHTML = '<i class="fas fa-play"></i>';
                    if (currentCard) {
                        currentCard.classList.remove('playing');
                    }
                    
                    // Detener la actualización de progreso
                    clearInterval(progressInterval);
                }
                return;
            }
            
            // Si hay otro audio reproduciendo, detenerlo
            if (currentAudio) {
                currentAudio.pause();
                currentAudio.currentTime = 0;
                
                // Actualizar UI del botón anterior
                if (currentButton) {
                    currentButton.innerHTML = '<i class="fas fa-play"></i>';
                }
                
                // Quitar clase 'playing' de la tarjeta anterior
                if (currentCard) {
                    currentCard.classList.remove('playing');
                }
                
                // Detener la actualización de progreso
                clearInterval(progressInterval);
                
                // Si el reproductor destacado estaba reproduciendo, actualizar su UI
                if (isFeaturedPlaying) {
                    featuredPlayButton.innerHTML = '<i class="fas fa-play"></i>';
                    document.querySelector('.featured-player').classList.remove('featured-playing');
                    isFeaturedPlaying = false;
                }
            }
            
            // Crear nuevo audio
            let audio = new Audio(audioSrc);
            
            // Manejar errores de carga
            audio.addEventListener('error', function(e) {
                console.error('Error al cargar el audio:', e);
                // Intentar cargar con extensión .mp3 si no se especificó
                if (!audioSrc.endsWith('.mp3')) {
                    const newSrc = audioSrc + '.mp3';
                    console.log('Intentando con:', newSrc);
                    audio = new Audio(newSrc);
                    loadAndPlay(audio);
                } else {
                    alert('No se pudo reproducir la canción. El archivo puede no existir o el navegador no lo soporta.');
                }
            });
            
            function loadAndPlay(audioElement) {
                // Reproducir
                audioElement.play().then(() => {
                    // Actualizar el botón a pausa
                    button.innerHTML = '<i class="fas fa-pause"></i>';
                    
                    // Agregar clase 'playing' a la tarjeta
                    card.classList.add('playing');
                    
                    // Actualizar las variables actuales
                    currentAudio = audioElement;
                    currentButton = button;
                    currentCard = card;
                    
                    // Configurar actualización de progreso
                    startProgressUpdate(audioElement, card.querySelector('.track-progress-bar'), card.querySelector('.track-current'));
                    
                    // Manejar fin de la canción
                    audioElement.addEventListener('ended', function() {
                        resetPlayState();
                    });
                    
                }).catch(error => {
                    console.error('Error al reproducir la canción:', error);
                    if (audioSrc.endsWith('.mp3') || audio !== audioElement) {
                        alert('No se pudo reproducir la canción. El archivo puede no existir o el navegador no lo soporta.');
                    }
                });
            }
            
            // Intentar reproducir
            loadAndPlay(audio);
        });
    });
    
    // Reproducir canción destacada
    if (featuredPlayButton) {
        featuredPlayButton.addEventListener('click', function() {
            const audioSrc = this.getAttribute('data-audio');
            const featuredPlayer = document.querySelector('.featured-player');
            
            // Si el botón destacado ya estaba reproduciendo, pausar/reanudar
            if (isFeaturedPlaying && currentAudio) {
                if (currentAudio.paused) {
                    // Si está pausado, reanudar
                    currentAudio.play();
                    this.innerHTML = '<i class="fas fa-pause"></i>';
                    featuredPlayer.classList.add('featured-playing');
                    
                    // Reiniciar intervalo de progreso
                    startProgressUpdate(
                        currentAudio, 
                        featuredPlayer.querySelector('.featured-progress-bar'), 
                        featuredPlayer.querySelector('.featured-current-time')
                    );
                } else {
                    // Si está reproduciendo, pausar
                    currentAudio.pause();
                    this.innerHTML = '<i class="fas fa-play"></i>';
                    featuredPlayer.classList.remove('featured-playing');
                    
                    // Detener la actualización de progreso
                    clearInterval(progressInterval);
                }
                return;
            }
            
            // Si hay otro audio reproduciendo, detenerlo
            if (currentAudio) {
                currentAudio.pause();
                currentAudio.currentTime = 0;
                
                // Actualizar UI del botón anterior
                if (currentButton) {
                    currentButton.innerHTML = '<i class="fas fa-play"></i>';
                }
                
                // Quitar clase 'playing' de la tarjeta anterior
                if (currentCard) {
                    currentCard.classList.remove('playing');
                }
                
                // Detener la actualización de progreso
                clearInterval(progressInterval);
            }
            
            // Crear nuevo audio
            let audio = new Audio(audioSrc);
            
            // Manejar errores de carga
            audio.addEventListener('error', function(e) {
                console.error('Error al cargar el audio destacado:', e);
                // Intentar cargar con extensión .mp3 si no se especificó
                if (!audioSrc.endsWith('.mp3')) {
                    const newSrc = audioSrc + '.mp3';
                    console.log('Intentando con:', newSrc);
                    audio = new Audio(newSrc);
                    loadAndPlay(audio);
                } else {
                    alert('No se pudo reproducir la canción destacada. El archivo puede no existir o el navegador no lo soporta.');
                }
            });
            
            function loadAndPlay(audioElement) {
                // Reproducir
                audioElement.play().then(() => {
                    // Actualizar el botón a pausa
                    featuredPlayButton.innerHTML = '<i class="fas fa-pause"></i>';
                    
                    // Agregar clase para mostrar ecualizador
                    featuredPlayer.classList.add('featured-playing');
                    
                    // Actualizar las variables actuales
                    currentAudio = audioElement;
                    currentButton = featuredPlayButton;
                    currentCard = null;
                    isFeaturedPlaying = true;
                    
                    // Configurar actualización de progreso
                    startProgressUpdate(
                        audioElement, 
                        featuredPlayer.querySelector('.featured-progress-bar'), 
                        featuredPlayer.querySelector('.featured-current-time')
                    );
                    
                    // Manejar fin de la canción
                    audioElement.addEventListener('ended', function() {
                        resetPlayState();
                        featuredPlayer.classList.remove('featured-playing');
                        isFeaturedPlaying = false;
                    });
                    
                }).catch(error => {
                    console.error('Error al reproducir la canción destacada:', error);
                    if (audioSrc.endsWith('.mp3') || audio !== audioElement) {
                        alert('No se pudo reproducir la canción destacada. El archivo puede no existir o el navegador no lo soporta.');
                    }
                });
            }
            
            // Intentar reproducir
            loadAndPlay(audio);
        });
    }
    
    // Función para iniciar la actualización del progreso
    function startProgressUpdate(audio, progressBar, currentTimeSpan) {
        // Detener cualquier intervalo existente
        clearInterval(progressInterval);
        
        // Iniciar un nuevo intervalo
        progressInterval = setInterval(() => {
            // Actualizar barra de progreso
            const progress = (audio.currentTime / audio.duration) * 100;
            progressBar.style.width = progress + '%';
            
            // Actualizar tiempo actual
            const minutes = Math.floor(audio.currentTime / 60);
            const seconds = Math.floor(audio.currentTime % 60);
            currentTimeSpan.textContent = `${minutes}:${seconds < 10 ? '0' + seconds : seconds}`;
            
            // Si la canción terminó (por si acaso no se activa el evento 'ended')
            if (audio.ended) {
                resetPlayState();
            }
        }, 100);
    }
    
    // Función para reiniciar el estado de reproducción
    function resetPlayState() {
        clearInterval(progressInterval);
        
        if (currentButton) {
            currentButton.innerHTML = '<i class="fas fa-play"></i>';
        }
        
        if (currentCard) {
            currentCard.classList.remove('playing');
            const progressBar = currentCard.querySelector('.track-progress-bar');
            const currentTimeSpan = currentCard.querySelector('.track-current');
            if (progressBar) progressBar.style.width = '0%';
            if (currentTimeSpan) currentTimeSpan.textContent = '0:00';
        }
        
        if (isFeaturedPlaying) {
            const featuredPlayer = document.querySelector('.featured-player');
            featuredPlayer.classList.remove('featured-playing');
            const progressBar = featuredPlayer.querySelector('.featured-progress-bar');
            const currentTimeSpan = featuredPlayer.querySelector('.featured-current-time');
            if (progressBar) progressBar.style.width = '0%';
            if (currentTimeSpan) currentTimeSpan.textContent = '0:00';
        }
        
        currentAudio = null;
        currentButton = null;
        currentCard = null;
        isFeaturedPlaying = false;
    }
    
    // Manejar clic en las barras de progreso para saltar
    const progressContainers = document.querySelectorAll('.track-progress, .featured-progress');
    
    progressContainers.forEach(progressContainer => {
        progressContainer.addEventListener('click', function(e) {
            if (!currentAudio) return;
            
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const width = rect.width;
            const percentage = x / width;
            
            // Actualizar tiempo
            currentAudio.currentTime = currentAudio.duration * percentage;
        });
    });
}

// Configurar animación de elementos
function setupFadeInElements() {
    const fadeElements = document.querySelectorAll('.fade-in');
    
    const options = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };
    
    const observer = new IntersectionObserver(function(entries, observer) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, options);
    
    fadeElements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        element.style.transitionDelay = (index * 0.1) + 's';
        observer.observe(element);
    });
}