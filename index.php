<?php
require_once 'config/db.php';

// Si ya está logueado, redirigir
if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'admin/dashboard.php' : 'votacion.php'));
    exit;
}

header('Location: login.php');
exit;
