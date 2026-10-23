# Constitución — ExpensesManager
1. Stack fijo: Laravel + Filament + Livewire + Pest; MySQL (base de datos), S3 (documentos) y Bedrock (RAG). Nada fuera sin aprobación.
2. Filament primero: todo el CRUD con recursos de Filament; solo el asistente es Livewire propio.
3. Fases en orden: conversación en sesión → SQL → RAG. Una fase por spec; no se adelanta la siguiente.
4. Conversación efímera: el historial vive solo en sesión; ninguna tabla ni fichero lo persiste.
5. SQL seguro: el asistente solo ejecuta consultas de solo lectura, acotadas al usuario autenticado.
6. Documentos en S3: ningún ticket ni factura en disco local.
7. Secretos en `.env`: nunca en código, tests ni commits.
8. Test primero: antes de escribir código, escribe el test Pest y comprueba que falla; el código se da por terminado cuando ese test pasa.
9. AWS mockeado: los tests nunca llaman a S3 ni a Bedrock reales.
10. Calidad: `./vendor/bin/pint` sin cambios y `./vendor/bin/pest` en verde antes de cada commit.
11. Idioma: referencias (código, nombres de clases, métodos, variables, tablas, claves) en inglés; mensajes al usuario en español.
12. Dependencias y esquema: no se añaden paquetes ni se cambia el esquema de BD sin preguntar.
13. Spec antes de código: cada cambio parte de una spec activa; sin spec, no se toca código.
