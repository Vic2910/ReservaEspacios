# TORNEO-UNI: Reserva de espacios deportivos

Primer avance del proyecto para el complejo deportivo universitario. El sistema permite registrar reservas de una cancha de futbol, una cancha de tenis y una piscina, aplicando tarifas distintas por tipo de espacio y recargo en horario pico.

## Requisitos

- PHP 8.2 o superior
- Composer 2
- Git

## Instalacion y ejecucion

```bash
composer install
composer dump-autoload
php main.php
```

El programa muestra reservas reales de prueba, calcula el costo mediante polimorfismo, guarda el reporte en `data/reservas.json` y demuestra una validacion lanzando una excepcion.

## Estructura

- `src/Contratos/`: interfaces `Reservable` y `Exportable`.
- `src/Espacios/`: clase abstracta `Espacio` y sus subclases deportivas.
- `src/Entidades/`: entidad `Reserva` con datos encapsulados.
- `src/Servicios/`: lectura y escritura del archivo JSON.
- `main.php`: punto de entrada de la demostracion.

## Evidencia para la exposicion

- **Abstraccion:** `Espacio` es abstracta y define `calcularCosto`; las interfaces definen contratos.
- **Encapsulamiento:** propiedades `private`, `protected`, `readonly` y validaciones con `InvalidArgumentException`.
- **Herencia:** `CanchaFutbol` extiende `Espacio` e invoca `parent::__construct()`.
- **Polimorfismo:** `main.php` trabaja con reservas de espacios heterogeneos; cada subclase resuelve `calcularCosto()` con su propia regla, sin `instanceof` ni `switch` por tipo.
- **Archivos:** `ArchivoReservas` serializa las reservas a JSON y las vuelve a leer.
