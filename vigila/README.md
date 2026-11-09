# VIGILA · Sistema de videovigilancia en la nube

Aplicación web académica en Laravel para el caso **“Optimización de la Infraestructura en la Nube para Servicios de Videovigilancia: Migración Eficiente y Segura en AWS”**. Incluye administración de cámaras, catálogo de grabaciones, carga privada de video, bitácora, panel de estimación FinOps, controles de seguridad de aplicación y guía de operación con AWS Systems Manager.

## Requisitos

- PHP 8.3 o superior y extensiones PDO SQLite (o PDO MySQL), Fileinfo, OpenSSL, Mbstring, Tokenizer, XML, Ctype y JSON.
- Composer 2.
- Laravel 13 se instala como dependencia Composer; no se necesita Node para compilar el CSS/JS de esta versión.

Laravel 13 requiere PHP 8.3 o superior, según la documentación oficial de [versiones Laravel](https://laravel.com/docs/releases). La aplicación usa el sistema de discos de Laravel para permitir almacenamiento local privado o S3; la [documentación del filesystem](https://laravel.com/docs/filesystem) describe la configuración del disco S3 y las URL temporales.

## Instalación local en Windows PowerShell

Desde la carpeta `vigila`:

```powershell
composer install
Copy-Item .env.example .env
New-Item -ItemType File -Path database/vigila.sqlite
php artisan key:generate
php artisan migrate --seed
.\iniciar-vigila.ps1
```

Abre `http://127.0.0.1:8000`. El script configura los límites de PHP para permitir videos de hasta 500 MB. En el formulario de grabación, el modo local muestra los límites activos del servidor. Si ya tienes `php artisan serve` ejecutándose, deténlo con `Ctrl+C` antes de iniciar el script.

Si prefieres seguir usando `php artisan serve`, configura `upload_max_filesize=512M`, `post_max_size=520M`, `max_execution_time=300` y `max_input_time=300` en el `php.ini` de CLI y reinicia el servidor. Confirma el archivo activo con `php --ini`.

**Usuario de demostración:** `admin@vigila.test`  
**Contraseña de demostración:** `Cambiar123!`

**Usuario del portal:** `usuario@vigila.test`  
**Contraseña del portal:** `Usuario123!`

La cuenta del portal queda asignada a las cámaras de demostración al ejecutar el seeder.

Estas credenciales son únicamente para desarrollo local. Cámbialas y elimina el usuario de demostración antes de desplegar en un entorno real. No uses el seeder de demostración en producción.

## Base de datos

El ejemplo usa SQLite para poder iniciar el proyecto con pocos pasos. Para MySQL o Amazon RDS/Aurora, configura `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` en `.env`, crea previamente la base y ejecuta `php artisan migrate --seed`. En producción, utiliza credenciales desde un gestor de secretos y habilita TLS para la conexión a la base.

## Guardar archivos en Amazon S3

1. Crea un bucket privado en la región objetivo. Habilita Block Public Access y cifrado en reposo; define su política de ciclo de vida según la retención legal y contractual de VIGILA.
2. Asigna a la aplicación un rol IAM con acceso limitado al prefijo necesario del bucket. En AWS, prefiere roles temporales del entorno de ejecución antes que access keys estáticas.
3. Configura en `.env`:

```dotenv
VIGILA_VIDEO_DISK=s3
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=nombre-del-bucket-privado
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
```

En EC2, deja las access keys vacías y usa un instance profile/rol IAM. En otros entornos, usa credenciales temporales apropiadas. Después de cambiar configuración en un despliegue con cache, ejecuta `php artisan config:clear`.

Las grabaciones se guardan privadas en el disco seleccionado. Cuando el filesystem lo permite, el botón de acceso genera una URL temporal de cinco minutos. La aplicación no hace público el bucket.

## Cómputo y costos

La aplicación no crea EC2, Aurora, colas, buckets ni políticas de IAM automáticamente. El panel muestra una **estimación educativa** basada en valores de `.env`, no facturación real. Ajusta `VIGILA_RATE_EC2_HOUR`, `VIGILA_RATE_S3_GB_MONTH`, `VIGILA_RATE_DB_HOUR` y `VIGILA_ESTIMATED_EC2_INSTANCES` para el ejercicio. Para consultar costos reales, integra AWS Cost Explorer/Cost and Usage Reports con permisos de solo lectura y precios de la región usada.

## Video en vivo y actualización automática

El resumen y los paneles de cámaras, grabaciones, usuarios, costos y bitácora refrescan sus datos desde Laravel cada 15 segundos (estimación de costos: 30 segundos).

Para una cámara, el administrador puede configurar en **Cámaras → Editar** una URL HTTP(S) HLS o un **nombre de stream** de Kinesis Video Streams. Para Kinesis, el dispositivo debe publicar video al stream usando un producer compatible; el rol IAM de la aplicación necesita `kinesisvideo:GetDataEndpoint` y `kinesisvideo:GetHLSStreamingSessionURL`. Laravel solicita una sesión HLS temporal al abrir el visor y la renueva automáticamente; el token no se guarda en la base. HLS suele tener unos segundos de latencia según dispositivo, reproductor y red. Para latencia subsegundo, la arquitectura requiere WebRTC y el SDK de Kinesis Video Streams en productor y navegador. Las URLs RTSP necesitan un gateway/proxy que las convierta a un formato reproducible en navegador.

El navegador debe poder acceder a la URL de medios y al reproductor HLS cargado desde jsDelivr. El origen de video debe permitir CORS cuando aplique. No incluyas usuario, contraseña ni tokens permanentes en `stream_url`; solo se aceptan direcciones HTTP(S), y el panel valida el acceso por cámara antes de entregar una sesión.

Las recomendaciones de arquitectura se implementan en AWS por separado: ALB y EC2 Auto Scaling Multi-AZ; Spot solo para workers recuperables con respaldo On-Demand; Aurora/RDS privado y cifrado; S3 Lifecycle para transición/expiración; CloudTrail, Config, GuardDuty e IAM de mínimo privilegio. La sección Operaciones deja el procedimiento de Systems Manager, pero no afirma que Systems Manager esté conectado.

## Alcance de esta versión

- CRUD de cámaras (crear, editar, eliminar) y política de retención informativa.
- Catálogo de grabaciones, filtros por cámara/fecha y carga de MP4, MOV, WebM o MKV de hasta 500 MB.
- Roles separados de administrador/usuario, cuentas cliente administradas desde el panel y asignación por cámara.
- Portal de usuario en `/portal`; cada cuenta ve solo las cámaras asignadas y puede consultar sus grabaciones.
- Archivos locales privados por defecto; disco S3 privado opcional.
- Acceso protegido por inicio de sesión, validación CSRF, límite de intentos y bitácora de actividad.
- Paneles académicos de resumen, costo estimado, lista de verificación de seguridad y guía Systems Manager.
- Visor en vivo para URL HTTP/HLS y sesión HLS temporal de Amazon Kinesis Video Streams.

La transmisión RTSP/RTMP en vivo, detección de movimiento, transcodificación, integración en vivo con APIs de facturación AWS y automatización real con Systems Manager requieren configurar dispositivos, credenciales y servicios AWS del entorno de VIGILA. Este proyecto es la aplicación de administración y consulta de grabaciones; no simula ser una conexión de video en vivo con AWS.

## Crear cuentas de usuario

1. Inicia sesión como administrador y abre **Usuarios**.
2. Selecciona **Crear usuario**, registra correo y contraseña, y marca las cámaras que esa persona podrá consultar.
3. La persona ingresa por la misma página `/login`; si su rol es `user`, Laravel la dirige al portal `/portal`.
4. Para modificar permisos, edita la cuenta y cambia sus cámaras asignadas. Al quitar una cámara, la persona pierde también el acceso a sus archivos.

Para volver a crear/restablecer únicamente la cuenta de demostración del portal y sus cámaras asignadas en una base ya migrada, ejecuta `php artisan db:seed`. El seeder restablece la contraseña de esa cuenta de demostración; no uses estas credenciales en producción.

El primer usuario administrador de demostración es `admin@vigila.test`. Las nuevas cuentas creadas desde el panel son cuentas de usuario y no pueden acceder a la administración.

## Estructura principal

```text
app/Http/Controllers/    Controladores y validación
app/Models/              Modelos Eloquent
database/migrations/     Esquema de cámaras, grabaciones y auditoría
database/seeders/        Datos de demostración locales
resources/views/         Vistas Blade en español
public/css, public/js/   Interfaz sin build frontend
routes/web.php            Rutas autenticadas
```
