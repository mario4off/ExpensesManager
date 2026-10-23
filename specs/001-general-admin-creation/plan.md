# Plan 001 — Administración básica de gastos

Implementa `spec.md` (RF-01 a RF-26). Respeta `docs/constitution.md`.

## Puntos que requieren aprobación (reglas 12 y 13)

Este plan propone esquema y dependencias; no se ejecuta nada hasta que se aprueben.

1. **Esquema:** tablas de `spatie/laravel-permission` (`roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`) con dos roles fijos, `admin` y `user`, y tablas `categories`, `expenses` y `expense_documents` (ver Modelo de datos). Sustituye a la columna `users.is_admin`, que ya está implementada y probada (T07, T08) y habrá que revertir.
2. **Dependencias nuevas (tres):** el driver S3 de Flysystem (`league/flysystem-aws-s3-v3`), necesario para el disco `s3`; el paquete de traducciones `laravel-lang/lang`, que aporta los mensajes de validación de Laravel en español; y `spatie/laravel-permission`, para los roles `admin` y `user` (las tres aprobadas por el usuario).
3. **Interpretación de la regla 6 (disco local):** Livewire guarda cada subida en un directorio temporal local hasta que se pulsa Guardar, y PHP ya recibe el archivo en su directorio temporal. Se interpreta que "ningún documento en disco local" significa **ningún almacenamiento persistente local**. Alternativa descartada: apuntar la subida temporal de Livewire a S3, lo que deja basura en el bucket que habría que limpiar con reglas de ciclo de vida. Confirmar esta lectura.

## Módulos

| # | Módulo | Contenido | RF |
|---|--------|-----------|----|
| 0 | Base | Proyecto Laravel con Filament y Pest, panel único, locale `es`, zona horaria `Europe/Madrid`, disco `s3` configurado solo por `.env`, conexión MySQL por `.env` para desarrollo y una base de datos aparte para las pruebas, config de subida (límites de PHP y de Livewire ≥ 10 MB) | RF-13, RF-19, RF-26 (cimientos) |
| 1 | Acceso y roles | Panel con login y sin registro; roles `admin` y `user` con `spatie/laravel-permission`; `UserResource` visible solo para `admin`; `UserPolicy` apoyada en `hasRole()` | RF-01, RF-02, RF-03 |
| 2 | Categorías | `CategoryResource`; `CategoryPolicy`: ver para todos, crear, editar y borrar solo administrador; bloqueo de borrado con gastos | RF-07, RF-08, RF-09 |
| 3 | Gastos | `ExpenseResource` con formulario, tabla, filtros y orden; consulta acotada al usuario; `ExpensePolicy` | RF-04, RF-05, RF-10 a RF-15 |
| 4 | Documentos | Repeater de documentos dentro del formulario del gasto, sin mínimo de elementos; servicio de almacenamiento S3; descarga desde la vista del gasto; `ExpenseDocumentPolicy` | RF-16 a RF-24, y RF-05 para documentos |
| 5 | Datos iniciales | `RoleSeeder` (roles `admin` y `user`), `AdminUserSeeder` (asigna el rol `admin`) y `CategorySeeder`, todos idempotentes, llamados desde `DatabaseSeeder` | RF-06, RF-25 |

Orden de implementación: 0, 5 (el administrador hace falta para entrar), 1, 2, 3, 4. Cada paso empieza por sus tests Pest en rojo (regla 8).

## Modelo de datos

- **users** (tabla existente de Laravel): usa el trait `HasRoles` de Spatie; el rol se guarda en las tablas del paquete, no en una columna propia. RF-03.
- Tablas de `spatie/laravel-permission`: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`, con los roles `admin` y `user` precargados por `RoleSeeder`. RF-03.
- **categories**: `id`, `name` (único), marcas de tiempo. Global, sin `user_id`. RF-06, RF-07.
- **expenses**: `id`, `user_id` (FK a users, borrado en cascada), `category_id` (FK a categories, **restrict**), `spent_on` (fecha), `amount` (decimal 10,2), `description` (texto, nulo), marcas de tiempo. Índice compuesto `(user_id, spent_on)` para el listado. No hay columna de moneda: es siempre EUR. RF-10 a RF-15.
- **expense_documents**: `id`, `expense_id` (FK a expenses, cascada solo como red de seguridad), `type` (ticket o factura), `path` (clave en S3), `original_name`, `mime_type`, `size`, marcas de tiempo. RF-16 a RF-24.

Motor MySQL (RF-26): tablas InnoDB, necesario para que las claves foráneas con restrict y cascada se apliquen, y codificación `utf8mb4`.

Claves de S3: con prefijo por usuario y nombre aleatorio, para que no colisionen y no revelen datos. El nombre original solo vive en la base de datos.

## Decisiones justificadas

| Decisión | Por qué | Alternativa descartada |
|----------|---------|------------------------|
| Roles con `spatie/laravel-permission` | Decisión explícita del usuario; deja preparado el terreno para permisos más finos si el proyecto crece más allá de dos roles fijos | La columna `users.is_admin`: más simple para un caso binario (regla 12) y ya implementada (T07, T08), pero el usuario pidió sustituirla por el paquete |
| `category_id` con restrict más comprobación previa en Filament | La base de datos garantiza la integridad aunque falle la interfaz; la comprobación da el mensaje en español (RF-09) | `nullOnDelete` (gastos huérfanos) o cascada (pérdida de datos de otros usuarios) |
| Los tests usan una base de datos MySQL de pruebas, separada de la de desarrollo (RF-26) | Las garantías que importan, como restrict en la categoría (RF-09), la unicidad y los decimales exactos (RF-11), dependen del motor y SQLite las trata distinto | SQLite en memoria: más rápido, pero un test en verde no probaría el comportamiento real en MySQL |
| Importe como decimal(10,2) | Las sumas SQL de la fase 2 salen directas y 2 decimales es lo que pide RF-11 | Entero en céntimos: obliga a convertir en cada formulario y en cada consulta del asistente |
| Aislamiento en `getEloquentQuery` del recurso, más policies | Filament devuelve 404 si el registro no está en la consulta del recurso (RF-05); la policy es la segunda barrera | Global scope en el modelo: afectaría a seeders, tests y al asistente de la fase 2, y oculta errores |
| El administrador también ve solo sus gastos | RF-04 no hace excepciones | Vista global para el administrador: contradice la spec |
| Documentos como Repeater dentro del formulario del gasto | Un único guardado permite la atomicidad de RF-18 y RF-20 | Relation manager: exige que el gasto exista antes de subir, y rompe "no guardar el gasto si falla la subida" |
| Subida y guardado en una transacción, con compensación | Si algo falla, se revierte la base de datos y se borran de S3 los archivos ya subidos en ese intento (RF-20) | Guardar primero y reparar después: deja registros sin archivo |
| Borrado: archivo en S3 primero, registro después, dentro de una transacción | Si S3 falla se lanza la excepción, se revierte y el registro queda intacto (RF-22). Borrar una clave inexistente en S3 no da error | Borrar el registro y encolar la limpieza: contradice RF-22 y deja huérfanos |
| El borrado de archivos vive en eventos `deleting` de `Expense` y `ExpenseDocument` | Cubre borrado individual, masivo y por quitar un documento del repeater, porque todos pasan por el modelo | Confiar en la cascada de la base de datos: no dispara eventos y dejaría archivos huérfanos |
| Descarga como acción de Filament en la vista del gasto, con streaming desde el disco `s3` usando las credenciales del `.env` | Es la decisión del usuario; no hay URL pública ni ruta propia que adivinar, así que RF-24 es estructural y la policy lo refuerza | URL temporal firmada: descartada por el usuario. Controlador propio: prohibido por AGENTS.md |
| Validación en servidor: tipos y tamaño de Filament más reglas de Laravel (`decimal:0,2`, `before_or_equal:today`, `min`) | La validación de cliente no es una garantía (RF-11, RF-12, RF-17) | Confiar solo en `minValue` o `maxDate` del campo |
| Zona horaria `Europe/Madrid` | "Fecha futura" debe evaluarse en la fecha local del usuario, también cerca de medianoche (RF-12) | UTC por defecto: aceptaría o rechazaría mal los gastos de hoy |
| Paquete `laravel-lang/lang` para el español de Laravel; Filament ya trae el suyo; mensajes personalizados solo donde la regla lo pide (por ejemplo, borrado de categoría o fallo de S3) | Cubre todos los mensajes de validación (RF-11, RF-12, RF-17, RF-18) sin mantener a mano un fichero de traducción que se desactualiza | Escribir `lang/es` a mano: trabajo repetido y mensajes que faltan al actualizar Laravel |

## Riesgos conocidos

- **Fallo parcial al borrar varios archivos:** si S3 falla en el segundo de tres documentos, el primero ya no existe y el registro sigue. Mitigación: la descarga de un archivo ausente muestra un error en español, no una excepción. No se resuelve del todo sin un proceso de reconciliación, que queda fuera de alcance.
- **Límites de PHP:** `upload_max_filesize` y `post_max_size` por defecto son menores de 10 MB y rechazarían archivos válidos. El módulo 0 los fija y un test comprueba la regla de Livewire.
- **MySQL de pruebas:** hace falta un servidor MySQL local y una base de datos de pruebas creada; los tests son algo más lentos que con SQLite. Si la base de pruebas no existe, la suite falla con un error de conexión, no de lógica. Una pasada de tests no debe tocar la base de datos de desarrollo.
- **SQLite heredado:** el esqueleto de T01 dejó `.env` con SQLite y un fichero `database/database.sqlite` con migraciones ya ejecutadas; se sustituyen por MySQL en T06b.
- **Idempotencia del seeder:** si el administrador ya existe, no se le cambia la contraseña. Las credenciales `admin@etecnia.es` / `password` son solo de desarrollo (RF-25).
- **Rework de T07 y T08:** `users.is_admin`, su cast, su entrada en `Fillable`, el estado `admin()` de la factory y `AdminUserSeeder` ya están implementados y probados sobre la columna. Hay que revertirlos: instalar el paquete, migrar quitando `is_admin`, añadir `RoleSeeder` y reescribir `AdminUserSeeder` para asignar el rol `admin`. No se toca código hasta que se apruebe este plan.

## Estrategia de tests

Pest, con `RefreshDatabase`. Livewire testing sobre las páginas de Filament. Test primero en rojo (regla 8).

**S3 (regla 9, RF-19):**

- Caso normal: `Storage::fake('s3')` en todos los tests de documentos. Nada llama a AWS.
- Fallos: un disco simulado con Mockery que lanza excepción al escribir o al borrar, sustituido con `Storage::set`. Un único helper en `tests/Pest.php` evita repetirlo.
- RF-19: tras subir, el archivo existe en el disco `s3` simulado y el disco local no recibe nada.

**Matriz RF → tests**

| RF | Test |
|----|------|
| RF-01 | Un visitante sin sesión que pide el panel es redirigido al login |
| RF-02 | No existe la ruta de registro |
| RF-03 | El administrador crea usuarios con cada rol; un usuario normal no ve el recurso ni puede acceder a su URL |
| RF-04 | El listado de gastos solo contiene los del usuario, incluso siendo administrador |
| RF-05 | Editar, ver y borrar un gasto ajeno devuelve 404; igual con un documento ajeno |
| RF-06 | Tras el seeder existen las categorías iniciales |
| RF-07 | El administrador crea, edita y elimina categorías sin gastos |
| RF-08 | Un usuario normal ve las categorías pero no puede crear, editar ni borrar |
| RF-09 | Borrar una categoría con gastos falla con notificación en español y la categoría sigue existiendo, también en borrado masivo |
| RF-10 | Faltan fecha, importe o categoría: errores; la descripción es opcional |
| RF-11 | Importe 0, negativo o con 3 decimales: error en español; 0,01 válido |
| RF-12 | Fecha de mañana: error; la de hoy se acepta, incluso cerca de medianoche |
| RF-13 | El importe se muestra en EUR |
| RF-14 | El usuario edita y elimina sus gastos |
| RF-15 | Orden por fecha descendente; filtros por categoría y por rango de fechas, por separado y combinados |
| RF-16 | Un gasto se guarda sin documentos; otro con varios, de ambos tipos; y a un gasto con documentos se le quitan todos y se guarda sin errores |
| RF-17 | PDF, JPG y PNG aceptados; otro formato rechazado; 10 MB aceptado y 10 MB + 1 rechazado |
| RF-18 | Con un archivo inválido no se crea gasto ni registros de documentos |
| RF-19 | Los archivos van a `s3` y nada a local |
| RF-20 | Con S3 caído al subir: no hay gasto, ni registros, ni archivos huérfanos, y sale el mensaje |
| RF-21 | Quitar un documento y borrar un gasto eliminan sus archivos del disco `s3` simulado |
| RF-22 | Con S3 caído al borrar: el registro sigue, el mensaje sale y el archivo no se toca |
| RF-23 | El dueño descarga el contenido exacto; no se usa URL firmada |
| RF-24 | Un usuario ajeno recibe 404 al intentar descargar |
| RF-25 | El seeder crea el administrador con `is_admin`; ejecutarlo dos veces no lo duplica ni cambia su contraseña |
| RF-26 | La conexión por defecto es `mysql`, se configura con variables de entorno y los tests corren contra una base de datos distinta de la de desarrollo |

**Tests de arquitectura (Pest arch):** no hay controladores propios para el CRUD ni vistas Blade manuales (criterio 5).

**Cierre de cada módulo:** `./vendor/bin/pest` en verde y `./vendor/bin/pint` sin cambios.

## Cobertura de la spec

RF-01 a RF-03 → módulo 1. RF-04 y RF-05 → módulos 3 y 4. RF-06 y RF-25 → módulo 5. RF-07 a RF-09 → módulo 2. RF-10 a RF-15 → módulo 3. RF-16 a RF-24 → módulo 4. RF-26 → módulo 0. RF-13 y RF-19 tienen además cimientos en el módulo 0. Ningún RF queda sin módulo ni sin test.
