<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../classes/Libro.php';
require_once __DIR__ . '/../classes/Revista.php';
require_once __DIR__ . '/../classes/DVD.php';

class MaterialRepo {

    public function findAll(): array {
        $stmt = db()->query('SELECT * FROM materiales ORDER BY tipo, titulo');
        $materiales = [];
        foreach ($stmt->fetchAll() as $fila) {
            $materiales[] = $this->hidratar($fila);
        }
        return $materiales;
    }

    public function findById(int $id): ?MaterialBiblioteca {
        $stmt = db()->prepare('SELECT * FROM materiales WHERE id = ?');
        $stmt->execute([$id]);
        $fila = $stmt->fetch();
        return $fila ? $this->hidratar($fila) : null;
    }

    /**
     * Convierte una fila de BBDD en el objeto PHP correcto.
     * También hidrata la fecha de creación desde 'creado_en'.
     */
    private function hidratar(array $fila): MaterialBiblioteca {
        $material = match ($fila['tipo']) {
            'libro'   => new Libro(
                $fila['titulo'], $fila['autor'], (int)$fila['anio'],
                $fila['isbn'] ?? '', (bool)$fila['disponible'], (int)$fila['id']
            ),
            'revista' => new Revista(
                $fila['titulo'], $fila['autor'], (int)$fila['anio'],
                (int)$fila['numero'], (bool)$fila['disponible'], (int)$fila['id']
            ),
            'dvd'     => new DVD(
                $fila['titulo'], $fila['autor'], (int)$fila['anio'],
                (int)$fila['duracion_min'], (bool)$fila['disponible'], (int)$fila['id']
            ),
            default   => throw new RuntimeException("Tipo desconocido: {$fila['tipo']}"),
        };
        $material->setFechaCreacion($fila['creado_en'] ?? null);
        return $material;
    }

    public function setDisponible(int $id, bool $disponible): void {
        $stmt = db()->prepare('UPDATE materiales SET disponible = ? WHERE id = ?');
        $stmt->execute([$disponible ? 1 : 0, $id]);
    }

    public function registrarPrestamo(int $materialId, int $usuarioId): void {
        $stmt = db()->prepare(
            'INSERT INTO prestamos (material_id, usuario_id) VALUES (?, ?)'
        );
        $stmt->execute([$materialId, $usuarioId]);
    }
}