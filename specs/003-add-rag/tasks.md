# Tareas 003 — RAG sobre documentos con Bedrock

Las tareas se ejecutan una a una, únicamente cuando el usuario indique cuál realizar. Los tests nuevos se limitan a controles especialmente críticos; en caso de duda se comprueba manualmente. S3, Bedrock y Ollama se mockean en tests.

- [x] **T01 — Retirar el metadato redundante del objeto S3.** Eliminar la escritura de `Metadata.area` durante las subidas; conservar `documents.area` y las rutas por área. Retirar el test que exige ese metadato y actualizar las reglas y referencias documentales afectadas. No modificar masivamente los objetos existentes. Depende de: —. RF: RF-02.
  **Hecho cuando:** las subidas conservan el área en MySQL y la ruta S3 sin añadir el metadato al objeto; las referencias afectadas describen el comportamiento vigente.

- [x] **T02 — Crear y configurar Bedrock desde la consola AWS.** Crear la Knowledge Base y el origen S3 usando el bucket existente y su región; limitar el origen a los prefijos de documentos de la aplicación. Configurar procesamiento de PDF y PNG, incluidos escaneados, almacenamiento, metadatos filtrables, rol de servicio y permisos de la aplicación para consultar e iniciar y comprobar ingestas. Recoger región e identificadores de Knowledge Base y origen para configurar Laravel mediante `.env`; documentar las opciones efectivas sin credenciales. Sin tests automatizados de configuración AWS. Depende de: —. RF: RF-15 a RF-18.
  **Hecho cuando:** los recursos están disponibles, el origen y los permisos están configurados y se han identificado los valores de conexión. La comprobación completa de ingesta y filtrado se realiza en T03, después de preparar los metadatos.

- [ ] **T03 — Preparar los metadatos de Knowledge Bases.** Generar y mantener los `.metadata.json` a partir de documentos vinculados, incluida su retirada al eliminar archivos. Añadir una operación inicial repetible para documentos existentes. Comprobar manualmente la ingesta de PDF y PNG, la recuperación de contenido y el filtrado por área. Depende de: T01, T02. RF: RF-01 a RF-03, RF-16.
  **Hecho cuando:** cada documento vinculado dispone de metadatos correctos, la operación inicial puede repetirse sin crear duplicados y se han comprobado manualmente la ingesta, recuperación y filtrado por área.

- [ ] **T04 — Automatizar la sincronización.** Conectar los cambios documentales con trabajos posteriores al commit, coordinación de ingestas, seguimiento de finalización y reintentos limitados. Depende de: T03. RF: RF-04 a RF-06.
  **Hecho cuando:** altas, sustituciones y eliminaciones solicitan la sincronización, los cambios durante una ingesta se procesan después y un fallo no deshace el registro guardado.

- [ ] **T05 — Integrar la recuperación en el asistente.** Aplicar filtros de permisos, verificar fuentes y proporcionar a Ollama los fragmentos autorizados. Escribir primero y verificar en rojo los tests críticos de aislamiento y rechazo de fuentes manipuladas o invalidadas. Depende de: T03. RF: RF-07 a RF-11.
  **Hecho cuando:** los controles críticos pasan y el asistente responde usando únicamente fuentes autorizadas, informa si faltan datos y maneja fallos en español.

- [ ] **T06 — Mostrar fuentes y enlaces.** Agrupar fuentes por documento e incorporar previsualización protegida y navegación al recurso de Filament. Escribir primero y verificar en rojo el test crítico de previsualización. Depende de: T05. RF: RF-12 a RF-14.
  **Hecho cuando:** cada fuente autorizada ofrece ambos enlaces y la previsualización comprueba el acceso antes de generar una URL de cinco minutos.

- [ ] **T07 — Verificar y documentar.** Documentar AWS, worker, carga inicial y recuperación; completar las comprobaciones críticas y manuales; ejecutar Pest y Pint. Depende de: T04, T06. RF: RF-01 a RF-18.
  **Hecho cuando:** las comprobaciones previstas se completan y se documentan las limitaciones del entorno.
