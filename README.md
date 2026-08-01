# Albatros Tlaxcala — Backend (API)

API REST en Laravel para el sitio de Albatros Tlaxcala.

La documentación completa del proyecto (arquitectura, cómo levantar todo, credenciales, variables de entorno, Docker) está en el [README de la raíz](../README.md).

## Arranque rápido (nativo)

```bash
composer install
php artisan migrate:fresh --seed
php artisan storage:link --relative
php -S 127.0.0.1:8000 -t public public/index.php
```

> No uses `php artisan serve` en esta máquina — ver la nota en el README de la raíz.

## Arranque rápido (Docker, solo backend)

```bash
pnpm run dev
```

Levanta `db` + `backend` (nada de frontend). Para bajarlo: `pnpm run dev:down`.

## Tests

```bash
composer test   # o: ./vendor/bin/pest
```

## Documentación de la API

Con el backend corriendo (nativo o Docker), visita `http://localhost:8000/docs/api` (Scramble, autogenerada, solo en `local`).
