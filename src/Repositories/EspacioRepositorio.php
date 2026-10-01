<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Espacios\Espacio;
use App\Factories\EspacioFactory;
use PDO;

/**
 * Acceso a datos de la entidad Espacio. Recibe la conexion PDO por
 * constructor (inyeccion de dependencias) y nunca construye SQL con datos
 * del usuario: todas las consultas van preparadas. [SEGURIDAD]
 */
final class EspacioRepositorio
{
    public function __construct(private PDO $pdo) // [INYECCION-DEPENDENCIAS]
    {
    }

    public function crear(Espacio $espacio): int // [CRUD-CREATE]
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO espacios (tipo, nombre, capacidad, tarifa_base, imagen, ubicacion, zona, deporte) '
            . 'VALUES (:tipo, :nombre, :capacidad, :tarifa_base, :imagen, :ubicacion, :zona, :deporte)'
        );

        $stmt->execute(EspacioFactory::filaPara($espacio));

        return (int) $this->pdo->lastInsertId();
    }

    /** @return Espacio[] */
    public function listar(): array // [CRUD-READ]
    {
        $filas = $this->pdo
            ->query('SELECT * FROM espacios ORDER BY nombre')
            ->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            static fn (array $fila): Espacio => EspacioFactory::desdeFila($fila),
            $filas
        );
    }

    public function buscarPorId(int $id): ?Espacio // [CRUD-READ]
    {
        $stmt = $this->pdo->prepare('SELECT * FROM espacios WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila === false ? null : EspacioFactory::desdeFila($fila);
    }

    /** @return Espacio[] */
    public function listarPorTipo(string $tipo): array // [CRUD-READ]
    {
        $stmt = $this->pdo->prepare('SELECT * FROM espacios WHERE tipo = :tipo ORDER BY nombre');
        $stmt->execute(['tipo' => $tipo]);

        return array_map(
            static fn (array $fila): Espacio => EspacioFactory::desdeFila($fila),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * Totales por tipo para el panel (la etiqueta la resuelve la fabrica).
     *
     * @return list<array{tipo: string, total: int}>
     */
    public function contarPorTipo(): array
    {
        $filas = $this->pdo
            ->query('SELECT tipo, COUNT(*) AS total FROM espacios GROUP BY tipo ORDER BY tipo')
            ->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            static fn (array $fila): array => [
                'tipo' => (string) $fila['tipo'],
                'total' => (int) $fila['total'],
            ],
            $filas
        );
    }

    public function contar(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM espacios')->fetchColumn();
    }

    public function actualizar(Espacio $espacio): void // [CRUD-UPDATE]
    {
        $stmt = $this->pdo->prepare(
            'UPDATE espacios SET tipo = :tipo, nombre = :nombre, capacidad = :capacidad, '
            . 'tarifa_base = :tarifa_base, imagen = :imagen, ubicacion = :ubicacion, '
            . 'zona = :zona, deporte = :deporte WHERE id = :id'
        );

        $datos = EspacioFactory::filaPara($espacio);
        $datos['id'] = $espacio->getId();

        $stmt->execute($datos);
    }

    public function eliminar(int $id): void // [CRUD-DELETE]
    {
        $stmt = $this->pdo->prepare('DELETE FROM espacios WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** El FK ON DELETE RESTRICT exige conocer si el espacio tiene reservas. */
    public function tieneReservas(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM reservas WHERE espacio_id = :id');
        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn() > 0;
    }
}
