# API — Endpoints principales

Este documento resume las rutas y su propósito. Para detalles de implementación, revisar `app/Http/Controllers`.

- `POST /api/analyses` — Crear un nuevo análisis (subir archivo). Retorna el `PrintAnalysis` creado y su `id`.
- `GET /api/analyses/{id}` — Obtener estado y detalles del análisis.
- `GET /api/analyses/{id}/results` — Obtener resultados del análisis (lista de `PrintAnalysisResult`).
- `GET /api/analyses` — Listar análisis del usuario (paginado).

Autenticación

- Las rutas API principales requieren autenticación (sanctum / tokens). Usa `Authorization: Bearer <token>`.

Ejemplo de petición para subir un archivo (cURL):

```bash
curl -X POST "https://tu-app.test/api/analyses" \
  -H "Authorization: Bearer <token>" \
  -F "file=@/ruta/a/archivo.pdf" \
  -F "name=Nombre del trabajo"
```

Web routes

- La interfaz web (panel) permite subir y ver resultados en la UI; revisar `routes/web.php`.

Notas

- Los endpoints encolan el trabajo y devuelven rápidamente el recurso creado; el procesamiento es asíncrono.
- Para notificaciones en tiempo real considere listeners y `broadcast` (Pusher/Redis) o polling.
