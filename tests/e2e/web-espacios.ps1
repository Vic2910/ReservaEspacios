# =====================================================================
# ReservaEspacios - pruebas web end-to-end (parte 1: espacios y panel)
# Requisitos (ejecutar desde la raiz del repositorio):
#   1. Base de datos cargada con database/schema.sql y database/seed.sql
#   2. Servidor local: php -S 127.0.0.1:8085 -t public
# Uso: powershell -ExecutionPolicy Bypass -File tests/e2e/web-espacios.ps1
# Imprime OK/FAIL por comprobacion y FALLOS=<n> al final (esperado: 0).
# =====================================================================
$ErrorActionPreference = 'Stop'
$base = 'http://127.0.0.1:8085'
$jar = "$env:TEMP\jar.txt"
$tmp = $env:TEMP
Remove-Item $jar -ErrorAction SilentlyContinue
$global:fallos = 0
function Check([bool]$cond, [string]$msg) { if ($cond) { "OK   - $msg" } else { "FAIL - $msg"; $script:fallos++ } }
function DoCurl([string[]]$argz) { (& curl.exe @argz) -join "`n" }

# 1) Paginas principales
$paginas = @('/index.php','/espacios/index.php','/reservas/index.php','/reporte.php','/espacios/crear.php','/reservas/crear.php','/espacios/ver.php?id=1','/reservas/ver.php?id=1','/espacios/editar.php?id=1','/reservas/editar.php?id=1','/espacios/eliminar.php?id=1','/reservas/eliminar.php?id=1','/css/estilos.css','/img/sin-imagen.svg','/js/formulario.js')
foreach ($p in $paginas) {
  $code = DoCurl @('-s','-b',$jar,'-c',$jar,'-o',"$tmp\out.html",'-w','%{http_code}',"$base$p")
  $html = [string](Get-Content "$tmp\out.html" -Raw -ErrorAction SilentlyContinue)
  Check ($code -eq '200') "GET $p -> $code"
  Check (-not ($html -match 'Warning|Fatal error|Stack trace|Notice:|Deprecated:')) "sin errores PHP en $p"
}

# 2) HTML5 semantico del panel
DoCurl @('-s','-b',$jar,'-c',$jar,'-o',"$tmp\panel.html","$base/index.php") | Out-Null
$panelHtml = [string](Get-Content "$tmp\panel.html" -Raw)
Check ($panelHtml -match '<!DOCTYPE html>') 'panel: DOCTYPE html'
Check ($panelHtml -match '<html lang="es">') 'panel: lang=es'
Check ($panelHtml -match '<meta name="viewport"') 'panel: viewport'
Check ($panelHtml -match '<header class="site-header">') 'panel: header'
Check ($panelHtml -match '<nav') 'panel: nav'
Check ($panelHtml -match '<main class="contenedor">') 'panel: main'
Check ($panelHtml -match '<footer class="site-footer">') 'panel: footer'
Check (-not ($panelHtml -match 'style=')) 'panel: sin estilos en linea'
Check (-not ($panelHtml -match '\binstanceof\b|get_class\(')) 'panel: sin instanceof/get_class'
Check (-not ($panelHtml -match '>Array<')) 'panel: sin valores Array impresos por error'

# 3) Token CSRF y validacion del servidor (alta invalida)
DoCurl @('-s','-b',$jar,'-c',$jar,'-o',"$tmp\crear.html","$base/espacios/crear.php") | Out-Null
$crear = [string](Get-Content "$tmp\crear.html" -Raw)
$token = [regex]::Match($crear, 'name="csrf" value="([0-9a-f]+)"').Groups[1].Value
Check ($token.Length -eq 64) 'token CSRF presente y de 64 caracteres'

$res = DoCurl @('-s','-b',$jar,'-c',$jar,'-o','NUL','-w','%{http_code} %{redirect_url}','-X','POST','--data-urlencode',"csrf=$token",'--data-urlencode','tipo=sala','--data-urlencode','nombre=','--data-urlencode','capacidad=0','--data-urlencode','tarifa_base=-3','--data-urlencode','ubicacion=',"$base/espacios/guardar.php")
Check ($res -like '302*crear.php?tipo=sala*') "alta invalida redirige PRG -> $res"
DoCurl @('-s','-b',$jar,'-c',$jar,'-o',"$tmp\errores.html","$base/espacios/crear.php?tipo=sala") | Out-Null
$errores = [string](Get-Content "$tmp\errores.html" -Raw)
Check ($errores -match 'El campo Nombre es obligatorio') 'error por campo: nombre'
Check ($errores -match 'El campo Capacidad debe ser mayor que 0') 'error por campo: capacidad > 0'
Check ($errores -match 'El campo Tarifa base debe ser mayor que 0') 'error por campo: tarifa > 0'
Check ($errores -match 'campo-error') 'clase CSS campo-error aplicada'
Check ($errores -match 'value="0"') 'valor conservado en capacidad'
Check ($errores -match 'ubicacion') 'error por campo: ubicacion especifica del tipo'

# 4) CSRF invalido
$res = DoCurl @('-s','-b',$jar,'-c',$jar,'-o','NUL','-w','%{http_code} %{redirect_url}','-X','POST','-d','csrf=incorrecto&tipo=sala&nombre=X&capacidad=2&tarifa_base=10&ubicacion=Y',"$base/espacios/guardar.php")
Check ($res -like '302*crear.php*') "POST sin token valido redirige -> $res"

# 4b) Limpieza previa del alta: si una corrida anterior dejo el espacio de
# prueba (la elimina la parte 2), se borra por la propia aplicacion para
# que esta parte pueda repetirse sin depender de la otra (idempotencia).
$listadoPre = DoCurl @('-s','-b',$jar,'-c',$jar,"$base/espacios/index.php")
foreach ($fila in ($listadoPre -split '<tr>')) {
    if ($fila -match 'Sala E2E Con Imagen' -and $fila -match 'ver\.php\?id=(\d+)') {
        $idPrevio = $Matches[1]
        DoCurl @('-s','-b',$jar,'-c',$jar,'-o','NUL','-X','POST','-d',"csrf=$token&id=$idPrevio","$base/espacios/eliminar.php") | Out-Null
    }
}
# 5) Alta valida con imagen PNG real
$res = DoCurl @('-s','-b',$jar,'-c',$jar,'-o','NUL','-w','%{http_code} %{redirect_url}','-X','POST','-F',"csrf=$token",'-F','tipo=sala','-F','nombre=Sala E2E Con Imagen','-F','capacidad=25','-F','tarifa_base=77.50','-F','ubicacion=Edificio E2E, sala 1','-F',"imagen=@$tmp\prueba.png;type=image/png","$base/espacios/guardar.php")
Check ($res -like '302*espacios/index.php*') "alta valida con imagen -> $res"
DoCurl @('-s','-b',$jar,'-c',$jar,'-o',"$tmp\lista.html","$base/espacios/index.php") | Out-Null
$lista = [string](Get-Content "$tmp\lista.html" -Raw)
Check ($lista -match 'Sala E2E Con Imagen') 'el listado muestra el nuevo espacio'
$uploads = @(Get-ChildItem "$PWD\public\uploads" -File | Where-Object { $_.Name -ne '.gitkeep' })
Check ($uploads.Count -eq 1) "un archivo de imagen en public/uploads (hay $($uploads.Count))"
$ids = [regex]::Matches($lista, 'ver\.php\?id=(\d+)') | ForEach-Object { [int]$_.Groups[1].Value }
$ultimo = ($ids | Sort-Object -Descending | Select-Object -First 1)
$ficha = DoCurl @('-s','-b',$jar,'-c',$jar,"$base/espacios/ver.php?id=$ultimo")
Check ($ficha -match '/uploads/') 'la ficha muestra la imagen subida'
Check ($ficha -match '2 h en horario pico') 'dato calculado polimorfico en la ficha'
Check ($ficha -match 'Sala de reunion') 'ficha: etiqueta de tipo'
Write-Output "ULTIMO_ID=$ultimo"
Write-Output "TOKEN=$token"
Write-Output "FALLOS=$global:fallos"