# VIGILA · Sistema de Videovigilancia y Gestión Cloud en AWS

[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-777bb4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel Version](https://img.shields.io/badge/Laravel-13.0-ff2d20?logo=laravel&logoColor=white)](https://laravel.com/)
[![AWS Cloud](https://img.shields.io/badge/AWS-Cloud%20Architecture-232f3e?logo=amazon-aws&logoColor=white)](https://aws.amazon.com/)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

Repositorio oficial del caso práctico de arquitectura e infraestructura en la nube: **“Optimización de la Infraestructura en la Nube para Servicios de Videovigilancia: Migración Eficiente y Segura a AWS”**.

Este proyecto contiene tanto la **propuesta formal de arquitectura cloud** como el **prototipo funcional de la aplicación web** en Laravel.

---

## 📁 Estructura del Repositorio

* **[`Propuesta_VIGILA_AWS.md`](Propuesta_VIGILA_AWS.md):** Documento técnico exhaustivo que detalla la arquitectura desacoplada, estrategia de cómputo (EC2 Auto Scaling, Spot y Lambda), almacenamiento elástico (S3 Lifecycle y Glacier), base de datos de metadatos (Amazon Aurora Multi-AZ), seguridad (IAM, KMS, VPC) y automatización con AWS Systems Manager.
* **[`vigila/`](vigila/):** Aplicación web funcional desarrollada en **Laravel 13** con panel de administración, catálogo de grabaciones, streaming en vivo y módulo de estimación FinOps.

---

## 🚀 Arquitectura Cloud Propuesta

```text
       [ Dispositivos / Cámaras ]                     [ Clientes / Navegadores ]
                   │                                              │
                   │ (1. Autenticación y solicitud)               │ (1. Consulta / Login)
                   ▼                                              ▼
     ┌────────────────────────────────────────────────────────────────────────┐
     │                Application Load Balancer (ALB) - Público               │
     └───────────────────────────────────┬────────────────────────────────────┘
                                         │
                   ┌─────────────────────┴─────────────────────┐
                   ▼                                           ▼
  ┌─────────────────────────────────┐         ┌─────────────────────────────────┐
  │  EC2 Auto Scaling (On-Demand)   │         │    EC2 Spot Instances (Workers) │
  │  • API REST & Portal Web        │◄──SQS───┤    • Transcodificación y alertas│
  │  • Laravel 13 (Multi-AZ)        │         │    • Trabajos batch tolerantes  │
  └────────────────┬────────────────┘         └─────────────────────────────────┘
                   │
         ┌─────────┴───────────────────────────────┐
         │ (2. Genera URLs prefirmadas / Tokens)   │ (3. Metadatos / Auditoría)
         ▼                                         ▼
┌─────────────────────────────────┐       ┌─────────────────────────────────────┐
│        Amazon S3 Bucket         │       │      Amazon Aurora / RDS Multi-AZ   │
│ • Carga/Descarga directa        │       │ • Cluster Primario (Escritura)      │
│ • S3 Lifecycle ──► Glacier Deep │       │ • Reader Endpoints (Lecturas)       │
└─────────────────────────────────┘       └─────────────────────────────────────┘
```

---

## 🛠️ Características del Prototipo (`vigila`)

1. **Desacoplamiento de Video:** Las grabaciones de video se almacenan en **Amazon S3**; las descargas y reproducciones se autorizan mediante **URLs prefirmadas de 5 minutos**, evitando que el ancho de banda congestione las instancias de cómputo.
2. **Streaming en Vivo:** Integración con **Amazon Kinesis Video Streams** para generar sesiones temporales HLS (`GetHLSStreamingSessionURL`) protegidas con roles IAM.
3. **Control de Acceso y Portal de Clientes:** Separación estricta de roles (`admin` y `user`). El portal de usuario (`/portal`) aplica mínimo privilegio, mostrando únicamente los dispositivos que el administrador asignó a cada cuenta.
4. **Módulo FinOps de Costos Cloud:** Simulador interactivo en tiempo real que calcula y proyecta el costo mensual de la infraestructura (instancias EC2, almacenamiento acumulado en S3 y horas de base de datos relacional).
5. **Auditoría Continua de Seguridad:** Registro de eventos en base de datos (`AuditLog`) para trazabilidad de altas, accesos y eliminaciones de grabaciones con IP y usuario.
6. **Guía de Operaciones con AWS Systems Manager:** Procedimientos para administrar instancias sin exponer el puerto 22 (SSH) a Internet usando *Session Manager* y *Patch Manager*.

---

## 💻 Puesta en Marcha Local (Windows PowerShell)

Para probar la aplicación en tu entorno local:

1. Clona el repositorio:
   ```powershell
   git clone https://github.com/carlosenriquesalva19-gif/Sistema_Videovigilancia.git
   cd Sistema_Videovigilancia/vigila
   ```

2. Instala dependencias y prepara el entorno (si clonas desde cero):
   ```powershell
   composer install
   Copy-Item .env.example .env
   New-Item -ItemType File -Path database/vigila.sqlite -Force
   php artisan key:generate
   php artisan migrate --seed
   ```

3. Inicia el servidor optimizado para videos de hasta 500 MB:
   ```powershell
   .\iniciar-vigila.ps1
   ```

4. Abre tu navegador en:
   ```text
   http://127.0.0.1:8000
   ```

---

## 🔑 Credenciales de Demostración

| Rol | Correo Electrónico | Contraseña | Nivel de Acceso |
|---|---|---|---|
| **Administrador** | `admin@vigila.test` | `Cambiar123!` | Panel de control completo (`/`) |
| **Cliente / Usuario** | `usuario@vigila.test` | `Usuario123!` | Portal restringido de cámaras (`/portal`) |

---

## 📊 Beneficios Esperados del Caso

* **Cómputo:** Reducción del **20% al 40%** en gastos de EC2 mediante Auto Scaling y hasta **60%** en workers utilizando instancias Spot.
* **Almacenamiento:** Ahorro de hasta el **60%** en retención histórica de video mediante políticas de ciclo de vida hacia **S3 Glacier Deep Archive**.
* **Base de Datos:** Subsegundo en latencia de consultas p95 gracias a réplicas de lectura en **Amazon Aurora**.
* **Seguridad:** Eliminación del 100% de puertos de administración expuestos utilizando **AWS Systems Manager Session Manager**.
