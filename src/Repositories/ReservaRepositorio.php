<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Entidades\Reserva;
use App\Espacios\Espacio;
use App\Factories\ReservaFactory;
use PDO;

/**
 * Acceso a datos de la entidad relacionada Reserva. Recibe la conexion PDO
 * y el repositorio de espacios por constructor (inyeccion de dependencias)
 * y usa exclusivamente consultas preparadas. [SEGURIDAD]
 */
final class ReservaRepositorio
{
    public function __construct(
        private PDO $pdo,
        private EspacioRepositorio $espacios
    ) { // [INYECCION-DEPENDENCIAS]
    }

    public function crear(Reserva $reserva): int // [CRUD-CREATE]
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO reservas (espacio_id, fecha, hora_inicio, hora_fin, cliente) '
            . 'VALUES (:espacio_id, :fecha, :hora_inicio, :hora_fin, :cliente)'
        );

        $stmt->execute($this->parametros($reserva));

        return (int) $this->pdo->lastInsertId();
    }

    public function actualizar(Reserva $reserva): void // [CRUD-UPDATE]
    {
        $stmt = $this->pdo->prepare(
            'UPDATE reservas SET espacio_id = :espacio_id, fecha = :fecha, '
            . 'hora_inicio = :hora_inicio, hora_fin = :hora_fin, cliente = :cliente '
            . 'WHERE id = :id'
        );

        $parametros = $this->parametros($reserva);
        $parametros['id'] = $reserva->getId();

        $stmt->execute($parametros);
    }

    public function eliminar(int $id): void // [CRUD-DELETE]
    {
        $stmt = $this->pdo->prepare('DELETE FROM reservas WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** @return Reserva[] */
    public function listar(): array // [CRUD-READ]
    {
        $filas = $this->pdo
            ->query('SELECT * FROM reservas ORDER BY fecha DESC, hora_inicio ASC, id ASC')
            ->fetchAll(PDO::FETCH_ASSOC);

        return $this->construir($filas);
    }

    /** @return Reserva[] */
    public function listarPorFecha(string $fecha): array // [CRUD-READ]
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM reservas WHERE fecha = :fecha ORDER BY hora_inicio ASC, id ASC'
        );
        $stmt->execute(['fecha' => $fecha]);

        return $this->construir($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function buscarPorId(int $id): ?Reserva // [CRUD-READ]
    {
        $stmt = $this->pdo->prepare('SELECT * FROM reservas WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $reservas = $this->construir($filas);

        return $reservas[0] ?? null;
    }

    /** @return Reserva[] */
    public function listarPorEspacio(int $espacioId): array // [CRUD-READ]
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM reservas WHERE espacio_id = :espacio_id '
            . 'ORDER BY fecha ASC, hora_inicio ASC'
        );
        $stmt->execute(['espacio_id' => $espacioId]);

        return $this->construir($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function contar(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM reservas')->fetchColumn();
    }

    public function contarPorFecha(string $fecha): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM reservas WHERE fecha = :fecha');
        $stmt->execute(['fecha' => $fecha]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Rechaza reservas traslapadas en el mismo espacio: dos intervalos se
     * pegan cuando el inicio de uno es menor que el fin del otro. [VALIDACION]
     */
    public function existeTraslape(
        int $espacioId,
        string $fecha,
        string $horaInicio,
        string $horaFin,
        ?int $excluirId = null
    ): bool {
        if ($excluirId === null) {
            $stmt = $this->pdo->prepare(
                'SELECT COUNT(*) FROM reservas WHERE espacio_id = :espacio '
                . 'AND fecha = :fecha AND hora_inicio < :hora_fin AND hora_fin > :hora_inicio'
            );
            $stmt->execute([
                'espacio' => $espacioId,
                'fecha' => $fecha,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
            ]);
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT COUNT(*) FROM reservas WHERE espacio_id = :espacio '
                . 'AND fecha = :fecha AND hora_inicio < :hora_fin AND hora_fin > :hora_inicio '
                . 'AND id <> :excluir'
            );
            $stmt->execute([
                'espacio' => $espacioId,
                'fecha' => $fecha,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'excluir' => $excluirId,
            ]);
        }

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * @param list<array<string, mixed>> $filas
     * @return Reserva[]
     */
    private function construir(array $filas): array
    {
        if ($filas === []) {
            return [];
        }

        // Dos consultas en total: los espacios se resuelven una sola vez y
        // la fabrica convierte cada fila en su objeto de dominio. [FABRICA]
        $mapa = [];

        foreach ($this->espacios->listar() as $espacio) {
            $mapa[$espacio->getId()] = $espacio;
        }

        $reservas = [];

        foreach ($filas as $fila) {
            $espacio = $mapa[(int) $fila['espacio_id']] ?? null;

            if ($espacio === null) {
                continue;
            }

            $reservas[] = ReservaFactory::desdeFila($fila, $espacio);
        }

        return $reservas;
    }

    /** @return array<string, mixed> */
    private function parametros(Reserva $reserva): array
    {
        return [
            'espacio_id' => $reserva->getEspacio()->getId(),
            'fecha' => $reserva->getFecha(),
            'hora_inicio' => $reserva->getHoraInicio(),
            'hora_fin' => $reserva->getHoraFin(),
            'cliente' => $reserva->getCliente(),
        ];
    }
}
