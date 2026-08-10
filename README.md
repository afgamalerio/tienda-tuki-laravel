# Tienda Tuki

## Descripción

Tienda Tuki es una aplicación web desarrollada con Laravel para gestionar los productos y categorías de una tienda.

El proyecto utiliza Laravel, PHP, MySQL y Eloquent ORM. Actualmente cuenta con una API REST organizada bajo `/api/v1/` que permite realizar operaciones CRUD sobre productos y categorías.

Los productos se encuentran relacionados con categorías mediante relaciones Eloquent.

---

## Funcionalidades implementadas

Actualmente el proyecto permite:

### Productos

- Listar todos los productos.
- Consultar un producto por su ID.
- Crear un producto.
- Modificar un producto.
- Eliminar un producto.
- Asociar un producto a una categoría.

### Categorías

- Listar todas las categorías.
- Consultar una categoría por su ID.
- Crear una categoría.
- Modificar una categoría.
- Eliminar una categoría.

### Base de datos

- Conexión con MySQL.
- Migraciones para las tablas principales.
- Seeders para cargar datos iniciales.
- Relaciones entre productos y categorías mediante Eloquent.
- Eliminación en cascada de productos cuando se elimina su categoría.

### API

La aplicación cuenta con endpoints REST para productos y categorías.

Las respuestas de la API se realizan en formato JSON.

> La validación mediante Form Requests y una regla de validación personalizada serán incorporadas en la próxima etapa del desarrollo.

---

## Tecnologías utilizadas

- PHP 8.2
- Laravel
- MySQL
- Eloquent ORM
- Composer
- Git
- GitHub

---

## Requisitos

Para ejecutar el proyecto es necesario tener instalado:

- PHP 8.2 o superior.
- Composer.
- MySQL.
- Git.

También se recomienda utilizar un entorno de desarrollo local como XAMPP para disponer de PHP y MySQL.

---

# Instalación y configuración

## 1. Clonar el repositorio

Desde una terminal:

```bash
git clone https://github.com/afgamalerio/tienda-tuki-laravel.git
```

Luego ingresar a la carpeta del proyecto:

```bash
cd tienda-tuki-laravel
```

## 2. Instalar las dependencias

Ejecutar:

```bash
composer install
```

Esto instala las dependencias definidas en `composer.json`.

## 3. Configurar el archivo `.env`

Laravel utiliza el archivo `.env` para configurar las variables de entorno.

Si el archivo `.env` no existe, copiar `.env.example`:

```bash
cp .env.example .env
```

En Windows también se puede copiar el archivo manualmente desde `.env.example` y renombrarlo como `.env`.

Configurar la conexión a MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tienda-tuki
DB_USERNAME=root
DB_PASSWORD=
```

El valor de `DB_PASSWORD` debe modificarse si el usuario de MySQL tiene una contraseña configurada.

## 4. Crear la base de datos

Crear una base de datos MySQL llamada:

```text
tienda-tuki
```

Puede hacerse desde phpMyAdmin, MySQL Workbench o la herramienta de administración de MySQL utilizada.

## 5. Generar la clave de Laravel

Ejecutar:

```bash
php artisan key:generate
```

## 6. Ejecutar las migraciones

Para crear las tablas:

```bash
php artisan migrate
```

Las migraciones crean las tablas necesarias para el funcionamiento de la aplicación.

## 7. Cargar los datos iniciales

Ejecutar:

```bash
php artisan db:seed
```

Esto ejecuta los Seeders configurados en el proyecto.

También se puede reconstruir completamente la base de datos y ejecutar los Seeders con:

```bash
php artisan migrate:fresh --seed
```

> Este comando elimina las tablas existentes y las vuelve a crear, por lo que debe utilizarse únicamente cuando se desea reiniciar la base de datos.

---

# Ejecución del proyecto

Para iniciar el servidor de desarrollo de Laravel:

```bash
php artisan serve
```

Por defecto, la aplicación estará disponible en:

```text
http://127.0.0.1:8000
```

---

# Estructura del proyecto

El proyecto utiliza la estructura estándar de Laravel.

## `app/Models`

Contiene los modelos Eloquent de la aplicación.

Actualmente se encuentran:

```text
Producto.php
Categoria.php
User.php
```

Los modelos `Producto` y `Categoria` representan las entidades principales de la tienda y contienen sus relaciones Eloquent.

### Relaciones

Un producto pertenece a una categoría:

```text
Producto → belongsTo → Categoria
```

Una categoría puede tener muchos productos:

```text
Categoria → hasMany → Producto
```

---

## `app/Http/Controllers`

Contiene los controladores que manejan las peticiones de la aplicación.

Actualmente se encuentran:

```text
ProductoController.php
CategoriaController.php
```

Los controladores implementan las operaciones principales del CRUD.

---

## `database/migrations`

Contiene las migraciones utilizadas para definir la estructura de la base de datos.

Entre las principales tablas se encuentran:

```text
users
categorias
productos
```

La tabla `productos` posee una clave foránea `categoria_id` que relaciona cada producto con una categoría.

---

## `database/seeders`

Contiene los Seeders utilizados para cargar datos iniciales en la base de datos.

Actualmente se utiliza para cargar categorías iniciales.

---

## `routes`

Contiene las rutas de la aplicación.

Las rutas de la API se encuentran en:

```text
routes/api.php
```

Los endpoints utilizan el prefijo:

```text
/api/v1/
```

---

# Endpoints de la API

## Categorías

### Listar categorías

```http
GET /api/v1/categorias
```

### Obtener una categoría

```http
GET /api/v1/categorias/{id}
```

### Crear una categoría

```http
POST /api/v1/categorias
```

Ejemplo de datos:

```json
{
    "nombre": "Soportes"
}
```

### Modificar una categoría

```http
PUT /api/v1/categorias/{id}
```

Ejemplo:

```json
{
    "nombre": "Soportes para celular"
}
```

### Eliminar una categoría

```http
DELETE /api/v1/categorias/{id}
```

---

# Productos

### Listar productos

```http
GET /api/v1/productos
```

### Obtener un producto

```http
GET /api/v1/productos/{id}
```

### Crear un producto

```http
POST /api/v1/productos
```

Ejemplo de datos:

```json
{
    "nombre": "Soporte para celular",
    "descripcion": "Soporte de celular impreso en 3D",
    "imagen": "soporte-celular.jpg",
    "precio": 8500,
    "stock": 10,
    "color": "Negro",
    "categoria_id": 1
}
```

### Modificar un producto

```http
PUT /api/v1/productos/{id}
```

Ejemplo:

```json
{
    "nombre": "Soporte para celular Tuki",
    "descripcion": "Soporte de celular impreso en 3D",
    "imagen": "soporte-celular.jpg",
    "precio": 9000,
    "stock": 15,
    "color": "Negro",
    "categoria_id": 1
}
```

### Eliminar un producto

```http
DELETE /api/v1/productos/{id}
```

---

# Forma de probar el proyecto

## 1. Iniciar Laravel

Ejecutar:

```bash
php artisan serve
```

## 2. Consultar los endpoints

Los endpoints pueden probarse mediante una herramienta para realizar solicitudes HTTP, como Postman, o cualquier cliente compatible con APIs REST.

También se pueden consultar los endpoints `GET` directamente desde el navegador.

Por ejemplo:

```text
http://127.0.0.1:8000/api/v1/productos
```

o:

```text
http://127.0.0.1:8000/api/v1/categorias
```

Para las operaciones `POST`, `PUT` y `DELETE` se debe utilizar una herramienta que permita enviar solicitudes HTTP.

## 3. Verificar la base de datos

Los datos creados o modificados mediante la API se almacenan en la base de datos MySQL configurada en el archivo `.env`.

También es posible consultar y manipular los modelos mediante Laravel Tinker:

```bash
php artisan tinker
```

---

# Comandos principales

### Iniciar el servidor

```bash
php artisan serve
```

### Ejecutar migraciones

```bash
php artisan migrate
```

### Ejecutar Seeders

```bash
php artisan db:seed
```

### Recrear la base de datos y ejecutar Seeders

```bash
php artisan migrate:fresh --seed
```

### Abrir Tinker

```bash
php artisan tinker
```

### Ver las rutas

```bash
php artisan route:list
```

---

# Autor

**Anahi Fernández Gamalerio**