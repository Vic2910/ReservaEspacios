# =====================================================================
# ReservaEspacios - pruebas web end-to-end (parte 2: imagenes, borrado,
# reservas traslapadas, reporte con filtro por tipo, exportacion CSV y
# escape de salida)
# Requisitos: base con seed, servidor en :8085 y haber ejecutado antes
# tests/e2e/web-espacios.ps1 (reutiliza su sesion y su fichero temporal).
# Uso: powershell -ExecutionPolicy Bypass -File tests/e2e/web-reservas-imagenes.ps1
# Imprime OK/FAIL por comprobacion y FALLOS=<n> al final (esperado: 0).
# =====================================================================
$ErrorActionPreference = 'Stop'
$base = 'http://127.0.0.1:8085'
$jar = "$env:TEMP\jar.txt"
$tmp = $env:TEMP
$global:fallos = 0
function Check([bool]$cond, [string]$msg) { if ($cond) { "OK   - $msg" } else { "FAIL - $msg"; $script:fallos++ } }
function DoCurl([string[]]$argz) { (& curl.exe @argz) -join "`n" }
$hoy = Get-Date -Format 'yyyy-MM-dd'

# archivo de mas de 2 MB
$bytes = New-Object byte[] (2621440); (New-Object Random).NextBytes($bytes); [IO.File]::WriteAllBytes("$tmp\grande.jpg", $bytes)

$crear = [string](Get-Content "$tmp\crear.html" -Raw)
$token = [regex]::Match($crear, 'name="csrf" value="([0-9a-f]+)"').Groups[1].Value
$uploadsDir = "$PWD\public\uploads"
function Archivos { @(Get-ChildItem $uploadsDir -File | Where-Object { $_.Name -ne '.gitkeep' }) }

# A) Imagen con extension falsa (contenido texto) rechazada por MIME real
$res = DoCurl @('-s','-b',$jar,'-c',$jar,'-o','NUL','-w','%{http_code} %{redirect_url}','-X','POST','-F',"csrf=$token",'-F','tipo=sala','-F','nombre=Sala Mime Mala','-F','capacidad=10','-F','tarifa_base=20','-F','ubicacion=Edificio M','-F',"imagen=@$tmp\falso.jpg;type=image/jpeg","$base/espacios/guardar.php")
Check ($res -like '302*crear.php*') "MIME falso rechazado -> $res"
Check ((Archivos).Count -eq 1) 'no se guardo archivo con MIME invalido'
DoCurl @('-s','-b',$jar,'-c',$jar,'-o',"$tmp\mime.html","$base/espacios/crear.php?tipo=sala") | Out-Null
Check ([string](Get-Content "$tmp\mime.html" -Raw) -match 'Formato no permitido') 'mensaje de formato no permitido junto al campo imagen'

# B) Imagen de mas de 2 MB rechazada
$res = DoCurl @('-s','-b',$jar,'-c',$jar,'-o','NUL','-w','%{http_code} %{redirect_url}','-X','POST','-F',"csrf=$token",'-F','tipo=sala','-F','nombre=Sala Grande','-F','capacidad=10','-F','tarifa_base=20','-F','ubicacion=Edificio G','-F',"imagen=@$tmp\grande.jpg;type=image/jpeg","$base/espacios/guardar.php")
Check ($res -like '302*crear.php*') "imagen > 2 MB rechazada -> $res"
DoCurl @('-s','-b',$jar,'-c',$jar,'-o',"$tmp\grande.html","$base/espacios/crear.php?tipo=sala") | Out-Null
Check ([string](Get-Content "$tmp\grande.html" -Raw) -match 'supera el tamano maximo') 'mensaje de tamano maximo'
Check ((Archivos).Count -eq 1) 'sin archivos huerfenos tras rechazos'

# C) Edicion con reemplazo de imagen
$lista = [string](Get-Content "$tmp\lista.html" -Raw)
$ids = [regex]::Matches($lista, 'ver\.php\?id=(\d+)') | ForEach-Object { [int]$_.Groups[1].Value }
$editId = ($ids | Sort-Object -Descending | Select-Object -First 1)
$vieja = (Archivos)[0].Name
$res = DoCurl @('-s','-b',$jar,'-c',$jar,'-o','NUL','-w','%{http_code} %{redirect_url}','-X','POST','-F',"csrf=$token",'-F',"id=$editId",'-F','tipo=sala','-F','nombre=Sala E2E Editada','-F','capacidad=30','-F','tarifa_base=80','-F','ubicacion=Edificio E2E, sala 2','-F',"imagen=@$tmp\prueba.jpg;type=image/jpeg","$base/espacios/actualizar.php")
Check ($res -like "302*ver.php?id=$editId*") "edicion valida -> $res"
$nueva = (Archivos)
Check ($nueva.Count -eq 1) 'solo una imagen tras el reemplazo'
Check ($nueva[0].Name -ne $vieja) 'la imagen anterior fue eliminada y sustituida'
Check ($nueva[0].Extension -eq '.jpg') 'extension generada por el sistema (.jpg)'
$ficha = DoCurl @('-s','-b',$jar,'-c',$jar,"$base/espacios/ver.php?id=$editId")
Check ($ficha -match 'Sala E2E Editada') 'ficha con los datos actualizados'

# D) Confirmacion de eliminacion por GET (no borra) y POST (si borra)
$res = DoCurl @('-s','-b',$jar,'-c',$jar,'-o',"$tmp\confirm.html",'-w','%{http_code}',"$base/espacios/eliminar.php?id=$editId")
Check ($res -eq '200') 'GET muestra pantalla de confirmacion'
Check ([string](Get-Content "$tmp\confirm.html" -Raw) -match 'Confirmar eliminacion') 'contenido de confirmacion'
Check ((DoCurl @('-s','-b',$jar,'-c',$jar,"$base/espacios/index.php")) -match 'Sala E2E Editada') 'GET no elimino el registro'
$res = DoCurl @('-s','-b',$jar,'-c',$jar,'-o','NUL','-w','%{http_code} %{redirect_url}','-X','POST','-d',"csrf=$token&id=$editId","$base/espacios/eliminar.php")
Check ($res -like '302*espacios/index.php*') "POST elimina y redirige -> $res"
Check (-not ((DoCurl @('-s','-b',$jar,'-c',$jar,"$base/espacios/index.php")) -match 'Sala E2E Editada')) 'registro eliminado'
Check ((Archivos).Count -eq 0) 'la imagen del registro eliminado tambien se borro'

# E) No se puede eliminar un espacio con reservas (FK RESTRICT)
$res = DoCurl @('-s','-b',$jar,'-c',$jar,'-o','NUL','-w','%{http_code} %{redirect_url}','-X','POST','-d',"csrf=$token&id=1","$base/espacios/eliminar.php")
Check ($res -like '302*ver.php?id=1*') "espacio con reservas protegido -> $res"
Check ((DoCurl @('-s','-b',$jar,'-c',$jar,"$base/espacios/index.php")) -match 'Aula Magna') 'el espacio 1 sigue existiendo'

# F) Escape de salida: nombre con etiquetas HTML
$res = DoCurl @('-s','-b',$jar,'-c',$jar,'-o','NUL','-w','%{http_code} %{redirect_url}','-X','POST','-F',"csrf=$token",'-F','tipo=cancha','--form-string','nombre=<script>alert(1)</script>','-F','capacidad=4','-F','tarifa_base=10','-F','deporte=Tenis',"$base/espacios/guardar.php")
$lista = DoCurl @('-s','-b',$jar,'-c',$jar,"$base/espacios/index.php")
Check ($lista -match '&lt;script&gt;alert\(1\)&lt;/script&gt;') 'nombre escapado con htmlspecialchars en el listado'
Check (-not ($lista -match '<script>alert\(1\)</script>')) 'no se imprime HTML crudo del usuario'
$ids = [regex]::Matches($lista, 'ver\.php\?id=(\d+)') | ForEach-Object { [int]$_.Groups[1].Value }
$escapeId = ($ids | Sort-Object -Descending | Select-Object -First 1)
DoCurl @('-s','-b',$jar,'-c',$jar,'-o','NUL','-X','POST','-d',"csrf=$token&id=$escapeId","$base/espacios/eliminar.php") | Out-Null

# G) Reservas: traslape rechazado
$res = DoCurl @('-s','-b',$jar,'-c',$jar,'-o','NUL','-w','%{http_code} %{redirect_url}','-X','POST','--data-urlencode',"csrf=$token",'--data-urlencode','espacio_id=1','--data-urlencode','cliente=Traslape Prueba','--data-urlencode',"fecha=$hoy",'--data-urlencode','hora_inicio=09:30','--data-urlencode','hora_fin=10:30',"$base/reservas/guardar.php")
Check ($res -like '302*reservas/crear.php*') "reserva traslapada rechazada -> $res"
DoCurl @('-s','-b',$jar,'-c',$jar,'-o',"$tmp\tras.html","$base/reservas/crear.php") | Out-Null
Check ([string](Get-Content "$tmp\tras.html" -Raw) -match 'traslapa') 'mensaje de reserva traslapada junto al campo'

# H) Reserva con hora fin <= inicio
$res = DoCurl @('-s','-b',$jar,'-c',$jar,'-o','NUL','-w','%{http_code} %{redirect_url}','-X','POST','--data-urlencode',"csrf=$token",'--data-urlencode','espacio_id=2','--data-urlencode','cliente=Horario Malo','--data-urlencode',"fecha=$hoy",'--data-urlencode','hora_inicio=20:00','--data-urlencode','hora_fin=19:00',"$base/reservas/guardar.php")
DoCurl @('-s','-b',$jar,'-c',$jar,'-o',"$tmp\hor.html","$base/reservas/crear.php") | Out-Null
Check ([string](Get-Content "$tmp\hor.html" -Raw) -match 'posterior al campo') 'validacion hora fin > hora inicio'

# I) Limpieza previa: si una corrida anterior dejo reservas "Prueba E2E"
# en la misma franja, se eliminan por la propia aplicacion para que la
# prueba sea repetible (idempotencia del conjunto de pruebas).
$listadoPrev = DoCurl @('-s','-b',$jar,'-c',$jar,"$base/reservas/index.php")
foreach ($fila in ($listadoPrev -split '<tr>')) {
    if ($fila -match 'Prueba E2E' -and $fila -match 'ver\.php\?id=(\d+)') {
        $idPrevio = $Matches[1]
        DoCurl @('-s','-b',$jar,'-c',$jar,'-o','NUL','-X','POST','-d',"csrf=$token&id=$idPrevio","$base/reservas/eliminar.php") | Out-Null
    }
}
# I) Reserva valida
$res = DoCurl @('-s','-b',$jar,'-c',$jar,'-o','NUL','-w','%{http_code} %{redirect_url}','-X','POST','--data-urlencode',"csrf=$token",'--data-urlencode','espacio_id=2','--data-urlencode','cliente=Prueba E2E','--data-urlencode',"fecha=$hoy",'--data-urlencode','hora_inicio=13:00','--data-urlencode','hora_fin=14:30',"$base/reservas/guardar.php")
Check ($res -like '302*reservas/index.php*') "reserva valida guardada -> $res"
$listaR = DoCurl @('-s','-b',$jar,'-c',$jar,"$base/reservas/index.php")
Check ($listaR -match 'Prueba E2E') 'la reserva aparece en el listado'
Check ($listaR -match 'S/ ') 'costo estimado polimorfico en el listado de reservas'

# J) Reporte del dia y fecha invalida
$rep = DoCurl @('-s','-b',$jar,'-c',$jar,"$base/reporte.php")
Check ($rep -match $hoy) 'reporte usa la fecha actual por defecto'
Check ($rep -match 'Prueba E2E') 'la reserva del dia aparece en el reporte'
Check ($rep -match 'Tarifas polimorficas') 'seccion de tarifas polimorficas'
Check ($rep -match '2 horas en horario pico') 'columna calculada por tipo'
$rep2 = DoCurl @('-s','-b',$jar,'-c',$jar,"$base/reporte.php?fecha=2026-01-15")
Check ($rep2 -match '2026-01-15') 'reporte por fecha elegida'
Check (-not ((DoCurl @('-s','-b',$jar,'-c',$jar,"$base/reporte.php?fecha=basura")) -match 'basura')) 'fecha invalida ignorada'

# K) Panel con totales
$panel = DoCurl @('-s','-b',$jar,'-c',$jar,"$base/index.php")
Check ($panel -match 'Espacios por tipo') 'panel: totales por tipo'
Check ($panel -match 'Reservas de hoy') 'panel: reservas de hoy'

# L) Reporte filtrado por tipo de espacio
$repBase = DoCurl @('-s','-b',$jar,'-c',$jar,"$base/reporte.php")
$filasBase = @(($repBase -split '<tr>') | Where-Object { $_ -match '\d{2}:\d{2} - \d{2}:\d{2}' })
$canchasBase = @($filasBase | Where-Object { $_ -match 'Cancha deportiva' })
Check ($filasBase.Count -gt 0) "reporte sin filtro: muestra reservas del dia ($($filasBase.Count))"
Check ($canchasBase.Count -gt 0) 'reporte sin filtro: hay reservas de tipo cancha'
Check ($repBase -match 'Prueba E2E') 'reporte sin filtro: la reserva de sala esta presente'

$res = DoCurl @('-s','-b',$jar,'-c',$jar,'-o','NUL','-w','%{http_code}',"$base/reporte.php?tipo=cancha")
Check ($res -eq '200') "GET /reporte.php?tipo=cancha -> $res"
$repTipo = DoCurl @('-s','-b',$jar,'-c',$jar,"$base/reporte.php?tipo=cancha")
Check (-not ($repTipo -match 'Warning|Fatal error|Stack trace|Notice:|Deprecated:')) 'sin errores PHP con ?tipo=cancha'
$filasTipo = @(($repTipo -split '<tr>') | Where-Object { $_ -match '\d{2}:\d{2} - \d{2}:\d{2}' })
Check ($filasTipo.Count -eq $canchasBase.Count) "tipo=cancha: solo las reservas de cancha ($($filasTipo.Count) de $($filasBase.Count))"
Check (($filasTipo -join ' ') -match 'Cancha de futbol 11') 'tipo=cancha: aparece una cancha del seed'
Check (-not (($filasTipo -join ' ') -match 'Prueba E2E|Aula Magna|Escritorio')) 'tipo=cancha: sin reservas de otros tipos'
Check (-not ($repTipo -match 'Sala Aula Magna')) 'tipo=cancha: la tabla de tarifas tambien se filtra'

$repInv = DoCurl @('-s','-b',$jar,'-c',$jar,"$base/reporte.php?tipo=tipo_invalido")
$filasInv = @(($repInv -split '<tr>') | Where-Object { $_ -match '\d{2}:\d{2} - \d{2}:\d{2}' })
Check (-not ($repInv -match 'Warning|Fatal error|Stack trace|Notice:|Deprecated:')) 'sin errores PHP con tipo invalido'
Check ($filasInv.Count -eq $filasBase.Count) "tipo invalido ignorado: mismas reservas que sin filtro ($($filasInv.Count))"
Check ($repInv -match 'Prueba E2E') 'tipo invalido: se muestran todos los tipos'

# M) Exportacion CSV del reporte del dia
$res = DoCurl @('-s','-b',$jar,'-c',$jar,'-o','NUL','-w','%{http_code}',"$base/reporte-csv.php?fecha=$hoy")
Check ($res -eq '200') "GET /reporte-csv.php?fecha=$hoy -> $res"
$ctype = DoCurl @('-s','-b',$jar,'-c',$jar,'-o','NUL','-w','%{content_type}',"$base/reporte-csv.php?fecha=$hoy")
Check ($ctype -eq 'text/csv; charset=utf-8') "Content-Type del CSV -> $ctype"
$csvCab = DoCurl @('-s','-b',$jar,'-c',$jar,'-D','-','-o','NUL',"$base/reporte-csv.php?fecha=$hoy")
Check ($csvCab -match "Content-Disposition: attachment; filename=`"reporte-$hoy\.csv`"") 'descarga con nombre reporte-AAAA-MM-DD.csv'
$csv = DoCurl @('-s','-b',$jar,'-c',$jar,"$base/reporte-csv.php?fecha=$hoy")
Check ($csv -match 'Horario;Cliente;Espacio;Tipo') 'cabecera de columnas separada por ;'
Check (-not ($csv -match 'Horario,Cliente')) 'el separador NO es la coma'
Check ($csv -match 'Prueba E2E') 'la reserva del dia aparece en el CSV'
Check (-not ($csv -match '<!DOCTYPE|<table|</html>')) 'el CSV no contiene HTML'
Check (-not ($csv -match 'Warning|Fatal error|Stack trace|Notice:|Deprecated:')) 'sin errores PHP en el CSV'
$filasCsv = @(($csv -split "`n") | Where-Object { $_ -match '\d{2}:\d{2} - \d{2}:\d{2}' })
Check ($filasCsv.Count -eq $filasBase.Count) "el CSV exporta todas las reservas del dia ($($filasCsv.Count))"

$csvTipo = DoCurl @('-s','-b',$jar,'-c',$jar,"$base/reporte-csv.php?fecha=$hoy&tipo=cancha")
$filasCsvTipo = @(($csvTipo -split "`n") | Where-Object { $_ -match '\d{2}:\d{2} - \d{2}:\d{2}' })
Check ($filasCsvTipo.Count -eq $canchasBase.Count) "CSV con tipo=cancha: solo canchas ($($filasCsvTipo.Count))"
Check (-not ($csvTipo -match 'Prueba E2E')) 'CSV con filtro: sin reservas de otros tipos'

$csvInv = DoCurl @('-s','-b',$jar,'-c',$jar,"$base/reporte-csv.php?fecha=$hoy&tipo=tipo_invalido")
$filasCsvInv = @(($csvInv -split "`n") | Where-Object { $_ -match '\d{2}:\d{2} - \d{2}:\d{2}' })
Check ($filasCsvInv.Count -eq $filasBase.Count) 'CSV con tipo invalido: se ignora y exporta todas'

Write-Output "FALLOS=$global:fallos"