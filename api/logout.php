<?php
require_once __DIR__ . '/../config/db.php';
logAuditoria('LOGOUT', 'Cierre de sesión');
session_destroy();
header('Location: ../login.php');
exit;
