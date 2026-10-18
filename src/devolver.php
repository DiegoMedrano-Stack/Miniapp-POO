<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/repos/MaterialRepo.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int)($_POST['material_id'] ?? 0);
$usuarioId = (int)currentUser()['id'];

$repo = new MaterialRepo();

if ($repo->devolverPrestamo($id, $usuarioId)) {
    header('Location: material.php?id=' . $id);
    exit;
}

http_response_code(400);
echo 'No tienes un préstamo activo de este material.';