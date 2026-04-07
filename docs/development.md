# Guía de desarrollo (local)

Pasos para poner el entorno en marcha y desarrollar sobre el proyecto.

1) Dependencias

```powershell
composer install
npm install
```

2) Variables de entorno

- Copia el ejemplo y configura valores:

```powershell
copy .env.example .env
```

- Ajusta las credenciales de la base de datos y agrega `OPENAI_API_KEY`.

3) Inicializar la base de datos

```powershell
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

4) Frontend

```powershell
npm run dev
```

5) Trabajos en background

Para procesar análisis en background ejecutar un worker:

```powershell
php artisan queue:work --sleep=3 --tries=3
```

6) Ejecución de tests

```powershell
php artisan test
```

7) Logs y debugging

- Revisa `storage/logs/laravel.log` para errores.
- Usa `telescope` o `laravel-debugbar` si las instalas para depuración local.

8) Buenas prácticas

- No subas claves (`OPENAI_API_KEY`) a repositorios.
- Mantén las migraciones pequeñas y atómicas.
