# Tareas 001 — Administración básica de la gestoría

Divide `plan.md` en tareas de menos de 30 minutos, ordenadas por dependencia. Cada tarea indica los RF de `spec.md` que cubre.

**Regla común (regla 8):** solo las tareas críticas (autorización, validaciones de negocio, documentos en S3) empiezan escribiendo su test Pest y comprobando que falla; se dan por terminadas cuando ese test pasa. Las demás se cierran con su "Hecho cuando" manual o de comando, sin test. En todas, `./vendor/bin/pest` está en verde (si ya hay tests) y `./vendor/bin/pint` no cambia nada. Los tests mockean S3 siempre (regla 9). Los tests de configuración de T02–T10r se retiraron por esta estrategia.

## Antes de empezar

- [x] **T00 — Aprobar los tres puntos del plan.** Esquema, dependencias (`league/flysystem-aws-s3-v3`, `laravel-lang/lang`, `spatie/laravel-permission`) e interpretación de la regla 6.
  **Hecho cuando:** el usuario confirma los tres puntos por escrito. (`spatie/laravel-permission` se aprobó después, en chat.) Con el cambio de alcance a la gestoría, el esquema aprobado pasa a ser el de `plan.md` actual: pendiente de confirmar por escrito antes de T16.

## Fase 0 — Base

- [x] **T01 — Crear el proyecto Laravel en la raíz.** Depende de: T00. RF: —
  **Hecho cuando:** `php artisan --version` responde y `git status` muestra `AGENTS.md`, `CLAUDE.md`, `docs/`, `specs/` y `.claude/` sin modificar.
- [x] **T02 — Instalar Pest con un test de humo.** Depende de: T01. RF: —
  **Hecho cuando:** `./vendor/bin/pest` termina en verde. (Hecha; el test de humo se retiró por la estrategia de tests críticos.)
- [x] **T03 — Instalar Filament y crear el panel único.** Depende de: T02. RF: —
  **Hecho cuando:** `/admin/login` responde en el navegador (comprobación manual).
- [x] **T04 — Fijar idioma, zona horaria y traducciones.** Locale `es`, zona `Europe/Madrid`, `laravel-lang/lang`. Depende de: T03. RF: RF-16 (base), mensajes en español
  **Hecho cuando:** `php artisan tinker` muestra locale `es` y zona `Europe/Madrid`, y un error de validación sale en español (comprobación manual).
- [x] **T05 — Configurar el disco `s3` solo por `.env`.** Driver S3, privado, sin valores literales. Depende de: T03. RF: RF-20 (base)
  **Hecho cuando:** `.env.example` trae las claves `AWS_*` vacías, `config/filesystems.php` define `s3` privado y `git grep` no encuentra credenciales en código.
- [x] **T06 — Permitir subidas de 10 MB.** Límite de Livewire y límites de PHP. Depende de: T03. RF: RF-18 (base)
  **Hecho cuando:** `public/.user.ini` declara `upload_max_filesize` y `post_max_size` de al menos 10M y `config/livewire.php` admite 10 MB (revisión del código).

## Fase 1 — Base de datos y datos ya hechos

- [x] **T06b — Configurar MySQL para desarrollo y pruebas.** Conexión `mysql` solo por `.env`, base de datos de pruebas aparte, y retirar el SQLite heredado de T01. Depende de: T02. RF: RF-30
  **Hecho cuando:** `php artisan migrate:status` conecta a MySQL, `phpunit.xml` usa una base de datos distinta de la de desarrollo, `.env.example` trae las variables `DB_*` sin credenciales reales y no queda `database/database.sqlite`.
- [x] **T07r — Instalar `spatie/laravel-permission` y revertir `users.is_admin`.** `User` usa `HasRoles`; `RoleSeeder`; factory `admin()`. Depende de: T06b. RF: RF-03 (base)
  **Hecho cuando:** `User` usa `HasRoles` y `php artisan migrate:fresh --seed` termina sin errores. (Hecha; los roles `admin` y `user` quedan superados por T10b.)
- [x] **T08r — `AdminUserSeeder` asigna el rol en vez de `is_admin`.** Depende de: T07r. RF: RF-28 (base)
  **Hecho cuando:** `php artisan migrate:fresh --seed` crea `admin@etecnia.es` con el rol `admin`. (Hecha; se amplía en T10c.)
- [x] **T09 — Crear modelo, migración y factory de `categories`.** Depende de: T06b. RF: —
  **Hecho cuando:** `php artisan migrate:fresh --seed` termina sin errores. (Hecha; superada por el cambio de alcance, se retiró en T10a.)
- [x] **T10r — `DatabaseSeeder` con `RoleSeeder`, `AdminUserSeeder` y `CategorySeeder`.** Depende de: T07r, T08r, T09. RF: —
  **Hecho cuando:** `php artisan migrate:fresh --seed` termina sin errores. (Hecha; `CategorySeeder` se retira en T10a.)

## Fase 1b — Ajuste al cambio de alcance (gestoría)

- [x] **T10a — Retirar las categorías.** Modelo, migración, factory, `CategorySeeder`, sus tests y su llamada en `DatabaseSeeder`. Depende de: T10r. RF: —
  **Hecho cuando:** `git grep -i category` no encuentra referencias en `app/`, `database/` ni `tests/`, la suite está en verde y `php artisan migrate:fresh --seed` termina sin errores.
- [x] **T10b — Rehacer `RoleSeeder` con permisos de área.** Permisos `invoices` e `insurance_policies`; roles `admin` (ambos), `invoices` e `insurance_policies` (el suyo); desaparece el rol `user`. Depende de: T10a. RF: RF-27
  **Hecho cuando:** `php artisan migrate:fresh --seed` termina sin errores y tinker muestra los dos permisos, los tres roles con sus permisos y ningún rol `user`; ejecutar el seeder dos veces no duplica.
- [x] **T10c — Sembrar los tres usuarios de prueba.** `facturas@etecnia.es` (rol `invoices`), `seguros@etecnia.es` (rol `insurance_policies`) y `admin@etecnia.es` (rol `admin`), contraseña `password`; la factory pasa a tener estados por rol. Depende de: T10b. RF: RF-28
  **Hecho cuando:** `php artisan migrate:fresh --seed` crea los tres usuarios con su rol y contraseña `password`, y ejecutar el seeder dos veces no los duplica ni cambia su contraseña (comprobación con tinker).
- [x] **T10d — Poner el logo en el panel.** Copiar la imagen `.webp` del logo a `public/` y declararla en el `AdminPanelProvider` con `brandLogo` (y `favicon`); es configuración fija, sin acción ni pantalla en el panel. Depende de: T03. RF: —
  **Hecho cuando:** el fichero existe en `public/images/` y la página de login del panel lo muestra (comprobación manual, sin test: es configuración fija).

## Fase 2 — Acceso y roles

- [x] **T11 — Exigir login y bloquear el registro público.** Depende de: T03, T10c. RF: RF-01, RF-02
  **Hecho cuando:** abrir `/admin` sin sesión redirige al login y no existe ruta de registro (comprobación manual).
- [x] **T12 — Restringir `UserResource` al rol `admin`.** `UserPolicy` apoyada en `hasRole('admin')` y recurso con listado. Depende de: T11. RF: RF-03
  **Hecho cuando:** tests comprueban que el `admin` ve el listado y que un usuario sin ese rol no lo ve en la navegación ni accede por URL.
- [x] **T13 — Crear usuarios con roles desde el panel.** Además, fijar el orden de navegación para que Usuarios sea la primera sección del `admin` (regla 15). Depende de: T12. RF: RF-03
  **Hecho cuando:** crear en el panel un usuario con un rol y otro con varios; ambos inician sesión, y al entrar en `/admin` el `admin` llega a Usuarios (comprobación manual).
- [x] **T14 — (Retirada) Gestión de roles.** El `admin` solo asigna o quita roles al crear un usuario (T13); no hay `RoleResource`, relation manager ni edición de permisos de roles. Sin trabajo pendiente.
  **Hecho cuando:** —

## Fase 3 — Facturas

- [x] **T15 — Crear modelo, migración y factory de `invoices`.** `number` único. Depende de: T10c. RF: RF-08 (base)
  **Hecho cuando:** la migración crea `invoices` con `number` único y `amount` decimal(10,2) (`php artisan migrate:fresh --seed` sin errores).
- [ ] **T16 — Crear `InvoiceResource` con autorización por permiso.** `InvoicePolicy` y navegación por el permiso `invoices`; listado. Depende de: T15, T11. RF: RF-05, RF-06, RF-07
  **Hecho cuando:** tests comprueban que quien tiene `invoices` ve todas las facturas, que quien no lo tiene no ve el recurso y recibe 404 al ver, editar o borrar una por URL.
- [ ] **T17 — Crear el formulario de alta de factura.** Número, cliente, fecha de emisión e importe. Depende de: T16, T04. RF: RF-08, RF-16
  **Hecho cuando:** tests comprueban que se crea con los cuatro campos y que falta cualquiera o un número repetido da error; el importe se ve en EUR (comprobación manual).
- [ ] **T18 — Validar el importe de la factura.** Depende de: T17. RF: RF-09
  **Hecho cuando:** tests comprueban que 0, negativos y tres decimales se rechazan con mensaje en español y que 0,01 se acepta.
- [ ] **T19 — Validar la fecha de emisión.** Depende de: T17. RF: RF-10
  **Hecho cuando:** tests con la hora fijada comprueban que mañana se rechaza y que hoy se acepta, también a las 23:59 y a las 00:01.
- [ ] **T20 — Editar, eliminar y ordenar facturas.** Depende de: T17. RF: RF-11
  **Hecho cuando:** editar, eliminar y revisar el orden por fecha de emisión descendente en el panel (comprobación manual).

## Fase 4 — Seguros

- [ ] **T21 — Crear modelo, migración y factory de `insurance_policies`.** `policy_number` único. Depende de: T10c. RF: RF-12 (base)
  **Hecho cuando:** la migración crea `insurance_policies` con `policy_number` único y `premium` decimal(10,2) (`php artisan migrate:fresh --seed` sin errores).
- [ ] **T22 — Crear `InsurancePolicyResource` con autorización por permiso.** `InsurancePolicyPolicy` y navegación por el permiso `insurance_policies`; listado. Depende de: T21, T11. RF: RF-05, RF-06, RF-07
  **Hecho cuando:** tests comprueban que quien tiene `insurance_policies` ve todas las pólizas, que quien no lo tiene no ve el recurso y recibe 404 al ver, editar o borrar una por URL.
- [ ] **T23 — Crear el formulario de alta de póliza.** Número, tomador, aseguradora, fecha de inicio, fecha de fin y prima. Depende de: T22, T04. RF: RF-12, RF-16
  **Hecho cuando:** tests comprueban que se crea con los seis campos y que falta cualquiera o un número repetido da error; la prima se ve en EUR (comprobación manual).
- [ ] **T24 — Validar la prima.** Depende de: T23. RF: RF-13
  **Hecho cuando:** tests comprueban que 0, negativas y tres decimales se rechazan con mensaje en español y que 0,01 se acepta.
- [ ] **T25 — Validar las fechas de la póliza.** Depende de: T23. RF: RF-14
  **Hecho cuando:** tests comprueban que una fecha de fin anterior a la de inicio se rechaza con mensaje en español y que una igual o posterior se acepta.
- [ ] **T26 — Editar, eliminar y ordenar pólizas.** Depende de: T23. RF: RF-15
  **Hecho cuando:** editar, eliminar y revisar el orden por fecha de inicio descendente en el panel (comprobación manual).
- [ ] **T27 — Verificar que un usuario con varios roles accede a ambas áreas.** Depende de: T10c, T16, T22. RF: RF-04
  **Hecho cuando:** un test comprueba que un usuario con los roles `invoices` e `insurance_policies` ve y accede a facturas y a pólizas, y que uno con solo `invoices` no accede a las pólizas.

## Fase 5 — Documentos

- [ ] **T28 — Crear modelo, migración y factory de `documents`, y `document_id` en las tablas de área.** `documents` con `area`, `path` y `original_name`; `document_id` nulo, único y con clave foránea en `invoices` e `insurance_policies`. Depende de: T15, T21. RF: RF-17
  **Hecho cuando:** `php artisan migrate:fresh --seed` termina sin errores y las tablas tienen `document_id` único con clave foránea a `documents`.
- [ ] **T29 — Crear el servicio de almacenamiento S3 y el helper de fallo.** Guardar con clave `{area}/{uuid}.{ext}` y metadato `area`, borrar y leer. Depende de: T05, T28. RF: RF-20, RF-21
  **Hecho cuando:** tests con `Storage::fake('s3')` comprueban que el archivo queda en `s3` con esa clave y el metadato de área, que el disco local no recibe nada, y el helper de `tests/Pest.php` simula fallos al escribir y al borrar.
- [ ] **T30 — Añadir el documento al formulario de factura.** Un único `FileUpload`. Depende de: T17, T29. RF: RF-17, RF-20, RF-21
  **Hecho cuando:** tests crean una factura sin documento y con uno, con el archivo en `s3` y metadato `invoices`, y nada en local.
- [ ] **T31 — Añadir el documento al formulario de póliza.** Un único `FileUpload`. Depende de: T23, T29. RF: RF-17, RF-20, RF-21
  **Hecho cuando:** tests crean una póliza sin documento y con uno, con el archivo en `s3` y metadato `insurance_policies`, y nada en local.
- [ ] **T32 — Validar formato y tamaño de documentos.** Depende de: T30, T31, T06. RF: RF-18, RF-19
  **Hecho cuando:** tests comprueban que un documento de formato o tamaño inválido no crea registro ni documento; que PDF, JPG y PNG y el límite de 10 MB se aceptan se comprueba manualmente.
- [ ] **T33 — Hacer atómica la subida.** Transacción y borrado compensatorio. Depende de: T30, T31. RF: RF-22
  **Hecho cuando:** un test con S3 caído al subir comprueba que no hay registro, ni documento, ni archivo huérfano, y que sale el mensaje en español.
- [ ] **T34 — Borrar el archivo al quitar el documento o eliminar el registro.** Eventos `deleting` en `Invoice` e `InsurancePolicy` (trait compartido). Depende de: T30, T31. RF: RF-23
  **Hecho cuando:** tests comprueban que quitar el documento, eliminar el registro y el borrado masivo eliminan el archivo del disco `s3` simulado.
- [ ] **T35 — Conservar el registro si falla el borrado en S3.** Depende de: T34. RF: RF-24
  **Hecho cuando:** un test con S3 caído al borrar comprueba que el registro sigue, que el archivo no se toca y que sale el mensaje en español.
- [ ] **T36 — Añadir la vista del registro con descarga del documento.** Acción de Filament con streaming desde `s3`. Depende de: T30, T31. RF: RF-25
  **Hecho cuando:** en el panel, quien tiene el permiso del área descarga el documento y un archivo ausente muestra un error en español (comprobación manual).
- [ ] **T37 — Denegar el acceso a documentos de un área sin permiso.** Con las policies del registro y de `Document` (por `area`). Depende de: T36. RF: RF-06, RF-26
  **Hecho cuando:** tests comprueban que un usuario sin el permiso del área recibe 404 al ver o descargar un documento.

## Fase 6 — Datos de ejemplo

- [ ] **T38 — Sembrar 3 facturas de ejemplo.** Valores fijos, idempotente. Depende de: T15. RF: RF-29
  **Hecho cuando:** `php artisan db:seed` crea 3 facturas con los valores fijos, y ejecutarlo dos veces no duplica (comprobación con tinker).
- [ ] **T39 — Sembrar 3 pólizas de ejemplo y registrar ambos seeders.** Valores fijos, idempotente; ambos seeders en `DatabaseSeeder`. Depende de: T21, T38. RF: RF-29
  **Hecho cuando:** `php artisan migrate:fresh --seed` termina sin errores y crea 3 pólizas con los valores fijos, sin duplicar al repetir (comprobación con tinker).

## Fase 7 — Cierre

- [ ] **T40 — Revisar que no hay CRUD manual.** Depende de: T37, T39. RF: criterio 5 de `spec.md`
  **Hecho cuando:** `ls app/Http/Controllers` y `resources/views` no contienen controladores ni vistas del CRUD (revisión manual, sin test).
- [ ] **T41 — Verificar los criterios de finalización.** Depende de: T40. RF: todos
  **Hecho cuando:** `./vendor/bin/pest` está en verde, `./vendor/bin/pint` no cambia nada, `git grep` no encuentra credenciales y cada RF crítico tiene test.

## Cobertura de RF

| RF | Tareas |
|----|--------|
| _Con test: RF-03 a RF-10, RF-12 a RF-14, RF-19, RF-21 a RF-24, RF-26. El resto, verificación manual (ver `plan.md`)._ | |
| RF-01, RF-02 | T11 |
| RF-03 | T12, T13 |
| RF-04 | T27 |
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
