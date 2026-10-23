# Spec 001 — Administración básica de gastos

## Por qué

Registrar gastos personales por categorías y conservar sus tickets y facturas en un único sitio. Esta spec cubre solo el panel de administración (CRUD en Filament). Es la base de datos sobre la que el asistente de chat trabajará en fases posteriores.

## Decisiones cerradas

- Multiusuario con aislamiento: cada usuario ve solo sus gastos y documentos.
- Categorías globales, gestionadas solo por el administrador.
- Dos roles: administrador y usuario normal. Los usuarios los crea el administrador.
- Moneda única: EUR.
- Documentos en S3 privado; descarga a través de la aplicación con las credenciales del `.env`, solo para el dueño.
- Base de datos MySQL.

## Requisitos funcionales (EARS)

### Acceso y roles

- **RF-01** Cuando un visitante no autenticado acceda al panel, el sistema le pedirá iniciar sesión.
- **RF-02** El sistema no permitirá el registro público de usuarios.
- **RF-03** Cuando un administrador lo solicite, el sistema permitirá crear usuarios y asignarles el rol de administrador o de usuario normal.

### Aislamiento

- **RF-04** El sistema mostrará a cada usuario únicamente sus propios gastos y documentos.
- **RF-05** Si un usuario intenta acceder a un gasto o documento de otro usuario, el sistema responderá como si no existiera (404).

### Categorías

- **RF-06** El sistema dispondrá de un conjunto inicial de categorías precargado.
- **RF-07** Cuando un administrador lo solicite, el sistema permitirá crear, editar y eliminar categorías.
- **RF-08** Mientras el usuario no sea administrador, el sistema solo le permitirá ver y seleccionar categorías.
- **RF-09** Si se intenta eliminar una categoría con gastos asociados, el sistema bloqueará el borrado y mostrará un mensaje en español que explique el motivo.

### Gastos

- **RF-10** Cuando un usuario registre un gasto, el sistema exigirá fecha, importe y categoría, y admitirá una descripción opcional.
- **RF-11** Si el importe no es mayor que 0 o tiene más de 2 decimales, el sistema rechazará el gasto con un mensaje en español.
- **RF-12** Si la fecha es futura, el sistema rechazará el gasto con un mensaje en español.
- **RF-13** El sistema expresará todos los importes en EUR.
- **RF-14** Cuando un usuario lo solicite, el sistema permitirá editar y eliminar sus propios gastos.
- **RF-15** El sistema listará los gastos del usuario ordenados por fecha descendente y permitirá filtrarlos por categoría y rango de fechas.

### Documentos

- **RF-16** El sistema permitirá guardar un gasto sin documentos asociados y adjuntarle, de forma opcional, uno o más documentos, cada uno marcado como ticket o factura. Un gasto seguirá siendo válido si se le quitan todos sus documentos.
- **RF-17** El sistema solo aceptará documentos PDF, JPG o PNG de hasta 10 MB.
- **RF-18** Si un documento no cumple formato o tamaño, el sistema lo rechazará con un mensaje en español y no guardará el gasto de forma parcial.
- **RF-19** El sistema almacenará los documentos únicamente en S3, nunca en disco local.
- **RF-20** Si falla la subida a S3, el sistema no guardará el gasto, informará en español y no dejará registros de documentos sin archivo.
- **RF-21** Cuando un usuario quite un documento o elimine un gasto, el sistema borrará también los archivos correspondientes de S3.
- **RF-22** Si falla el borrado en S3, el sistema informará del error en español y no eliminará el registro, para no dejar archivos huérfanos ni registros sin archivo.
- **RF-23** Cuando el dueño de un documento solicite su descarga, el sistema se la servirá usando las credenciales del `.env`, con el bucket privado.
- **RF-24** Si quien solicita la descarga no es el dueño, el sistema la denegará como si el documento no existiera.

### Datos iniciales

- **RF-25** Cuando se ejecute la carga inicial de datos (seeder), el sistema creará un usuario administrador inicial con email `admin@etecnia.es` y contraseña `password`, sin duplicarlo si ya existe. Son credenciales solo de desarrollo y deben cambiarse fuera de local.

### Plataforma

- **RF-26** El sistema almacenará los datos de usuarios, categorías, gastos y documentos en una base de datos MySQL, con la conexión configurada solo en el `.env`.

## Fuera de alcance

- Asistente de chat, consultas SQL del asistente y RAG con Bedrock (fases 1-3, specs propias).
- Informes, gráficos y paneles de resumen.
- Importación y exportación de datos.
- Gastos recurrentes y presupuestos.
- OCR o lectura automática de tickets.
- Multimoneda.
- Registro público de usuarios y recuperación de contraseña autoservicio.
- Categorías propias por usuario.

## Criterios de finalización

1. Cada RF tiene al menos un test Pest escrito antes de implementar y verificado en rojo, y todos pasan.
2. S3 está mockeado en todos los tests; ninguno llama a AWS real.
3. Un usuario nunca ve ni descarga datos de otro (RF-04, RF-05, RF-24 cubiertos por tests).
4. Todos los mensajes al usuario están en español; las referencias de código, en inglés.
5. Todo el CRUD usa recursos de Filament; sin controladores ni vistas Blade manuales.
6. `./vendor/bin/pest` en verde y `./vendor/bin/pint` sin cambios.
7. Ninguna credencial en código ni commits.
