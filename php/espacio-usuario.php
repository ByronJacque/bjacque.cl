<?php
require_once 'config.php';
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Obtener información del usuario
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Manejar carga de archivos
$upload_message = '';
$file_uploaded = false;
$note_added = false;
$note_message = '';

// Crear directorio de usuario si no existe
$user_dir = "../usuarios/" . $user['username'];
if (!file_exists($user_dir)) {
    mkdir($user_dir, 0777, true);
}

// Procesar carga de archivos
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] == 0) {
    $target_dir = $user_dir . "/";
    $target_file = $target_dir . basename($_FILES["file_upload"]["name"]);
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Verificar si el archivo ya existe
    if (file_exists($target_file)) {
        $upload_message = "El archivo ya existe.";
    } else {
        // Intentar subir el archivo
        if (move_uploaded_file($_FILES["file_upload"]["tmp_name"], $target_file)) {
            $upload_message = "El archivo " . htmlspecialchars(basename($_FILES["file_upload"]["name"])) . " ha sido subido.";
            $file_uploaded = true;
            
            // Guardar información del archivo en la base de datos
            $file_name = $_FILES["file_upload"]["name"];
            $file_path = $target_file;
            
            $sql = "INSERT INTO archivos_usuario (user_id, nombre_archivo, ruta_archivo) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $user_id, $file_name, $file_path);
            $stmt->execute();
        } else {
            $upload_message = "Error al subir el archivo.";
        }
    }
}

// Procesar notas nuevas
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['note_content']) && !empty($_POST['note_content'])) {
    $note_title = $_POST['note_title'];
    $note_content = $_POST['note_content'];
    
    $sql = "INSERT INTO notas_usuario (user_id, titulo, contenido) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $note_title, $note_content);
    
    if ($stmt->execute()) {
        $note_message = "Nota guardada correctamente.";
        $note_added = true;
    } else {
        $note_message = "Error al guardar la nota: " . $stmt->error;
    }
}

// Procesar carga de foto de perfil
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile_photo']) && isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
    $upload_dir = "../assets/profile_photos/";
    
    // Crear el directorio si no existe
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_name = $user['username'] . "_" . time() . "." . pathinfo($_FILES["profile_photo"]["name"], PATHINFO_EXTENSION);
    $target_file = $upload_dir . $file_name;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Permitir solo ciertos formatos de imagen
    $allowed_formats = ["jpg", "jpeg", "png", "gif"];
    if(in_array($file_type, $allowed_formats)) {
        // Eliminar la foto anterior si existe
        if(!empty($user['profile_photo'])) {
            $old_photo_path = "../" . $user['profile_photo'];
            if(file_exists($old_photo_path)) {
                unlink($old_photo_path);
            }
        }
        
        if(move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
            // Guardar ruta en la base de datos
            $photo_path = str_replace("../", "", $target_file);
            
            $sql = "UPDATE usuarios SET profile_photo = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $photo_path, $user_id);
            
            if($stmt->execute()) {
                // Actualizar datos del usuario en la sesión
                $user['profile_photo'] = $photo_path;
                $profile_message = "Foto de perfil actualizada correctamente.";
                $profile_success = true;
            } else {
                $profile_message = "Error al actualizar la foto de perfil en la base de datos.";
                $profile_success = false;
            }
        } else {
            $profile_message = "Error al subir la foto de perfil.";
            $profile_success = false;
        }
    } else {
        $profile_message = "Formato de archivo no permitido. Solo se permiten JPG, JPEG, PNG y GIF.";
        $profile_success = false;
    }
}

// Eliminar archivo
if (isset($_GET['delete_file']) && !empty($_GET['delete_file'])) {
    $file_id = $_GET['delete_file'];
    
    // Obtener información del archivo
    $sql = "SELECT * FROM archivos_usuario WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $file_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $file = $result->fetch_assoc();
        
        // Eliminar archivo físico
        if (file_exists($file['ruta_archivo'])) {
            unlink($file['ruta_archivo']);
        }
        
        // Eliminar registro de la base de datos
        $sql = "DELETE FROM archivos_usuario WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $file_id);
        $stmt->execute();
        
        $upload_message = "Archivo eliminado correctamente.";
    }
}

// Eliminar nota
if (isset($_GET['delete_note']) && !empty($_GET['delete_note'])) {
    $note_id = $_GET['delete_note'];
    
    $sql = "DELETE FROM notas_usuario WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $note_id, $user_id);
    $stmt->execute();
    
    $note_message = "Nota eliminada correctamente.";
}

// Obtener archivos del usuario
$sql = "SELECT * FROM archivos_usuario WHERE user_id = ? ORDER BY fecha_subida DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$archivos_result = $stmt->get_result();

// Obtener notas del usuario
$sql = "SELECT * FROM notas_usuario WHERE user_id = ? ORDER BY fecha_creacion DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notas_result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Espacio Personal | <?php echo $user['username']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css">
    <!-- El estilo se mantiene -->
    <style>
        :root {
            --primary-color: #00c853;
            --primary-hover: #00e676;
            --secondary-color: #4f46e5;
            --secondary-hover: #6366f1;
            --danger-color: #ef4444;
            --danger-hover: #f87171;
            --success-color: #10b981;
            --text-color: #e0e0e0;
            --text-muted: #888;
            --bg-dark: rgba(0,0,0,0.7);
            --bg-card: rgba(10,10,10,0.85);
            --bg-element: rgba(20,20,20,0.9);
            --border-color: #333;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            color: var(--text-color);
            background-color: #000;
            min-height: 100vh;
            position: relative;
        }
        
        .digital-grid {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: linear-gradient(rgba(0, 0, 0, 0.8) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(0, 0, 0, 0.8) 1px, transparent 1px);
            background-size: 20px 20px;
            z-index: -1;
            opacity: 0.5;
            pointer-events: none;
        }
        
        #matrix-rain {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            opacity: 0.8;
            pointer-events: none;
        }
        
        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -3;
            overflow: hidden;
            pointer-events: none;
        }
        
        #background-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.3;
        }
        
        header {
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 230, 118, 0.2);
            transition: all 0.3s ease;
        }
        
        header .logo {
            float: left;
            margin-left: 30px;
        }
        
        header .logo-text {
            color: #00e676;
            text-decoration: none;
            font-family: 'Share Tech Mono', monospace;
            font-size: 24px;
            letter-spacing: 1px;
            text-shadow: 0 0 10px rgba(0, 230, 118, 0.5);
            transition: all 0.3s ease;
        }
        
        header .logo-text:hover {
            text-shadow: 0 0 15px rgba(0, 230, 118, 0.8);
        }
        
        header nav {
            float: right;
            margin-right: 30px;
        }
        
        header nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        header nav ul li {
            display: inline-block;
            margin-left: 20px;
        }
        
        header nav ul li a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            position: relative;
            transition: all 0.3s ease;
        }
        
        header nav ul li a:hover {
            color: #00e676;
        }
        
        header nav ul li a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background-color: #00e676;
            bottom: -5px;
            left: 0;
            transition: width 0.3s ease;
        }
        
        header nav ul li a:hover::after {
            width: 100%;
        }
        
        .user-container {
            max-width: 1200px;
            margin: 100px auto 50px;
            padding: 30px;
            background-color: var(--bg-dark);
            border-radius: 12px;
            box-shadow: 0 0 30px rgba(0, 230, 118, 0.15);
        }
        
        .welcome-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .welcome-section h1 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 2.5rem;
            text-shadow: 0 0 10px rgba(0, 230, 118, 0.3);
        }
        
        .welcome-section p {
            color: var(--text-color);
            font-size: 1.2em;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .content-area {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-top: 30px;
        }
        
        .upload-section, .notes-section {
            flex: 1;
            min-width: 300px;
            padding: 30px;
            background-color: var(--bg-card);
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .upload-section:hover, .notes-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3), 0 0 10px rgba(0, 230, 118, 0.1);
        }
        
        .section-title {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            font-size: 1.25rem;
        }
        
        .file-form, .note-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
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
            color: var(--secondary-color);
            border: 2px dashed var(--secondary-color);
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
        
        .file-name-display {
            margin-top: 10px;
            padding: 8px 12px;
            background-color: rgba(79, 70, 229, 0.1);
            color: var(--text-color);
            border-radius: 6px;
            font-size: 0.9em;
            display: none;
            word-break: break-all;
            animation: fadeIn 0.3s ease;
        }
        
        .file-name-display i {
            color: var(--secondary-color);
            margin-right: 5px;
        }
        
        .file-form input[type="file"], .note-form input[type="text"], .note-form textarea {
            padding: 15px;
            background-color: rgba(18, 18, 18, 0.7);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        .file-form input[type="file"] {
            opacity: 0;
            position: absolute;
            width: 0.1px;
            height: 0.1px;
            z-index: -1;
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
            background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));
            border-radius: 5px;
            transition: width 0.2s ease;
            box-shadow: 0 0 10px rgba(0, 230, 118, 0.3);
        }
        
        .progress-text {
            color: var(--text-color);
            font-size: 0.8em;
            text-align: center;
            margin-top: 5px;
            display: none;
        }
        
        .note-form input[type="text"]:focus, .note-form textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 230, 118, 0.2);
        }
        
        .note-form textarea {
            min-height: 180px;
            resize: vertical;
            font-family: 'Poppins', sans-serif;
            line-height: 1.5;
        }
        
        button.upload-btn, button.save-note-btn {
            padding: 14px;
            background-color: var(--primary-color);
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
        
        button.upload-btn:hover, button.save-note-btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 230, 118, 0.3);
        }
        
        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
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
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .message i {
            margin-right: 10px;
            font-size: 1.2em;
        }
        
        .success {
            background-color: rgba(16, 185, 129, 0.15);
            color: var(--success-color);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .error {
            background-color: rgba(239, 68, 68, 0.15);
            color: var(--danger-color);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        .subsection-title {
            color: var(--secondary-color);
            margin: 25px 0 15px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .files-list, .notes-list {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .file-item, .note-item {
            background-color: var(--bg-element);
            padding: 18px;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .file-item:hover, .note-item:hover {
            border-color: var(--primary-color);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3), 0 0 5px rgba(0, 230, 118, 0.2);
            transform: translateY(-3px);
        }
        
        .file-header, .note-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }
        
        .file-info {
            display: flex;
            align-items: center;
        }
        
        .file-icon {
            color: var(--secondary-color);
            font-size: 1.5em;
            margin-right: 10px;
            width: 28px;
            text-align: center;
        }
        
        .file-name, .note-title-display {
            flex-grow: 1;
            color: var(--text-color);
            font-weight: 500;
            font-size: 1.1em;
            word-break: break-all;
        }
        
        .file-metadata, .note-metadata {
            display: flex;
            align-items: center;
            gap: 15px;
            color: var(--text-muted);
            font-size: 0.85em;
        }
        
        .file-metadata i, .note-metadata i {
            margin-right: 5px;
        }
        
        .file-actions, .note-actions {
            display: flex;
            gap: 10px;
            margin-top: 5px;
        }
        
        .action-btn {
            padding: 8px 12px;
            background-color: transparent;
            color: var(--text-color);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.25s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9em;
            font-weight: 500;
            text-decoration: none;
        }
        
        .action-btn:hover {
            background-color: rgba(0, 200, 83, 0.1);
            border-color: var(--primary-color);
            color: var(--primary-hover);
        }
        
        .delete-btn {
            color: var(--text-muted);
            border-color: transparent;
        }
        
        .delete-btn:hover {
            background-color: rgba(239, 68, 68, 0.1);
            border-color: var(--danger-color);
            color: var(--danger-color);
        }
        
        .note-content {
            margin-top: 5px;
            padding: 15px;
            background-color: rgba(30,30,30,0.5);
            border-radius: 8px;
            color: #ccc;
            font-size: 0.95em;
            max-height: 150px;
            overflow-y: auto;
            line-height: 1.6;
            border: 1px solid var(--border-color);
        }
        
        .user-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .nav-btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .nav-btn i {
            margin-right: 8px;
        }
        
        .back-btn {
            background-color: rgba(255, 255, 255, 0.1);
            color: var (--text-color);
        }
        
        .back-btn:hover {
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateX(-2px);
        }
        
        .logout-btn {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }
        
        .logout-btn:hover {
            background-color: rgba(239, 68, 68, 0.2);
            transform: translateX(2px);
        }
        
        .empty-message {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
            background-color: rgba(0,0,0,0.1);
            border-radius: 8px;
            font-style: italic;
            border: 1px dashed var(--border-color);
        }
        
        .empty-message i {
            font-size: 2em;
            margin-bottom: 15px;
            opacity: 0.7;
            display: block;
        }
        
        /* Estilos para la sección de perfil */
        .profile-section {
            margin-bottom: 40px;
        }
        
        .profile-card {
            background-color: var(--bg-card);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3), 0 0 10px rgba(0, 230, 118, 0.1);
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 25px;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            position: relative;
            overflow: hidden;
            border-radius: 50%;
            box-shadow: 0 0 20px rgba(0, 200, 83, 0.3);
        }
        
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border: 4px solid var(--primary-color);
        }
        
        .avatar-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: #000;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 3em;
            font-weight: bold;
        }
        
        .profile-info {
            flex: 1;
        }
        
        .profile-info h2 {
            color: var(--text-color);
            margin-bottom: 10px;
            font-size: 1.8em;
        }
        
        .profile-username {
            color: var(--primary-color);
            margin-bottom: 8px;
            font-size: 1.2em;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .profile-email {
            color: var(--text-muted);
            font-size: 0.95em;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .profile-photo-form {
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid var(--border-color);
        }
        
        .profile-photo-form label {
            display: block;
            margin-bottom: 10px;
            color: var(--text-color);
            font-weight: 500;
            font-size: 1.05em;
        }
        
        .profile-photo-form small {
            display: block;
            margin-top: 8px;
            color: var(--text-muted);
            font-size: 0.85em;
            line-height: 1.5;
        }
        
        .profile-photo-form input[type="file"] {
            opacity: 0;
            position: absolute;
            width: 0.1px;
            height: 0.1px;
            z-index: -1;
        }
        
        .update-photo-btn, .photo-input-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        
        .photo-input-label {
            background-color: rgba(79, 70, 229, 0.1);
            color: var(--secondary-color);
            border: 1px solid var(--secondary-color);
            margin-bottom: 15px;
        }
        
        .photo-input-label:hover {
            background-color: rgba(79, 70, 229, 0.2);
            transform: translateY(-2px);
        }
        
        .update-photo-btn:hover {
            background-color: var(--secondary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
        
        .profile-photo-preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            margin-top: 15px;
            display: none;
            object-fit: contain;
            border: 2px solid var(--secondary-color);
        }
        
        /* Estilos footer */
        footer {
            background-color: rgba(0, 0, 0, 0.8);
            color: #fff;
            padding: 40px 0;
            margin-top: 60px;
        }
        
        footer .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        footer .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        footer .footer-logo h3 {
            color: #00e676;
            margin: 0;
            font-size: 20px;
            font-family: 'Share Tech Mono', monospace;
        }
        
        footer .footer-logo p {
            margin: 5px 0 0;
            font-size: 14px;
            opacity: 0.8;
        }
        
        footer .social-links {
            display: flex;
        }
        
        footer .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            margin-left: 10px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            transition: all 0.3s ease;
        }
        
        footer .social-links a:hover {
            background-color: #00e676;
            transform: translateY(-3px);
        }
        
        footer .copyright {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 14px;
            opacity: 0.8;
        }
        
        /* Estilos para iconos de archivos */
        .file-pdf {
            color: #f56565 !important;
        }
        
        .file-word {
            color: #4299e1 !important;
        }
        
        .file-excel {
            color: #48bb78 !important;
        }
        
        .file-powerpoint {
            color: #ed8936 !important;
        }
        
        .file-image {
            color: #9f7aea !important;
        }
        
        .file-archive {
            color: #ecc94b !important;
        }
        
        .file-audio {
            color: #38b2ac !important;
        }
        
        .file-video {
            color: #f687b3 !important;
        }
        
        .file-code {
            color: #4fd1c5 !important;
        }
        
        .file-alt {
            color: #a0aec0 !important;
        }
        
        /* Adaptabilidad para móviles */
        @media (max-width: 992px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .profile-avatar {
                margin: 0 auto;
            }
            
            footer .footer-content {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            footer .social-links {
                justify-content: center;
            }
        }
        
        @media (max-width: 768px) {
            header {
                padding: 10px 0;
            }
            
            header .logo {
                float: none;
                margin-left: 0;
                text-align: center;
                margin-bottom: 10px;
            }
            
            header nav {
                float: none;
                margin-right: 0;
                text-align: center;
            }
            
            header nav ul li {
                margin: 0 10px;
            }
            
            .user-container {
                padding: 20px;
                margin: 80px 15px 30px;
            }
            
            .content-area {
                flex-direction: column;
                gap: 25px;
            }
            
            .section-title {
                font-size: 1.3rem;
            }
            
            .upload-section, .notes-section {
                width: 100%;
                padding: 20px;
            }
            
            .welcome-section h1 {
                font-size: 1.8rem;
            }
        }
        
        @media (max-width: 576px) {
            header nav ul {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            header nav ul li {
                margin: 5px;
            }
            
            .user-nav {
                flex-direction: column;
                gap: 10px;
            }
            
            .nav-btn {
                width: 100%;
                justify-content: center;
            }
            
            .profile-card {
                padding: 20px;
            }
            
            .file-header, .note-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .file-actions, .note-actions {
                width: 100%;
                justify-content: space-between;
                margin-top: 10px;
            }
        }
        
        /* Sweetalert2 custom styles */
        .swal2-popup {
            background-color: var(--bg-card);
            border-radius: 12px;
            color: var(--text-color);
        }
        
        .swal2-title {
            color: var(--text-color);
        }
        
        .swal2-content {
            color: var(--text-muted);
        }
        
        .swal2-confirm {
            background-color: var(--primary-color) !important;
        }
        
        .swal2-cancel {
            background-color: #555 !important;
        }
        
        /* Estilos para scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-hover);
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

    <div class="user-container">
        <div class="user-nav">
            <a href="../index.html" class="nav-btn back-btn"><i class="fas fa-arrow-left"></i> Volver al Inicio</a>
            <a href="logout.php" class="nav-btn logout-btn"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </div>
        
        <div class="welcome-section">
            <h1>¡Bienvenido a tu Espacio Personal, <?php echo htmlspecialchars($user['username']); ?>!</h1>
            <p>Aquí puedes gestionar tus archivos y notas personales de manera segura y organizada.</p>
        </div>
        
        <!-- Sección de Perfil -->
        <div class="profile-section">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php if (!empty($user['profile_photo'])): ?>
                            <img src="../<?php echo htmlspecialchars($user['profile_photo']); ?>" alt="Foto de perfil">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="profile-info">
                        <h2><?php echo !empty($user['nombre_completo']) ? htmlspecialchars($user['nombre_completo']) : htmlspecialchars($user['username']); ?></h2>
                        <div class="profile-username">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['username']); ?>
                        </div>
                        <?php if (!empty($user['email'])): ?>
                            <div class="profile-email">
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (isset($profile_message)): ?>
                    <div class="message <?php echo $profile_success ? 'success' : 'error'; ?>">
                        <i class="fas <?php echo $profile_success ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                        <?php echo $profile_message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="profile-photo-form">
                    <form action="espacio-usuario.php" method="post" enctype="multipart/form-data">
                        <label>Actualizar foto de perfil</label>
                        <label for="profile_photo" class="photo-input-label">
                            <i class="fas fa-camera"></i> Seleccionar imagen
                        </label>
                        <input type="file" id="profile_photo" name="profile_photo" accept="image/*" onchange="previewProfilePhoto(this)">
                        <img id="profile-photo-preview" class="profile-photo-preview" src="#" alt="Vista previa">
                        <small>Formatos permitidos: JPG, JPEG, PNG, GIF. Tamaño máximo: 2MB.</small>
                        <button type="submit" name="update_profile_photo" class="update-photo-btn">
                            <i class="fas fa-save"></i> Guardar foto de perfil
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="content-area">
            <!-- Sección de Archivos -->
            <div class="upload-section">
                <h2 class="section-title"><i class="fas fa-file-upload"></i> Mis Archivos</h2>
                
                <?php if (!empty($upload_message)): ?>
                    <div class="message <?php echo $file_uploaded ? 'success' : 'error'; ?>">
                        <i class="fas <?php echo $file_uploaded ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                        <?php echo $upload_message; ?>
                    </div>
                <?php endif; ?>
                
                <form class="file-form" id="fileUploadForm" action="espacio-usuario.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="file_upload">Seleccionar archivo para subir</label>
                        <div class="custom-file-input">
                            <label for="file_upload" class="file-input-button">
                                <i class="fas fa-cloud-upload-alt"></i> Seleccionar archivo
                            </label>
                            <input type="file" id="file_upload" name="file_upload" required>
                            <div id="file-name" class="file-name-display"></div>
                            <div id="progress-container" class="progress-container">
                                <div id="progress-bar" class="progress-bar"></div>
                            </div>
                            <div id="progress-text" class="progress-text">0%</</div>
                        </div>
                    </div>
                    <button type="submit" id="upload-btn" class="upload-btn">
                        <i class="fas fa-upload"></i> Subir Archivo
                    </button>
                </form>
                
                <h3 class="subsection-title"><i class="fas fa-folder-open"></i> Mis Archivos Guardados</h3>
                
                <div class="files-list">
                    <?php if ($archivos_result->num_rows > 0): ?>
                        <?php while ($archivo = $archivos_result->fetch_assoc()): ?>
                            <div class="file-item">
                                <div class="file-header">
                                    <?php
                                    $extension = pathinfo($archivo['nombre_archivo'], PATHINFO_EXTENSION);
                                    $icon_class = 'file-default';
                                    $icon = 'file-alt';
                                    
                                    if (in_array($extension, ['pdf'])) {
                                        $icon_class = 'file-pdf';
                                        $icon = 'file-pdf';
                                    } elseif (in_array($extension, ['doc', 'docx'])) {
                                        $icon_class = 'file-word';
                                        $icon = 'file-word';
                                    } elseif (in_array($extension, ['xls', 'xlsx'])) {
                                        $icon_class = 'file-excel';
                                        $icon = 'file-excel';
                                    } elseif (in_array($extension, ['ppt', 'pptx'])) {
                                        $icon_class = 'file-powerpoint';
                                        $icon = 'file-powerpoint';
                                    } elseif (in_array($extension, ['zip', 'rar', '7z'])) {
                                        $icon_class = 'file-archive';
                                        $icon = 'file-archive';
                                    } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
                                        $icon_class = 'file-image';
                                        $icon = 'file-image';
                                    } elseif (in_array($extension, ['mp3', 'wav', 'ogg'])) {
                                        $icon_class = 'file-audio';
                                        $icon = 'file-audio';
                                    } elseif (in_array($extension, ['mp4', 'avi', 'mov', 'wmv'])) {
                                        $icon_class = 'file-video';
                                        $icon = 'file-video';
                                    } elseif (in_array($extension, ['txt', 'csv'])) {
                                        $icon_class = 'file-alt';
                                        $icon = 'file-alt';
                                    } elseif (in_array($extension, ['html', 'css', 'js', 'php'])) {
                                        $icon_class = 'file-code';
                                        $icon = 'file-code';
                                    }
                                    ?>
                                    <div class="file-info">
                                        <i class="fas fa-<?php echo $icon; ?> file-icon <?php echo $icon_class; ?>"></i>
                                        <span class="file-name"><?php echo htmlspecialchars($archivo['nombre_archivo']); ?></span>
                                    </div>
                                </div>
                                <div class="file-metadata">
                                    <span><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y H:i', strtotime($archivo['fecha_subida'])); ?></span>
                                </div>
                                <div class="file-actions">
                                    <a href="<?php echo htmlspecialchars(str_replace('../', '', $archivo['ruta_archivo'])); ?>" class="action-btn" download>
                                        <i class="fas fa-download"></i> Descargar
                                    </a>
                                    <a href="espacio-usuario.php?delete_file=<?php echo $archivo['id']; ?>" class="action-btn delete-btn" onclick="return confirm('¿Estás seguro de que deseas eliminar este archivo?');">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-message">
                            <i class="fas fa-folder-open"></i>
                            No has subido ningún archivo todavía.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Sección de Notas -->
            <div class="notes-section">
                <h2 class="section-title"><i class="fas fa-sticky-note"></i> Mis Notas</h2>
                
                <?php if (!empty($note_message)): ?>
                    <div class="message <?php echo $note_added ? 'success' : 'error'; ?>">
                        <i class="fas <?php echo $note_added ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                        <?php echo $note_message; ?>
                    </div>
                <?php endif; ?>
                
                <form class="note-form" action="espacio-usuario.php" method="post">
                    <div class="form-group">
                        <label for="note_title">Título de la nota</label>
                        <input type="text" id="note_title" name="note_title" placeholder="Escribe un título para tu nota" required>
                    </div>
                    <div class="form-group">
                        <label for="note_content">Contenido</label>
                        <textarea id="note_content" name="note_content" placeholder="Escribe aquí el contenido de tu nota..." required></textarea>
                    </div>
                    <button type="submit" class="save-note-btn">
                        <i class="fas fa-save"></i> Guardar Nota
                    </button>
                </form>
                
                <h3 class="subsection-title"><i class="fas fa-list"></i> Mis Notas Guardadas</h3>
                
                <div class="notes-list">
                    <?php if ($notas_result->num_rows > 0): ?>
                        <?php while ($nota = $notas_result->fetch_assoc()): ?>
                            <div class="note-item">
                                <div class="note-header">
                                    <span class="note-title-display"><?php echo htmlspecialchars($nota['titulo']); ?></span>
                                </div>
                                <div class="note-metadata">
                                    <span><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y H:i', strtotime($nota['fecha_creacion'])); ?></span>
                                </div>
                                <div class="note-content">
                                    <?php echo nl2br(htmlspecialchars($nota['contenido'])); ?>
                                </div>
                                <div class="note-actions">
                                    <a href="espacio-usuario.php?delete_note=<?php echo $nota['id']; ?>" class="action-btn delete-btn" onclick="return confirm('¿Estás seguro de que deseas eliminar esta nota?');">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-message">
                            <i class="fas fa-clipboard"></i>
                            No has creado ninguna nota todavía.
                        </div>
                    <?php endif; ?>
                </div>
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
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>
    <script>
        // Función para mostrar el nombre del archivo seleccionado
        document.getElementById('file_upload').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : '';
            const fileNameDisplay = document.getElementById('file-name');
            
            if (fileName) {
                fileNameDisplay.innerHTML = '<i class="fas fa-file"></i> ' + fileName;
                fileNameDisplay.style.display = 'block';
            } else {
                fileNameDisplay.style.display = 'none';
            }
        });
        
        // Función para previsualizar la foto de perfil
        function previewProfilePhoto(input) {
            const preview = document.getElementById('profile-photo-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
        
        // Configurar la barra de progreso para la carga de archivos
        const fileUploadForm = document.getElementById('fileUploadForm');
        const progressContainer = document.getElementById('progress-container');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const uploadBtn = document.getElementById('upload-btn');
        
        fileUploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fileInput = document.getElementById('file_upload');
            if (!fileInput.files[0]) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor, selecciona un archivo para subir',
                    confirmButtonColor: '#00c853'
                });
                return;
            }
            
            const xhr = new XMLHttpRequest();
            const formData = new FormData(fileUploadForm);
            
            // Mostrar barra de progreso
            progressContainer.style.display = 'block';
            progressText.style.display = 'block';
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo...';
            
            xhr.upload.addEventListener('progress', function(event) {
                if (event.lengthComputable) {
                    const percentComplete = Math.round((event.loaded / event.total) * 100);
                    progressBar.style.width = percentComplete + '%';
                    progressText.textContent = percentComplete + '%';
                }
            });
            
            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    // Completado - redirigir para refrescar la página
                    window.location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error durante la carga del archivo',
                        confirmButtonColor: '#00c853'
                    });
                    
                    progressContainer.style.display = 'none';
                    progressText.style.display = 'none';
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Subir Archivo';
                }
            });
            
            xhr.addEventListener('error', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error durante la carga del archivo',
                    confirmButtonColor: '#00c853'
                });
                
                progressContainer.style.display = 'none';
                progressText.style.display = 'none';
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Subir Archivo';
            });
            
            xhr.open('POST', 'espacio-usuario.php', true);
            xhr.send(formData);
        });
        
        // Efecto Matrix
        const canvas = document.getElementById('matrix-rain');
        const ctx = canvas.getContext('2d');
        
        // Configurar el tamaño del canvas
        function setCanvasSize() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        
        setCanvasSize();
        window.addEventListener('resize', setCanvasSize);
        
        // Configuración del efecto Matrix
        const columns = Math.floor(canvas.width / 20);
        const drops = [];
        for (let i = 0; i < columns; i++) {
            drops[i] = Math.random() * -100;
        }
        
        function draw() {
            ctx.fillStyle = 'rgba(0, 0, 0, 0.04)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            ctx.fillStyle = '#0f0';
            ctx.font = '15px monospace';
            
            for (let i = 0; i < drops.length; i++) {
                const text = String.fromCharCode(Math.floor(Math.random() * 94) + 33);
                ctx.fillText(text, i * 20, drops[i] * 20);
                
                if (drops[i] * 20 > canvas.height && Math.random() > 0.975) {
                    drops[i] = 0;
                }
                drops[i]++;
            }
        }
        
        setInterval(draw, 35);
    </script>
</body>
</html>