# Spec 003 — RAG sobre documentos con Bedrock

## Por qué

Permitir que el usuario consulte en lenguaje natural el contenido de los documentos de facturas y pólizas desde el asistente de la barra lateral. La respuesta identifica sus fuentes y ofrece enlaces al archivo y al registro asociado en Filament.

## Decisiones cerradas

- Bedrock Knowledge Bases extrae, indexa y recupera el contenido de los PDF y PNG, incluidos documentos escaneados. Ollama redacta la respuesta en español a partir de los fragmentos recuperados.
- Se incluyen todos los documentos existentes y nuevos vinculados a facturas o pólizas de la aplicación. La sincronización es automática y se ejecuta en segundo plano.
- Se amplía el asistente de `002-agent-ui` y se conserva su conversación en sesión. SQL queda para una spec posterior.
- Se reutilizan el SDK de AWS y las colas existentes, sin dependencias nuevas ni cambios de esquema.
- El área de referencia es `documents.area`. Los archivos `.metadata.json` permiten a Bedrock filtrar por área y relacionar las fuentes con los registros. Se retirará el metadato redundante `area` de los objetos S3.
- La extracción y el reconocimiento de los documentos se delegan en Knowledge Bases, sin imponer Bedrock Data Automation ni implementar reconocimiento de imágenes en Laravel.

## Requisitos funcionales (EARS)

### Documentos e indexación

- **RF-01.** El sistema incluirá en la Knowledge Base únicamente documentos vinculados a facturas o pólizas existentes.
- **RF-02.** Cuando prepare un documento para indexar, el sistema generará junto al archivo un `.metadata.json` con su identificador y área obtenidos de la base de datos.
- **RF-03.** El sistema ofrecerá una operación inicial repetible para preparar los metadatos y solicitar la indexación de los documentos existentes.
- **RF-04.** Cuando se confirme una subida, sustitución, retirada de documento o eliminación de registro, el sistema solicitará la sincronización mediante trabajos en cola después de confirmar la transacción.
- **RF-05.** El sistema coordinará las sincronizaciones, comprobará su finalización y aplicará reintentos limitados. Los cambios recibidos durante una ingesta se procesarán en otra posterior.
- **RF-06.** Si falla la indexación, el sistema conservará los registros correctamente guardados y registrará el fallo sin contenido documental. La interfaz advertirá de que los documentos recientes pueden tardar en estar disponibles.

### Recuperación y respuestas

- **RF-07.** Cuando el usuario pregunte por documentos, el sistema consultará Bedrock mediante `Retrieve` y enviará los fragmentos autorizados a Ollama para elaborar la respuesta.
- **RF-08.** El sistema filtrará por los permisos actuales de área y comprobará cada resultado contra el documento, la ruta S3 y la factura o póliza asociada. Descartará resultados ajenos, incoherentes, eliminados o sustituidos.
- **RF-09.** El asistente responderá basándose en el contenido recuperado e identificará sus fuentes. Tratará los documentos como información, no como instrucciones. Si no encuentra información suficiente, lo indicará sin inventar datos.
- **RF-10.** El asistente utilizará el historial para contextualizar preguntas de seguimiento. Guardará las respuestas y sus referencias documentales en sesión, sin conservar los fragmentos recuperados.
- **RF-11.** Si Bedrock u Ollama fallan, el asistente mostrará un error en español y no guardará el intercambio fallido. Se conservarán el reinicio, el límite de historial y los estados de espera existentes.

### Fuentes y navegación

- **RF-12.** Cada fuente mostrará el nombre del archivo, la factura o póliza asociada y los enlaces «Previsualizar documento» y «Abrir registro». Los fragmentos del mismo documento se agruparán en una sola fuente.
- **RF-13.** Cuando el usuario solicite la previsualización, el sistema comprobará autenticación, permisos y asociación vigente, y abrirá el archivo en otra pestaña mediante una URL temporal de S3. La URL caducará a los cinco minutos y no se guardará en el historial.
- **RF-14.** Cuando el usuario abra el registro, el sistema abrirá la edición de la factura o póliza en su recurso existente de Filament. La aplicación construirá ambos enlaces a partir de registros verificados.

### Creación y configuración de Bedrock

- **RF-15.** La entrega incluirá crear y configurar desde la consola AWS una Knowledge Base y su origen de datos S3, utilizando el bucket existente y su región.
- **RF-16.** La configuración limitará el origen a los prefijos de documentos de la aplicación y permitirá recuperar contenido de PDF y PNG, incluidos escaneados, así como filtrar mediante `area` y relacionar resultados mediante `document_id`. No se impondrá BDA.
- **RF-17.** Se configurarán el rol de servicio de Bedrock y los permisos de la aplicación para consultar la Knowledge Base e iniciar y comprobar ingestas, limitados a los recursos utilizados.
- **RF-18.** Se recogerán la región, el identificador de Knowledge Base y el de origen de datos para configurar Laravel mediante `.env`. La configuración efectiva de procesamiento, embeddings y almacenamiento quedará documentada sin incluir credenciales.

## Fuera de alcance

- Consultas SQL de negocio y extracción de información hacia campos del CRUD.
- Archivos externos sin registro asociado y recurso independiente de Documentos.
- Retirada automática de respuestas antiguas del historial cuando cambien los permisos o las fuentes.
- Persistencia propia de conversaciones, dependencias nuevas y migraciones.

## Criterios de finalización

1. Se cumplen RF-01 a RF-18, con documentación de preparación de AWS, worker, carga inicial y recuperación de fallos.
2. Los tests nuevos se limitan a controles especialmente críticos: aislamiento entre áreas, rechazo de fuentes manipuladas o invalidadas y protección de la previsualización. Se escriben primero y se verifican en rojo; en caso de duda se comprueba manualmente.
3. Los servicios externos se mockean y los tests usan MySQL de pruebas. Respuestas, seguimientos, estados, sincronización y navegación se verifican manualmente.
4. `./vendor/bin/pest` pasa y `./vendor/bin/pint` se ejecuta. No se añaden secretos, dependencias ni migraciones.
5. La Knowledge Base está disponible y, después de preparar los `.metadata.json`, se comprueban manualmente la ingesta de PDF y PNG, la recuperación de contenido y el filtrado por área.
