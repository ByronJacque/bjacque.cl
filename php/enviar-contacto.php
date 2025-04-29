<?php
// Configuración de cabeceras para evitar problemas de codificación
header('Content-Type: text/html; charset=utf-8');

// Verificar si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir y limpiar datos del formulario
    $nombre = filter_var(trim($_POST["nombre"]), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $telefono = filter_var(trim($_POST["telefono"]), FILTER_SANITIZE_STRING);
    $asunto = filter_var(trim($_POST["asunto"]), FILTER_SANITIZE_STRING);
    $mensaje = filter_var(trim($_POST["mensaje"]), FILTER_SANITIZE_STRING);
    
    // Validar el correo electrónico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Por favor, introduce una dirección de correo electrónico válida."]);
        exit;
    }
    
    // Verificar que los campos requeridos no estén vacíos
    if (empty($nombre) || empty($email) || empty($asunto) || empty($mensaje)) {
        echo json_encode(["success" => false, "message" => "Por favor, completa todos los campos requeridos."]);
        exit;
    }
    
    // Dirección a la que se enviará el correo
    $destinatario = "byron.jacque16@gmail.com";
    
    // Construir el cuerpo del mensaje
    $cuerpo_mensaje = "Has recibido un nuevo mensaje desde el formulario de contacto de tu sitio web.\n\n";
    $cuerpo_mensaje .= "Nombre: " . $nombre . "\n";
    $cuerpo_mensaje .= "Email: " . $email . "\n";
    if (!empty($telefono)) {
        $cuerpo_mensaje .= "Teléfono: " . $telefono . "\n";
    }
    $cuerpo_mensaje .= "Asunto: " . $asunto . "\n\n";
    $cuerpo_mensaje .= "Mensaje:\n" . $mensaje . "\n";
    
    // Configurar cabeceras del correo
    $cabeceras = "From: " . $email . "\r\n";
    $cabeceras .= "Reply-To: " . $email . "\r\n";
    $cabeceras .= "X-Mailer: PHP/" . phpversion();
    
    // Intentar enviar el correo
    if (mail($destinatario, "Nuevo mensaje de contacto: " . $asunto, $cuerpo_mensaje, $cabeceras)) {
        echo json_encode(["success" => true, "message" => "Tu mensaje ha sido enviado correctamente. Me pondré en contacto contigo lo antes posible."]);
    } else {
        echo json_encode(["success" => false, "message" => "Lo sentimos, hubo un problema al enviar tu mensaje. Por favor, inténtalo de nuevo más tarde."]);
    }
} else {
    // Si alguien intenta acceder directamente a este archivo
    echo json_encode(["success" => false, "message" => "Acceso no permitido."]);
}
?>