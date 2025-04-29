<?php
require_once 'config.php';

// Verificar si el usuario ha iniciado sesión
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Verificar si el usuario es mvalladares
if($_SESSION["username"] !== "mvalladares"){
    header("location: area-exclusiva.php");
    exit;
}

// Procesamiento de formularios para guardar notas y fotos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Si se está agregando una nota
    if (isset($_POST['add_note'])) {
        $note_title = $conn->real_escape_string($_POST['note_title']);
        $note_content = $conn->real_escape_string($_POST['note_content']);
        
        $sql = "INSERT INTO maria_notes (title, content) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $note_title, $note_content);
        $stmt->execute();
        $stmt->close();
        
        // Redirigir para evitar reenvío del formulario
        header("location: mvalladares.php?section=notes&status=success");
        exit;
    }
    
    // Si se está subiendo una foto
    if (isset($_POST['add_photo']) && isset($_FILES['photo'])) {
        $photo_caption = $conn->real_escape_string($_POST['photo_caption']);
        $upload_dir = "../assets/maria_photos/";
        
        // Crear el directorio si no existe
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = basename($_FILES["photo"]["name"]);
        $target_file = $upload_dir . time() . "_" . $file_name; // Añadir timestamp para evitar duplicados
        $upload_ok = true;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Verificar si es una imagen real
        $check = getimagesize($_FILES["photo"]["tmp_name"]);
        if($check === false) {
            $upload_ok = false;
        }
        
        // Verificar el tamaño del archivo (max 5MB)
        if ($_FILES["photo"]["size"] > 5000000) {
            $upload_ok = false;
        }
        
        // Permitir solo ciertos formatos de archivo
        if($image_file_type != "jpg" && $image_file_type != "png" && $image_file_type != "jpeg" && $image_file_type != "gif" ) {
            $upload_ok = false;
        }
        
        if ($upload_ok) {
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                $file_path = str_replace("../", "", $target_file); // Guardar ruta relativa
                
                $sql = "INSERT INTO maria_photos (photo_path, caption) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $file_path, $photo_caption);
                $stmt->execute();
                $stmt->close();
                
                header("location: mvalladares.php?section=photos&status=success");
                exit;
            }
        }
    }
}

// Crear las tablas si no existen
$sql_create_notes = "CREATE TABLE IF NOT EXISTS maria_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$sql_create_photos = "CREATE TABLE IF NOT EXISTS maria_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    photo_path VARCHAR(255) NOT NULL,
    caption VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$conn->query($sql_create_notes);
$conn->query($sql_create_photos);

// Obtener la sección actual
$section = isset($_GET['section']) ? $_GET['section'] : 'home';

// Obtener notas guardadas
$notes = array();
$sql = "SELECT * FROM maria_notes ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $notes[] = $row;
    }
}

// Obtener fotos guardadas
$photos = array();
$sql = "SELECT * FROM maria_photos ORDER BY uploaded_at DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $photos[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área Personal | Maria Valladares</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/index.css">
    <style>
        .mvalladares-container {
            max-width: 1000px;
            margin: 100px auto;
            padding: 20px;
            background-color: rgba(0,0,0,0.8);
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(255, 105, 180, 0.4);
        }
        .welcome-message {
            color: #ff69b4;
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
            background-color: rgba(40, 0, 20, 0.5);
            border-radius: 8px;
            border-left: 4px solid #ff69b4;
        }
        .exclusive-title {
            color: #ff69b4;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        .special-message {
            font-size: 1.2rem;
            line-height: 1.8;
            padding: 20px;
            background-color: rgba(255, 105, 180, 0.1);
            border-radius: 8px;
            margin: 20px 0;
        }
        .photo-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .photo-item {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .photo-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(255, 105, 180, 0.3);
        }
        .photo-item img {
            width: 100%;
            display: block;
            transition: transform 0.5s;
        }
        .photo-item:hover img {
            transform: scale(1.05);
        }
        .photo-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px;
            transform: translateY(100%);
            transition: transform 0.3s;
        }
        .photo-item:hover .photo-caption {
            transform: translateY(0);
        }
        .heart-icon {
            color: #ff69b4;
            margin-right: 8px;
        }
        .love-letter {
            font-style: italic;
            line-height: 1.8;
            padding: 25px;
            background-color: rgba(255, 105, 180, 0.1);
            border-radius: 8px;
            margin: 20px 0;
            position: relative;
        }
        .love-letter:before {
            content: """;
            position: absolute;
            top: -10px;
            left: 10px;
            font-size: 60px;
            color: rgba(255, 105, 180, 0.2);
            font-family: Georgia, serif;
        }
        .love-letter:after {
            content: """;
            position: absolute;
            bottom: -40px;
            right: 10px;
            font-size: 60px;
            color: rgba(255, 105, 180, 0.2);
            font-family: Georgia, serif;
        }
        .love-note {
            font-style: italic;
            text-align: center;
            margin: 30px 0;
            font-size: 1.2rem;
            color: #ddd;
        }
        .logout-btn {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 24px;
            background-color: #ff3d85;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .logout-btn:hover {
            background-color: #ff69b4;
        }
        
        /* Nuevos estilos para las funcionalidades interactivas */
        .tab-menu {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 30px;
            border-radius: 8px;
            overflow: hidden;
        }
        .tab-button {
            flex: 1;
            padding: 12px 15px;
            background: #333;
            color: #fff;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.9rem;
        }
        .tab-button:hover {
            background-color: rgba(255, 105, 180, 0.5);
        }
        .tab-button.active {
            background-color: #ff69b4;
            color: white;
            font-weight: bold;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .form-container {
            background: rgba(40, 0, 20, 0.3);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .form-title {
            color: #ff69b4;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #e0e0e0;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            background: rgba(30, 30, 30, 0.8);
            border: 1px solid #555;
            border-radius: 4px;
            color: #fff;
            font-family: 'Montserrat', sans-serif;
        }
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        .submit-btn {
            background-color: #ff69b4;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .submit-btn:hover {
            background-color: #ff3d85;
        }
        .notes-list {
            margin-top: 30px;
        }
        .note-card {
            background: rgba(30, 30, 30, 0.6);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 3px solid #ff69b4;
        }
        .note-title {
            color: #ff69b4;
            margin-bottom: 10px;
            font-size: 1.2rem;
        }
        .note-content {
            color: #e0e0e0;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        .note-date {
            color: #aaa;
            font-size: 0.8rem;
            margin-top: 10px;
            text-align: right;
        }
        .status-message {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .status-success {
            background-color: rgba(0, 255, 0, 0.1);
            color: #00e676;
            border: 1px solid #00e676;
        }
        .maria-photos-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .upload-preview {
            max-width: 100%;
            margin-top: 10px;
            display: none;
            border-radius: 8px;
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

    <div class="mvalladares-container">
        <h2 class="welcome-message">Bienvenida Maria 💖</h2>
        
        <div class="tab-menu">
            <button class="tab-button <?php echo $section == 'home' ? 'active' : ''; ?>" data-tab="home">
                <i class="fas fa-heart"></i> Mensaje de Byron
            </button>
            <button class="tab-button <?php echo $section == 'notes' ? 'active' : ''; ?>" data-tab="notes">
                <i class="fas fa-pen"></i> Tus Notas
            </button>
            <button class="tab-button <?php echo $section == 'photos' ? 'active' : ''; ?>" data-tab="photos">
                <i class="fas fa-camera"></i> Tus Fotos
            </button>
            <button class="tab-button <?php echo $section == 'gallery' ? 'active' : ''; ?>" data-tab="gallery">
                <i class="fas fa-images"></i> Nuestros Momentos
            </button>
        </div>
        
        <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="status-message status-success">
                <?php if($_GET['section'] == 'notes'): ?>
                    <i class="fas fa-check-circle"></i> Tu nota ha sido guardada correctamente.
                <?php elseif($_GET['section'] == 'photos'): ?>
                    <i class="fas fa-check-circle"></i> Tu foto ha sido subida correctamente.
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="protected-content">
            <!-- Sección de inicio - Mensaje de Byron -->
            <div id="home" class="tab-content <?php echo $section == 'home' ? 'active' : ''; ?>">
                <div class="special-message">
                    <p><i class="fas fa-heart heart-icon"></i>Hola Maria, eres la persona más especial en mi vida. Gracias por existir y por compartir tantos momentos maravillosos conmigo. Te amo infinitamente y cada día que pasa te quiero más.</p>
                </div>
                
                <div class="exclusive-section">
                    <h3 class="exclusive-title"><i class="fas fa-heart"></i> De Mi Corazón Para Ti</h3>
                    
                    <div class="love-letter">
                        <p>Querida Maria,</p>
                        <p>Quiero que sepas que eres el primer amor verdadero de mi vida, la primera mujer que he amado con todo mi ser. Contigo he descubierto lo que significa amar realmente a alguien, y cada día me sorprende lo profundo que pueden ser mis sentimientos por ti.</p>
                        <p>Quiero todo contigo: los buenos momentos, los desafíos, las risas, incluso las lágrimas. Quiero construir una vida juntos, soñar juntos, crecer juntos. A pesar de la distancia que a veces nos separa físicamente, mi corazón siempre está unido al tuyo. No hay día en que no piense en ti, en tu sonrisa, en la forma en que iluminas mi mundo.</p>
                        <p>Has cambiado mi vida de maneras que nunca imaginé posibles. Me has enseñado a ser mejor persona, a amar sin condiciones, a valorar cada pequeño momento. Cuando estoy contigo, siento que estoy exactamente donde debo estar.</p>
                        <p>Te amo infinitamente, hoy y siempre.</p>
                        <p>Byron</p>
                    </div>
                    
                    <div class="love-note">
                        "Tú eres la razón por la que creo en el amor verdadero"
                    </div>
                </div>
                
                <div class="exclusive-section">
                    <h3 class="exclusive-title"><i class="fas fa-gift"></i> Tu Espacio Personal</h3>
                    <p>He creado este espacio especialmente para ti, donde puedes:</p>
                    <ul>
                        <li>Guardar notas y pensamientos que quieras conservar para siempre</li>
                        <li>Subir fotos especiales con sus descripciones</li>
                        <li>Ver nuestra galería de recuerdos juntos</li>
                    </ul>
                    <p>Todo lo que guardes aquí se mantendrá para siempre y solo tú puedes verlo cuando inicies sesión.</p>
                </div>
            </div>
            
            <!-- Sección de Notas de Maria -->
            <div id="notes" class="tab-content <?php echo $section == 'notes' ? 'active' : ''; ?>">
                <div class="exclusive-section">
                    <h3 class="exclusive-title"><i class="fas fa-pen"></i> Tus Notas Personales</h3>
                    <p>Este es tu espacio para guardar pensamientos, ideas o cualquier cosa que quieras recordar.</p>
                    
                    <div class="form-container">
                        <h4 class="form-title">Crear Nueva Nota</h4>
                        <form action="mvalladares.php" method="post">
                            <div class="form-group">
                                <label for="note_title">Título</label>
                                <input type="text" id="note_title" name="note_title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="note_content">Contenido</label>
                                <textarea id="note_content" name="note_content" class="form-control" required></textarea>
                            </div>
                            <button type="submit" name="add_note" class="submit-btn">Guardar Nota</button>
                        </form>
                    </div>
                    
                    <div class="notes-list">
                        <h4 class="exclusive-title">Tus Notas Guardadas</h4>
                        
                        <?php if(empty($notes)): ?>
                            <p>Aún no has guardado ninguna nota. ¿Por qué no creas la primera?</p>
                        <?php else: ?>
                            <?php foreach($notes as $note): ?>
                                <div class="note-card">
                                    <h5 class="note-title"><?php echo htmlspecialchars($note['title']); ?></h5>
                                    <div class="note-content"><?php echo htmlspecialchars($note['content']); ?></div>
                                    <div class="note-date">Guardada el: <?php echo date('d/m/Y H:i', strtotime($note['created_at'])); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sección de Fotos de Maria -->
            <div id="photos" class="tab-content <?php echo $section == 'photos' ? 'active' : ''; ?>">
                <div class="exclusive-section">
                    <h3 class="exclusive-title"><i class="fas fa-camera"></i> Tus Fotos Especiales</h3>
                    <p>Sube fotos que quieras guardar y recordar siempre.</p>
                    
                    <div class="form-container">
                        <h4 class="form-title">Subir Nueva Foto</h4>
                        <form action="mvalladares.php" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="photo">Seleccionar Imagen</label>
                                <input type="file" id="photo" name="photo" class="form-control" accept="image/*" required onchange="previewImage()">
                                <img id="photo-preview" src="#" alt="Vista previa" class="upload-preview">
                            </div>
                            <div class="form-group">
                                <label for="photo_caption">Descripción</label>
                                <input type="text" id="photo_caption" name="photo_caption" class="form-control" required>
                            </div>
                            <button type="submit" name="add_photo" class="submit-btn">Subir Foto</button>
                        </form>
                    </div>
                    
                    <div class="maria-photos-gallery">
                        <?php if(empty($photos)): ?>
                            <p>Aún no has subido ninguna foto. ¿Por qué no subes la primera?</p>
                        <?php else: ?>
                            <?php foreach($photos as $photo): ?>
                                <div class="photo-item">
                                    <img src="../<?php echo htmlspecialchars($photo['photo_path']); ?>" alt="<?php echo htmlspecialchars($photo['caption']); ?>">
                                    <div class="photo-caption"><?php echo htmlspecialchars($photo['caption']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sección de Galería de Fotos Juntos -->
            <div id="gallery" class="tab-content <?php echo $section == 'gallery' ? 'active' : ''; ?>">
                <div class="exclusive-section">
                    <h3 class="exclusive-title"><i class="fas fa-images"></i> Nuestros Momentos</h3>
                    <p>Una pequeña selección de nuestros recuerdos favoritos juntos:</p>
                    
                    <div class="photo-gallery">
                        <div class="photo-item">
                            <img src="https://via.placeholder.com/500x350/ff69b4/ffffff?text=Foto+Juntos+1" alt="Nosotros">
                            <div class="photo-caption">Nuestro primer viaje juntos</div>
                        </div>
                        
                        <div class="photo-item">
                            <img src="https://via.placeholder.com/500x350/ff69b4/ffffff?text=Foto+Juntos+2" alt="Nosotros">
                            <div class="photo-caption">Momentos especiales</div>
                        </div>
                        
                        <div class="photo-item">
                            <img src="https://via.placeholder.com/500x350/ff69b4/ffffff?text=Foto+Juntos+3" alt="Nosotros">
                            <div class="photo-caption">Nuestra cena favorita</div>
                        </div>
                        
                        <div class="photo-item">
                            <img src="https://via.placeholder.com/500x350/ff69b4/ffffff?text=Foto+Juntos+4" alt="Nosotros">
                            <div class="photo-caption">Momentos de risa</div>
                        </div>
                    </div>
                </div>
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

    <script>
        // Cambio de pestañas
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Actualizar botones activos
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Actualizar contenido activo
                    tabContents.forEach(content => content.classList.remove('active'));
                    document.getElementById(tabId).classList.add('active');
                    
                    // Actualizar URL (opcional)
                    window.history.replaceState(null, null, `?section=${tabId}`);
                });
            });
        });
        
        // Vista previa de imagen
        function previewImage() {
            const preview = document.getElementById('photo-preview');
            const file = document.getElementById('photo').files[0];
            const reader = new FileReader();
            
            reader.onloadend = function () {
                preview.src = reader.result;
                preview.style.display = 'block';
            }
            
            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
        }
    </script>
    <script src="../Js/index.js"></script>
</body>
</html>