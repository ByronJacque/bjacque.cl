<?php
require_once 'config.php';

// Verificar si el usuario ha iniciado sesión
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Verificar si el usuario es smashbyron
if($_SESSION["username"] !== "smashbyron"){
    header("location: area-exclusiva.php");
    exit;
}

// Procesar formularios
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Procesar formulario de notas
    if (isset($_POST['add_note'])) {
        $note_title = $conn->real_escape_string($_POST['note_title']);
        $note_content = $conn->real_escape_string($_POST['note_content']);
        $has_image = 0;
        $image_path = '';
        
        // Comprobar si hay una imagen para subir
        if(isset($_FILES['note_image']) && $_FILES['note_image']['error'] == 0) {
            $upload_dir = "../assets/byron_notes_images/";
            
            // Crear el directorio si no existe
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = basename($_FILES["note_image"]["name"]);
            $target_file = $upload_dir . time() . "_" . $file_name;
            $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Permitir solo ciertos formatos de imagen
            $allowed_formats = ["jpg", "jpeg", "png", "gif"];
            if(in_array($image_file_type, $allowed_formats)) {
                if(move_uploaded_file($_FILES["note_image"]["tmp_name"], $target_file)) {
                    $has_image = 1;
                    $image_path = str_replace("../", "", $target_file);
                }
            }
        }
        
        // Insertar nota en la base de datos
        $sql = "INSERT INTO byron_notes (title, content, has_image, image_path, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssis", $note_title, $note_content, $has_image, $image_path);
        $stmt->execute();
        $stmt->close();
        
        header("location: smashbyron.php?section=notes&status=success&message=Nota+guardada+correctamente");
        exit;
    }
    
    // Procesar formulario de subida de archivos
    if (isset($_POST['add_file'])) {
        $file_description = $conn->real_escape_string($_POST['file_description']);
        
        if(isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] == 0) {
            $upload_dir = "../assets/byron_files/";
            
            // Crear el directorio si no existe
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = basename($_FILES["file_upload"]["name"]);
            $target_file = $upload_dir . time() . "_" . $file_name;
            $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Permitir formatos de archivo seguros
            $allowed_formats = ["pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "txt", "zip", "rar"];
            if(in_array($file_type, $allowed_formats) && $_FILES["file_upload"]["size"] < 50000000) { // Límite de 50MB
                if(move_uploaded_file($_FILES["file_upload"]["tmp_name"], $target_file)) {
                    // Guardar en la base de datos
                    $file_path = str_replace("../", "", $target_file);
                    $file_size = $_FILES["file_upload"]["size"];
                    $file_type = $_FILES["file_upload"]["type"];
                    
                    $sql = "INSERT INTO byron_files (file_name, file_path, file_type, file_size, description, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssss", $file_name, $file_path, $file_type, $file_size, $file_description);
                    $stmt->execute();
                    $stmt->close();
                    
                    header("location: smashbyron.php?section=files&status=success&message=Archivo+subido+correctamente");
                    exit;
                }
            } else {
                header("location: smashbyron.php?section=files&status=error&message=Formato+de+archivo+no+permitido+o+tamaño+excedido");
                exit;
            }
        }
    }
    
    // Eliminar una nota
    if (isset($_POST['delete_note'])) {
        $note_id = intval($_POST['note_id']);
        
        // Obtener información de la nota para eliminar la imagen si existe
        $sql = "SELECT has_image, image_path FROM byron_notes WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $note_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $note = $result->fetch_assoc();
            
            // Si hay una imagen, eliminarla
            if ($note['has_image'] == 1 && !empty($note['image_path'])) {
                $image_full_path = "../" . $note['image_path'];
                if (file_exists($image_full_path)) {
                    unlink($image_full_path);
                }
            }
        }
        $stmt->close();
        
        // Eliminar la nota
        $sql = "DELETE FROM byron_notes WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $note_id);
        $stmt->execute();
        $stmt->close();
        
        header("location: smashbyron.php?section=notes&status=success&message=Nota+eliminada+correctamente");
        exit;
    }
    
    // Eliminar un archivo
    if (isset($_POST['delete_file'])) {
        $file_id = intval($_POST['file_id']);
        
        // Obtener la ruta del archivo para eliminarlo
        $sql = "SELECT file_path FROM byron_files WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $file_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $file = $result->fetch_assoc();
            $file_full_path = "../" . $file['file_path'];
            
            if (file_exists($file_full_path)) {
                unlink($file_full_path);
            }
        }
        $stmt->close();
        
        // Eliminar registro de la base de datos
        $sql = "DELETE FROM byron_files WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $file_id);
        $stmt->execute();
        $stmt->close();
        
        header("location: smashbyron.php?section=files&status=success&message=Archivo+eliminado+correctamente");
        exit;
    }
}

// Crear las tablas necesarias si no existen
$sql_create_notes = "CREATE TABLE IF NOT EXISTS byron_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    has_image TINYINT(1) DEFAULT 0,
    image_path VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$sql_create_files = "CREATE TABLE IF NOT EXISTS byron_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    file_size VARCHAR(50) NOT NULL,
    description TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$conn->query($sql_create_notes);
$conn->query($sql_create_files);

// Obtener la sección actual
$section = isset($_GET['section']) ? $_GET['section'] : 'notes';

// Obtener las notas guardadas
$notes = [];
$sql = "SELECT * FROM byron_notes ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $notes[] = $row;
    }
}

// Obtener los archivos guardados
$files = [];
$sql = "SELECT * FROM byron_files ORDER BY uploaded_at DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $files[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Personal | SmashByron</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/index.css">
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --secondary-color: #8b5cf6;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --dark-bg: #1e1b4b;
            --card-bg: #312e81;
            --light-text: #e0e7ff;
            --muted-text: #a5b4fc;
            --border-color: rgba(165, 180, 252, 0.2);
        }
        
        body {
            background-color: #0f172a;
            color: var(--light-text);
        }
        
        .byron-container {
            max-width: 1200px;
            margin: 100px auto 50px;
            padding: 0 20px;
        }
        
        .byron-header {
            text-align: center;
            margin-bottom: 30px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .byron-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                transparent, 
                transparent, 
                transparent, 
                rgba(255, 255, 255, 0.1)
            );
            transform: rotate(45deg);
            animation: shimmer 3s linear infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateY(50%) rotate(45deg); }
            100% { transform: translateY(-50%) rotate(45deg); }
        }
        
        .byron-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            font-family: 'Montserrat', sans-serif;
        }
        
        .byron-header p {
            color: var(--light-text);
            font-size: 1.1rem;
            position: relative;
            z-index: 2;
            opacity: 0.9;
        }
        
        .tab-menu {
            display: flex;
            background: var(--card-bg);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .tab-button {
            flex: 1;
            padding: 15px 20px;
            border: none;
            background: transparent;
            color: var(--muted-text);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            text-align: center;
        }
        
        .tab-button i {
            margin-right: 8px;
            font-size: 1rem;
        }
        
        .tab-button:hover {
            background: rgba(79, 70, 229, 0.1);
            color: var(--light-text);
        }
        
        .tab-button.active {
            background: var(--primary-color);
            color: white;
            position: relative;
        }
        
        .tab-button.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .dashboard-card {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
        }
        
        .dashboard-card h2 {
            display: flex;
            align-items: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: var(--light-text);
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
            font-family: 'Montserrat', sans-serif;
        }
        
        .dashboard-card h2 i {
            margin-right: 10px;
            color: var(--secondary-color);
        }
        
        .form-container {
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--light-text);
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background-color: rgba(30, 27, 75, 0.5);
            color: var(--light-text);
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.3);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1rem;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .alert i {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        .alert-success {
            background-color: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
        }
        
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }
        
        .notes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .note-card {
            background: rgba(49, 46, 129, 0.7);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }
        
        .note-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.2);
        }
        
        .note-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .note-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--light-text);
            flex: 1;
            margin-right: 20px;
        }
        
        .note-date {
            font-size: 0.8rem;
            color: var(--muted-text);
            margin-bottom: 10px;
        }
        
        .note-content {
            font-size: 0.95rem;
            color: var(--muted-text);
            line-height: 1.6;
            margin-bottom: 20px;
            flex: 1;
            white-space: pre-wrap;
        }
        
        .note-image {
            width: 100%;
            margin-top: 15px;
            margin-bottom: 15px;
            border-radius: 6px;
            max-height: 200px;
            object-fit: cover;
        }
        
        .note-actions {
            margin-top: auto;
            display: flex;
            justify-content: flex-end;
        }
        
        .image-preview {
            max-width: 100%;
            margin-top: 10px;
            border-radius: 6px;
            display: none;
        }
        
        .files-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .files-table th {
            background-color: var(--dark-bg);
            color: var(--light-text);
            text-align: left;
            padding: 12px 15px;
            font-weight: 600;
        }
        
        .files-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
            color: var(--muted-text);
        }
        
        .files-table tr:hover td {
            background-color: rgba(79, 70, 229, 0.05);
        }
        
        .file-icon {
            font-size: 1.5rem;
            margin-right: 10px;
            vertical-align: middle;
        }
        
        .file-pdf { color: #ef4444; }
        .file-word { color: #3b82f6; }
        .file-excel { color: #10b981; }
        .file-ppt { color: #f59e0b; }
        .file-zip { color: #8b5cf6; }
        .file-default { color: #94a3b8; }
        
        .file-actions {
            display: flex;
            gap: 10px;
        }
        
        .file-link {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .file-link:hover {
            color: var(--primary-color);
        }
        
        .file-description {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .logout-bar {
            display: flex;
            justify-content: flex-end;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }
        
        .back-button {
            margin-right: auto;
            background-color: transparent;
            border: 1px solid var(--secondary-color);
            color: var(--secondary-color);
        }
        
        .back-button:hover {
            background-color: rgba(139, 92, 246, 0.1);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: rgba(49, 46, 129, 0.4);
            border-radius: 8px;
            border: 1px dashed var(--border-color);
            margin-top: 20px;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: var(--muted-text);
            margin-bottom: 15px;
            opacity: 0.7;
        }
        
        .empty-state h3 {
            font-size: 1.2rem;
            color: var(--light-text);
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: var(--muted-text);
            max-width: 500px;
            margin: 0 auto;
        }
        
        @media (max-width: 768px) {
            .byron-container {
                margin-top: 80px;
            }
            
            .byron-header h1 {
                font-size: 2rem;
            }
            
            .tab-button {
                padding: 12px 15px;
                font-size: 0.9rem;
            }
            
            .tab-button i {
                margin-right: 5px;
            }
            
            .notes-grid {
                grid-template-columns: 1fr;
            }
            
            .btn {
                padding: 10px 20px;
            }
        }
        
        @media (max-width: 576px) {
            .tab-button {
                padding: 10px;
                font-size: 0.8rem;
            }
            
            .tab-button i {
                margin-right: 0;
                margin-bottom: 5px;
                display: block;
                font-size: 1.2rem;
            }
            
            .byron-header {
                padding: 20px;
            }
            
            .files-table {
                display: block;
                overflow-x: auto;
            }
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

    <div class="byron-container">
        <div class="byron-header">
            <h1>¡Bienvenido, SmashByron!</h1>
            <p>Tu panel personal para gestionar notas y archivos</p>
        </div>
        
        <?php if(isset($_GET['status']) && isset($_GET['message'])): ?>
            <div class="alert alert-<?php echo $_GET['status'] == 'success' ? 'success' : 'danger'; ?>">
                <i class="fas fa-<?php echo $_GET['status'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="tab-menu">
            <button class="tab-button <?php echo $section == 'notes' ? 'active' : ''; ?>" data-tab="notes">
                <i class="fas fa-sticky-note"></i> Mis Notas
            </button>
            <button class="tab-button <?php echo $section == 'files' ? 'active' : ''; ?>" data-tab="files">
                <i class="fas fa-file-alt"></i> Mis Archivos
            </button>
        </div>
        
        <!-- Sección de Notas -->
        <div id="notes" class="tab-content <?php echo $section == 'notes' ? 'active' : ''; ?>">
            <div class="dashboard-card">
                <h2><i class="fas fa-plus-circle"></i> Nueva Nota</h2>
                
                <div class="form-container">
                    <form action="smashbyron.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="note_title">Título</label>
                            <input type="text" id="note_title" name="note_title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="note_content">Contenido</label>
                            <textarea id="note_content" name="note_content" class="form-control" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="note_image">Imagen (opcional)</label>
                            <input type="file" id="note_image" name="note_image" class="form-control" accept="image/*" onchange="previewImage()">
                            <img id="image-preview" class="image-preview" src="#" alt="Vista previa">
                        </div>
                        <button type="submit" name="add_note" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Nota
                        </button>
                    </form>
                </div>
            </div>
            
            <?php if(empty($notes)): ?>
                <div class="empty-state">
                    <i class="fas fa-sticky-note"></i>
                    <h3>No hay notas guardadas</h3>
                    <p>Crea tu primera nota usando el formulario de arriba. Las notas te ayudarán a organizar tus ideas y tareas.</p>
                </div>
            <?php else: ?>
                <div class="notes-grid">
                    <?php foreach($notes as $note): ?>
                        <div class="note-card">
                            <div class="note-header">
                                <h3 class="note-title"><?php echo htmlspecialchars($note['title']); ?></h3>
                            </div>
                            <div class="note-date">
                                <i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y H:i', strtotime($note['created_at'])); ?>
                            </div>
                            
                            <?php if($note['has_image'] == 1 && !empty($note['image_path'])): ?>
                                <img class="note-image" src="../<?php echo htmlspecialchars($note['image_path']); ?>" alt="Imagen de la nota">
                            <?php endif; ?>
                            
                            <div class="note-content"><?php echo nl2br(htmlspecialchars($note['content'])); ?></div>
                            
                            <div class="note-actions">
                                <form action="smashbyron.php" method="post" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta nota?');">
                                    <input type="hidden" name="note_id" value="<?php echo $note['id']; ?>">
                                    <button type="submit" name="delete_note" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sección de Archivos -->
        <div id="files" class="tab-content <?php echo $section == 'files' ? 'active' : ''; ?>">
            <div class="dashboard-card">
                <h2><i class="fas fa-cloud-upload-alt"></i> Subir Archivo</h2>
                
                <div class="form-container">
                    <form action="smashbyron.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="file_upload">Seleccionar Archivo</label>
                            <input type="file" id="file_upload" name="file_upload" class="form-control" required>
                            <small style="color: var(--muted-text); display: block; margin-top: 5px;">
                                Formatos permitidos: PDF, Word, Excel, PowerPoint, TXT, ZIP, RAR (Máx. 50MB)
                            </small>
                        </div>
                        <div class="form-group">
                            <label for="file_description">Descripción</label>
                            <textarea id="file_description" name="file_description" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" name="add_file" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Subir Archivo
                        </button>
                    </form>
                </div>
            </div>
            
            <?php if(empty($files)): ?>
                <div class="empty-state">
                    <i class="fas fa-file-upload"></i>
                    <h3>No hay archivos subidos</h3>
                    <p>Sube tus primeros archivos usando el formulario de arriba. Podrás acceder a tus documentos desde cualquier lugar.</p>
                </div>
            <?php else: ?>
                <div class="dashboard-card">
                    <h2><i class="fas fa-folder-open"></i> Mis Archivos</h2>
                    
                    <table class="files-table">
                        <thead>
                            <tr>
                                <th>Archivo</th>
                                <th>Descripción</th>
                                <th>Tamaño</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($files as $file): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $extension = pathinfo($file['file_name'], PATHINFO_EXTENSION);
                                        $icon_class = 'file-default';
                                        $icon = 'file-alt';
                                        
                                        if(in_array($extension, ['pdf'])) {
                                            $icon_class = 'file-pdf';
                                            $icon = 'file-pdf';
                                        } elseif(in_array($extension, ['doc', 'docx'])) {
                                            $icon_class = 'file-word';
                                            $icon = 'file-word';
                                        } elseif(in_array($extension, ['xls', 'xlsx'])) {
                                            $icon_class = 'file-excel';
                                            $icon = 'file-excel';
                                        } elseif(in_array($extension, ['ppt', 'pptx'])) {
                                            $icon_class = 'file-ppt';
                                            $icon = 'file-powerpoint';
                                        } elseif(in_array($extension, ['zip', 'rar'])) {
                                            $icon_class = 'file-zip';
                                            $icon = 'file-archive';
                                        }
                                        ?>
                                        <i class="fas fa-<?php echo $icon; ?> file-icon <?php echo $icon_class; ?>"></i>
                                        <?php echo htmlspecialchars($file['file_name']); ?>
                                    </td>
                                    <td class="file-description"><?php echo empty($file['description']) ? '-' : htmlspecialchars($file['description']); ?></td>
                                    <td><?php echo formatFileSize($file['file_size']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($file['uploaded_at'])); ?></td>
                                    <td>
                                        <div class="file-actions">
                                            <a href="../<?php echo htmlspecialchars($file['file_path']); ?>" class="file-link" target="_blank">
                                                <i class="fas fa-download"></i> Descargar
                                            </a>
                                            <form action="smashbyron.php" method="post" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este archivo?');" style="display: inline;">
                                                <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                                <button type="submit" name="delete_file" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="logout-bar">
            <a href="../index.html" class="btn back-button">
                <i class="fas fa-arrow-left"></i> Volver al Inicio
            </a>
            <a href="logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
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
            const preview = document.getElementById('image-preview');
            const file = document.getElementById('note_image').files[0];
            const reader = new FileReader();
            
            reader.onloadend = function() {
                preview.src = reader.result;
                preview.style.display = 'block';
            }
            
            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = '#';
                preview.style.display = 'none';
            }
        }
    </script>
    <script src="../Js/index.js"></script>
</body>
</html>

<?php
// Función para formatear el tamaño de archivo
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } else if ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } else if ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } else if ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } else if ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }
    return $bytes;
}
?>