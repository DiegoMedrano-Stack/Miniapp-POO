<?php
require_once __DIR__ . '/Prestable.php';
require_once __DIR__ . '/Prestable.php';
require_once __DIR__ . '/traits/TieneFechas.php';
require_once __DIR__ . '/traits/Loggable.php';

// src/classes/MaterialBiblioteca.php

class MaterialBiblioteca implements Prestable {
    use TieneFechas, Loggable;

    private int $id;
    private string $titulo;
    private string $autor;
    private ?int $anio;
    private bool $disponible;
    private string $editorial = '';

    public function __construct(
        string $titulo,
        string $autor,
        ?int $anio = null,
        bool $disponible = true,
        int $id = 0
    ) {
        $this->titulo     = $titulo;
        $this->autor      = $autor;
        $this->anio       = $anio;
        $this->disponible = $disponible;
        $this->id         = $id;
    }

    public function getId(): int          { return $this->id; }
    public function getTitulo(): string   { return $this->titulo; }
    public function getAutor(): string    { return $this->autor; }
    public function getAnio(): ?int       { return $this->anio; }
    public function setAnio(?int $a): void { $this->anio = $a; }
    public function setAutor(string $a): void { $this->autor = $a; }
    public function isDisponible(): bool  { return $this->disponible; }
    public function getEditorial(): string { return $this->editorial; }
    public function setEditorial(string $e): void { $this->editorial = $e; }
    public function setTitulo(string $t): void { $this->titulo = $t; }
    public function setDisponible(bool $d): void { $this->disponible = $d; }
    public function resumen(): string {
    return $this->tipoMaterial() . ': ' . $this->titulo;
    }
    public function mostrarFicha(): string {
        $anio = $this->anio ?? 's/f';
        return sprintf('[%s] %s — %s (%s)',
            $this->tipoMaterial(), $this->titulo, $this->autor, $anio
        );
    }
   


    public function tipoMaterial(): string {
        return 'material';
    }
    public function findDisponibles(): array {
    $stmt = db()->query('SELECT * FROM materiales WHERE disponible = 1 ORDER BY titulo');
    $out = [];
    foreach ($stmt->fetchAll() as $fila) {
        $out[] = $this->hidratar($fila);
    }
    return $out;
}public function findByTipo(string $tipo): array {
    $stmt = db()->prepare('SELECT * FROM materiales WHERE tipo = ? ORDER BY titulo');
    $stmt->execute([$tipo]);
    $out = [];
    foreach ($stmt->fetchAll() as $fila) {
        $out[] = $this->hidratar($fila);
    }
    return $out;
}public function contarPorTipo(): array {
    $stmt = db()->query('SELECT tipo, COUNT(*) AS total FROM materiales GROUP BY tipo');
    $out = [];
    foreach ($stmt->fetchAll() as $fila) {
        $out[$fila['tipo']] = (int)$fila['total'];
    }
    return $out;
}public function crear(MaterialBiblioteca $m): int {
    $tipo = match (true) {
        $m instanceof Libro   => 'libro',
        $m instanceof Revista => 'revista',
        $m instanceof DVD     => 'dvd',
        default               => throw new InvalidArgumentException('Tipo no soportado'),
    };

    $isbn   = $m instanceof Libro   ? $m->getIsbn()        : null;
    $numero = $m instanceof Revista ? $m->getNumero()      : null;
    $dur    = $m instanceof DVD     ? $m->getDuracionMin() : null;

    $stmt = db()->prepare(
        'INSERT INTO materiales (tipo, titulo, autor, anio, isbn, numero, duracion_min, disponible)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $tipo, $m->getTitulo(), $m->getAutor(), $m->getAnio(),
        $isbn, $numero, $dur, $m->isDisponible() ? 1 : 0
    ]);
    return (int)db()->lastInsertId();
}

    public function antiguedad(): int {
        if ($this->anio === null) return 0;
        return (int)date('Y') - $this->anio;
    }

    public function prestar(): bool {
         if (!$this->disponible) {
        $this->log("Intento fallido de préstamo: {$this->titulo}");
        return false;
    }
    $this->disponible = false;
    $this->log("Préstamo realizado: {$this->titulo}");
    return true;
}   

    public function devolver(): void { $this->disponible = true; }

    public function diasPrestamo(): int { return 15; }

    public function puedePrestarse(): bool {
    return $this->disponible
        && $this->anio !== null
        && $this->anio < 2010;
}
}