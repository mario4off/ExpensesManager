# Plan 001 — Administración básica de la gestoría

Implementa `spec.md` (RF-01 a RF-30). Respeta `docs/constitution.md`. Es un PoC de mínimos.

## Puntos que requieren aprobación (reglas 12 y 13)

Este plan propone esquema y dependencias; no se ejecuta nada hasta que se aprueben.

1. **Esquema:** tablas `invoices`, `insurance_policies` y `documents`, con `document_id` en las dos primeras (ver Modelo de datos). Se retira la tabla `categories`. Las tablas de `spatie/laravel-permission` ya existen y no cambian.
2. **Dependencias nuevas (tres):** el driver S3 de Flysystem (`league/flysystem-aws-s3-v3`), necesario para el disco `s3`; el paquete de traducciones `laravel-lang/lang`, que aporta los mensajes de validación de Laravel en español; y `spatie/laravel-permission`, para roles y permisos (las tres aprobadas por el usuario).
3. **Interpretación de la regla 6 (disco local):** Livewire guarda cada subida en un directorio temporal local hasta que se pulsa Guardar, y PHP ya recibe el archivo en su directorio temporal. Se interpreta que "ningún documento en disco local" significa **ningún almacenamiento persistente local**. Alternativa descartada: apuntar la subida temporal de Livewire a S3, lo que deja basura en el bucket que habría que limpiar con reglas de ciclo de vida. Confirmar esta lectura.

## Módulos

| # | Módulo | Contenido | RF |
|---|--------|-----------|----|
| 0 | Base | Proyecto Laravel con Filament y Pest, panel único, locale `es`, zona horaria `Europe/Madrid`, disco `s3` configurado solo por `.env`, conexión MySQL por `.env` para desarrollo y una base de datos aparte para las pruebas, config de subida (límites de PHP y de Livewire ≥ 10 MB) | RF-16, RF-20, RF-30 (cimientos) |
| 1 | Acceso, roles y permisos | Panel con login y sin registro; `UserResource` solo para el rol `admin`, con un selector de roles al crear el usuario; los roles y sus permisos son fijos (sin `RoleResource` ni relation manager) | RF-01 a RF-04 |
| 2 | Facturas | `InvoiceResource` con formulario, tabla ordenada y validaciones; `InvoicePolicy` por permiso `invoices` | RF-05 a RF-11 |
| 3 | Seguros | `InsurancePolicyResource` con formulario, tabla ordenada y validaciones; `InsurancePolicyPolicy` por permiso `insurance_policies` | RF-05 a RF-07, RF-12 a RF-15 |
| 4 | Documentos | Modelo `Document` y `document_id` único en cada tabla de área (1:1); un `FileUpload` en el formulario de factura y de póliza; servicio de almacenamiento S3 con metadato de área | RF-17 a RF-24, y RF-06 para documentos |
| 5 | Datos iniciales | `RoleSeeder` (permisos y roles), seeder de usuarios de prueba, ambos idempotentes y llamados desde `DatabaseSeeder` | RF-27 a RF-28 |

Orden de implementación: 0, 5 (los usuarios hacen falta para entrar), 1, 2, 3, 4. Cada paso empieza por sus tests Pest en rojo (regla 8).

## Modelo de datos

- **users** (tabla existente de Laravel): usa el trait `HasRoles` de Spatie. RF-03.
- Tablas de `spatie/laravel-permission`: permisos `invoices` e `insurance_policies`; roles `admin` (ambos permisos), `invoices` e `insurance_policies` (su permiso), precargados por `RoleSeeder`. RF-04, RF-27.
- **invoices**: `id`, `number` (único), `client_name`, `issued_on` (fecha), `amount` (decimal 10,2), `document_id` (nulo, único, clave foránea a `documents`), marcas de tiempo. RF-08 a RF-11.
- **insurance_policies**: `id`, `policy_number` (único), `holder_name`, `insurer`, `starts_on` (fecha), `ends_on` (fecha), `premium` (decimal 10,2), `document_id` (nulo, único, clave foránea a `documents`), marcas de tiempo. RF-12 a RF-15.
- **documents**: `id`, `area` (`invoices` o `insurance_policies`), `path` (clave en S3), `original_name`, marcas de tiempo. Sin tipo MIME ni tamaño: se validan al subir pero no se guardan. RF-17 a RF-24.

No hay columna de moneda: es siempre EUR.

Motor MySQL (RF-30): tablas InnoDB y codificación `utf8mb4`.

Claves de S3: `docs/{area}/{uuid}.{ext}` (carpeta raíz `docs`, una subcarpeta por área), con el metadato `area` en el objeto (RF-21). El nombre original solo vive en la base de datos.

## Decisiones justificadas

| Decisión | Por qué | Alternativa descartada |
|----------|---------|------------------------|
| Roles con `spatie/laravel-permission` | Decisión explícita del usuario; permite roles por área y que un usuario tenga varios (RF-04) | — |
| Autorizar las áreas por **permiso** (`can('invoices')`), no por nombre de rol | Un usuario con varios roles obtiene la unión de sus permisos sin lógica extra (RF-04) | Comprobar nombres de rol: habría que combinar roles a mano en cada policy |
| Gestión de usuarios solo para el rol `admin`; al crear un usuario se le asignan o quitan roles con un selector; los roles y sus permisos son fijos (los carga `RoleSeeder`) | Es lo mínimo para RF-03 y RF-04 | `RoleResource` o relation manager de roles para editar permisos: más superficie de la necesaria para un PoC. Crear o borrar roles desde el panel: fuera de alcance |
| Visibilidad por área en policies (`viewAny`, `view`) y navegación oculta sin permiso; Filament deniega el acceso por URL si la policy lo niega (RF-06) | Modelo de gestoría: el área es la unidad de acceso (RF-05 a RF-07) | Aislamiento por `user_id`: contradice que quien tiene el área vea todos sus registros |
| `documents` con `area`, y `document_id` único y con clave foránea en `invoices` e `insurance_policies` | Una sola lógica de subida y borrado; el `unique` garantiza 0 o 1 documento y la clave foránea da integridad real; el área queda en base de datos y en S3 | Relación polimórfica (`documentable_type` y `documentable_id`): sin clave foránea y con columnas redundantes. Columnas de documento en cada tabla: duplica la lógica |
| Un único `FileUpload` en el formulario de factura y de póliza | Cada registro tiene 0 o 1 documento (RF-17) | Repeater: innecesario con un solo documento |
| Los tests usan una base de datos MySQL de pruebas, separada de la de desarrollo (RF-30) | Las garantías que importan, como la unicidad (RF-08, RF-12), dependen del motor y SQLite las trata distinto | SQLite en memoria: más rápido, pero un test en verde no probaría el comportamiento real en MySQL |
| Campos de importe y prima con un componente compartido `MoneyInput` (`numeric`, `step(0.01)`, sufijo €, alineado a la derecha) | Un solo sitio para importe y prima; el navegador limita a 2 decimales y la columna decimal(10,2) guarda 2 | Máscara de JavaScript, formato al perder el foco y reglas propias: más código del que pide un PoC. El formato de miles queda para más adelante |
| Importe y prima como decimal(10,2) | Las sumas SQL de la fase 2 salen directas y 2 decimales es lo que piden RF-09 y RF-13 | Entero en céntimos: obliga a convertir en cada formulario y en cada consulta del asistente |
| Subida y guardado en una transacción, con compensación | Si algo falla, se revierte la base de datos y se borra de S3 el archivo ya subido en ese intento (RF-22) | Guardar primero y reparar después: deja registros sin archivo |
| Borrado: archivo en S3 primero, registro después, dentro de una transacción | Si S3 falla se lanza la excepción, se revierte y el registro queda intacto (RF-24). Borrar una clave inexistente en S3 no da error | Borrar el registro y encolar la limpieza: contradice RF-24 y deja huérfanos |
| El borrado de archivos vive en eventos `deleting` de `Invoice` e `InsurancePolicy` (trait compartido), que borran el archivo y la fila de `documents` | Cubre borrado individual, masivo y quitar el documento del formulario, porque todos pasan por el modelo | Confiar en la cascada de la base de datos: no dispara eventos y dejaría archivos huérfanos |
| Validación en servidor: tipos y tamaño de Filament más reglas de Laravel (`before_or_equal:today`, `after_or_equal:starts_on`, `min`) | La validación de cliente no es una garantía (RF-10, RF-14, RF-18) | Confiar solo en `minValue` o `maxDate` del campo |
| Zona horaria `Europe/Madrid` | "Fecha futura" debe evaluarse en la fecha local, también cerca de medianoche (RF-10) | UTC por defecto: aceptaría o rechazaría mal las facturas de hoy |
| Paquete `laravel-lang/lang` para el español de Laravel; Filament ya trae el suyo; mensajes personalizados solo donde la regla lo pide (por ejemplo, fallo de S3) | Cubre todos los mensajes de validación sin mantener a mano un fichero de traducción que se desactualiza | Escribir `lang/es` a mano: trabajo repetido y mensajes que faltan al actualizar Laravel |

## Riesgos conocidos

- **Fallo al borrar en S3:** si S3 falla, el registro se conserva (RF-24). Con un solo documento por registro no hay borrados parciales.
- **Límites de PHP:** `upload_max_filesize` y `post_max_size` por defecto son menores de 10 MB y rechazarían archivos válidos. El módulo 0 los fija y un test comprueba la regla de Livewire.
- **MySQL de pruebas:** hace falta un servidor MySQL local y una base de datos de pruebas creada; los tests son algo más lentos que con SQLite. Si la base de pruebas no existe, la suite falla con un error de conexión, no de lógica. Una pasada de tests no debe tocar la base de datos de desarrollo.
- **SQLite heredado:** el esqueleto de T01 dejó `.env` con SQLite y un fichero `database/database.sqlite` con migraciones ya ejecutadas; se sustituyeron por MySQL en T06b.
- **Idempotencia de los seeders:** si un usuario de prueba ya existe, no se le cambia la contraseña. Las credenciales son solo de desarrollo (RF-28).
- **Cambio de alcance:** las categorías (T09, T10r) y los roles `admin`/`user` (T07r, T08r) ya están implementados. Hay que retirar las categorías y rehacer roles, permisos y seeders (Fase 1b de `tasks.md`).

## Estrategia de tests

Pest, con `RefreshDatabase`. Livewire testing sobre las páginas de Filament. Test primero en rojo (regla 8) **solo en lo crítico**.

**Crítico (lleva test):** autorización (rol `admin` para usuarios y roles, permisos de área), validaciones de negocio (número único, importe y prima, fechas) y documentos en S3 (sin guardado parcial, metadato de área, atomicidad, borrado y fallo de borrado). **No crítico (sin test, verificación manual o por comando):** login y registro de Filament, EUR, orden de listados, editar y borrar básico, seeders, configuración, logo y arquitectura.

**S3 (regla 9, RF-20):**

- Caso normal: `Storage::fake('s3')` en todos los tests de documentos. Nada llama a AWS.
- Fallos: un disco simulado con Mockery que lanza excepción al escribir o al borrar, sustituido con `Storage::set`. Un único helper en `tests/Pest.php` evita repetirlo.
- RF-20: tras subir, el archivo existe en el disco `s3` simulado y el disco local no recibe nada.
- RF-21: el archivo se escribe con el metadato `area` correcto; se comprueba sobre las opciones con que el servicio llama al disco.

**Matriz RF → tests (solo críticos)**

| RF | Test |
|----|------|
| RF-03 | Un usuario sin rol `admin` no ve el recurso de usuarios ni accede por URL |
| RF-04 | Un usuario con los roles `invoices` e `insurance_policies` ve y accede a las dos áreas |
| RF-05 | El usuario de facturas no ve el recurso de seguros en la navegación, y al revés |
| RF-06 | Un registro o documento de un área sin permiso no es accesible por URL |
| RF-07 | Con el permiso, el listado muestra todos los registros del área, sin importar quién los creó |
| RF-08 | Faltan número, cliente, fecha o importe: errores; número repetido: error |
| RF-10 | Fecha de emisión de mañana: error; la de hoy se acepta, incluso cerca de medianoche |
| RF-12 | Faltan número de póliza, tomador, aseguradora, fechas o prima: errores; número repetido: error |
| RF-14 | Fecha de fin anterior a la de inicio: error; igual o posterior: válida |
| RF-19 | Con un archivo inválido no se crea el registro ni el documento |
| RF-21 | El archivo se guarda con el metadato de área que corresponde a su registro |
| RF-22 | Con S3 caído al subir: no hay registro, ni documento, ni archivo huérfano, y sale el mensaje |
| RF-23 | Quitar el documento y borrar el registro eliminan su archivo del disco `s3` simulado |
| RF-24 | Con S3 caído al borrar: el registro sigue, el mensaje sale y el archivo no se toca |

**Verificación manual (sin test):**

| RF | Cómo se comprueba |
|----|-------------------|
| RF-01, RF-02 | Abrir `/admin` sin sesión y comprobar que redirige al login; comprobar que no hay registro |
| RF-11, RF-15 | Editar, eliminar y revisar el orden del listado en el panel |
| RF-16 | Ver importes y primas en EUR en el panel |
| RF-09, RF-13 | En el formulario, el importe y la prima aceptan 2 decimales y admiten negativos |
| RF-17, RF-18, RF-20 | Subir un PDF, un PNG y un formato inválido en el panel; comprobar el bucket |
| RF-27, RF-28 | `php artisan migrate:fresh --seed` y comprobar roles y usuarios con tinker |
| RF-30 | `php artisan migrate:status` conecta a MySQL; los datos se guardan en MySQL |

**Cierre de cada módulo:** `./vendor/bin/pest` en verde y `./vendor/bin/pint` sin cambios.

## Cobertura de la spec

RF-01 a RF-04 → módulo 1. RF-05 a RF-07 → módulos 2, 3 y 4. RF-08 a RF-11 → módulo 2. RF-12 a RF-15 → módulo 3. RF-16 → módulos 0, 2 y 3. RF-17 a RF-24 → módulo 4. RF-27 a RF-28 → módulo 5. RF-30 → módulo 0. Ningún RF queda sin módulo; los críticos tienen test.
