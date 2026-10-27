# Plan 003 — RAG sobre documentos con Bedrock

Implementa `spec.md` (RF-01 a RF-18). Las tareas se ejecutan una a una cuando el usuario lo indique.

## Integración

| Módulo | Responsabilidad | RF |
|--------|-----------------|----|
| Preparación de AWS | Crear y configurar desde la consola la Knowledge Base, origen S3, procesamiento documental, almacenamiento, IAM y valores de conexión | RF-15 a RF-18 |
| Documentos | Generar y retirar `.metadata.json`; preparar documentos existentes | RF-01 a RF-03 |
| Sincronización | Solicitar ingestas después del commit; coordinar, comprobar resultados y reintentar | RF-04 a RF-06 |
| Recuperación | Consultar Bedrock, filtrar áreas y verificar cada fuente en MySQL y S3 | RF-07, RF-08 |
| Asistente | Proporcionar fragmentos autorizados a `ChatModel`, conservar referencias en sesión y gestionar errores | RF-09 a RF-11 |
| Enlaces | Agrupar fuentes, comprobar acceso y generar previsualizaciones temporales y enlaces a Filament | RF-12 a RF-14 |

Mantener `ChatModel` como abstracción de Ollama. Reutilizar el SDK AWS y las colas instaladas, sin nuevas dependencias ni tablas. Configurar mediante `.env` la región, Knowledge Base, origen de datos, tiempos máximos y límite de resultados: cinco fragmentos por defecto.

## Preparación y operación

Crear y configurar manualmente desde la consola AWS una Knowledge Base y su origen S3 antes de integrar Laravel. Utilizar el bucket existente y su región, limitar el origen a los prefijos de documentos de la aplicación y configurar el procesamiento nativo de PDF y PNG, incluidos escaneados, el almacenamiento y los metadatos filtrables. No imponer BDA ni implementar reconocimiento de imágenes en Laravel.

Configurar el rol de servicio de Bedrock y los permisos de la aplicación para consultar, iniciar y comprobar ingestas, limitados a los recursos utilizados. Recoger la región y los identificadores de Knowledge Base y origen para la configuración de Laravel mediante `.env`; documentar las opciones efectivas de procesamiento, embeddings y almacenamiento sin incluir credenciales.

La creación y configuración inicial de AWS es una tarea manual diferenciada de las sincronizaciones automáticas que solicitará Laravel. Comprobar la disponibilidad de los recursos al finalizar su preparación; realizar la comprobación completa de ingesta de PDF y PNG, recuperación y filtrado por área después de preparar los `.metadata.json`. Documentar también el worker, la operación inicial y la recuperación de sincronizaciones fallidas.

Referencias oficiales:

- [Creación de Knowledge Bases](https://docs.aws.amazon.com/bedrock/latest/userguide/knowledge-base-create.html).
- [Conexión con S3](https://docs.aws.amazon.com/bedrock/latest/userguide/s3-data-source-connector.html).
- [Permisos IAM](https://docs.aws.amazon.com/bedrock/latest/userguide/kb-permissions.html).

La aplicación solicita la sincronización después de confirmar los cambios. La coordinación debe impedir ingestas simultáneas, comprobar su resultado, limitar reintentos y garantizar otra ingesta cuando haya cambios pendientes. Un fallo de indexación no deshace un registro guardado; los logs no incluyen contenido documental.

## Verificación

Añadir solo tests para aislamiento de áreas, rechazo de fuentes manipuladas o invalidadas y protección de previsualización. Escribirlos primero y verificar que fallan; mockear S3, Bedrock y Ollama. En caso de duda, preferir la comprobación manual. No añadir tests automatizados de configuración AWS. Verificar manualmente la preparación de AWS, la ingesta de PDF y PNG, la recuperación filtrada por área, respuestas, seguimientos, estados, sincronización y navegación. Ejecutar Pest y Pint al terminar la implementación.
