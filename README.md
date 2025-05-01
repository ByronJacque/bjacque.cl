# Sitio Web Personal de Byron Jacque

Este repositorio contiene el código fuente completo del sitio web personal de Byron Jacque, un Ingeniero Informático y Desarrollador Web. El sitio web está construido con HTML, CSS, JavaScript y PHP, con una estética cyberpunk/matrix.

## Estructura Completa del Proyecto

### Archivos Principales
- **index.html**: Página principal del sitio web con secciones como introducción, habilidades, proyectos, y formulario de contacto.
- **README.md**: Documentación del proyecto (este archivo).

### Carpetas de Recursos
- **assets/**: Carpeta con todos los recursos multimedia.
  - **archivos/**
    - `Curriculum Vitae.pdf`: CV actualizado disponible para descarga.
  - **Certificados/**: Colección de certificaciones profesionales en PDF.
    - `CERTIFICADO EN ARQUITECTURA CLOUD.pdf`
    - `CERTIFICADO EN DESARROLLADOR FULL STACK.pdf`
    - `CERTIFICADO EN DESARROLLO DE APLICACIONES.pdf`
    - `CERTIFICADO EN DISEÑO ÁGIL DE SISTEMAS.pdf`
    - `CERTIFICADO EN DISEÑO Y GESTIÓN DE BASE DE.pdf`
    - `CERTIFICADO EN INNOVACIÓN Y EMPRENDIMIENTO.pdf`
    - `CERTIFICADO EN SOPORTE COMPUTACIONAL.pdf`
    - `Certificado Power Bi.pdf`
    - `Certificado Python.pdf`
  - **imagenes certificados/**: Versiones PNG de los certificados para visualización web.
  - **imagenesjuegos/**: Imágenes relacionadas con videojuegos.
    - `eldenring.jpg`
    - `videojuegos.jpg`
  - **imagenesjuegosplatinados/**: Screenshots de logros en videojuegos.
    - `completado.png`
    - `southparkthestick.png`
  - **imagenesmusicas/**: Portadas de canciones para el reproductor de música.
  - **images/**: Imágenes generales utilizadas en todo el sitio.
  - **musica/**: Archivos MP3 para el reproductor de música.
    - `bury-the-light.mp3`
    - `cancioninicio.mp3`
    - `cancioninicio2.mp3`
    - `howdeepisyourlove.mp3`
    - `sariasong.mp3`
    - `sinoescontigoremix.mp3`
    - `wickedgame.mp3`
  - **videos/**: Videos de fondo y contenido multimedia.
    - `fondomatrix.mp4`: Video principal de fondo con efecto Matrix.
    - `matrix.mp4`
    - `rat-dance-but-its-dante-devil-may-cry.mp4`
  - **byron_notes_images/**: Carpeta donde se almacenan las imágenes subidas con las notas del usuario SmashByron (creada automáticamente).
  - **byron_files/**: Carpeta donde se almacenan los archivos subidos por el usuario SmashByron (creada automáticamente).

### Estilos y Scripts
- **css/**: Hojas de estilo para cada sección del sitio.
  - `blogs.css`: Estilos para la sección de blogs.
  - `certificados.css`: Estilos para la página de certificados.
  - `datos.css`: Estilos para la sección de información personal.
  - `hobbys.css`: Estilos para la sección de hobbies.
  - `index.css`: Estilos principales para la página de inicio.
  - `musica.css`: Estilos para el reproductor y sección de música.
  - `proyecto.css`: Estilos para la sección de proyectos.

- **Js/**: Scripts JavaScript que añaden interactividad al sitio.
  - `blogs.js`: Funcionalidad para la sección de blogs.
  - `certificados.js`: Script para visualización de certificados.
  - `datos.js`: Interactividad para la sección de datos personales.
  - `hobbys.js`: Funcionalidad para la sección de hobbies.
  - `index.js`: Script principal con funciones como:
    - Efecto lluvia Matrix
    - Ajuste del video de fondo
    - Navegación móvil
    - Reproductor de música
    - Animaciones de scroll
    - Efecto tipográfico en nombre
    - Formulario de contacto
    - Modal para el CV
    - Terminal interactivo
    - Partículas de cursor
    - Efecto 3D en tarjetas de habilidades
    - Efectos de glitch
  - `musica.js`: Control del reproductor de música.
  - `proyecto.js`: Interactividad para la sección de proyectos.

### Páginas HTML
- **Paginas/**: Secciones principales del sitio web.
  - `blogs.html`: Sección de blogs y artículos.
  - `certificados.html`: Galería de certificados obtenidos.
  - `datos.html`: Información personal y profesional.
  - `hobbys.html`: Sección sobre intereses personales, incluyendo videojuegos.
  - `musica.html`: Reproductor de música y playlist personal.
  - `proyectos.html`: Portafolio de proyectos realizados.

- **PaginasProyectos/**: Páginas detalladas de cada proyecto.
  - `FormularioFacil.html`: Descripción de un proyecto específico.

### Scripts PHP y Sistema de Usuarios
- **php/**: Scripts del lado del servidor.
  - `area-exclusiva.php`: Página de área exclusiva general para usuarios autenticados.
  - `config.php`: Configuración de conexión a la base de datos y sesiones.
    ```php
   
    ```
  - `crear_usuario_mvalladares.php`: Script para crear el usuario de mvalladares.
  - `crear_usuario_smashbyron.php`: Script para crear el usuario SmashByron con contraseña q7qrnggn.
  - `diagnostico.php`: Herramienta de diagnóstico del sistema.
  - `enviar-contacto.php`: Procesa el formulario de contacto y envía emails a byron.jacque16@gmail.com.
  - `login.php`: Sistema de inicio de sesión para áreas exclusivas.
  - `logout.php`: Cierra la sesión de usuario.
  - `mvalladares.php`: Área exclusiva para el usuario mvalladares.
  - `register.php`: Formulario de registro para nuevos usuarios.
  - `smashbyron.php`: Área exclusiva para el usuario SmashByron con sistema de notas y archivos.

## Funcionalidades Detalladas

### 1. Sitio Web Principal
- **Página de inicio con efectos visuales avanzados**:
  - Animación de lluvia de código al estilo Matrix creada con canvas
  - Video de fondo con efecto matrix (fondomatrix.mp4)
  - Navegación fija en la parte superior
  - Efectos de desplazamiento y animaciones al hacer scroll

- **Secciones principales**:
  - **Proyectos**: Portafolio de trabajos realizados con capturas y descripciones
  - **Blogs**: Artículos técnicos y personales
  - **Hobbies**: Sección sobre videojuegos y otros intereses
  - **Datos Personales**: Información profesional y contacto
  - **Certificados**: Galería de certificaciones obtenidas
  - **Música**: Reproductor personalizado con playlist integrada

- **Formulario de contacto**:
  - Envía mensajes directamente al correo byron.jacque16@gmail.com
  - Implementado con PHP usando PHPMailer
  - Validación de campos en cliente y servidor
  - Mensajes de confirmación y errores

- **Reproductor de música integrado**:
  - Reproductor personalizado con controles de reproducción
  - Playlist con canciones almacenadas en assets/musica/
  - Portadas de canciones con efecto visual
  - Control de volumen y progreso de reproducción

- **Terminal interactivo**:
  - Interfaz de terminal al estilo hacker
  - Responde a comandos como: help, about, clear, projects, contact, skills, exit
  - Accesible desde el botón terminal en la interfaz

- **Efectos visuales y animaciones**:
  - Partículas que siguen al cursor
  - Tarjetas de habilidades con efecto 3D al pasar el mouse
  - Efectos de glitch en títulos de secciones
  - Efecto tipográfico en el nombre con animación hover
  - Parallax en scroll para elementos específicos

### 2. Áreas Exclusivas
El sitio incluye áreas de acceso restringido para usuarios específicos:

#### Área de SmashByron
- **Acceso**:
  - URL: php/login.php
  

- **Sistema de notas completo**:
  - Creación de notas con título y contenido de texto
  - Opción para adjuntar imágenes a las notas
  - Visualización de notas en formato de tarjetas
  - Almacenamiento persistente en la base de datos MySQL
  - Eliminación de notas con confirmación
  - Las imágenes se guardan en assets/byron_notes_images/

- **Sistema de gestión de archivos**:
  - Subida de documentos en múltiples formatos (PDF, Word, Excel, PowerPoint, etc.)
  - Descripción personalizada para cada archivo
  - Tamaño máximo permitido: 50MB
  - Almacenamiento en assets/byron_files/
  - Listado de archivos con información de tipo, tamaño y fecha
  - Descarga directa de archivos
  - Eliminación de archivos con confirmación

- **Interfaz gráfica personalizada**:
  - Diseño coherente con el estilo cyberpunk/matrix del sitio principal
  - Sistema de pestañas para alternar entre notas y archivos
  - Mensajes de estado para confirmación de acciones
  - Vista previa de imágenes antes de subir
  - Diseño responsive para dispositivos móviles

#### Otras áreas exclusivas
- **Área mvalladares**:
  - Acceso específico para el usuario mvalladares
  - Funcionalidades específicas implementadas en mvalladares.php

### 3. Sistema de Usuarios
- **Login seguro**:
  - Encriptación de contraseñas con password_hash() y password_verify()
  - Protección contra inyección SQL usando prepared statements
  - Gestión de sesiones seguras
  - Redirección personalizada según el tipo de usuario

- **Registro de usuarios**:
  - Formulario de registro en register.php (opcional)
  - Validación de datos de entrada
  - Creación de usuario con contraseña encriptada

- **Gestión de sesiones**:
  - Control de acceso a áreas restringidas
  - Cierre de sesión seguro
  - Timeout de sesión para mayor seguridad

## Tablas de Base de Datos

### 1. Tabla `usuarios`
```sql
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

### 2. Tabla `byron_notes`
```sql
CREATE TABLE IF NOT EXISTS byron_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    has_image TINYINT(1) DEFAULT 0,
    image_path VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

### 3. Tabla `byron_files`
```sql
CREATE TABLE IF NOT EXISTS byron_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    file_size VARCHAR(50) NOT NULL,
    description TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

## Guía de Mantenimiento y Actualización

### Formulario de Contacto
1. El formulario en `index.html` envía los datos a `php/enviar-contacto.php`
2. Se utiliza PHPMailer para enviar correos a byron.jacque16@gmail.com
3. Para modificar el destinatario, editar la variable `$mail->addAddress()` en el archivo `enviar-contacto.php`
4. El formulario incluye campos para nombre, correo, asunto y mensaje

### Áreas Exclusivas
Para crear una nueva área exclusiva personalizada:
1. Crear un script similar a `smashbyron.php` con las funcionalidades deseadas
2. Crear un script para generar el usuario (como `crear_usuario_smashbyron.php`)
3. Añadir la redirección en `login.php` en la sección de redirecciones personalizadas
4. Crear las tablas necesarias en la base de datos

Para modificar el área existente de SmashByron:
1. Editar `php/smashbyron.php` para cambiar la estructura o funcionalidades
2. Añadir nuevos campos a las tablas `byron_notes` o `byron_files` si es necesario
3. Modificar los estilos en la sección `<style>` del archivo para cambiar la apariencia

### Sistema de Usuarios
Para añadir o modificar usuarios:
1. Ejecutar `php/crear_usuario_[nombre].php` para crear un nuevo usuario
2. O utilizar el formulario de registro en `php/register.php`
3. Los usuarios se almacenan en la tabla `usuarios` con contraseñas encriptadas

## Tecnologías Utilizadas

- **Frontend**:
  - HTML5
  - CSS3 (Flexbox, Grid, Animaciones, Variables CSS)
  - JavaScript (ES6+)
  - Canvas API para efectos visuales
  - Web Audio API para el reproductor de música

- **Backend**:
  - PHP 7.4+
  - MySQL/MariaDB
  - PHPMailer para envío de correos

- **Bibliotecas externas**:
  - Font Awesome 6.4.0 para iconos
  - Google Fonts (Montserrat, Poppins, Share Tech Mono)

- **Técnicas de seguridad**:
  - Encriptación de contraseñas con bcrypt
  - Protección contra inyección SQL
  - Validación de entradas
  - Sanitización de salidas con htmlspecialchars
  - Manejo seguro de archivos subidos

## Configuración del Servidor

- **Requisitos del servidor**:
  - PHP 7.4 o superior
  - MySQL 5.7 o superior / MariaDB 10.2 o superior
  - Extensiones PHP: mysqli, fileinfo, gd
  - Permisos de escritura en directorios assets/byron_notes_images/ y assets/byron_files/
  - Configuración PHP: upload_max_filesize ≥ 50M, post_max_size ≥ 50M

- **Configuración de base de datos**:
  - Servidor: localhost (o el proporcionado por el hosting)
  - Base de datos: bjacquec_bjacque.cl
  - Usuario: bjacquec_byron
  - Contraseña: #Q7qrnggn2002 (cambiar en entorno de producción)

## Licencia y Autoría

Este sitio web es propiedad intelectual de Byron Jacque. Todos los derechos reservados.

## Contacto

Para consultas técnicas, contactar a Byron Jacque:
- Email: byron.jacque16@gmail.com
- GitHub: https://github.com/ByronJacque
- LinkedIn: https://www.linkedin.com/in/byron-jacque-rivero-a1b670248/
