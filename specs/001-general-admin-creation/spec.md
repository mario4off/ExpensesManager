# Spec 001 — Administración básica de la gestoría

## Por qué

Simular una gestoría ficticia con dos áreas, **Facturas** y **Seguros**, para experimentar. Esta spec cubre solo el panel de administración (CRUD en Filament). Deja datos estructurados de ambas áreas, y sus documentos en S3, para fases posteriores. Es un MVP / PoC de mínimos.

## Decisiones cerradas

- Gestoría ficticia con dos áreas: Facturas y Seguros.
- Roles y permisos con Spatie Laravel-permission: permisos de área `invoices` e `insurance_policies`; roles `admin`, `invoices` e `insurance_policies`.
- El rol `admin` tiene los dos permisos y gestiona los usuarios: al crear un usuario le asigna o le quita roles. Los roles y sus permisos son fijos: no se crean, borran ni modifican desde el panel.
- Quien tiene el permiso de un área ve todos los registros de esa área.
- Moneda única: EUR.
- Los campos de fecha de los formularios usan el selector de fecha de Filament (`native(false)`), no el del navegador.
- Las acciones de cada registro de una tabla se agrupan en un único menú (`ActionGroup`), no como botones sueltos.
- Cada factura o póliza tiene como máximo un documento, guardado en S3 privado con un metadato de área; la descarga pasa por la aplicación con las credenciales del `.env`.
- Base de datos MySQL.

## Requisitos funcionales (EARS)

### Acceso y roles

- **RF-01** Cuando un visitante no autenticado acceda al panel, el sistema le pedirá iniciar sesión.
- **RF-02** El sistema no permitirá el registro público de usuarios.
- **RF-03** Cuando un usuario con rol `admin` lo solicite, el sistema permitirá crear usuarios y asignarles uno o varios roles.
- **RF-04** Mientras un usuario tenga varios roles, el sistema le dará acceso a las áreas de todos ellos.

### Autorización por área

- **RF-05** Mientras un usuario no tenga el permiso de un área, el sistema no le mostrará los recursos de esa área en el panel.
- **RF-06** Si un usuario intenta acceder a un registro o documento de un área sin tener su permiso, el sistema se lo denegará.
- **RF-07** Mientras un usuario tenga el permiso de un área, el sistema le mostrará todos los registros de esa área.

### Facturas

- **RF-08** Cuando se registre una factura, el sistema exigirá número de factura (único), nombre del cliente, fecha de emisión e importe.
- **RF-09** El sistema limitará el campo de importe a 2 decimales y admitirá importes negativos.
- **RF-10** Si la fecha de emisión es futura, el sistema rechazará la factura con un mensaje en español.
- **RF-11** El sistema permitirá editar y eliminar facturas, y las listará ordenadas por fecha de emisión descendente.

### Seguros

- **RF-12** Cuando se registre una póliza, el sistema exigirá número de póliza (único), nombre del tomador, aseguradora, fecha de inicio, fecha de fin y prima.
- **RF-13** El sistema limitará el campo de prima a 2 decimales y admitirá primas negativas.
- **RF-14** Si la fecha de fin es anterior a la fecha de inicio, el sistema rechazará la póliza con un mensaje en español.
- **RF-15** El sistema permitirá editar y eliminar pólizas, y las listará ordenadas por fecha de inicio descendente.

### Común

- **RF-16** El sistema expresará todos los importes y primas en EUR.

### Documentos

- **RF-17** El sistema permitirá guardar una factura o póliza sin documento, o con un único documento.
- **RF-18** El sistema solo aceptará documentos PDF, JPG o PNG de hasta 10 MB.
- **RF-19** Si un documento no cumple formato o tamaño, el sistema lo rechazará con un mensaje en español y no guardará el registro de forma parcial.
- **RF-20** El sistema almacenará los documentos únicamente en S3, nunca en disco local.
- **RF-21** Cuando el sistema guarde un documento en S3, le añadirá un metadato con su área (`invoices` o `insurance_policies`).
- **RF-22** Si falla la subida a S3, el sistema no guardará el registro, informará en español y no dejará registros de documento sin archivo.
- **RF-23** Cuando se quite el documento de un registro o se elimine el registro, el sistema borrará también el archivo de S3.
- **RF-24** Si falla el borrado en S3, el sistema informará del error en español y no eliminará el registro, para no dejar archivos huérfanos ni registros sin archivo.
- **RF-25** Cuando un usuario con el permiso del área solicite la descarga de un documento, el sistema se la servirá usando las credenciales del `.env`, con el bucket privado.
- **RF-26** Si quien solicita la descarga no tiene el permiso del área, el sistema la denegará.

### Datos iniciales

- **RF-27** Cuando se ejecute la carga inicial de datos (seeder), el sistema creará los permisos `invoices` e `insurance_policies` y los roles `admin` (con ambos permisos), `invoices` (con `invoices`) e `insurance_policies` (con `insurance_policies`), sin duplicarlos.
- **RF-28** Cuando se ejecute la carga inicial de datos, el sistema creará tres usuarios de prueba con contraseña `password`: `facturas@etecnia.es` con rol `invoices`, `seguros@etecnia.es` con rol `insurance_policies` y `admin@etecnia.es` con rol `admin`, sin duplicarlos si ya existen. Son credenciales solo de desarrollo y deben cambiarse fuera de local.
- **RF-29** Cuando se ejecute la carga inicial de datos, el sistema creará 3 facturas y 3 pólizas de ejemplo con valores fijos, sin duplicarlas.

### Plataforma

- **RF-30** El sistema almacenará los datos de usuarios, roles, facturas, pólizas y documentos en una base de datos MySQL, con la conexión configurada solo en el `.env`.

## Fuera de alcance

- Asistente de chat, consultas SQL del asistente y RAG con Bedrock (fases 1-3, specs propias).
- Informes, gráficos y paneles de resumen.
- Importación y exportación de datos.
- OCR o lectura automática de documentos.
- Multimoneda.
- Registro público de usuarios y recuperación de contraseña autoservicio.
- Más áreas que Facturas y Seguros.
- Crear, borrar o modificar roles y permisos desde el panel (los roles y sus permisos son fijos).
- Más de un documento por registro.

## Criterios de finalización

1. Los RF críticos (RF-03 a RF-08, RF-10, RF-12, RF-14, RF-19, RF-21 a RF-24, RF-26) tienen test Pest escrito antes de implementar y verificado en rojo, y todos pasan. El resto se verifica manualmente.
2. S3 está mockeado en todos los tests; ninguno llama a AWS real.
3. Un usuario nunca ve ni descarga datos de un área sin su permiso (RF-05, RF-06, RF-26 cubiertos por tests).
4. Todos los mensajes al usuario están en español; las referencias de código, en inglés.
5. Todo el CRUD usa recursos de Filament; sin controladores ni vistas Blade manuales (se verifica revisando el código).
6. `./vendor/bin/pest` en verde y `./vendor/bin/pint` sin cambios.
7. Ninguna credencial en código ni commits.
