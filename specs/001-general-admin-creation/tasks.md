# Tareas 001 — Administración básica de la gestoría

Divide `plan.md` en tareas de menos de 30 minutos, ordenadas por dependencia. Cada tarea indica los RF de `spec.md` que cubre.

**Regla común (regla 8):** cada tarea empieza escribiendo su test Pest y comprobando que falla. Se da por terminada cuando ese test pasa, `./vendor/bin/pest` está en verde y `./vendor/bin/pint` no cambia nada. Los tests mockean S3 siempre (regla 9).

## Antes de empezar

- [x] **T00 — Aprobar los tres puntos del plan.** Esquema, dependencias (`league/flysystem-aws-s3-v3`, `laravel-lang/lang`, `spatie/laravel-permission`) e interpretación de la regla 6.
  **Hecho cuando:** el usuario confirma los tres puntos por escrito. (`spatie/laravel-permission` se aprobó después, en chat.) Con el cambio de alcance a la gestoría, el esquema aprobado pasa a ser el de `plan.md` actual: pendiente de confirmar por escrito antes de T16.

## Fase 0 — Base

- [x] **T01 — Crear el proyecto Laravel en la raíz.** Depende de: T00. RF: —
  **Hecho cuando:** `php artisan --version` responde y `git status` muestra `AGENTS.md`, `CLAUDE.md`, `docs/`, `specs/` y `.claude/` sin modificar.
- [x] **T02 — Instalar Pest con un test de humo.** Depende de: T01. RF: —
  **Hecho cuando:** `./vendor/bin/pest` termina en verde con ese test.
- [x] **T03 — Instalar Filament y crear el panel único.** Depende de: T02. RF: —
  **Hecho cuando:** un test comprueba que la ruta del panel existe y responde.
- [x] **T04 — Fijar idioma, zona horaria y traducciones.** Locale `es`, zona `Europe/Madrid`, `laravel-lang/lang`. Depende de: T03. RF: RF-16 (base), mensajes en español
  **Hecho cuando:** un test comprueba el locale, la zona horaria y que el mensaje de validación `required` sale en español.
- [x] **T05 — Configurar el disco `s3` solo por `.env`.** Driver S3, privado, sin valores literales. Depende de: T03. RF: RF-20 (base)
  **Hecho cuando:** `.env.example` trae las claves `AWS_*` vacías, un test comprueba que el disco `s3` es privado y `git grep` no encuentra credenciales en código.
- [x] **T06 — Permitir subidas de 10 MB.** Límite de Livewire y límites de PHP. Depende de: T03. RF: RF-18 (base)
  **Hecho cuando:** un test comprueba que la regla de subida temporal de Livewire admite 10 MB y que `public/.user.ini` declara `upload_max_filesize` y `post_max_size` de al menos 10M.

## Fase 1 — Base de datos y datos ya hechos

- [x] **T06b — Configurar MySQL para desarrollo y pruebas.** Conexión `mysql` solo por `.env`, base de datos de pruebas aparte, y retirar el SQLite heredado de T01. Depende de: T02. RF: RF-30
  **Hecho cuando:** un test comprueba que la conexión por defecto es `mysql` y que la de pruebas usa una base de datos distinta de la de desarrollo, `php artisan migrate:status` conecta, `.env.example` trae las variables `DB_*` sin credenciales reales y no queda `database/database.sqlite`.
- [x] **T07r — Instalar `spatie/laravel-permission` y revertir `users.is_admin`.** `User` usa `HasRoles`; `RoleSeeder`; factory `admin()`. Depende de: T06b. RF: RF-03 (base)
  **Hecho cuando:** un test comprueba que un usuario nuevo no tiene el rol `admin`, que la factory lo asigna, y que `RoleSeeder` crea los roles sin duplicarlos. (Hecha; los roles `admin` y `user` quedan superados por T10b.)
- [x] **T08r — `AdminUserSeeder` asigna el rol en vez de `is_admin`.** Depende de: T07r. RF: RF-28 (base)
  **Hecho cuando:** `admin@etecnia.es` se crea con el rol `admin` y ejecutarlo dos veces no duplica. (Hecha; se amplía en T10c.)
- [x] **T09 — Crear modelo, migración y factory de `categories`.** Depende de: T06b. RF: —
  **Hecho cuando:** un test comprueba que el nombre es único. (Hecha; superada por el cambio de alcance, se retira en T10a.)
- [x] **T10r — `DatabaseSeeder` con `RoleSeeder`, `AdminUserSeeder` y `CategorySeeder`.** Depende de: T07r, T08r, T09. RF: —
  **Hecho cuando:** `php artisan migrate:fresh --seed` termina sin errores. (Hecha; `CategorySeeder` se retira en T10a.)

## Fase 1b — Ajuste al cambio de alcance (gestoría)

- [x] **T10a — Retirar las categorías.** Modelo, migración, factory, `CategorySeeder`, sus tests y su llamada en `DatabaseSeeder`. Depende de: T10r. RF: —
  **Hecho cuando:** `git grep -i category` no encuentra referencias en `app/`, `database/` ni `tests/`, la suite está en verde y `php artisan migrate:fresh --seed` termina sin errores.
- [ ] **T10b — Rehacer `RoleSeeder` con permisos de área.** Permisos `invoices` e `insurance_policies`; roles `admin` (ambos), `invoices` e `insurance_policies` (el suyo); desaparece el rol `user`. Depende de: T10a. RF: RF-27
  **Hecho cuando:** tests comprueban que existen los dos permisos y los tres roles con sus permisos, que no existe el rol `user` y que ejecutarlo dos veces no duplica.
- [ ] **T10c — Sembrar los tres usuarios de prueba.** `facturas@etecnia.es` (rol `invoices`), `seguros@etecnia.es` (rol `insurance_policies`) y `admin@etecnia.es` (rol `admin`), contraseña `password`; la factory pasa a tener estados por rol. Depende de: T10b. RF: RF-28
  **Hecho cuando:** tests comprueban que los tres usuarios existen con su rol y contraseña válida, y que ejecutarlo dos veces no los duplica ni cambia su contraseña.
- [x] **T10d — Poner el logo en el panel.** Copiar la imagen `.webp` del logo a `public/` y declararla en el `AdminPanelProvider` con `brandLogo` (y `favicon`); es configuración fija, sin acción ni pantalla en el panel. Depende de: T03. RF: —
  **Hecho cuando:** el fichero existe en `public/images/` y la página de login del panel lo muestra (comprobación manual, sin test: es configuración fija).

## Fase 2 — Acceso y roles

- [ ] **T11 — Exigir login y bloquear el registro público.** Depende de: T03, T10c. RF: RF-01, RF-02
  **Hecho cuando:** tests comprueban que un visitante es redirigido al login y que no existe ruta de registro.
- [ ] **T12 — Restringir `UserResource` al rol `admin`.** `UserPolicy` apoyada en `hasRole('admin')` y recurso con listado. Depende de: T11. RF: RF-03
  **Hecho cuando:** tests comprueban que el `admin` ve el listado y que un usuario sin ese rol no lo ve en la navegación ni accede por URL.
- [ ] **T13 — Crear usuarios con roles desde el panel.** Depende de: T12. RF: RF-03
  **Hecho cuando:** un test crea usuarios con un rol y con varios, con contraseña cifrada, y todos pueden iniciar sesión.
- [ ] **T14 — Crear `RoleResource` para editar los permisos de área.** Solo `admin`; solo edita los permisos de los roles existentes, sin crear ni borrar roles. Depende de: T12. RF: RF-04
  **Hecho cuando:** tests comprueban que el `admin` cambia los permisos de un rol, que un usuario sin rol `admin` no accede, y que no hay acciones de crear ni borrar.

## Fase 3 — Facturas

- [ ] **T15 — Crear modelo, migración y factory de `invoices`.** `number` único. Depende de: T10c. RF: RF-08 (base)
  **Hecho cuando:** un test comprueba que el número es único y que `amount` guarda 2 decimales exactos.
- [ ] **T16 — Crear `InvoiceResource` con autorización por permiso.** `InvoicePolicy` y navegación por el permiso `invoices`; listado. Depende de: T15, T11. RF: RF-05, RF-06, RF-07
  **Hecho cuando:** tests comprueban que quien tiene `invoices` ve todas las facturas, que quien no lo tiene no ve el recurso y recibe 404 al ver, editar o borrar una por URL.
- [ ] **T17 — Crear el formulario de alta de factura.** Número, cliente, fecha de emisión e importe. Depende de: T16, T04. RF: RF-08, RF-16
  **Hecho cuando:** tests comprueban que se crea con los cuatro campos, que falta cualquiera o un número repetido da error, y que el importe se muestra en EUR.
- [ ] **T18 — Validar el importe de la factura.** Depende de: T17. RF: RF-09
  **Hecho cuando:** tests comprueban que 0, negativos y tres decimales se rechazan con mensaje en español y que 0,01 se acepta.
- [ ] **T19 — Validar la fecha de emisión.** Depende de: T17. RF: RF-10
  **Hecho cuando:** tests con la hora fijada comprueban que mañana se rechaza y que hoy se acepta, también a las 23:59 y a las 00:01.
- [ ] **T20 — Editar, eliminar y ordenar facturas.** Depende de: T17. RF: RF-11
  **Hecho cuando:** tests comprueban que se editan y eliminan, y que el listado está ordenado por fecha de emisión descendente, con desempate estable.

## Fase 4 — Seguros

- [ ] **T21 — Crear modelo, migración y factory de `insurance_policies`.** `policy_number` único. Depende de: T10c. RF: RF-12 (base)
  **Hecho cuando:** un test comprueba que el número de póliza es único y que `premium` guarda 2 decimales exactos.
- [ ] **T22 — Crear `InsurancePolicyResource` con autorización por permiso.** `InsurancePolicyPolicy` y navegación por el permiso `insurance_policies`; listado. Depende de: T21, T11. RF: RF-05, RF-06, RF-07
  **Hecho cuando:** tests comprueban que quien tiene `insurance_policies` ve todas las pólizas, que quien no lo tiene no ve el recurso y recibe 404 al ver, editar o borrar una por URL.
- [ ] **T23 — Crear el formulario de alta de póliza.** Número, tomador, aseguradora, fecha de inicio, fecha de fin y prima. Depende de: T22, T04. RF: RF-12, RF-16
  **Hecho cuando:** tests comprueban que se crea con los seis campos, que falta cualquiera o un número repetido da error, y que la prima se muestra en EUR.
- [ ] **T24 — Validar la prima.** Depende de: T23. RF: RF-13
  **Hecho cuando:** tests comprueban que 0, negativas y tres decimales se rechazan con mensaje en español y que 0,01 se acepta.
- [ ] **T25 — Validar las fechas de la póliza.** Depende de: T23. RF: RF-14
  **Hecho cuando:** tests comprueban que una fecha de fin anterior a la de inicio se rechaza con mensaje en español y que una igual o posterior se acepta.
- [ ] **T26 — Editar, eliminar y ordenar pólizas.** Depende de: T23. RF: RF-15
  **Hecho cuando:** tests comprueban que se editan y eliminan, y que el listado está ordenado por fecha de inicio descendente, con desempate estable.
- [ ] **T27 — Verificar que cambiar los permisos de un rol cambia el acceso.** Depende de: T14, T16, T22. RF: RF-04
  **Hecho cuando:** un test comprueba que, tras dar el permiso `insurance_policies` al rol `invoices`, el usuario de facturas ve los seguros, y que al quitárselo deja de verlos, sin cerrar sesión.

## Fase 5 — Documentos

- [ ] **T28 — Crear modelo, migración y factory de `documents`, y `document_id` en las tablas de área.** `documents` con `area`, `path` y `original_name`; `document_id` nulo, único y con clave foránea en `invoices` e `insurance_policies`. Depende de: T15, T21. RF: RF-17
  **Hecho cuando:** tests comprueban que una factura y una póliza se guardan sin documento y con uno, y que dos registros no pueden compartir el mismo documento.
- [ ] **T29 — Crear el servicio de almacenamiento S3 y el helper de fallo.** Guardar con clave `{area}/{uuid}.{ext}` y metadato `area`, borrar y leer. Depende de: T05, T28. RF: RF-20, RF-21
  **Hecho cuando:** tests con `Storage::fake('s3')` comprueban que el archivo queda en `s3` con esa clave y el metadato de área, que el disco local no recibe nada, y el helper de `tests/Pest.php` simula fallos al escribir y al borrar.
- [ ] **T30 — Añadir el documento al formulario de factura.** Un único `FileUpload`. Depende de: T17, T29. RF: RF-17, RF-20, RF-21
  **Hecho cuando:** tests crean una factura sin documento y con uno, con el archivo en `s3` y metadato `invoices`, y nada en local.
- [ ] **T31 — Añadir el documento al formulario de póliza.** Un único `FileUpload`. Depende de: T23, T29. RF: RF-17, RF-20, RF-21
  **Hecho cuando:** tests crean una póliza sin documento y con uno, con el archivo en `s3` y metadato `insurance_policies`, y nada en local.
- [ ] **T32 — Validar formato y tamaño de documentos.** Depende de: T30, T31, T06. RF: RF-18, RF-19
  **Hecho cuando:** tests comprueban que se aceptan PDF, JPG y PNG, que se rechaza otro formato, que 10 MB pasa y 10 MB + 1 no, y que ante un rechazo no se crea registro ni documento.
- [ ] **T33 — Hacer atómica la subida.** Transacción y borrado compensatorio. Depende de: T30, T31. RF: RF-22
  **Hecho cuando:** un test con S3 caído al subir comprueba que no hay registro, ni documento, ni archivo huérfano, y que sale el mensaje en español.
- [ ] **T34 — Borrar el archivo al quitar el documento o eliminar el registro.** Eventos `deleting` en `Invoice` e `InsurancePolicy` (trait compartido). Depende de: T30, T31. RF: RF-23
  **Hecho cuando:** tests comprueban que quitar el documento, eliminar el registro y el borrado masivo eliminan el archivo del disco `s3` simulado.
- [ ] **T35 — Conservar el registro si falla el borrado en S3.** Depende de: T34. RF: RF-24
  **Hecho cuando:** un test con S3 caído al borrar comprueba que el registro sigue, que el archivo no se toca y que sale el mensaje en español.
- [ ] **T36 — Añadir la vista del registro con descarga del documento.** Acción de Filament con streaming desde `s3`. Depende de: T30, T31. RF: RF-25
  **Hecho cuando:** tests comprueban que quien tiene el permiso del área descarga el contenido exacto sin usar URL firmada y que un archivo ausente muestra un error en español.
- [ ] **T37 — Denegar el acceso a documentos de un área sin permiso.** Con las policies del registro y de `Document` (por `area`). Depende de: T36. RF: RF-06, RF-26
  **Hecho cuando:** tests comprueban que un usuario sin el permiso del área recibe 404 al ver o descargar un documento.

## Fase 6 — Datos de ejemplo

- [ ] **T38 — Sembrar 3 facturas de ejemplo.** Valores fijos, idempotente. Depende de: T15. RF: RF-29
  **Hecho cuando:** un test comprueba que existen 3 facturas con los valores fijos y que ejecutarlo dos veces no duplica.
- [ ] **T39 — Sembrar 3 pólizas de ejemplo y registrar ambos seeders.** Valores fijos, idempotente; ambos seeders en `DatabaseSeeder`. Depende de: T21, T38. RF: RF-29
  **Hecho cuando:** un test comprueba que existen 3 pólizas con los valores fijos, que ejecutarlo dos veces no duplica y que `php artisan migrate:fresh --seed` termina sin errores.

## Fase 7 — Cierre

- [ ] **T40 — Añadir tests de arquitectura.** Depende de: T37, T39. RF: criterio 5 de `spec.md`
  **Hecho cuando:** los tests comprueban que no hay controladores propios para el CRUD ni vistas Blade manuales.
- [ ] **T41 — Verificar los criterios de finalización.** Depende de: T40. RF: todos
  **Hecho cuando:** `./vendor/bin/pest` está en verde, `./vendor/bin/pint` no cambia nada, `git grep` no encuentra credenciales y cada RF-01 a RF-30 tiene al menos un test.

## Cobertura de RF

| RF | Tareas |
|----|--------|
| RF-01, RF-02 | T11 |
| RF-03 | T12, T13 |
| RF-04 | T14, T27 |
| RF-05, RF-06, RF-07 | T16, T22 (RF-06 también T37) |
| RF-08 | T15, T17 |
| RF-09 | T18 |
| RF-10 | T19 |
| RF-11 | T20 |
| RF-12 | T21, T23 |
| RF-13 | T24 |
| RF-14 | T25 |
| RF-15 | T26 |
| RF-16 | T04, T17, T23 |
| RF-17 | T28, T30, T31 |
| RF-18, RF-19 | T06, T32 |
| RF-20 | T05, T29, T30, T31 |
| RF-21 | T29, T30, T31 |
| RF-22 | T33 |
| RF-23 | T34 |
| RF-24 | T35 |
| RF-25 | T36 |
| RF-26 | T37 |
| RF-27 | T10b |
| RF-28 | T10c |
| RF-29 | T38, T39 |
| RF-30 | T06b |
