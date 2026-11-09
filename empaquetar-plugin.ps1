# ==============================================================================
# Script de empaquetado directo para WordPress.org
# ==============================================================================

$SLUG        = "mtwdev-custom-html-attributes-for-blocks"
$ARCHIVO_ZIP = "$SLUG.zip"

Write-Host "Iniciando empaquetado para: $SLUG..." -ForegroundColor Cyan

# 1. Compilar assets de produccion
if (Test-Path "package.json") {
    Write-Host "Compilando assets de produccion con gestor de paquetes..." -ForegroundColor Yellow
    if (Get-Command "pnpm" -ErrorAction SilentlyContinue) {
        pnpm run build
    } elseif (Get-Command "npm" -ErrorAction SilentlyContinue) {
        npm run build
    }
}

# 2. Crear area temporal limpia
$STAGING_ROOT = Join-Path $env:TEMP "wp_staging_build"
$PLUGIN_DIR   = Join-Path $STAGING_ROOT $SLUG

if (Test-Path $STAGING_ROOT) {
    Remove-Item -Path $STAGING_ROOT -Recurse -Force
}
New-Item -ItemType Directory -Path $PLUGIN_DIR -Force | Out-Null

# 3. Lista blanca de archivos que deben incluirse en el plugin
$archivosPermitidos = @(
    "readme.txt",
    "LICENSE*",
    "*.php",
    "package.json",
    "build",
    "src",
    "languages"
)

Write-Host "Copiando archivos seleccionados al contenedor del plugin..." -ForegroundColor Yellow
foreach ($patron in $archivosPermitidos) {
    $coincidencias = Get-Item -Path $patron -ErrorAction SilentlyContinue
    foreach ($item in $coincidencias) {
        if ($item.PSIsContainer) {
            Copy-Item -Path $item.FullName -Destination $PLUGIN_DIR -Recurse -Force
        } else {
            Copy-Item -Path $item.FullName -Destination $PLUGIN_DIR -Force
        }
    }
}

# 4. Generar archivo comprimido .ZIP
$RutaZip = Join-Path (Get-Location) $ARCHIVO_ZIP
if (Test-Path $RutaZip) {
    Remove-Item -Path $RutaZip -Force
}

Write-Host "Comprimiendo carpeta $SLUG en $ARCHIVO_ZIP..." -ForegroundColor Yellow
Compress-Archive -Path $PLUGIN_DIR -DestinationPath $RutaZip -Force

# 5. Limpieza de temporales
Remove-Item -Path $STAGING_ROOT -Recurse -Force

# 6. Validacion automatica del resultado
Add-Type -AssemblyName System.IO.Compression.FileSystem
$zip = [System.IO.Compression.ZipFile]::OpenRead($RutaZip)

$entradas = $zip.Entries.FullName
$tieneCarpetaRaiz = $true
$tieneReadme      = $false
$tienePHP         = $false

foreach ($entrada in $entradas) {
    $norm = $entrada.Replace("\", "/")
    if (-not ($norm -like "$SLUG/*")) { $tieneCarpetaRaiz = $false }
    if ($norm -eq "$SLUG/readme.txt") { $tieneReadme = $true }
    if ($norm -like "$SLUG/*.php")    { $tienePHP = $true }
}
$zip.Dispose()

Write-Host "`n=== RESULTADO DE LA VALIDACION ===" -ForegroundColor Cyan
if ($tieneCarpetaRaiz -and $tieneReadme -and $tienePHP) {
    Write-Host "  [OK] Carpeta contenedora verificada: /$SLUG/" -ForegroundColor Green
    Write-Host "  [OK] readme.txt localizado en la raiz." -ForegroundColor Green
    Write-Host "  [OK] Archivo PHP principal localizado en la raiz." -ForegroundColor Green
    Write-Host "`nPAQUETE $ARCHIVO_ZIP LISTO PARA SUBIR." -ForegroundColor Green
} else {
    Write-Host "`nError en la estructura del archivo ZIP generado." -ForegroundColor Red
}