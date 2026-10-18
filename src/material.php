<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/repos/MaterialRepo.php';

$id = (int)($_GET['id'] ?? 0);
$repo = new MaterialRepo();
$material = $repo->findById($id);

if (!$material) {
    http_response_code(404);
    exit('Material no encontrado');
}
$usuarioId = (int) currentUser()['id'];

$prestamoEsMio = $repo->tienePrestamoActivo(
    $material->getId(),
    $usuarioId
);
require __DIR__ . '/includes/header.php';
?>
<h1><?= htmlspecialchars($material->mostrarFicha()) ?></h1>

<dl>
  <dt>Tipo</dt><dd><?= htmlspecialchars($material->tipoMaterial()) ?></dd>
  <dt>Autor</dt><dd><?= htmlspecialchars($material->getAutor()) ?></dd>
  <dt>Año</dt><dd><?= $material->getAnio() ?? '—' ?></dd>
  <dt>Antigüedad</dt><dd><?= $material->antiguedad() ?> años</dd>
  <dt>Días de préstamo</dt><dd><?= $material->diasPrestamo() ?></dd>
  <dt>Añadido al catálogo</dt><dd><?= $material->fechaFormateada() ?></dd>
  <dt>Estado</dt><dd><?= $material->isDisponible() ? 'Disponible' : 'Prestado' ?></dd>
</dl>




<?php if ($material->isDisponible()): ?>

    <form method="POST" action="prestar.php">
        <input
            type="hidden"
            name="material_id"
            value="<?= (int) $material->getId() ?>"
        >
        <button type="submit">Prestar material</button>
    </form>

<?php elseif ($prestamoEsMio): ?>

    <form method="POST" action="devolver.php">
        <input
            type="hidden"
            name="material_id"
            value="<?= (int) $material->getId() ?>"
        >
        <button type="submit">Devolver material</button>
    </form>

<?php else: ?>

    <p>Este material está prestado a otro usuario.</p>

<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>