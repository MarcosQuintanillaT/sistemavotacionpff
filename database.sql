-- ==========================================
-- SISTEMA DE ELECCIONES ESTUDIANTILES
-- Base de datos: elecciones_estudiantiles
-- ==========================================

CREATE DATABASE IF NOT EXISTS elecciones_estudiantiles
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE elecciones_estudiantiles;

-- Tabla de configuración de la elección
CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_eleccion VARCHAR(255) NOT NULL DEFAULT 'Elecciones Estudiantiles 2026',
    fecha_inicio DATETIME,
    fecha_fin DATETIME,
    activa TINYINT(1) DEFAULT 1,
    logo_url VARCHAR(500) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO configuracion (nombre_eleccion, fecha_inicio, fecha_fin) VALUES
('Elecciones de Gobierno Estudiantil 2026', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY));

-- Tabla de usuarios (administradores y votantes)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    email VARCHAR(200) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'votante') NOT NULL DEFAULT 'votante',
    grado VARCHAR(50),
    seccion VARCHAR(10),
    codigo_estudiantil VARCHAR(20) UNIQUE,
    foto_url VARCHAR(500) DEFAULT '',
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin por defecto: admin@elecciones.edu / admin123
INSERT INTO usuarios (nombre, email, password, rol, codigo_estudiantil) VALUES
('Administrador del Sistema', 'admin@elecciones.edu', '$2y$10$HbKunmmWwqeLpDpAcnZwieVVO69DwJgiNX/X.XsxNpz3lKKJzorEu', 'admin', 'ADM001');

-- Tabla de partidos/movimientos estudiantiles
CREATE TABLE partidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    slogan VARCHAR(500),
    color VARCHAR(7) DEFAULT '#d4a520',
    logo_url VARCHAR(500) DEFAULT '',
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de cargos disputados
CREATE TABLE cargos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion VARCHAR(500),
    orden INT DEFAULT 0
);

INSERT INTO cargos (nombre, descripcion, orden) VALUES
('Presidente/a', 'Máxima representación del gobierno estudiantil', 1),
('Vicepresidente/a', 'Suplencia y apoyo al presidente/a', 2),
('Secretario/a General', 'Gestión documental y comunicaciones', 3),
('Tesorero/a', 'Administración de fondos estudiantiles', 4),
('Vocal 1', 'Representante de actividades culturales', 5),
('Vocal 2', 'Representante de actividades deportivas', 6);

-- Tabla de candidatos
CREATE TABLE candidatos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    partido_id INT,
    cargo_id INT NOT NULL,
    propuesta TEXT,
    foto_url VARCHAR(500) DEFAULT '',
    numero_candidato INT,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (partido_id) REFERENCES partidos(id) ON DELETE SET NULL,
    FOREIGN KEY (cargo_id) REFERENCES cargos(id) ON DELETE CASCADE
);

-- Tabla de votos
CREATE TABLE votos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    votante_id INT NOT NULL,
    candidato_id INT NOT NULL,
    cargo_id INT NOT NULL,
    fecha_voto TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    UNIQUE KEY unique_voto_cargo (votante_id, cargo_id),
    FOREIGN KEY (votante_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (candidato_id) REFERENCES candidatos(id) ON DELETE CASCADE,
    FOREIGN KEY (cargo_id) REFERENCES cargos(id) ON DELETE CASCADE
);

-- Tabla de auditoría
CREATE TABLE auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(255) NOT NULL,
    detalles TEXT,
    ip_address VARCHAR(45),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Vistas útiles
CREATE OR REPLACE VIEW v_resultados AS
SELECT
    c.id AS candidato_id,
    u.nombre AS nombre_candidato,
    u.foto_url,
    ca.nombre AS cargo,
    ca.id AS cargo_id,
    p.nombre AS partido,
    p.color AS partido_color,
    COUNT(v.id) AS total_votos,
    (SELECT COUNT(*) FROM usuarios WHERE rol = 'votante' AND activo = 1) AS total_votantes
FROM candidatos c
JOIN usuarios u ON c.usuario_id = u.id
JOIN cargos ca ON c.cargo_id = ca.id
LEFT JOIN partidos p ON c.partido_id = p.id
LEFT JOIN votos v ON c.id = v.candidato_id
WHERE c.activo = 1
GROUP BY c.id, u.nombre, u.foto_url, ca.nombre, ca.id, p.nombre, p.color;
