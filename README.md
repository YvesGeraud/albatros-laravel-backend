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

## Rutas rápidas para probar que el backend responde

```
GET  /health                          → {"estado":"ok","entorno":"...","base_datos":"conectada"}
GET  /up                              → health check nativo de Laravel (200 si la app arrancó)
GET  /docs/api                        → documentación Scramble (solo entorno local)

GET  /api/v1/catalog                  → categorías + productos + combos (público)
GET  /api/v1/events                   → lista de eventos (público)
GET  /api/v1/events/live-now          → evento en vivo, o null si ninguno (público)
POST /api/v1/login                    → { email, password } → { user, token }
GET  /api/v1/user                     → requiere header Authorization: Bearer <token>
```

Ejemplo rápido con curl:
```bash
curl http://localhost:8000/health
curl http://localhost:8000/api/v1/catalog
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@albatrostlaxcala.com","password":"albatros2026"}'
```

## Usuario de prueba (creado por el seeder)

```
Correo:     admin@albatrostlaxcala.com
Contraseña: albatros2026
```

Se crea con `php artisan db:seed` (o `migrate:fresh --seed`) — ver `database/seeders/DatabaseSeeder.php`. Cámbialo antes de dejar esto como producción real.
