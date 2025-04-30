<?php
require_once 'config.php';

// Verificar si el usuario ha iniciado sesión
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Variables para la carga de archivos
$upload_message = '';
$file_uploaded = false;

// Crear directorio para archivos de usuario si no existe
$uploads_dir = "../assets/archivos/usuarios/" . $_SESSION["username"];
if (!file_exists($uploads_dir)) {
    mkdir($uploads_dir, 0777, true);
}

// Procesar la carga de archivos
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] == 0) {
    $target_dir = $uploads_dir . "/";
    $target_file = $target_dir . basename($_FILES["file_upload"]["name"]);
    
    // Verificar si el archivo ya existe
    if (file_exists($target_file)) {
        $upload_message = "El archivo ya existe.";
    } else {
        // Intentar subir el archivo
        if (move_uploaded_file($_FILES["file_upload"]["tmp_name"], $target_file)) {
            $upload_message = "El archivo " . htmlspecialchars(basename($_FILES["file_upload"]["name"])) . " ha sido subido correctamente.";
            $file_uploaded = true;
        } else {
            $upload_message = "Error al subir el archivo.";
        }
    }
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
        
        /* Estilos para la sección de carga de archivos */
        .upload-section {
            margin-top: 30px;
            padding: 20px;
            background-color: rgba(0, 40, 20, 0.5);
            border-radius: 8px;
            border-left: 4px solid #00e676;
        }
        
        .file-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #e0e0e0;
            font-weight: 500;
        }
        
        .custom-file-input {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .file-input-button {
            display: inline-block;
            padding: 12px 20px;
            background-color: rgba(79, 70, 229, 0.1);
            color: #a5b4fc;
            border: 2px dashed #4f46e5;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .file-input-button:hover {
            background-color: rgba(79, 70, 229, 0.2);
            transform: translateY(-2px);
        }
        
        .file-input-button i {
            margin-right: 8px;
            font-size: 1.2em;
        }
        
        .file-form input[type="file"] {
            opacity: 0;
            position: absolute;
            width: 0.1px;
            height: 0.1px;
            z-index: -1;
        }
        
        .file-name-display {
            margin-top: 10px;
            padding: 8px 12px;
            background-color: rgba(79, 70, 229, 0.1);
            color: #e0e0e0;
            border-radius: 6px;
            font-size: 0.9em;
            display: none;
            word-break: break-all;
            animation: fadeIn 0.3s ease;
        }
        
        .file-name-display i {
            color: #a5b4fc;
            margin-right: 5px;
        }
        
        .progress-container {
            width: 100%;
            height: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            margin-top: 15px;
            overflow: hidden;
            display: none;
        }
        
        .progress-bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #4f46e5, #00c853);
            border-radius: 5px;
            transition: width 0.2s ease;
            box-shadow: 0 0 10px rgba(0, 230, 118, 0.3);
        }
        
        .progress-text {
            color: #e0e0e0;
            font-size: 0.8em;
            text-align: center;
            margin-top: 5px;
            display: none;
        }
        
        button.upload-btn {
            padding: 14px;
            background-color: #00c853;
            color: #000;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        button.upload-btn:hover {
            background-color: #00e676;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 230, 118, 0.3);
        }
        
        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .message {
            margin: 15px 0;
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 500;
            display: flex;
            align-items: center;
            animation: fadeIn 0.5s ease;
        }
        
        .success {
            background-color: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .error {
            background-color: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css">
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
            
            <!-- Sección de carga de archivos -->
            <div class="exclusive-section upload-section">
                <h3 class="exclusive-title"><i class="fas fa-upload"></i> Carga de Archivos</h3>
                <p>Sube tus archivos de forma segura a tu área personal.</p>
                
                <?php if (!empty($upload_message)): ?>
                    <div class="message <?php echo $file_uploaded ? 'success' : 'error'; ?>">
                        <?php echo $upload_message; ?>
                    </div>
                <?php endif; ?>
                
                <form class="file-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="file_upload">Selecciona un archivo para subir:</label>
                        <div class="custom-file-input">
                            <label class="file-input-button" for="file_upload">
                                <i class="fas fa-cloud-upload-alt"></i> Seleccionar Archivo
                            </label>
                            <input type="file" name="file_upload" id="file_upload">
                            <div class="file-name-display" id="file-name-display">
                                <i class="fas fa-file"></i> <span id="file-name"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="progress-container" id="progress-container">
                        <div class="progress-bar" id="progress-bar"></div>
                    </div>
                    <div class="progress-text" id="progress-text"></div>
                    
                    <button type="submit" class="upload-btn" id="upload-btn" disabled>
                        <i class="fas fa-upload"></i> Subir Archivo
                    </button>
                </form>
            </div>
            
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>
    <script>
        // Manejar la visualización del nombre del archivo
        const fileInput = document.getElementById('file_upload');
        const fileNameDisplay = document.getElementById('file-name-display');
        const fileName = document.getElementById('file-name');
        const uploadBtn = document.getElementById('upload-btn');
        const progressContainer = document.getElementById('progress-container');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileName.textContent = this.files[0].name;
                fileNameDisplay.style.display = 'block';
                uploadBtn.disabled = false;
            } else {
                fileNameDisplay.style.display = 'none';
                uploadBtn.disabled = true;
            }
        });
        
        // Manejar el formulario de carga con barra de progreso
        document.querySelector('.file-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!fileInput.files.length) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor selecciona un archivo para subir',
                    confirmButtonColor: '#00c853'
                });
                return;
            }
            
            const formData = new FormData(this);
            const xhr = new XMLHttpRequest();
            
            // Mostrar barra de progreso
            progressContainer.style.display = 'block';
            progressText.style.display = 'block';
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo...';
            
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percentComplete + '%';
                    progressText.textContent = percentComplete + '%';
                }
            });
            
            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    // La carga se completó, recargar la página para mostrar el resultado
                    window.location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error durante la carga del archivo',
                        confirmButtonColor: '#00c853'
                    });
                    resetUploadForm();
                }
            });
            
            xhr.addEventListener('error', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error durante la carga del archivo',
                    confirmButtonColor: '#00c853'
                });
                resetUploadForm();
            });
            
            xhr.open('POST', '<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>', true);
            xhr.send(formData);
        });
        
        function resetUploadForm() {
            progressContainer.style.display = 'none';
            progressText.style.display = 'none';
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Subir Archivo';
        }
    </script>
</body>
</html>