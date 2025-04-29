// Esperar a que el DOM esté completamente cargado
document.addEventListener("DOMContentLoaded", function() {
    // Inicializar la lluvia Matrix
    initMatrixRain();
    
    // Configurar el video de fondo
    setupBackgroundVideo();
    
    // Configurar la navegación móvil
    setupMobileNav();
    
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
        element.style.transitionDelay = (index * 0.2) + 's';
        observer.observe(element);
    });
}