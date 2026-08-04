# ExamenTAP

Arquitectura base para un proyecto full-stack con backend en **Laravel 11 (PHP 8.2)** y frontend en **Angular 19 (standalone)**, preparados para conectarse a **Firebase / Firestore**. Este repositorio contiene únicamente la estructura, configuración inicial y mejores prácticas necesarias para comenzar el desarrollo de funcionalidades de negocio. No incluye CRUDs, autenticación ni pantallas funcionales.

## Instalación

```bash
git clone <url-del-repositorio>
cd examentap
```

## Estructura del proyecto

```
examentap/
├── backend/
│   ├── app/
│   │   ├── Console/
│   │   ├── DTOs/
│   │   ├── Enums/
│   │   ├── Exceptions/
│   │   ├── Helpers/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   └── Middleware/
│   │   ├── Interfaces/
│   │   ├── Models/
│   │   ├── OpenApi/
│   │   ├── Providers/
│   │   ├── Repositories/
│   │   ├── Requests/
│   │   ├── Resources/
│   │   ├── Services/
│   │   │   └── Firebase/
│   │   └── Traits/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── routes/
│   └── tests/
└── frontend/
    └── src/
        ├── app/
        │   ├── core/
        │   │   ├── config/
        │   │   ├── constants/
        │   │   ├── guards/
        │   │   ├── interceptors/
        │   │   ├── layouts/
        │   │   ├── models/
        │   │   ├── services/
        │   │   └── utils/
        │   ├── shared/
        │   │   ├── components/
        │   │   ├── directives/
        │   │   ├── pipes/
        │   │   └── services/
        │   └── features/
        └── environments/
```


## Cómo ejecutar el backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

El servidor queda disponible en `http://localhost:8000`. Endpoints útiles:

- `GET /up` — healthcheck interno de Laravel.
- `GET /api/v1/health` — endpoint de ejemplo con la respuesta JSON estandarizada.
- `GET /api/documentation` — documentación interactiva Swagger/OpenAPI.

Comandos adicionales:

```bash
php artisan test          # ejecuta la suite de PHPUnit
./vendor/bin/pint          # formatea el código a PSR-12
./vendor/bin/pint --test   # verifica el formato sin modificar archivos
php artisan l5-swagger:generate   # regenera la documentación de la API
```

## Cómo ejecutar el frontend

```bash
cd frontend
npm install
ng serve
```

La aplicación queda disponible en `http://localhost:4200`.

Comandos adicionales:

```bash
ng build            # compila la aplicación para producción
ng test             # ejecuta los tests unitarios con Karma
ng lint             # ejecuta ESLint
npm run format       # formatea el código con Prettier
npm run format:check # verifica el formato sin modificar archivos
```
