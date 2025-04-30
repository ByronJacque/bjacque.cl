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
                        <form action="mvalladares.php" method="post" enctype="multipart/form-data" id="photoUploadForm">
                            <div class="form-group">
                                <label for="photo">Seleccionar Imagen</label>
                                <input type="file" id="photo" name="photo" class="form-control" accept="image/*" required onchange="previewImage()">
                                <img id="photo-preview" src="#" alt="Vista previa" class="upload-preview">
                                <div class="file-name-display" id="file-name-display">
                                    <i class="fas fa-image"></i> <span id="file-name"></span>
                                </div>
                                <div class="progress-container" id="progress-container">
                                    <div class="progress-bar" id="progress-bar"></div>
                                </div>
                                <div class="progress-text" id="progress-text"></div>
                            </div>
                            <div class="form-group">
                                <label for="photo_caption">Descripción</label>
                                <input type="text" id="photo_caption" name="photo_caption" class="form-control" required>
                            </div>
                            <button type="submit" name="add_photo" id="upload-btn" class="submit-btn">
                                <i class="fas fa-upload"></i> Subir Foto
                            </button>
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
                            <img src="../assets/mvalladares/maria3.jpg" alt="Nosotros">
                            <div class="photo-caption">Nuestro primer 14f juntos</div>
                        </div>
                        
                        <div class="photo-item">
                            <img src="../assets/mvalladares/maria4.jpg" alt="Nosotros">
                            <div class="photo-caption">Nuestro primer 14f juntos</div>
                        </div>
                        
                        <div class="photo-item">
                            <img src="../assets/mvalladares/maria1.jpeg" alt="Nosotros">
                            <div class="photo-caption">Nuestra 2da noche durmiendo juntitos</div>
                        </div>
                        
                        <div class="photo-item">
                            <img src="../assets/mvalladares/maria2.jpeg" alt="Nosotros">
                            <div class="photo-caption">Nuestra 2da noche durmiendo juntitos</div>
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

    <script src="../Js/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>
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
            
            // Configuración de la barra de progreso para la carga de fotos
            const photoUploadForm = document.getElementById('photoUploadForm');
            const fileInput = document.getElementById('photo');
            const fileNameDisplay = document.getElementById('file-name-display');
            const fileName = document.getElementById('file-name');
            const progressContainer = document.getElementById('progress-container');
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            const uploadBtn = document.getElementById('upload-btn');
            
            // Mostrar nombre del archivo seleccionado
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    fileName.textContent = this.files[0].name;
                    fileNameDisplay.style.display = 'block';
                    previewImage();
                } else {
                    fileNameDisplay.style.display = 'none';
                }
            });
            
            // Manejar el envío del formulario con barra de progreso
            if (photoUploadForm) {
                photoUploadForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    if (!fileInput.files.length) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Por favor selecciona una foto para subir',
                            confirmButtonColor: '#ff69b4'
                        });
                        return;
                    }
                    
                    const caption = document.getElementById('photo_caption').value;
                    if (!caption) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Por favor añade una descripción para la foto',
                            confirmButtonColor: '#ff69b4'
                        });
                        return;
                    }
                    
                    // Mostrar barra de progreso
                    progressContainer.style.display = 'block';
                    progressText.style.display = 'block';
                    uploadBtn.disabled = true;
                    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo...';
                    
                    const xhr = new XMLHttpRequest();
                    const formData = new FormData(photoUploadForm);
                    
                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            const percentComplete = Math.round((e.loaded / e.total) * 100);
                            progressBar.style.width = percentComplete + '%';
                            progressText.textContent = percentComplete + '%';
                        }
                    });
                    
                    xhr.addEventListener('load', function() {
                        if (xhr.status === 200) {
                            // Redirigir a la sección de fotos con mensaje de éxito
                            window.location.href = 'mvalladares.php?section=photos&status=success';
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurrió un error durante la carga de la foto',
                                confirmButtonColor: '#ff69b4'
                            });
                            resetUploadForm();
                        }
                    });
                    
                    xhr.addEventListener('error', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error durante la carga de la foto',
                            confirmButtonColor: '#ff69b4'
                        });
                        resetUploadForm();
                    });
                    
                    xhr.open('POST', 'mvalladares.php?section=photos', true);
                    xhr.send(formData);
                });
            }
        });
        
        // Función para resetear el formulario de carga
        function resetUploadForm() {
            const progressContainer = document.getElementById('progress-container');
            const progressText = document.getElementById('progress-text');
            const uploadBtn = document.getElementById('upload-btn');
            
            if (progressContainer) progressContainer.style.display = 'none';
            if (progressText) progressText.style.display = 'none';
            if (uploadBtn) {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Subir Foto';
            }
        }
        
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
</body>
</html>