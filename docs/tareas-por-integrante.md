# Tareas por integrante (Fase 2)

Objetivo: cada integrante del equipo **sube sus propios commits con su usuario
de Git** para que el historial refleje el aporte de los cinco (la rúbrica pide
**5 commits por integrante en 3 o más días**).

Reglas del equipo (importantes):

1. **Nunca reescribas fechas ni autorías** (`git commit --date=...` está
   prohibido): los commits se hacen el día que se hacen.
2. Cada quien configura su identidad **una sola vez** en su equipo:

   ```bash
   git config user.name  "Nombre y apellido"
   git config user.email "correo@ejemplo.com"
   git clone https://github.com/Vic2910/ReservaEspacios.git
   git checkout main
   ```

3. Haz **1 tarea por día o sesión** (o máximo 2): así alcanzan ≥3 días
   distintos con calma.
4. Antes de commitear, verifica con los comandos indicados en cada tarea.
5. Súbelos con `git push origin main`. Si un commit falla el estilo del repo,
   corrige el mensaje con `git commit --amend` **solo si aún no se subió**.

Mensajes: Conventional Commits (`feat`, `fix`, `docs`, `test`, `style`,
`refactor`) + la etiqueta `[CONCEPTO]` que se indica en cada tarea.

---

## Gabriela Abigail Díaz Rivera — validación en cliente y accesibilidad

| # | Tarea | Archivos | Verificación | Mensaje sugerido |
|---|-------|----------|--------------|------------------|
| 1 | Mientras el usuario escribe, quitarle la clase de error a un campo que ya quedó válido (borrar `.campo-error` y su `<small>` cuando el valor cumple la regla: no vacío, capacidad/tarifa > 0, hora fin > hora inicio). | `public/js/formulario.js` | Abrir `/espacios/crear.php`, escribir y borrar en *Nombre* con la consola abierta: no debe quedar rojo. | `fix(js): limpiar el error del campo cuando el usuario lo corrige [VALIDACION]` |
| 2 | Validar en el navegador el peso de la imagen antes de enviar: si `input.files[0].size` supera 2 MB, mostrar «La imagen supera el tamano maximo permitido de 2 MB.» junto al campo y bloquear el envío. | `public/js/formulario.js` | Probar con un archivo grande; debe salir el mensaje sin llegar al servidor. | `feat(js): validar el tamano de la imagen en el cliente [VALIDACION]` |
| 3 | Avisar con `aria-live="polite"` los mensajes flash de éxito/error para que lector de pantalla los anuncie. | `views/partials/mensajes.php` | Inspeccionar el HTML del aviso: debe incluir `role="alert"` o `aria-live`. | `feat(vistas): anunciar los mensajes flash con aria-live [VALIDACION]` |
| 4 | Relacionar cada error con su campo usando `aria-describedby` apuntando al `<small class="mensaje-error">`. | `views/espacios/formulario.php`, `views/reservas/formulario.php` | Enviar el formulario vacío y revisar la inspección: cada input debe apuntar a su mensaje. | `feat(form): vincular los mensajes de error con aria-describedby [VALIDACION]` |
| 5 | Pedir `confirm()` al cambiar el *Tipo de espacio* si el usuario ya escribió campos específicos (se perderían al ocultarse). | `public/js/formulario.js` | Llenar *Ubicación*, cambiar el tipo a *Cancha*: debe preguntar antes de limpiar. | `feat(js): confirmar el cambio de tipo cuando hay datos cargados [VALIDACION]` |

## Victor Manuel Mira Hernández — contenido, ayudas y datos de prueba

| # | Tarea | Archivos | Verificación | Mensaje sugerido |
|---|-------|----------|--------------|------------------|
| 1 | Agregar en el formulario de reserva una línea de ayuda: «Las reservas que empiecen a partir de las 17:00 tienen recargo según el tipo de espacio.» | `views/reservas/formulario.php` | Abrir `/reservas/crear.php` y ver la línea bajo *Hora de inicio*. | `feat(reservas): explicar el horario pico en el formulario [VALIDACION]` |
| 2 | En la ficha del espacio, mostrar junto a la tarifa base el precio con recargo: «S/ 120.00 por hora · pico S/ 144.00». El valor se obtiene con `calcularCosto(1.0, true)` (polimórfico, sin `instanceof`). | `views/espacios/ficha.php` | Ver `/espacios/ver.php?id=1`: debe mostrar los dos precios. | `feat(fichas): mostrar la tarifa con recargo de horario pico [POLIMORFISMO]` |
| 3 | Si el reporte del día no tiene reservas, mostrar un mensaje amigable en lugar de una tabla vacía. | `views/reporte.php` | Probar con una fecha sin reservas (`/reporte.php?fecha=2026-01-15`). | `feat(reporte): mensaje cuando no hay reservas en la fecha [CRUD-READ]` |
| 4 | Ampliar `database/seed.sql` con **dos espacios más por tipo** (misma estructura de las filas actuales, nombres nuevos, sin duplicar). | `database/seed.sql` | `mysql -u root < database/seed.sql` sin errores; el panel debe marcar 5 por tipo. **Atención:** el seed borra y repone los datos de prueba. | `feat(seed): ampliar los datos de prueba a 5 espacios por tipo` |
| 5 | Documentar en el README una sección «Problemas frecuentes»: puerto ocupado, permisos de `public/uploads/`, base sin crear. | `README.md` | Leer la sección nueva y probar un comando. | `docs(readme): soluciones a problemas frecuentes de instalacion` |

## Diego Marcelo Rivera López — reporte y pruebas

| # | Tarea | Archivos | Verificación | Mensaje sugerido |
|---|-------|----------|--------------|------------------|
| 1 | Agregar al reporte una fila de **totales** al final de la tabla «Tarifas polimorficas»: suma de «2 horas» y de «2 horas en horario pico». | `views/reporte.php` | Abrir `/reporte.php` y comprobar los totales con una suma rápida. | `feat(reporte): totales por tarifa en el reporte [CRUD-READ]` |
| 2 | Filtrar el reporte por tipo con un enlace tipo `?fecha=...&tipo=sala` (mantener el parámetro de fecha; si `tipo` no es válido, ignorarlo). | `public/reporte.php`, `views/reporte.php` | `/reporte.php?tipo=cancha` debe listar solo canchas; sin `tipo`, todas. | `feat(reporte): filtrar por tipo de espacio [CRUD-READ] [FABRICA]` |
| 3 | Añadir botón «Exportar CSV» que descargue las reservas del día (cabecera `Content-Type: text/csv; charset=utf-8`, separador `;`, sin HTML). Documentarlo después en el informe como ampliación. | `public/reporte-csv.php` (nuevo) | Abrir el enlace y comprobar que baja un `.csv` con las filas del día. | `feat(reporte): exportar el reporte del dia a CSV [CRUD-READ]` |
| 4 | Extender la suite e2e con comprobaciones de lo anterior (filtro por tipo y CSV) en el mismo estilo de los scripts existentes. | `tests/e2e/web-reservas-imagenes.ps1` | `powershell -ExecutionPolicy Bypass -File tests/e2e/web-reservas-imagenes.ps1` → `FALLOS=0`. | `test(e2e): comprobar filtro por tipo y exportacion del reporte` |
| 5 | Revisar que `php main.php` y `php tests/smoke.php` sigan pasando tras los cambios y corregir lo que rompa. | los que haga falta | Ambos comandos terminan bien. | `fix: mantener la demostracion de la Fase 1 tras los cambios` |

## Luis José Rodríguez Centeno (aporta desde la Fase 1) — revisión y defensa

1. Revisar el informe `docs/informe/informe.html` (nombres, secciones) y
   corregir lo que haga falta → `docs(informe): corregir observaciones de la revision`
2. Añadir al README una sección «Qué mirar en la defensa» (5 puntos cortos)
   → `docs(readme): guia rapida para la defensa`
3. Revisar los commits del equipo con `git log --oneline` y verificar que
   todos lleven `[CONCEPTO]` donde corresponde; si falta alguno, corregir el
   mensaje (solo si no está subido) o anotarlo para el informe.
4. Comprobar que `.gitignore` siga excluyendo `config/config.php`,
   `vendor/` y `public/uploads/*` → `chore(git): verificar el gitignore`
5. Correr la suite e2e una vez y reportar resultados al equipo
   → `test(e2e): verificacion del equipo antes de la presentacion`

## Victor Rafael Arévalo Sierra — cierre

1. Tras recibir los commits de todos: `git pull` y volver a correr
   `php tests/smoke.php` + las dos suites e2e.
2. Actualizar la sección 9 del informe con lo que cada quien realmente subió
   (coherente con `git shortlog -sne`).
3. Si el equipo lo decide, mover la etiqueta `v2.0` al final
   (`git tag -fa v2.0 -m "v2.0 con aportes del equipo" && git push -f origin v2.0`)
   — solo con el visto bueno de todos, y **nunca** alterando fechas.

---

### Resumen esperado al final

| Integrante | Commits mínimos | Días distintos |
|---|---|---|
| Gabriela Abigail Díaz Rivera | 5 | ≥3 |
| Victor Manuel Mira Hernández | 5 | ≥3 |
| Diego Marcelo Rivera López | 5 | ≥3 |
| Luis José Rodríguez Centeno | 5 (ya tiene 5 de la Fase 1) | ≥3 en total |
| Victor Rafael Arévalo Sierra | ≥5 (ya los tiene) | ≥3 |

Comprobación: `git shortlog -sne` y `git log --format="%ad %an" --date=short`.
