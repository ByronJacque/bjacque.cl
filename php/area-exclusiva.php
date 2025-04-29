<?php
require_once 'config.php';

// Verificar si el usuario ha iniciado sesión
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área Exclusiva | Byron Jacque</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/index.css">
    <style>
        .protected-container {
            max-width: 1000px;
            margin: 100px auto;
            padding: 20px;
            background-color: rgba(0,0,0,0.8);
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.3);
        }
        .welcome-message {
            color: #00e676;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
        }
        .protected-content {
            color: #e0e0e0;
            line-height: 1.6;
        }
        .exclusive-section {
            margin-top: 30px;
            padding: 20px;
            background-color: rgba(0, 40, 20, 0.5);
            border-radius: 8px;
            border-left: 4px solid #00e676;
        }
        .exclusive-title {
            color: #00e676;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        .resource-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .resource-item {
            padding: 15px;
            background-color: rgba(10, 10, 10, 0.7);
            border-radius: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .resource-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 230, 118, 0.2);
        }
        .resource-icon {
            font-size: 2rem;
            color: #00e676;
            margin-bottom: 10px;
            display: block;
            text-align: center;
        }
        .resource-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: #fff;
        }
        .resource-description {
            font-size: 0.9rem;
            color: #aaa;
        }
        .resource-link {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 12px;
            background-color: #00966e;
            color: #fff;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background-color 0.2s;
        }
        .resource-link:hover {
            background-color: #00c853;
        }
        .logout-btn {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 24px;
            background-color: #ff3d00;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .logout-btn:hover {
            background-color: #ff6e40;
        }
        .admin-section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #333;
        }
    </style>
</head>
<body>
    <!-- Digital Grid -->
    <div class="digital-grid"></div>
    
    <!-- Matrix Rain Canvas -->
    <canvas id="matrix-rain"></canvas>
    
    <!-- Video de fondo -->
    <div class="video-background">
        <video autoplay muted loop id="background-video">
            <source src="../assets/videos/fondomatrix.mp4" type="video/mp4">
        </video>
    </div>
    
    <!-- Header Fijo -->
    <header>
        <div class="logo">
            <a href="../index.html" class="logo-text">Byron Jacque</a>
        </div>
        <nav>
            <ul>
                <li><a href="../index.html">Inicio</a></li>
                <li><a href="../Paginas/proyectos.html">Proyectos</a></li>
                <li><a href="../Paginas/blogs.html">Blogs</a></li>
                <li><a href="../Paginas/hobbys.html">Hobbys</a></li>
                <li><a href="../Paginas/datos.html">Mis Datos</a></li>
                <li><a href="../Paginas/certificados.html">Certificados</a></li>
                <li><a href="../Paginas/musica.html">Música</a></li>
            </ul>
        </nav>
    </header>

    <div class="protected-container">
        <h2 class="welcome-message">Bienvenido, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
        
        <div class="protected-content">
            <p>Estás en el área exclusiva para miembros registrados de BjacqueCl. Aquí encontrarás contenido premium y recursos especiales que solo están disponibles para usuarios como tú.</p>
            
            <div class="exclusive-section">
                <h3 class="exclusive-title"><i class="fas fa-code"></i> Recursos para Desarrolladores</h3>
                <p>Accede a tutoriales exclusivos, código fuente de proyectos y herramientas para mejorar tus habilidades de desarrollo.</p>
                
                <div class="resource-list">
                    <div class="resource-item">
                        <i class="fas fa-file-code resource-icon"></i>
                        <h4 class="resource-title">Template Matrix Responsive</h4>
                        <p class="resource-description">Plantilla HTML/CSS con efecto Matrix para tus proyectos web.</p>
                        <a href="../assets/archivos/template-matrix.zip" class="resource-link">Descargar</a>
                    </div>
                    
                    <div class="resource-item">
                        <i class="fas fa-book resource-icon"></i>
                        <h4 class="resource-title">Tutorial: APIs en Python</h4>
                        <p class="resource-description">Aprende a crear y consumir APIs RESTful con Python y FastAPI.</p>
                        <a href="#" class="resource-link">Ver tutorial</a>
                    </div>
                    
                    <div class="resource-item">
                        <i class="fas fa-laptop-code resource-icon"></i>
                        <h4 class="resource-title">Proyecto Furgonite</h4>
                        <p class="resource-description">Repositorio completo del proyecto Furgonite para estudio.</p>
                        <a href="#" class="resource-link">Acceder</a>
                    </div>
                </div>
            </div>
            
            <div class="exclusive-section">
                <h3 class="exclusive-title"><i class="fas fa-graduation-cap"></i> Formación Exclusiva</h3>
                <p>Material educativo de alta calidad para seguir aprendiendo y mejorando tus habilidades.</p>
                
                <div class="resource-list">
                    <div class="resource-item">
                        <i class="fas fa-video resource-icon"></i>
                        <h4 class="resource-title">Curso: Full Stack Developer</h4>
                        <p class="resource-description">Curso completo de desarrollo web full stack con proyectos prácticos.</p>
                        <a href="#" class="resource-link">Ver curso</a>
                    </div>
                    
                    <div class="resource-item">
                        <i class="fas fa-file-pdf resource-icon"></i>
                        <h4 class="resource-title">E-book: Patrones de Diseño</h4>
                        <p class="resource-description">E-book completo sobre patrones de diseño en programación.</p>
                        <a href="#" class="resource-link">Descargar</a>
                    </div>
                    
                    <div class="resource-item">
                        <i class="fas fa-chalkboard-teacher resource-icon"></i>
                        <h4 class="resource-title">Webinar: DevOps & CI/CD</h4>
                        <p class="resource-description">Grabación del webinar sobre implementación de DevOps y CI/CD.</p>
                        <a href="#" class="resource-link">Ver webinar</a>
                    </div>
                </div>
            </div>
            
            <!-- Sección Administrativa (visible solo para administradores) -->
            <?php if(isset($_SESSION["username"]) && $_SESSION["username"] === "admin"): ?>
            <div class="exclusive-section admin-section">
                <h3 class="exclusive-title"><i class="fas fa-user-shield"></i> Panel de Administración</h3>
                <p>Como administrador, tienes acceso a funcionalidades adicionales para gestionar el contenido del sitio.</p>
                
                <div class="resource-list">
                    <div class="resource-item">
                        <i class="fas fa-users-cog resource-icon"></i>
                        <h4 class="resource-title">Gestión de Usuarios</h4>
                        <p class="resource-description">Administra los usuarios registrados en la plataforma.</p>
                        <a href="#" class="resource-link">Administrar</a>
                    </div>
                    
                    <div class="resource-item">
                        <i class="fas fa-edit resource-icon"></i>
                        <h4 class="resource-title">Editor de Contenido</h4>
                        <p class="resource-description">Edita el contenido del sitio web de forma sencilla.</p>
                        <a href="#" class="resource-link">Editar</a>
                    </div>
                    
                    <div class="resource-item">
                        <i class="fas fa-chart-line resource-icon"></i>
                        <h4 class="resource-title">Estadísticas</h4>
                        <p class="resource-description">Visualiza las estadísticas de visitas y comportamiento.</p>
                        <a href="#" class="resource-link">Ver estadísticas</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: 40px;">
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h3>Byron Jacque</h3>
                    <p>Desarrollador Web & Ingeniero Informático</p>
                </div>
                <div class="social-links">
                    <a href="https://github.com/ByronJacque"><i class="fab fa-github"></i></a>
                    <a href="https://www.linkedin.com/in/byron-jacque-rivero-a1b670248/"><i class="fab fa-linkedin"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 Byron Jacque. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="../Js/index.js"></script>
</body>
</html>