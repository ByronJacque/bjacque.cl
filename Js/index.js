// Esperar a que el DOM esté completamente cargado
document.addEventListener("DOMContentLoaded", function() {
    // Inicializar la lluvia Matrix
    initMatrixRain();
    
    // Configurar el video de fondo
    setupBackgroundVideo();
    
    // Configurar la navegación móvil
    setupMobileNav();
    
    // Configurar el reproductor de música
    setupMusicPlayer();
    
    // Configurar las animaciones de scroll
    setupScrollAnimations();
    
    // Configurar efecto tipográfico en el nombre
    setupNameEffect();
    
    // Configurar el formulario de contacto
    setupContactForm();
    
    // Configurar el modal para el CV
    setupCvModal();
    
    // Configurar el terminal
    setupTerminal();
    
    // Configurar partículas de cursor
    setupCursorParticles();
    
    // Efecto hover 3D para las tarjetas de habilidades
    setupSkillCards3D();
    
    // Efectos de glitch aleatorios
    setupGlitchEffects();
    
    // Manejo del formulario de contacto
    const contactForm = document.getElementById('contactForm');
    const formMessage = document.getElementById('formMessage');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Mostrar efecto de carga
            formMessage.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando mensaje...';
            formMessage.style.display = 'block';
            formMessage.className = 'form-message';
            
            // Crear objeto FormData para enviar los datos del formulario
            const formData = new FormData(contactForm);
            
            // Enviar datos usando fetch API
            fetch('php/enviar-contacto.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Si el mensaje se envió correctamente
                    formMessage.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                    formMessage.className = 'form-message success';
                    contactForm.reset(); // Limpiar el formulario
                } else {
                    // Si hubo un error
                    formMessage.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.message;
                    formMessage.className = 'form-message error';
                }
            })
            .catch(error => {
                // Error en la conexión o procesando la respuesta
                formMessage.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Ha ocurrido un error al enviar el mensaje. Por favor, inténtalo de nuevo más tarde.';
                formMessage.className = 'form-message error';
                console.error('Error:', error);
            });
        });
    }
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

    // Caracteres Matrix (puedes usar cualquier conjunto de caracteres)
    const matrix = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789@#$%^&*()*&^%$#@!~';
    
    function draw() {
        // Fondo ligeramente transparente para crear efecto de desvanecimiento
        ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        ctx.fillStyle = '#00ff41';
        ctx.font = fontSize + 'px monospace';
        
        for (let i = 0; i < drops.length; i++) {
            // Seleccionar un caracter aleatorio
            const text = matrix[Math.floor(Math.random() * matrix.length)];
            
            // x = i * fontSize, y = valor de drops[i] * fontSize
            ctx.fillText(text, i * fontSize, drops[i] * fontSize);
            
            // Resetear a una posición aleatoria si ha llegado al final o aleatoriamente
            if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
                drops[i] = 0;
            }
            
            // Incrementar el valor de y
            drops[i]++;
        }
    }

    // Manejar el redimensionamiento de la ventana
    window.addEventListener('resize', function() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        
        // Recalcular columnas
        const newColumns = Math.floor(canvas.width / fontSize);
        
        // Ajustar el array de drops si es necesario
        if (newColumns > columns) {
            for (let i = columns; i < newColumns; i++) {
                drops[i] = Math.floor(Math.random() * canvas.height / fontSize) * -1;
            }
        }
    });

    // Animar
    setInterval(draw, 35);
}

// Configurar el video de fondo
function setupBackgroundVideo() {
    const video = document.getElementById('background-video');
    
    if (video) {
        // Comprobar si el navegador puede reproducir videos
        if (video.canPlayType) {
            // Ajustar el tamaño del video al cambiar el tamaño de la ventana
            window.addEventListener('resize', function() {
                adjustVideoSize(video);
            });
            
            // Ajustar inicialmente
            adjustVideoSize(video);
            
            // Manejar errores
            video.addEventListener('error', function() {
                console.error('Error al reproducir el video de fondo');
                // Si hay error, asegurarse de que la lluvia matrix es visible
                document.getElementById('matrix-rain').style.opacity = '1';
            });
        } else {
            // Fallback para navegadores que no soportan video
            console.warn('Este navegador no soporta la reproducción de video HTML5');
            document.getElementById('matrix-rain').style.opacity = '1';
        }
    }
}

// Ajustar tamaño del video
function adjustVideoSize(videoElement) {
    const windowWidth = window.innerWidth;
    const windowHeight = window.innerHeight;
    const videoRatio = 16/9; // Relación de aspecto estándar, ajustar según el video
    
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
    
    // Cerrar menú al hacer clic en un enlace
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

// Configurar el reproductor de música
function setupMusicPlayer() {
    const audio = document.getElementById('audio');
    const playBtn = document.getElementById('playBtn');
    const volumeBtn = document.getElementById('volumeBtn');
    const progressBar = document.querySelector('.progress-bar');
    const progressContainer = document.querySelector('.progress-container');
    const timeEl = document.querySelector('.time');
    
    if (!audio || !playBtn) return;
    
    playBtn.addEventListener('click', function() {
        if (audio.paused) {
            audio.play();
            playBtn.innerHTML = '<i class="fas fa-pause"></i>';
        } else {
            audio.pause();
            playBtn.innerHTML = '<i class="fas fa-play"></i>';
        }
    });
    
    if (volumeBtn) {
        volumeBtn.addEventListener('click', function() {
            if (audio.volume > 0) {
                audio.volume = 0;
                volumeBtn.innerHTML = '<i class="fas fa-volume-mute"></i>';
            } else {
                audio.volume = 1;
                volumeBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
            }
        });
    }
    
    if (progressContainer) {
        progressContainer.addEventListener('click', function(e) {
            const width = this.clientWidth;
            const clickX = e.offsetX;
            const duration = audio.duration;
            
            audio.currentTime = (clickX / width) * duration;
        });
    }
    
    if (audio) {
        audio.addEventListener('timeupdate', function() {
            const currentTime = this.currentTime;
            const duration = this.duration;
            
            if (progressBar && !isNaN(duration)) {
                const progressPercent = (currentTime / duration) * 100;
                progressBar.style.width = progressPercent + '%';
                
                if (timeEl) {
                    const minutes = Math.floor(currentTime / 60);
                    const seconds = Math.floor(currentTime % 60);
                    const durationMinutes = Math.floor(duration / 60);
                    const durationSeconds = Math.floor(duration % 60);
                    
                    timeEl.textContent = `${minutes}:${seconds < 10 ? '0' + seconds : seconds} / ${durationMinutes}:${durationSeconds < 10 ? '0' + durationSeconds : durationSeconds}`;
                }
            }
        });
    }
}

// Configurar animaciones de scroll
function setupScrollAnimations() {
    // Animación de aparición al hacer scroll
    const sections = document.querySelectorAll('section');
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
    
    sections.forEach(section => {
        if (section.id !== 'home') {
            section.style.opacity = '0';
            section.style.transform = 'translateY(20px)';
            observer.observe(section);
        }
    });
    
    // Parallax en scroll
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const parallaxElements = document.querySelector('.profile-image');
        
        if (parallaxElements) {
            parallaxElements.style.transform = `translateY(${scrollTop * 0.05}px)`;
        }
    });
}

// Efecto tipográfico en el nombre
function setupNameEffect() {
    const nameElement = document.querySelector('.name');
    
    if (nameElement) {
        // Efecto al pasar el mouse
        nameElement.addEventListener('mouseover', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.textShadow = '0 5px 15px rgba(0, 255, 65, 0.7)';
        });
        
        nameElement.addEventListener('mouseout', function() {
            this.style.transform = 'translateY(0)';
            this.style.textShadow = '0 0 10px rgba(0, 255, 65, 0.7)';
        });
        
        // Glitch aleatorio
        setInterval(() => {
            if (Math.random() > 0.95) {
                nameElement.classList.add('glitched');
                setTimeout(() => {
                    nameElement.classList.remove('glitched');
                }, 400);
            }
        }, 3000);
    }
}

// Configurar el formulario de contacto
function setupContactForm() {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Aquí iría la lógica para enviar el formulario
            // Simulamos una respuesta exitosa
            const submitBtn = this.querySelector('.submit');
            const originalText = submitBtn.textContent;
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';
            
            setTimeout(() => {
                submitBtn.textContent = '¡Enviado!';
                submitBtn.style.background = 'rgba(0, 200, 83, 0.3)';
                submitBtn.style.borderColor = '#00c853';
                submitBtn.style.color = '#ffffff';
                
                // Resetear el formulario
                this.reset();
                
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    submitBtn.style.background = '';
                    submitBtn.style.borderColor = '';
                    submitBtn.style.color = '';
                }, 2000);
            }, 1500);
        });
    }
}

// Configurar modal para el CV
function setupCvModal() {
    const modal = document.getElementById('cvModal');
    const btn = document.getElementById('verCvBtn');
    const closeBtn = document.querySelector('.close-modal');
    
    if (!modal || !btn || !closeBtn) return;
    
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Prevenir scroll
    });
    
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        document.body.style.overflow = ''; // Restaurar scroll
    });
    
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    });
    
    // Cerrar con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    });
}

// Configurar terminal
function setupTerminal() {
    const terminal = document.querySelector('.terminal');
    const terminalToggle = document.querySelector('.terminal-toggle');
    const terminalClose = document.querySelector('.terminal-close');
    const terminalInput = document.querySelector('.terminal-input');
    const terminalContent = document.querySelector('.terminal-content');
    
    if (!terminal || !terminalToggle || !terminalInput || !terminalContent) return;
    
    terminalToggle.addEventListener('click', function() {
        terminal.classList.toggle('active');
        if (terminal.classList.contains('active')) {
            terminalInput.focus();
        }
    });
    
    if (terminalClose) {
        terminalClose.addEventListener('click', function() {
            terminal.classList.remove('active');
        });
    }
    
    // Comandos del terminal
    const commands = {
        help: "Comandos disponibles: help, about, clear, projects, contact, skills, exit",
        about: "Byron Jacque: Ingeniero Informático y Desarrollador Web con experiencia en múltiples lenguajes y frameworks.",
        clear: function() {
            terminalContent.innerHTML = '';
            return '';
        },
        projects: "Actualmente trabajando en: Proyecto1, Proyecto2, Proyecto3...",
        contact: "Email: byron@example.com | Teléfono: (+56) XXX XXX XXX",
        skills: "Lenguajes: Python, C++, JavaScript, Java, C#, etc.\nFrameworks: React, Vue.js, Angular, etc.",
        exit: function() {
            terminal.classList.remove('active');
            return 'Cerrando terminal...';
        }
    };
    
    terminalInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            
            const command = this.value.trim().toLowerCase();
            let response = '';
            
            if (command) {
                // Agregar comando a la terminal
                terminalContent.innerHTML += `<p>> ${command}</p>`;
                
                // Procesar comando
                if (commands[command]) {
                    if (typeof commands[command] === 'function') {
                        response = commands[command]();
                    } else {
                        response = commands[command];
                    }
                } else {
                    response = `Comando no reconocido: '${command}'. Escribe 'help' para ver los comandos disponibles.`;
                }
                
                // Mostrar respuesta
                if (response) {
                    terminalContent.innerHTML += `<p>${response}</p>`;
                }
                
                // Limpiar input
                this.value = '';
                
                // Scroll al final
                terminalContent.scrollTop = terminalContent.scrollHeight;
            }
        }
    });
}

// Configurar partículas de cursor
function setupCursorParticles() {
    document.addEventListener('mousemove', function(e) {
        // Limitar la creación de partículas (1 de cada 3 movimientos)
        if (Math.random() > 0.7) {
            createParticle(e.clientX, e.clientY);
        }
    });
    
    function createParticle(x, y) {
        const particle = document.createElement('div');
        particle.className = 'cursor-particle';
        particle.style.left = x + 'px';
        particle.style.top = y + 'px';
        
        document.body.appendChild(particle);
        
        // Eliminar la partícula después de la animación
        setTimeout(() => {
            particle.remove();
        }, 1000);
    }
}

// Configurar efecto 3D para tarjetas de habilidades
function setupSkillCards3D() {
    const cards = document.querySelectorAll('.skill-item');
    
    cards.forEach(card => {
        card.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left; // x position within the element
            const y = e.clientY - rect.top; // y position within the element
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const deltaX = (x - centerX) / 10;
            const deltaY = (centerY - y) / 10;
            
            this.style.transform = `translateY(-5px) rotateX(${deltaY}deg) rotateY(${deltaX}deg)`;
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) rotateX(0) rotateY(0)';
        });
    });
}

// Configurar efectos de glitch
function setupGlitchEffects() {
    // Títulos de sección con efecto glitch aleatorio
    const sectionTitles = document.querySelectorAll('section h2');
    
    sectionTitles.forEach(title => {
        setInterval(() => {
            if (Math.random() > 0.95) {
                title.style.transform = 'skew(10deg)';
                title.style.color = 'var(--accent-color)';
                
                setTimeout(() => {
                    title.style.transform = '';
                    title.style.color = '';
                }, 100);
            }
        }, 5000);
    });
}