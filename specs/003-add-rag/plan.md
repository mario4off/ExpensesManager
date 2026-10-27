# Plan 003 — RAG sobre documentos con Bedrock

Implementa `spec.md` (RF-01 a RF-14). Las tareas se ejecutan una a una cuando el usuario lo indique.

## Cambios en las reglas del proyecto

Actualizar `AGENTS.md` y `docs/constitution.md` al orden aprobado: conversación → RAG → SQL. Sustituir en las reglas y referencias documentales afectadas la exigencia de metadato `area` en el objeto S3 por el área en `documents.area` y los metadatos de indexación de Bedrock. Conservar las rutas `docs/{area}/...`.

## Integración

| Módulo | Responsabilidad | RF |
|--------|-----------------|----|
| Documentos | Generar y retirar `.metadata.json`; preparar documentos existentes | RF-01 a RF-03 |
| Sincronización | Solicitar ingestas después del commit; coordinar, comprobar resultados y reintentar | RF-04 a RF-06 |
| Recuperación | Consultar Bedrock, filtrar áreas y verificar cada fuente en MySQL y S3 | RF-07, RF-08 |
| Asistente | Proporcionar fragmentos autorizados a `ChatModel`, conservar referencias en sesión y gestionar errores | RF-09 a RF-11 |
| Enlaces | Agrupar fuentes, comprobar acceso y generar previsualizaciones temporales y enlaces a Filament | RF-12 a RF-14 |

Mantener `ChatModel` como abstracción de Ollama. Reutilizar el SDK AWS y las colas instaladas, sin nuevas dependencias ni tablas. Configurar mediante `.env` la región, Knowledge Base, origen de datos, tiempos máximos y límite de resultados: cinco fragmentos por defecto.

## Preparación y operación

Documentar la preparación manual de la Knowledge Base con origen S3, filtros por metadatos y soporte de PDF y PNG mediante su procesamiento nativo. No imponer BDA ni implementar reconocimiento de imágenes en Laravel. Documentar también el worker, la operación inicial y la recuperación de sincronizaciones fallidas.

La aplicación solicita la sincronización después de confirmar los cambios. La coordinación debe impedir ingestas simultáneas, comprobar su resultado, limitar reintentos y garantizar otra ingesta cuando haya cambios pendientes. Un fallo de indexación no deshace un registro guardado; los logs no incluyen contenido documental.

## Verificación

Añadir solo tests para aislamiento de áreas, rechazo de fuentes manipuladas o invalidadas y protección de previsualización. Escribirlos primero y verificar que fallan; mockear S3, Bedrock y Ollama. En caso de duda, preferir la comprobación manual. Verificar manualmente respuestas, seguimientos, estados, sincronización y navegación. Ejecutar Pest y Pint al terminar la implementación.
