<?php
require __DIR__ . '/config.php';
if (!empty($_SESSION['id_user'])) audit($pdo, (int)$_SESSION['id_user'], 'LOGOUT');
$_SESSION = []; session_destroy();
header('Location: index.php');
