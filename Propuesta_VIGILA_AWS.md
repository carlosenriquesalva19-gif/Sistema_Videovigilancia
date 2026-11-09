# Optimización de la infraestructura en la nube para servicios de videovigilancia
## Migración eficiente y segura de VIGILA a AWS

**Asignatura:** Tecnología Cloud con AWS  
**Estudiante:** ____________________  
**Docente:** ____________________  
**Institución:** ____________________  
**Fecha:** 6 de octubre de 2026

## 1. Resumen ejecutivo

VIGILA enfrenta costos altos de cómputo, controles de seguridad incompletos, consultas de base de datos lentas y crecimiento acelerado de las grabaciones. Se propone separar el plano de aplicación del plano de video: la aplicación y sus metadatos se ejecutan en una arquitectura escalable dentro de una VPC; los archivos de video se guardan como objetos en Amazon S3; y las tareas que toleran interrupciones se procesan con instancias Spot o funciones bajo demanda.

La solución combina EC2 Auto Scaling con capacidad On-Demand y Spot, Amazon Aurora o RDS Multi-AZ para metadatos, S3 con políticas de ciclo de vida, cifrado con KMS, IAM de mínimo privilegio, Systems Manager para operación segura y monitoreo con CloudWatch, CloudTrail y AWS Config. La política exacta de retención, las regiones y los tamaños deben confirmarse con métricas y obligaciones contractuales de VIGILA antes del despliegue.

## 2. Objetivo

Diseñar una arquitectura AWS segura, escalable y eficiente en costos para procesar y almacenar video de vigilancia en tiempo real, manteniendo la disponibilidad del servicio y facilitando el crecimiento según la demanda.

## 3. Diagnóstico de la situación actual

El caso no proporciona inventario, métricas ni facturas de AWS. Por ello, este diagnóstico identifica puntos probables que deben verificarse durante una evaluación inicial; no afirma que ya se hayan observado valores específicos.

| Área | Punto crítico probable | Efecto | Dato que se debe revisar |
|---|---|---|---|
| EC2 | Instancias sobredimensionadas, siempre encendidas o sin escalado automático | Pago por capacidad ociosa y picos de latencia | CPU, memoria, red, IOPS, horas de ejecución y costo por etiqueta |
| Aplicación | API, portal y procesamiento de video comparten servidores | Un pico de carga afecta todo el servicio | Uso por proceso, solicitudes por segundo, colas y tiempos de respuesta |
| Base de datos | Consultas sin índices, almacenamiento de video o lecturas concentradas en la instancia principal | Saturación de CPU, conexiones y almacenamiento | Performance Insights, consultas lentas, conexiones, IOPS y crecimiento |
| Almacenamiento | Videos retenidos en EBS o en clases de S3 no alineadas con su frecuencia de acceso | Alto costo y poca elasticidad | GB por cámara/día, edad, frecuencia de recuperación y transferencia |
| Seguridad | Puertos abiertos, credenciales de larga duración o recursos públicos | Riesgo de acceso no autorizado y exposición de datos | IAM Access Analyzer, Security Hub/GuardDuty, CloudTrail y reglas de red |
| Operaciones | Parches y cambios manuales sin inventario central | Errores, exposición a vulnerabilidades y tiempos de atención altos | Inventario, cumplimiento de parches y registro de cambios |

### Revisión inicial recomendada

1. Etiquetar recursos por entorno, servicio, propietario y centro de costo.
2. Revisar AWS Cost Explorer y Cost and Usage Report para identificar costos por servicio y recurso.
3. Usar CloudWatch para obtener al menos 2–4 semanas de métricas, incluyendo períodos pico.
4. Inventariar EC2, EBS, snapshots, RDS, buckets, transferencia de datos, NAT Gateway y balanceadores.
5. Identificar clases de video, requisitos de retención, recuperación, privacidad y disponibilidad.
6. Definir una línea base: costo mensual, latencia p95, disponibilidad, errores, volumen procesado y tiempo de recuperación.

## 4. Arquitectura propuesta

```text
Cámaras / dispositivos
        |
        v
API de ingreso y autenticación (ALB/API Gateway según protocolo)
        |
        +--> Aplicación web/API en EC2 Auto Scaling (Multi-AZ)
        |       +--> tareas elásticas / AWS Lambda para eventos y trabajos breves
        |       +--> Amazon SQS para desacoplar trabajos de procesamiento
        |
        +--> Video --> Amazon S3 (carga directa con URL prefirmada)
        |                +--> S3 Lifecycle --> Glacier para archivo
        |
        +--> Metadatos --> Amazon Aurora/RDS en subredes privadas
                          +--> réplicas de lectura / RDS Proxy si corresponde

Administración: Systems Manager | Observabilidad: CloudWatch, CloudTrail, Config
Seguridad: IAM, KMS, Secrets Manager, Security Groups, WAF/GuardDuty según riesgo
```

El uso de URL prefirmadas permite que el dispositivo o cliente cargue el objeto directamente a S3, evitando que el tráfico de video atraviese las instancias de aplicación. La API valida identidad, autorización, tamaño y metadatos; asigna una ruta por cliente/cámara/fecha y emite una autorización temporal. La carga multipart se reserva para archivos suficientemente grandes y clientes compatibles. La ingesta debe validar integridad y registrar el objeto en la base de metadatos.

## 5. Optimización de cómputo

### EC2 y escalado

- Ejecutar la capa web/API en un Application Load Balancer y un grupo EC2 Auto Scaling distribuido entre al menos dos zonas de disponibilidad.
- Medir y ajustar familias/tamaños de instancia usando métricas reales. Evitar dimensionar por máximos históricos sin revisar el patrón de uso.
- Definir mínimo, deseado y máximo de capacidad; escalar con CPU, solicitudes por objetivo, latencia o profundidad de cola. Establecer alarmas para evitar tanto falta como exceso de capacidad.
- Mantener la capacidad crítica y estable con On-Demand. Incorporar Spot de forma gradual para workers sin estado, tolerantes a interrupciones y con tareas reintentables. Usar varios tipos de instancia y zonas, estrategia price-capacity-optimized y Capacity Rebalancing. No colocar una sesión de video activa o una base de datos en Spot.
- Guardar estado/checkpoints y mensajes en SQS/S3, permitir reintentos idempotentes y drenar conexiones antes de retirar workers.
- Evaluar Savings Plans solo después de medir una base de consumo estable. No comprometer gasto durante la fase de descubrimiento.
- AWS Lambda es apropiada para tareas cortas activadas por eventos (validación de carga, notificaciones, miniaturas o metadatos). No es la opción predeterminada para transcodificación continua, sesiones persistentes ni cómputo que exceda sus límites operativos; para ello usar workers EC2 o un servicio de contenedores.
- Elastic Beanstalk puede simplificar el despliegue de una aplicación web existente, pero no elimina la necesidad de diseñar el escalado, la red, la seguridad y el almacenamiento de video.

Spot ofrece descuento a cambio de capacidad interrumpible; por eso se recomienda solo para procesos recuperables. AWS Auto Scaling permite combinar capacidad Spot y On-Demand, y recomienda asignación price-capacity-optimized para escoger capacidad y precio. [Grupos mixtos de EC2 Auto Scaling](https://docs.aws.amazon.com/autoscaling/ec2/userguide/ec2-auto-scaling-mixed-instances-groups.html) y [estrategias de asignación](https://docs.aws.amazon.com/autoscaling/ec2/userguide/allocation-strategies.html).

## 6. Optimización de base de datos

### Modelo de datos

La base relacional debe guardar metadatos, usuarios, cámaras, políticas, eventos y referencias a objetos; no los bytes del video.

Tablas sugeridas:

- **Cliente** (`cliente_id`, nombre, estado).
- **Usuario** (`usuario_id`, `cliente_id`, rol, estado); almacenar hashes de credenciales con mecanismo seguro, nunca contraseñas en texto claro.
- **Camara** (`camara_id`, `cliente_id`, ubicación lógica, estado, configuración).
- **Grabacion** (`grabacion_id`, `camara_id`, `inicio_utc`, `fin_utc`, `s3_bucket`, `s3_key`, `tamano_bytes`, `checksum`, `estado`, `retencion_hasta`).
- **Evento** (`evento_id`, `camara_id`, `timestamp_utc`, tipo, referencia a grabación).

Índices y controles:

- Índice compuesto para búsquedas por `(camara_id, inicio_utc)` y por `cliente_id` según patrones reales.
- Paginación por cursor y límites de tiempo; evitar descargar listas completas de grabaciones.
- Particionar eventos/grabaciones por fecha si el volumen lo justifica y eliminar particiones vencidas conforme a retención aprobada.
- Revisar planes de ejecución, consultas lentas y conexiones antes de aumentar el tamaño de la base.

### Servicio y alta disponibilidad

- Elegir Amazon Aurora compatible con MySQL/PostgreSQL si se requiere crecimiento y separación de lectura/escritura; si la carga es moderada y predecible, comparar con RDS Multi-AZ por costo y operación.
- Desplegar en subredes privadas y Multi-AZ. Configurar backups automáticos, retención, snapshots y pruebas de restauración.
- En Aurora, dirigir escrituras al endpoint del clúster y lecturas al reader endpoint cuando la aplicación pueda aceptar consistencia de lectura. Las réplicas de Aurora distribuyen consultas de solo lectura y reducen carga sobre el primario. [Reader endpoint de Aurora](https://docs.aws.amazon.com/AmazonRDS/latest/AuroraUserGuide/Aurora.Endpoints.Reader.html).
- Usar RDS Proxy si el patrón de conexiones de funciones o servicios produce demasiadas conexiones breves.
- Activar Performance Insights/Database Insights y alarmas de CPU, memoria, almacenamiento, conexiones y latencia.
- Probar restauración y failover; una réplica no sustituye a un backup.

## 7. Almacenamiento de video

### S3 como almacén principal

Guardar objetos de video en S3; usar EBS solo para volúmenes de arranque, caché temporal o procesamiento que requiera disco de bloque. No dimensionar EBS para retener indefinidamente el archivo central de grabaciones.

Estructura sugerida de claves: `cliente_id/camara_id/AAAA/MM/DD/HH/uuid.ext`. El nombre no debe contener datos personales innecesarios. La aplicación conserva la clave y metadatos en la base de datos.

| Antigüedad/acceso | Clase o acción sugerida | Uso |
|---|---|---|
| Reciente y consultado con frecuencia | S3 Standard | Reproducción inmediata de grabaciones recientes |
| Patrón impredecible | S3 Intelligent-Tiering | Optimización automática según acceso, considerando cargos de monitoreo |
| Poco acceso, pero recuperación inmediata necesaria | S3 Standard-IA o Glacier Instant Retrieval tras evaluar costos | Archivo consultable sin demora de restauración |
| Retención legal/operativa con recuperación no inmediata | S3 Glacier Flexible Retrieval | Archivo con restauración previa |
| Archivo muy infrecuente y de larga duración | S3 Glacier Deep Archive | Conservación de largo plazo con mayor tiempo de recuperación |

Aplicar reglas de S3 Lifecycle según una política aprobada. Ejemplo inicial sujeto a los requisitos de VIGILA: Standard durante 30 días; transición a una clase de acceso esporádico entre 30 y 90 días; Glacier Flexible Retrieval después de 180 días; expiración al cumplir el plazo contractual/legal. No usar esta cronología como política real sin validarla. Glacier Flexible Retrieval y Deep Archive requieren restaurar una copia para consultar el objeto en tiempo real, y tienen duraciones mínimas de almacenamiento; incluir recuperación y solicitudes en el cálculo. [Gestión del ciclo de vida de S3](https://docs.aws.amazon.com/AmazonS3/latest/userguide/object-lifecycle-mgmt.html) y [consideraciones de transición a Glacier](https://docs.aws.amazon.com/AmazonS3/latest/userguide/lifecycle-transition-general-considerations.html).

### Protección y disponibilidad del almacenamiento

- Bloquear acceso público a todos los buckets; habilitar versionado cuando aporte recuperación ante borrado y definir limpieza de versiones antiguas.
- Cifrar objetos con SSE-KMS cuando se requiera control de claves y auditoría; rotar y restringir las claves KMS. Evaluar costo de solicitudes KMS y diseñar el uso de claves según volumen.
- Aplicar políticas de bucket para exigir TLS, cifrado y acceso desde roles/cuentas autorizados. No entregar credenciales AWS a cámaras; usar credenciales temporales/servicio de ingreso.
- Considerar S3 Object Lock solo para casos con obligación de inmutabilidad y con retención revisada; no activarlo sin evaluar irreversibilidad operacional.
- Configurar replicación entre regiones únicamente si RPO/RTO y continuidad justifican su costo, y replicar también configuración, claves y metadatos necesarios.
- Definir métricas de bytes almacenados, solicitudes, fallos de carga, restauraciones y transferencia de salida. Controlar egress y CDN si las reproducciones remotas representan una parte importante del costo.

## 8. Seguridad

1. **IAM:** aplicar mínimo privilegio; roles de instancia y roles de servicio en vez de claves permanentes. Separar cuentas/entornos con AWS Organizations; habilitar MFA para usuarios privilegiados y acceso federado/SSO. Revisar periódicamente permisos no usados.
2. **Red:** desplegar aplicaciones en subredes privadas detrás del balanceador; exponer solo los puertos necesarios. Los grupos de seguridad de base de datos aceptan conexiones únicamente desde el grupo de la aplicación. Evitar SSH/RDP público y limitar egreso. Usar VPC endpoints para S3/SSM si la arquitectura y el análisis de costos lo justifican.
3. **Cifrado:** TLS para tránsito; KMS para S3, EBS, snapshots y RDS/Aurora. Gestionar secretos con Secrets Manager o Parameter Store SecureString y rotarlos.
4. **Protección de cuentas:** MFA, cuentas separadas para administración y cargas, CloudTrail organizacional, alertas sobre cambios de IAM/red, AWS Config y detección con GuardDuty. AWS WAF puede proteger endpoints HTTP frente a patrones web comunes.
5. **Datos de video:** control de acceso por tenant/cliente, autorización temporal para reproducir, registros de auditoría de lectura y borrado conforme a retención. Minimizar datos personales en nombres y logs.
6. **Continuidad:** backups cifrados, plan de recuperación, objetivos RPO/RTO, restauraciones periódicas y simulacros. La redundancia no sustituye la recuperación probada.

Aurora puede cifrar datos, backups, snapshots y réplicas con KMS, y admite autenticación IAM para MySQL y PostgreSQL; esta última utiliza tokens temporales y conexiones TLS, con limitaciones que deben verificarse para el motor y cliente elegidos. [Seguridad de Aurora](https://docs.aws.amazon.com/rds/latest/auroraextendedcontent/aurora-features-security.html) y [autenticación IAM de Aurora](https://docs.aws.amazon.com/AmazonRDS/latest/AuroraUserGuide/UsingWithRDS.IAMDBAuth.html).

## 9. Automatización con AWS Systems Manager

- **Inventory:** mantener inventario de versiones, parches y configuración de instancias.
- **Patch Manager:** establecer ventanas de mantenimiento, grupos de parches y despliegues primero en desarrollo/canary, luego producción; medir cumplimiento y disponer de reversión.
- **Session Manager:** administrar instancias sin abrir SSH/RDP entrante ni distribuir llaves; registrar sesiones según política.
- **State Manager y Automation:** aplicar configuraciones repetibles, ejecutar tareas aprobadas y automatizar respuestas operativas con roles limitados.
- **Parameter Store:** almacenar parámetros de configuración; usar Secrets Manager para secretos con rotación administrada cuando aplique.
- **Runbooks:** documentar mantenimiento, recuperación, cambio de AMI, escalado y respuesta a incidentes; exigir aprobación humana para acciones de alto impacto.

## 10. Observabilidad y control de costos

Configurar dashboards y alarmas para:

- EC2: CPU, red, estado de instancias, capacidad deseada/activa, interrupciones Spot y latencia del balanceador.
- Procesamiento: mensajes pendientes/edad de SQS, errores y duración de tareas Lambda/workers.
- RDS/Aurora: CPU, memoria, conexiones, almacenamiento, IOPS, latencia y consultas lentas.
- S3: bytes por clase, solicitudes, errores, costo de recuperación y transferencia de salida.
- Seguridad: cambios IAM, buckets públicos, reglas de red, inicios de sesión y hallazgos de seguridad.

Usar etiquetas obligatorias, presupuestos con alertas, Cost Explorer y reportes por aplicación/cliente. Revisar mensualmente recursos huérfanos, EBS sin uso, snapshots antiguos, IP elásticas y balanceadores sin tráfico. Aplicar límites presupuestarios de alerta; no asumir que un presupuesto por sí solo detiene el gasto.

## 11. Plan de migración

| Fase | Actividades | Criterio de salida |
|---|---|---|
| 1. Descubrimiento | Inventario, costos, métricas, dependencias, retención, RPO/RTO | Línea base validada por operaciones y negocio |
| 2. Fundación segura | Cuentas, VPC, subredes, IAM, logging, KMS, Systems Manager | Revisiones de acceso y red aprobadas |
| 3. Datos | Crear RDS/Aurora, migrar metadatos, validar índices y backups | Conteos y consultas conciliados; restauración probada |
| 4. Video | Crear buckets, políticas, cargas de prueba, lifecycle y permisos | Integridad de objetos y reproducción verificadas |
| 5. Aplicación | Desplegar API/web en Auto Scaling; separar workers y colas | Pruebas de carga y resiliencia satisfactorias |
| 6. Corte gradual | Migrar clientes por grupos, monitorear, mantener rollback | Errores y latencia dentro de umbrales acordados |
| 7. Optimización | Ajustar tamaños, porcentajes Spot, retención y compromisos | Métricas comparadas con la línea base |

Realizar migración progresiva con sincronización y conciliación de datos. Mantener una ventana de reversión, evitar doble escritura sin estrategia de consistencia y comunicar cambios de reproducción/retención a usuarios afectados.

## 12. Beneficios y estimación de ahorro

Sin inventario, precios regionales, consumo y patrón de accesos no se puede prometer un ahorro monetario verificable. La siguiente tabla expresa metas de gestión iniciales para comprobar durante un piloto, no resultados garantizados.

| Frente | Meta inicial de piloto | Cómo medir |
|---|---:|---|
| EC2 | Reducir 20–40% del costo de cómputo flexible | Costo EC2 por 1.000 horas de cámara activa antes/después |
| Spot en workers tolerantes | Sustituir 30–60% de capacidad variable elegible | Proporción Spot, interrupciones, tiempo de cola y costo del worker |
| Almacenamiento | Reducir 25–60% del costo de objetos antiguos elegibles | Costo por TB-mes más solicitudes y restauración |
| Base de datos | Reducir lecturas del primario y estabilizar p95 | Latencia p95, CPU/IOPS y costo por consulta/cámara |
| Operaciones | Reducir tareas manuales repetitivas al menos 30% | Horas de operación por mes y cumplimiento de parches |

Estos rangos son objetivos propuestos para probar, no una cotización ni un dato de AWS. El ahorro neto debe calcularse incluyendo transferencia, solicitudes, recuperación Glacier, KMS, NAT, balanceadores, observabilidad y capacidad de reserva. Comparar costo total con la misma carga y retención, además de vigilar rendimiento y errores.

## 13. Indicadores de éxito

- Disponibilidad mensual del servicio según el SLA que VIGILA defina.
- Latencia p95 de consulta y reproducción; tasa de errores de ingesta.
- Porcentaje de cargas completadas y tiempo desde captura hasta disponibilidad.
- Costo por cámara activa/día y costo por TB-mes retenido.
- Porcentaje de trabajadores Spot recuperables y tasa de interrupciones con éxito de reintento.
- Porcentaje de recursos cifrados, sin exposición pública y administrados por Systems Manager.
- Cumplimiento de parches, restauraciones probadas y RPO/RTO alcanzados.

## 14. Seguridad y salud en el trabajo y responsabilidad ambiental

Para el trabajo académico y las operaciones técnicas, aplicar pausas, ergonomía, orden del puesto y procedimientos de cambio que reduzcan incidentes y errores humanos. En materia ambiental, reducir capacidad ociosa, apagar ambientes no productivos fuera de horario, aplicar retención y borrado responsable, y elegir regiones cercanas a usuarios cuando latencia y requisitos legales lo permitan. La medición debe considerar el consumo real y las políticas corporativas, sin atribuir reducciones ambientales numéricas sin datos.

## 15. Conclusiones

La solución propuesta separa video y metadatos, ajusta cómputo según demanda, reserva Spot para tareas tolerantes a interrupciones, optimiza consultas y ciclo de vida de objetos, y establece controles de acceso, cifrado y administración centralizada. La primera acción de VIGILA debe ser medir costos y patrones de uso; después, desplegar un piloto con una cantidad limitada de cámaras y comparar disponibilidad, latencia y costo contra la línea base. La adopción de políticas de retención, regiones, clases de almacenamiento y metas de ahorro debe aprobarse con los responsables del negocio, seguridad y cumplimiento.

## 16. Referencias técnicas

- AWS, [Grupos mixtos de instancias de EC2 Auto Scaling](https://docs.aws.amazon.com/autoscaling/ec2/userguide/ec2-auto-scaling-mixed-instances-groups.html).
- AWS, [Estrategias de asignación para varios tipos de instancias](https://docs.aws.amazon.com/autoscaling/ec2/userguide/allocation-strategies.html).
- AWS, [Reader endpoints para Amazon Aurora](https://docs.aws.amazon.com/AmazonRDS/latest/AuroraUserGuide/Aurora.Endpoints.Reader.html).
- AWS, [Administración del ciclo de vida de objetos S3](https://docs.aws.amazon.com/AmazonS3/latest/userguide/object-lifecycle-mgmt.html).
- AWS, [Consideraciones para transiciones S3 Lifecycle](https://docs.aws.amazon.com/AmazonS3/latest/userguide/lifecycle-transition-general-considerations.html).
- AWS, [Seguridad de Amazon Aurora](https://docs.aws.amazon.com/rds/latest/auroraextendedcontent/aurora-features-security.html).
- AWS, [Autenticación IAM de bases de datos Aurora](https://docs.aws.amazon.com/AmazonRDS/latest/AuroraUserGuide/UsingWithRDS.IAMDBAuth.html).
