# 🗳️ Sistema de Elecciones Estudiantiles

Sistema web completo para la gestión de elecciones de gobierno estudiantil en centros educativos.

![Diseño](https://img.shields.io/badge/Diseño-Glassmorphism%20%2B%20Dorado-blue)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-purple)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange)
![Bootstrap](https://img.shields.io/badge/Frontend-Custom%20CSS-gold)

## ✨ Características

- 🎨 Diseño moderno con glassmorphism y paleta azul profundo + dorado
- 🔐 Autenticación segura (admin y votantes)
- 🗳️ Sistema de votación por cargo con verificación de voto único
- 📊 Resultados en tiempo actualizándose cada 10 segundos
- 🎉 Animaciones de confetti al votar
- 📱 100% responsive
- ⚡ Dashboard admin con estadísticas en vivo
- 🏛️ Gestión de candidatos, partidos y votantes
- 📝 Auditoría de todas las acciones

## 🚀 Instalación

### Requisitos
- PHP 8.0+
- MySQL 8.0+
- Apache/Nginx

### Pasos

1. Clonar el repositorio
```bash
git clone https://github.com/tu-usuario/elecciones-estudiantiles.git
cd elecciones-estudiantiles
```

2. Configurar la base de datos en `config/db.php`
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'elecciones_estudiantiles');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
```

3. Importar la base de datos
```bash
mysql -u root -p < database.sql
```

4. Abrir en el navegador
```
http://localhost/elecciones-estudiantiles/
```

### Credenciales por defecto
| Rol | Email | Contraseña |
|-----|-------|------------|
| Admin | admin@elecciones.edu | admin123 |

## 📁 Estructura del Proyecto

```
elecciones-estudiantiles/
├── config/
│   └── db.php              # Conexión y autenticación
├── api/
│   ├── login.php           # Autenticación
│   ├── registro.php        # Registro de votantes
│   ├── logout.php          # Cierre de sesión
│   ├── votar.php           # Emisión de votos
│   ├── resultados.php      # Resultados en vivo
│   ├── estadisticas.php    # Estadísticas del dashboard
│   ├── candidatos.php      # CRUD candidatos
│   ├── partidos.php        # CRUD partidos
│   ├── cargos.php          # Lista de cargos
│   ├── usuarios.php        # Gestión de votantes
│   └── mis_votos.php       # Votos del usuario actual
├── admin/
│   ├── dashboard.php       # Panel de administración
│   ├── candidatos.php      # Gestión candidatos
│   └── votantes.php        # Gestión votantes
├── assets/
│   ├── css/style.css       # Estilos (glassmorphism)
│   └── js/app.js           # JavaScript interactivo
├── login.php               # Login/Registro
├── votacion.php            # Página de votación
├── resultados.php          # Resultados públicos
├── database.sql            # Esquema de base de datos
└── README.md
```

## 🛠️ Tecnologías

- **Backend:** PHP 8+ con PDO
- **Base de datos:** MySQL 8+
- **Frontend:** HTML5, CSS3 (Glassmorphism), JavaScript Vanilla
- **Tipografía:** Poppins + Inter (Google Fonts)
- **Sin frameworks** — 100% código limpio y portable

## 📄 Licencia

MIT
