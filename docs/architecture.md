# Arquitectura del proyecto — Print Check AI

Resumen de los componentes principales y responsabilidades.

- `app/Models/PrintAnalysis.php` — entidad que representa un análisis solicitado por un usuario.
- `app/Models/PrintAnalysisResult.php` — resultados detallados del análisis (errores, advertencias, métricas).
- `app/Models/OpenAIUsageLog.php` — registros de uso de la API de OpenAI para auditoría y facturación.
- `app/Services/PrintCheck/` — lógica de negocio para procesar y analizar archivos (adaptadores a OpenAI u otros motores).
- `app/Jobs/AnalyzePrintFileJob.php` — job que delega el procesamiento de un archivo a la lógica del servicio; diseñado para ejecutarse en la cola.
- `app/Http/Controllers/` — controladores que exponen endpoints API/WEB para subir archivos, consultar resultados y administrar análisis.

Flujo simplificado:

1. Usuario sube archivo desde UI o llama a un endpoint API.
2. El servidor crea un `PrintAnalysis` y encola `AnalyzePrintFileJob`.
3. Worker de colas ejecuta el job; este usa `app/Services/PrintCheck` para extraer características, validar y enviar prompts a OpenAI según sea necesario.
4. Resultados se guardan en `PrintAnalysisResult` y se notifica al usuario (websocket/email/estado polling según configuración).

Persistencia y colas

- Base de datos: migraciones en `database/migrations` crean tablas `print_analyses`, `print_analysis_results`, `openai_usage_logs`.
- Colas: soporta drivers `database` o `redis` (configurable en `.env` con `QUEUE_CONNECTION`).

Diagrama ER (resumen):

- `users` 1 — * `print_analyses`
- `print_analyses` 1 — * `print_analysis_results`
- `print_analyses` 1 — * `openai_usage_logs`

Notas de escalabilidad

- Ejecutar múltiples workers de cola para procesamiento paralelo.
- Externalizar almacenamiento de archivos a un `filesystem` (S3, etc.) para alta disponibilidad.
- Monitorizar consumo de OpenAI y aplicar límites o batching para optimizar costos.
