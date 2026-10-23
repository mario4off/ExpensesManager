# AGENTS.md — ExpensesManager

## Proyecto
Aplicación de gastos personales en Laravel + Filament: registrar gastos por categorías y subir tickets y facturas. Incluye un asistente de chat que responde en lenguaje natural sobre los gastos y sus documentos.
El foco del proyecto es **experimentar con la creación del chatbot**; el resto es CRUD estándar.
- Panel y CRUD: recursos de Filament.
- Asistente: componente Livewire personalizado, con conversación temporal guardada en la sesión (sin persistencia en base de datos).
- Documentos: almacenados en S3.
- RAG sobre documentos: AWS Bedrock.
- Consultas sobre gastos: SQL.

## Fases del chatbot
1. Asistente Livewire con conversación en sesión.
2. Consultas SQL sobre gastos y categorías.
3. RAG con Bedrock sobre los documentos de S3.

Implementa solo la fase pedida. No adelantes fases posteriores.

## Comandos
- Instalar: `composer install && npm install`
- Ejecutar: `composer dev` (alternativa: `php artisan serve`)
- Migraciones: `php artisan migrate`
- Tests: `./vendor/bin/pest`
- Un test: `./vendor/bin/pest --filter="nombre del test"`
- Formato: `./vendor/bin/pint`

## Estilo y convenciones
- PHP y Laravel en sus versiones estables actuales; sigue PSR-12 vía Pint.
- Referencias (clases, métodos, variables, tablas, claves) en inglés.
- Mensajes al usuario (interfaz, validaciones, respuestas del asistente) en español.
- Tests con Pest, no PHPUnit clásico.

## Reglas
- Lee `docs/constitution.md` antes de tocar código.
- Usa recursos, formularios y tablas de Filament para todo lo que no sea el asistente. No crees controladores ni vistas Blade a mano para el CRUD.
- El asistente es un componente Livewire propio, no un widget de Filament.
- Subidas de tickets y facturas: disco `s3`. No guardes documentos en local.
- Toda consulta SQL del asistente debe ser de solo lectura y limitada al usuario autenticado.
- Credenciales de AWS y claves solo en `.env`; nunca en código ni en commits.
- No añadas dependencias nuevas sin preguntar.
- Pregunta antes de cambiar el esquema de la base de datos o la estructura de las fases.

## Al terminar cualquier tarea
- Escribe primero el test Pest y verifica que falla; implementa hasta que pase.
- Ejecuta `./vendor/bin/pest` y confirma que pasa.
- Ejecuta `./vendor/bin/pint`.
- Mockea S3 y Bedrock en los tests: nunca llames a AWS real.
