# TORNEO-UNI: Reserva de espacios (Fase 2 · Caso A)

Aplicacion web en PHP para administrar los espacios del complejo (salas de
reunion, escritorios de trabajo y canchas deportivas) y las reservas que se
hacen sobre ellos, con un CRUD completo, imagenes propias, validacion en el
servidor y cliente, y un reporte web que calcula las tarifas de forma
polimorfica.

| Panel inicial | Reporte web del dia |
| --- | --- |
| ![Panel](docs/capturas/panel.png) | ![Reporte](docs/capturas/reporte.png) |

| Listado de espacios | Formulario de registro |
| --- | --- |
| ![Espacios](docs/capturas/espacios-listado.png) | ![Formulario](docs/capturas/espacios-formulario.png) |

| Ficha del espacio | Listado de reservas |
| --- | --- |
| ![Ficha](docs/capturas/espacios-ficha.png) | ![Reservas](docs/capturas/reservas-listado.png) |

## Requisitos

- PHP 8.1 o superior (probado con PHP 8.2.12 de XAMPP)
- Composer 2
- MySQL o MariaDB (probado con MariaDB 10.4.32 de XAMPP / MySQL 9.3)
- Un servidor web local: el embebido de PHP basta

## Instalacion y ejecucion

### 1. Dependencias (PSR-4)

```bash
composer install
```

Si ya existia el directorio `vendor/`, basta con:

```bash
composer dump-autoload -o
```

### 2. Base de datos

Crea la base `reserva_espacios` con sus datos de prueba. Cualquiera de las
dos opciones es valida:

**Opcion A - PHPMyAdmin (recomendada):**

1. Abre `http://localhost/phpmyadmin`.
2. Pestaña **Importar**: selecciona `database/schema.sql` y ejecutar.
3. Repite el paso con `database/seed.sql`.

**Opcion B - linea de comandos:**

```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed.sql
```

`schema.sql` crea la base, las tablas `espacios` (tabla unica con la columna
`tipo` y constraints `CHECK`) y `reservas` (con clave foranea
`ON DELETE RESTRICT`). `seed.sql` inserta 3 espacios por tipo y 10 reservas,
varias con la fecha actual para que el panel y el reporte muestren datos.

### 3. Configuracion

Copia el ejemplo y ajusta tus credenciales (host, puerto, usuario y clave):

```bash
copy config\config.example.php config\config.php    # Windows
# cp config/config.example.php config/config.php    # Linux/macOS
```

`config/config.php` esta en `.gitignore`: no se sube al repositorio.

### 4. Servidor local

Desde la raiz del proyecto:

```bash
php -S 127.0.0.1:8085 -t public
```

Abre <http://127.0.0.1:8085/index.php>. Si usas XAMPP/Apache, en su lugar
puedes apuntar el VirtualHost a la carpeta `public/` (todo lo accesible por
HTTP vive ahi; `src/`, `views/`, `config/` y `database/` quedan fuera).

> El directorio `public/uploads/` debe ser escribible: ahi se guardan las
> imagenes con un nombre aleatorio generado por el sistema.

## Pruebas

```bash
php main.php          # demo de escritorio de la Fase 1 (JSON + polimorfismo)
php tests/smoke.php   # se espera SMOKE_TEST_OK
```

Pruebas web end-to-end (requieren el servidor en el puerto 8085 y la base
cargada con el seed; se ejecutan desde la raiz y terminan con `FALLOS=0`):

```bash
powershell -ExecutionPolicy Bypass -File tests/e2e/web-espacios.ps1
powershell -ExecutionPolicy Bypass -File tests/e2e/web-reservas-imagenes.ps1
```

Cubren mas de 100 comprobaciones: paginas HTTP 200 sin errores de PHP,
HTML5 semantico, token CSRF (ademas de un POST con token invalido), PRG,
errores de validacion por campo, alta/edicion/baja de espacios con imagen
(subida valida, MIME falso, archivo de mas de 2 MB, reemplazo y borrado del
archivo viejo), eliminacion protegida por reservas asociadas, rechazo de
reservas traslapadas y de horarios invertidos, reporte por fecha y escape de
`htmlspecialchars` con nombres que contienen etiquetas HTML.

## Estructura

```
config/          config.example.php (se sube) y config.php local (no se sube)
database/        schema.sql y seed.sql
docs/capturas/   capturas de pantalla usadas en README e informe
public/          unico punto de entrada HTTP: paginas, css, js, uploads/
src/             Codigo fuente (PSR-4 App\): bootstrap, modelos, fabricas,
                 repositorios, validacion, servicios y helpers
tests/           smoke.php (CLI) y e2e/ (pruebas web)
views/           Plantillas HTML (layout, parciales y vistas de cada modulo)
```

## Seguridad y buenas practicas aplicadas

- Todas las consultas usan PDO con consultas preparadas (sin concatenar datos
  del usuario).
- Tokens CSRF en cada formulario POST, verificados en el servidor.
- Patron PRG (Post/Redirect/Get) con mensajes flash en todas las acciones.
- Validacion en el servidor (clase `Validador`) y mejora progresiva en el
  cliente (`public/js/formulario.js`); las vistas escapan con `e()`.
- Imagenes: tamano maximo 2 MB, tipo MIME real por `finfo`, nombre aleatorio
  generado por el sistema, y imagen por defecto (`public/img/sin-imagen.svg`)
  cuando no hay fotografia.
- El polimorfismo se concentra en `EspacioFactory`: las vistas no usan
  `instanceof`, `get_class()` ni condicionales por tipo concreto.

## Evidencia para la exposicion (etiquetas `[CONCEPTO]`)

- **[ABSTRACCION]** `Espacio` abstracta con `etiquetaTipo()` y `datoCalculado()`;
  contrato `Reservable`.
- **[ENCAPSULAMIENTO]** propiedades `private/protected/readonly` y getters.
- **[HERENCIA]** `Sala`, `Escritorio` y `Cancha` extienden `Espacio`.
- **[POLIMORFISMO]** `calcularCosto()` y `datoCalculado()` resuelven cada tipo
  sin condicionales en el llamador (listados, fichas y reporte).
- **[INTERFAZ]** `Reservable` y `Exportable` definen contratos.
- **[COMPOSICION]** `Reserva` conoce a `Espacio`; `ReservaRepositorio` recibe
  `EspacioRepositorio` y `GestorImagenes` por constructor.
- **[FABRICA]** `EspacioFactory` y `ReservaFactory` crean objetos desde la fila
  de la base o desde el formulario.
- **[INYECCION-DEPENDENCIAS]** repositorios y servicios reciben `PDO` y
  colaboradores por constructor.
- **[CRUD-CREATE/READ/UPDATE/DELETE]** `EspacioRepositorio` y
  `ReservaRepositorio`.
- **[SEGURIDAD]** CSRF, consultas preparadas, nombres aleatorios de imagen,
  escape de salida.
- **[VALIDACION]** `Validador` (servidor) + `public/js/formulario.js` (cliente).
- **[PRG]** todas las acciones POST redirigen con mensajes flash.

## Despliegue

Etiqueta final:

```bash
git fetch --tags
git checkout v2.0
composer install --no-dev
```

Copia de ejemplo en cada equipo: `config/config.example.php` → `config/config.php`.
