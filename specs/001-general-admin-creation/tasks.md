# Tareas 001 — Administración básica de gastos

Divide `plan.md` en tareas de menos de 30 minutos, ordenadas por dependencia. Cada tarea indica los RF de `spec.md` que cubre.

**Regla común (regla 8):** cada tarea empieza escribiendo su test Pest y comprobando que falla. Se da por terminada cuando ese test pasa, `./vendor/bin/pest` está en verde y `./vendor/bin/pint` no cambia nada. Los tests mockean S3 siempre (regla 9).

## Antes de empezar

- [x] **T00 — Aprobar los tres puntos del plan.** Esquema, dependencias (`league/flysystem-aws-s3-v3`, `laravel-lang/lang`) e interpretación de la regla 6.
  **Hecho cuando:** el usuario confirma los tres puntos por escrito.

## Fase 0 — Base

- [x] **T01 — Crear el proyecto Laravel en la raíz.** Depende de: T00. RF: —
  **Hecho cuando:** `php artisan --version` responde y `git status` muestra `AGENTS.md`, `CLAUDE.md`, `docs/`, `specs/` y `.claude/` sin modificar.
- [x] **T02 — Instalar Pest con un test de humo.** Depende de: T01. RF: —
  **Hecho cuando:** `./vendor/bin/pest` termina en verde con ese test.
- [ ] **T03 — Instalar Filament y crear el panel único.** Depende de: T02. RF: —
  **Hecho cuando:** un test comprueba que la ruta del panel existe y responde.
- [ ] **T04 — Fijar idioma, zona horaria y traducciones.** Locale `es`, zona `Europe/Madrid`, `laravel-lang/lang`. Depende de: T03. RF: RF-13 (base), mensajes en español
  **Hecho cuando:** un test comprueba el locale, la zona horaria y que el mensaje de validación `required` sale en español.
- [ ] **T05 — Configurar el disco `s3` solo por `.env`.** Driver S3, privado, sin valores literales. Depende de: T03. RF: RF-19 (base)
  **Hecho cuando:** `.env.example` trae las claves `AWS_*` vacías, un test comprueba que el disco `s3` es privado y `git grep` no encuentra credenciales en código.
- [ ] **T06 — Permitir subidas de 10 MB.** Límite de Livewire y límites de PHP. Depende de: T03. RF: RF-17 (base)
  **Hecho cuando:** un test comprueba que la regla de subida temporal de Livewire admite 10 MB y `php -r` muestra `upload_max_filesize` y `post_max_size` de al menos 10M en el entorno de desarrollo.

## Fase 1 — Datos iniciales

- [ ] **T06b — Configurar MySQL para desarrollo y pruebas.** Conexión `mysql` solo por `.env`, base de datos de pruebas aparte, y retirar el SQLite heredado de T01. Depende de: T02. RF: RF-26
  **Hecho cuando:** un test comprueba que la conexión por defecto es `mysql` y que la de pruebas usa una base de datos distinta de la de desarrollo, `php artisan migrate:status` conecta, `.env.example` trae las variables `DB_*` sin credenciales reales y no queda `database/database.sqlite`.
- [ ] **T07 — Añadir `users.is_admin` y el estado `admin` de la factory.** Depende de: T06b. RF: RF-03 (base)
  **Hecho cuando:** un test comprueba que `is_admin` es falso por defecto y que la factory crea administradores.
- [ ] **T08 — Crear `AdminUserSeeder` idempotente.** Depende de: T07. RF: RF-25
  **Hecho cuando:** tests comprueban que crea `admin@etecnia.es` con `is_admin` y contraseña `password` válida, y que ejecutarlo dos veces no lo duplica ni cambia su contraseña.
- [ ] **T09 — Crear modelo, migración y factory de `categories`.** Depende de: T06b. RF: RF-06, RF-07 (base)
  **Hecho cuando:** un test comprueba que el nombre es único.
- [ ] **T10 — Crear `CategorySeeder` y `DatabaseSeeder`.** Depende de: T08, T09. RF: RF-06
  **Hecho cuando:** tests comprueban que existen las categorías iniciales y que ejecutarlo dos veces no duplica; `php artisan migrate:fresh --seed` termina sin errores.

## Fase 2 — Acceso y roles

- [ ] **T11 — Exigir login y bloquear el registro público.** Depende de: T03, T08. RF: RF-01, RF-02
  **Hecho cuando:** tests comprueban que un visitante es redirigido al login y que no existe ruta de registro.
- [ ] **T12 — Restringir `UserResource` a administradores.** `UserPolicy` y recurso con listado. Depende de: T11. RF: RF-03
  **Hecho cuando:** tests comprueban que el administrador ve el listado y que un usuario normal no lo ve ni accede por URL.
- [ ] **T13 — Crear usuarios con rol desde el panel.** Depende de: T12. RF: RF-03
  **Hecho cuando:** un test crea un usuario administrador y otro normal, con contraseña cifrada, y ambos pueden iniciar sesión.

## Fase 3 — Categorías

- [ ] **T14 — Crear `CategoryPolicy`.** Ver para todos; crear, editar y borrar solo administrador. Depende de: T09, T12. RF: RF-07, RF-08
  **Hecho cuando:** tests de la policy cubren cada acción para administrador y usuario normal.
- [ ] **T15 — Crear `CategoryResource` con CRUD.** Depende de: T14. RF: RF-07, RF-08
  **Hecho cuando:** tests comprueban que el administrador crea, edita y elimina una categoría sin gastos, y que un usuario normal ve el listado sin acciones de escritura.
- [ ] **T16 — Crear modelo, migración y factory de `expenses`.** FK de categoría con restrict e índice `(user_id, spent_on)`. Depende de: T09. RF: RF-04, RF-10 (base)
  **Hecho cuando:** un test comprueba que borrar a nivel de base de datos una categoría con gastos lanza error.
- [ ] **T17 — Bloquear el borrado de categorías con gastos.** Individual y masivo. Depende de: T15, T16. RF: RF-09
  **Hecho cuando:** tests comprueban que sale una notificación en español y que la categoría sigue existiendo en ambos casos.

## Fase 4 — Gastos

- [ ] **T18 — Aislar los gastos por usuario.** `ExpensePolicy` y consulta acotada del recurso, con listado básico. Depende de: T16, T11. RF: RF-04, RF-05
  **Hecho cuando:** tests comprueban que el listado solo contiene gastos propios, incluso para un administrador, y que ver, editar y borrar un gasto ajeno devuelve 404.
- [ ] **T19 — Crear el formulario de alta de gasto.** Fecha, importe, categoría y descripción opcional. Depende de: T18, T04. RF: RF-10, RF-13
  **Hecho cuando:** tests comprueban que se crea con los tres campos obligatorios, que falta cualquiera da error, que la descripción es opcional y que el importe se muestra en EUR.
- [ ] **T20 — Validar el importe.** Depende de: T19. RF: RF-11
  **Hecho cuando:** tests comprueban que 0, negativos y tres decimales se rechazan con mensaje en español y que 0,01 se acepta.
- [ ] **T21 — Validar la fecha.** Depende de: T19. RF: RF-12
  **Hecho cuando:** tests con la hora fijada comprueban que mañana se rechaza y que hoy se acepta, también a las 23:59 y a las 00:01.
- [ ] **T22 — Editar y eliminar gastos propios.** Depende de: T19. RF: RF-14
  **Hecho cuando:** tests comprueban que el usuario edita y elimina sus gastos y que los cambios se reflejan.
- [ ] **T23 — Ordenar el listado por fecha descendente.** Depende de: T18. RF: RF-15, RF-13
  **Hecho cuando:** un test comprueba el orden, con desempate estable para fechas iguales.
- [ ] **T24 — Filtrar por categoría y rango de fechas.** Depende de: T23. RF: RF-15
  **Hecho cuando:** tests comprueban cada filtro por separado y combinados, y que nunca devuelven gastos de otros usuarios.

## Fase 5 — Documentos

- [ ] **T25 — Crear modelo, migración y factory de `expense_documents`.** Depende de: T16. RF: RF-16
  **Hecho cuando:** tests comprueban que un gasto es válido sin documentos y que admite varios, de tipo ticket y factura.
- [ ] **T26 — Crear el servicio de almacenamiento S3 y el helper de fallo.** Guardar, borrar y leer con claves `documents/{user_id}/{uuid}`. Depende de: T05. RF: RF-19
  **Hecho cuando:** tests con `Storage::fake('s3')` comprueban que el archivo queda en `s3` con esa clave y que el disco local no recibe nada, y el helper de `tests/Pest.php` simula fallos al escribir y al borrar.
- [ ] **T27 — Añadir el repeater de documentos al formulario.** Tipo y archivo, sin mínimo de elementos. Depende de: T19, T25, T26. RF: RF-16, RF-19
  **Hecho cuando:** tests crean un gasto con 0, 1 y varios documentos de ambos tipos, con los archivos en `s3` y nada en local.
- [ ] **T28 — Validar formato y tamaño de documentos.** Depende de: T27, T06. RF: RF-17, RF-18
  **Hecho cuando:** tests comprueban que se aceptan PDF, JPG y PNG, que se rechaza otro formato, que 10 MB pasa y 10 MB + 1 no, y que ante un rechazo no se crea gasto ni registros.
- [ ] **T29 — Hacer atómica la subida.** Transacción y borrado compensatorio. Depende de: T27. RF: RF-20
  **Hecho cuando:** un test con S3 caído al subir comprueba que no hay gasto, ni registros, ni archivos huérfanos, y que sale el mensaje en español.
- [ ] **T30 — Borrar los archivos al quitar un documento o eliminar un gasto.** Eventos `deleting`. Depende de: T27. RF: RF-21
  **Hecho cuando:** tests comprueban que quitar un documento, eliminar un gasto y el borrado masivo eliminan los archivos del disco `s3` simulado.
- [ ] **T31 — Conservar el registro si falla el borrado en S3.** Depende de: T30. RF: RF-22
  **Hecho cuando:** un test con S3 caído al borrar comprueba que el registro sigue, que el archivo no se toca y que sale el mensaje en español.
- [ ] **T32 — Permitir quitar todos los documentos de un gasto.** Depende de: T30. RF: RF-16, RF-21
  **Hecho cuando:** un test quita todos los documentos de un gasto, lo guarda sin errores y sus archivos desaparecen de `s3`.
- [ ] **T33 — Añadir la vista del gasto con descarga de documentos.** Acción de Filament con streaming desde `s3`. Depende de: T27. RF: RF-23
  **Hecho cuando:** tests comprueban que el dueño descarga el contenido exacto sin usar URL firmada y que un archivo ausente muestra un error en español.
- [ ] **T34 — Denegar el acceso a documentos ajenos.** `ExpenseDocumentPolicy`. Depende de: T33, T18. RF: RF-05, RF-24
  **Hecho cuando:** tests comprueban que un usuario ajeno recibe 404 al ver o descargar un documento.

## Fase 6 — Cierre

- [ ] **T35 — Añadir tests de arquitectura.** Depende de: T34. RF: criterio 5 de `spec.md`
  **Hecho cuando:** los tests comprueban que no hay controladores propios para el CRUD ni vistas Blade manuales.
- [ ] **T36 — Verificar los criterios de finalización.** Depende de: T35. RF: todos
  **Hecho cuando:** `./vendor/bin/pest` está en verde, `./vendor/bin/pint` no cambia nada, `git grep` no encuentra credenciales y cada RF-01 a RF-26 tiene al menos un test.

## Cobertura de RF

| RF | Tareas |
|----|--------|
| RF-01, RF-02 | T11 |
| RF-03 | T07, T12, T13 |
| RF-04 | T16, T18 |
| RF-05 | T18, T34 |
| RF-06 | T09, T10 |
| RF-07, RF-08 | T14, T15 |
| RF-09 | T16, T17 |
| RF-10 | T19 |
| RF-11 | T20 |
| RF-12 | T21 |
| RF-13 | T04, T19, T23 |
| RF-14 | T22 |
| RF-15 | T23, T24 |
| RF-16 | T25, T27, T32 |
| RF-17, RF-18 | T06, T28 |
| RF-19 | T05, T26, T27 |
| RF-20 | T29 |
| RF-21 | T30, T32 |
| RF-22 | T31 |
| RF-23 | T33 |
| RF-24 | T34 |
| RF-25 | T08 |
| RF-26 | T06b |
