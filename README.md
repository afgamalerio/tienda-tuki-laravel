# Tienda Tuki

API REST de una tienda desarrollada con Laravel. Permite administrar productos
y categorías, gestionar carritos asociados a usuarios autenticados y confirmar
compras con control de stock y transacciones.

## Índice

- [Descripción](#descripción)
- [Funcionalidades](#funcionalidades-implementadas)
- [Tecnologías](#tecnologías-utilizadas)
- [Instalación](#instalación-y-configuración)
- [Ejecución](#ejecución-del-proyecto)
- [Estructura](#estructura-del-proyecto)
- [Endpoints](#endpoints-de-la-api)
- [Pruebas](#forma-de-probar-el-proyecto)
- [Seguridad](#seguridad-y-autenticación)

## Descripción

Tienda Tuki utiliza Laravel, PHP, MySQL y Eloquent ORM. La API está organizada
bajo `/api/v1/` y devuelve respuestas JSON.

El proyecto se enfoca en el backend y no incluye una interfaz de tienda para el
cliente final. Los endpoints pueden consumirse desde Postman o desde cualquier
aplicación frontend compatible con APIs REST.

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
- Validar los datos recibidos mediante Form Requests.
- Validar que no existan variantes duplicadas de un producto según su nombre y color.

### Categorías

- Listar todas las categorías.
- Consultar una categoría por su ID.
- Crear una categoría.
- Modificar una categoría.
- Eliminar una categoría.
- Validar los datos recibidos mediante Form Requests.

### Base de datos

- Conexión con MySQL.
- Migraciones para las tablas principales.
- Seeders para cargar datos iniciales.
- Relaciones entre productos y categorías mediante Eloquent.
- Eliminación en cascada de productos cuando se elimina su categoría.

### API

La aplicación cuenta con endpoints REST para productos, categorías,
autenticación, carrito y checkout. Las respuestas se realizan en formato JSON
y utilizan códigos HTTP según el resultado de cada operación.

### Autenticación

- Registro y login mediante JWT.
- Tokens Bearer con expiración configurable.
- Consulta del usuario autenticado.
- Renovación e invalidación de tokens.
- Carritos y checkout protegidos por usuario.

### Flujo principal

1. Registrar un usuario en `/api/v1/auth/register` o iniciar sesión en
    `/api/v1/auth/login`.
2. Guardar el JWT recibido y enviarlo como Bearer Token en las rutas protegidas.
3. Agregar productos al carrito mediante `/api/v1/carrito/items`.
4. Revisar el resumen en `/api/v1/checkout/revisar` y confirmar la compra en
    `/api/v1/checkout/confirmar`.
5. El sistema verifica el stock, crea el pedido, descuenta las unidades y
    vacía el carrito dentro de una transacción.

---

## Validaciones

La aplicación utiliza **Form Requests** para centralizar y organizar las validaciones de los datos recibidos por la API.

Actualmente se utilizan:

```text
app/Http/Requests/
├── StoreCategoriaRequest.php
├── StoreProductRequest.php
└── UpdateProductRequest.php
```

Las validaciones incluyen, entre otras:

- Campos obligatorios.
- Tipos de datos.
- Valores numéricos.
- Valores mínimos para precio y stock.
- Existencia de la categoría asociada.
- Campos opcionales como la imagen.

Las respuestas de validación se devuelven en formato JSON con código HTTP `422`.

Ejemplo:

```json
{
    "mensaje": "Error de validación",
    "errores": {
        "precio": [
            "El precio no puede ser negativo."
        ]
    }
}
```

## Data Transfer Objects (DTOs)

La API utiliza DTOs para organizar los datos que se envían entre las distintas
partes de la aplicación y para preparar respuestas con una estructura clara.

### `CartSummaryData`

Se encuentra en `app/Data/CartSummaryData.php` y representa el resumen del
carrito. Contiene:

- Items del carrito.
- Subtotal.
- Impuestos.
- Costo de envío.
- Total.

Se utiliza en `/api/v1/carrito/resumen` y `/api/v1/checkout/revisar`.

### `CheckoutData`

Se encuentra en `app/Data/CheckoutData.php` y organiza los datos necesarios para
confirmar una compra:

- Nombre del destinatario.
- Dirección.
- Ciudad.
- Método de pago.

Se utiliza en `/api/v1/checkout/confirmar`, después de que
`CheckoutRequest` valida los datos recibidos.

### Regla de validación personalizada

El proyecto cuenta con una regla personalizada llamada:

```text
UniqueProductVariant
```

Esta regla verifica que no exista más de un producto con la misma combinación de:

```text
nombre + color
```

La regla contempla también las actualizaciones de productos, ignorando el propio ID del producto que se está modificando.

Por ejemplo:

```text
Producto 1 → Soporte para celular / Rojo
Producto 2 → Soporte para celular / Negro
```

Ambas variantes son válidas porque tienen colores diferentes.

Sin embargo, intentar crear otro:

```text
Soporte para celular / Negro
```

generará un error de validación porque esa variante ya existe.

---

## Tecnologías utilizadas

- PHP 8.2+
- Laravel 12
- MySQL
- Eloquent ORM
- Composer
- PHPUnit
- Postman
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

## Instalación y configuración

### 1. Clonar el repositorio

Desde una terminal:

```bash
git clone https://github.com/afgamalerio/tienda-tuki-laravel.git
```

Luego ingresar a la carpeta del proyecto:

```bash
cd tienda-tuki-laravel
```

### 2. Instalar las dependencias

Ejecutar:

```bash
composer install
```

Esto instala las dependencias definidas en `composer.json`.

### 3. Configurar el archivo `.env`

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

Generar las claves de la aplicación y de JWT:

```bash
php artisan key:generate
php artisan jwt:secret
```

`JWT_SECRET` se guarda únicamente en `.env`. No debe copiarse al repositorio,
compartirse públicamente ni incluirse en la colección de Postman.

El valor de `DB_PASSWORD` debe modificarse si el usuario de MySQL tiene una contraseña configurada.

### 4. Crear la base de datos

Crear una base de datos MySQL llamada:

```text
tienda-tuki
```

Puede hacerse desde phpMyAdmin, MySQL Workbench o la herramienta de administración de MySQL utilizada.

### 5. Ejecutar las migraciones

Para crear las tablas:

```bash
php artisan migrate
```

Las migraciones crean las tablas necesarias para el funcionamiento de la aplicación.

### 6. Cargar los datos iniciales

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

## Ejecución del proyecto

Para iniciar el servidor de desarrollo de Laravel:

```bash
php artisan serve
```

Por defecto, la aplicación estará disponible en:

```text
http://127.0.0.1:8000
```

En otra terminal puede iniciarse el entorno completo definido por Composer:

```bash
composer run dev
```

---

## Estructura del proyecto

El proyecto utiliza la estructura estándar de Laravel.

## `app/Models`

Contiene los modelos Eloquent de la aplicación.

Actualmente se encuentran:

```text
Producto.php
Categoria.php
Carrito.php
CarritoItem.php
Pedido.php
PedidoItem.php
User.php
```

El modelo `User` implementa `JWTSubject` y se relaciona con sus carritos y
pedidos mediante `user_id`.

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
CarritoController.php
AuthController.php
```

Los controladores implementan las operaciones principales del CRUD.

---

## `app/Http/Requests`

Contiene los Form Requests utilizados para validar los datos recibidos por la API.

Actualmente se encuentran:

```text
StoreCategoriaRequest.php
UpdateCategoriaRequest.php
StoreProductRequest.php
LoginRequest.php
RegisterRequest.php
UpdateProductRequest.php
AddCartItemRequest.php
UpdateCartItemRequest.php
CheckoutRequest.php
```

Estos archivos permiten separar la lógica de validación de los controladores.

---

## `app/Rules`

Contiene las reglas de validación personalizadas.

Actualmente se encuentra:

```text
UniqueProductVariant.php
```

Esta regla permite controlar que no existan variantes duplicadas de productos según su nombre y color.

---

## `database/migrations`

Contiene las migraciones utilizadas para definir la estructura de la base de datos.

Entre las principales tablas se encuentran:

```text
users
categorias
productos
carritos
carrito_items
pedidos
pedido_items
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

La ruta `/api/user` también requiere un JWT válido y devuelve los datos públicos
del usuario autenticado.

---

## Flujo general de una petición

La API sigue un flujo basado en la arquitectura de Laravel:

```text
Request
   ↓
Route
   ↓
Form Request
   ↓
Controller
   ↓
Model / Eloquent
   ↓
Database
   ↓
Response JSON
```

Los Form Requests se encargan de validar los datos antes de que lleguen al controlador.

Los modelos Eloquent se encargan de interactuar con la base de datos y gestionar las relaciones entre las entidades.

---

## Endpoints de la API

## Principios REST

La API utiliza una arquitectura cliente-servidor: el cliente realiza
peticiones HTTP y el servidor procesa la solicitud, consulta la base de datos
y devuelve una respuesta JSON. Cada recurso se identifica mediante una URL y
los verbos HTTP expresan la operación:

- `GET`: consultar recursos.
- `POST`: crear un recurso o ejecutar una confirmación.
- `PUT`: reemplazar o actualizar datos.
- `DELETE`: eliminar un recurso.

Las respuestas utilizan códigos HTTP para comunicar el resultado: `200` para
operaciones correctas, `201` para recursos creados, `404` cuando no existe el
recurso y `422` cuando los datos no superan la validación.

## Autenticación

| Método | Endpoint | Autenticación |
| --- | --- | --- |
| `POST` | `/api/v1/auth/register` | Pública |
| `POST` | `/api/v1/auth/login` | Pública |
| `GET` | `/api/v1/auth/me` | JWT requerido |
| `POST` | `/api/v1/auth/refresh` | JWT requerido |
| `POST` | `/api/v1/auth/logout` | JWT requerido |
| `GET` | `/api/user` | JWT requerido |

Para las rutas protegidas se debe enviar:

```http
Authorization: Bearer <token>
```

## Carrito y checkout

El carrito se persiste en la base de datos y pertenece al usuario autenticado.
Las peticiones deben incluir:

```http
Authorization: Bearer <token>
```

El `X-Session-Id` ya no determina el propietario del carrito. El servidor usa
el usuario identificado por el JWT, por lo que un usuario no puede acceder al
carrito de otro.

### Agregar un producto

```http
POST /api/v1/carrito/items
```

```json
{
    "producto_id": 1,
    "cantidad": 2
}
```

### Consultar carrito

```http
GET /api/v1/carrito
```

### Actualizar cantidad

```http
PUT /api/v1/carrito/items/{productoId}
```

```json
{
    "cantidad": 3
}
```

### Eliminar un producto del carrito

```http
DELETE /api/v1/carrito/items/{productoId}
```

### Vaciar carrito

```http
DELETE /api/v1/carrito
```

### Resumen de compra

```http
GET /api/v1/carrito/resumen
```

El resumen devuelve `subtotal`, `impuestos`, `envio` y `total`. Los impuestos
se calculan al 21% y el envío es gratuito desde un subtotal de 50000.

### Revisar checkout

```http
GET /api/v1/checkout/revisar
```

### Confirmar compra

```http
POST /api/v1/checkout/confirmar
```

```json
{
    "nombre_destinatario": "Ana Pérez",
    "direccion": "Calle 123",
    "ciudad": "Buenos Aires",
    "metodo_pago": "tarjeta"
}
```

Los métodos de pago aceptados son `tarjeta`, `transferencia` y `efectivo`.
Al confirmar, se crea el pedido, se guardan sus items, se descuenta el stock
y se vacía el carrito dentro de una transacción.

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

## Productos

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

## Forma de probar el proyecto

### Pruebas automatizadas

Ejecutar:

```bash
php artisan test
```

Las pruebas utilizan SQLite en memoria para mantener los casos aislados. La
aplicación y las migraciones de desarrollo utilizan MySQL según la configuración
del archivo `.env`.

### 1. Iniciar Laravel

Ejecutar:

```bash
php artisan serve
```

### 2. Consultar los endpoints

Los endpoints pueden probarse mediante una herramienta para realizar solicitudes HTTP, como Postman, o cualquier cliente compatible con APIs REST.

La colección disponible en
`postman/Tienda-Tuki.postman_collection.json` incluye registro, login, captura
automática del token, carrito, checkout y ejemplos de respuestas `401`, `404` y
`422`.

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

### 3. Probar las validaciones

Las validaciones pueden comprobarse enviando datos incorrectos mediante una herramienta HTTP.

Por ejemplo, un producto con precio negativo:

```json
{
    "nombre": "Soporte",
    "descripcion": "Prueba",
    "precio": -500,
    "stock": 10,
    "color": "Negro",
    "categoria_id": 1
}
```

La API responderá con código `422` y los mensajes de validación correspondientes.

También puede probarse la regla `UniqueProductVariant` intentando crear o actualizar un producto utilizando una combinación de nombre y color que ya exista.

### 4. Verificar la base de datos

Los datos creados o modificados mediante la API se almacenan en la base de datos MySQL configurada en el archivo `.env`.

También es posible consultar y manipular los modelos mediante Laravel Tinker:

```bash
php artisan tinker
```

---

## Comandos principales

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

## Seguridad y autenticación

La API utiliza JSON Web Tokens (JWT). Un token tiene la estructura:

```text
HEADER.PAYLOAD.SIGNATURE
```

El header indica el tipo y algoritmo, el payload contiene los claims mínimos
como el identificador del usuario y la expiración, y la firma permite verificar
que el token no fue alterado. El token se transmite mediante
`Authorization: Bearer <token>` y no debe enviarse en una URL ni guardarse en
logs.

### Registro y login

```http
POST /api/v1/auth/register
POST /api/v1/auth/login
```

El registro valida email único y confirmación de contraseña. Las contraseñas se
guardan mediante hashing bcrypt y nunca se devuelven en JSON. El login responde
`401` si las credenciales son incorrectas y entrega un token con expiración
configurada mediante `JWT_TTL`.

El ciclo de vida también incluye:

```http
GET  /api/v1/auth/me
POST /api/v1/auth/refresh
POST /api/v1/auth/logout
```

`me` devuelve los datos públicos del usuario autenticado, `refresh` renueva un
token válido y `logout` lo invalida mediante la blacklist de JWT.

### Rutas protegidas

Requieren JWT válido todas las rutas de carrito y checkout. La protección usa
el middleware oficial `jwt.auth` provisto por `php-open-source-saver/jwt-auth`,
que verifica el Bearer Token, su firma, expiración e identificación del
usuario. No se implementa criptografía JWT manual ni se duplica el middleware
de la biblioteca.

- `/api/v1/carrito`
- `/api/v1/carrito/items`
- `/api/v1/carrito/resumen`
- `/api/v1/checkout/revisar`
- `/api/v1/checkout/confirmar`

Sin token, con token inválido o con token expirado la API responde `401`.
Los datos inválidos responden `422` y los recursos inexistentes responden
`404`.

### Códigos HTTP

| Código | Significado |
| --- | --- |
| `200` | Operación exitosa |
| `201` | Recurso creado |
| `401` | Token ausente, inválido o expirado |
| `404` | Recurso inexistente |
| `422` | Datos inválidos o regla de negocio incumplida |
| `500` | Error interno del servidor |

### CSRF, XSS y SQL Injection

La API autenticada con Bearer Token es stateless y no depende de cookies de
sesión para proteger el carrito o el checkout. No se desactiva globalmente el
middleware CSRF. Los datos se validan con Form Requests, las respuestas son
JSON y las vistas Blade mantienen el escaping de Laravel. Las consultas se
realizan mediante Eloquent o Query Builder sin concatenar valores recibidos
del usuario.

### HTTPS

En desarrollo local puede utilizarse `http://127.0.0.1:8000`. En producción
deben utilizarse conexiones `https://`, especialmente para registro, login,
JWT, carrito, checkout y datos de pedidos.

La colección de Postman contiene ejemplos de registro, login, errores `401`,
errores `404`, validaciones `422` y peticiones protegidas.

Para probar un token expirado, se puede establecer temporalmente un valor bajo
en `JWT_TTL`, generar un token nuevo con login y enviar la petición una vez
superado ese tiempo. No se deben guardar tokens reales ni secretos en Git.

## Autora

**Anahi Fernández Gamalerio**