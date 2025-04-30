<?php
require_once 'config.php';
session_start();

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Prevenir inyección SQL
    $username = $conn->real_escape_string($username);
    
    $sql = "SELECT id, username, password FROM usuarios WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            
            // Redirección personalizada para diferentes usuarios
            if ($row['username'] === "mvalladares") {
                header("location: mvalladares.php");
            } elseif ($row['username'] === "smashbyron") {
                header("location: smashbyron.php");
            } else {
                // Redirigir a la página de espacio personal para usuarios normales
                header("location: espacio-usuario.php");
            }
            exit;
        } else {
            $error = "La contraseña no es válida";
        }
    } else {
        $error = "El nombre de usuario no existe";
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Byron Jacque</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/index.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background-color: rgba(0,0,0,0.7);
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.2);
        }
        .login-form {
            display: flex;
            flex-direction: column;
        }
        .login-form input {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #2a2a2a;
            background-color: #121212;
            color: #e0e0e0;
            border-radius: 4px;
        }
        .login-form button {
            padding: 12px;
            background-color: #00c853;
            color: #000;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        .login-form button:hover {
            background-color: #00e676;
        }
        .error-message {
            color: #ff3d00;
            margin-bottom: 15px;
        }
        .register-link {
            text-align: center;
            margin-top: 15px;
            color: #e0e0e0;
        }
        .register-link a {
            color: #00e676;
            text-decoration: none;
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

    <div class="login-container">
        <h2 style="text-align: center; color: #00e676; margin-bottom: 20px;">Iniciar Sesión</h2>
        
        <?php if(!empty($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form class="login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="text" name="username" placeholder="Nombre de usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Iniciar Sesión</button>
        </form>
        
        <div class="register-link">
            ¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>
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