$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot

# Liberar el puerto 8000 si hay procesos huérfanos anteriores
try {
    $connections = Get-NetTCPConnection -LocalPort 8000 -ErrorAction SilentlyContinue
    if ($connections) {
        $connections | Where-Object { $_.OwningProcess -gt 0 } | ForEach-Object {
            Stop-Process -Id $_.OwningProcess -Force -ErrorAction SilentlyContinue
        }
        Start-Sleep -Milliseconds 400
    }
} catch {}

$phpArguments = @(
    '-d', 'upload_max_filesize=512M',
    '-d', 'post_max_size=520M',
    '-d', 'max_execution_time=300',
    '-d', 'max_input_time=300',
    '-S', '127.0.0.1:8000',
    '-t', 'public',
    'server.php'
)

Write-Host 'VIGILA listo en http://127.0.0.1:8000'
Write-Host 'Carga máxima de video: 500 MB. Presiona Ctrl+C para detener.'
& php @phpArguments
