-- Crear tabla para archivos de usuario
CREATE TABLE IF NOT EXISTS archivos_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Crear tabla para notas de usuario
CREATE TABLE IF NOT EXISTS notas_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    contenido TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Añadir campo de foto de perfil a la tabla de usuarios si no existe
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS profile_photo VARCHAR(255) DEFAULT NULL;