# Print Check AI

Aplicación para analizar archivos de impresión y detectar problemas o características relevantes mediante procesamiento automatizado y asistencia de modelos (OpenAI).

## Documentación del repositorio

- **Guía de desarrollo:** [docs/development.md](docs/development.md)
- **Arquitectura y componentes:** [docs/architecture.md](docs/architecture.md)
- **API y rutas principales:** [docs/api.md](docs/api.md)

## Inicio rápido (desarrollo)

En Windows (desde la raíz del proyecto):

```powershell
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run dev
php artisan storage:link
```

- Para procesar trabajos en segundo plano:

```powershell
php artisan queue:work --sleep=3 --tries=3
```

- Ejecutar tests:

```powershell
php artisan test
```

## Variables de entorno importantes

- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `OPENAI_API_KEY`
- `QUEUE_CONNECTION` (ej. `database` o `redis`)

Consulta la carpeta `docs/` para más información.

## Acerca de este proyecto

- **Nombre:** Print Check AI
- **Propósito:** Analizar archivos de impresión (archivos subidos por usuarios) y detectar problemas o características relevantes usando análisis automatizado y OpenAI para asistencia en el procesamiento.
- **Stack:** Laravel (PHP), Vite, Tailwind CSS, queues (Redis/DB), OpenAI (integración vía openai-php).

## Documentación del repositorio (rápida)

- **Guía de desarrollo:** [docs/development.md](docs/development.md)
- **Arquitectura y componentes:** [docs/architecture.md](docs/architecture.md)
- **API y rutas principales:** [docs/api.md](docs/api.md)

## Inicio rápido (desarrollo)

En Windows (desde la raíz del proyecto):

```powershell
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run dev
php artisan storage:link
```

- Para procesar trabajos en segundo plano:

```powershell
php artisan queue:work --sleep=3 --tries=3
```

- Ejecutar tests:

```powershell
php artisan test
```

## Variables de entorno importantes

- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `OPENAI_API_KEY` (clave para la integración con OpenAI)
- `QUEUE_CONNECTION` (ej. `database` o `redis`)

Consulta `docs/development.md` para más detalles sobre la configuración local.
