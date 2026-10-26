---
name: filament-ui-defaults
description: Decisiones de interfaz por defecto para paneles Laravel + Filament 5 en español. Úsala siempre que crees o modifiques un panel, recurso, formulario o tabla de Filament, o añadas campos de dinero, importes, fechas, acciones de fila, botones de alta o la página de entrada del panel, aunque el usuario no pida "defaults" ni nombre la skill.
---

# Filament UI defaults

Convenciones de interfaz que se repiten entre proyectos. Aplícalas desde el primer recurso: es más barato que corregirlas luego. Si el usuario o la spec del proyecto dicen otra cosa, mandan ellos.

## Se imponen una vez, en código global

Pon esto en `AppServiceProvider::boot()` (referencia en `assets/AppServiceProvider.php`; si el proyecto ya tiene el suyo, añade solo las dos líneas de `boot()`). Al ser global, no hay que repetirlo por recurso ni documentarlo en ficheros que se cargan siempre.

- **Sin "Crear y crear otro":** `CreateRecord::disableCreateAnother()`. Las altas solo muestran "Crear" y "Cancelar".
- **Selector de fecha de Filament, no el del navegador:** `DatePicker::configureUsing(fn (DatePicker $picker) => $picker->native(false))`. Solo cubre `DatePicker`; un `DateTimePicker` necesitaría su propio `configureUsing`.

## Se aplican en cada recurso

No existe ajuste global para estas, así que hay que acordarse al crear cada tabla o formulario.

- **Acciones de fila agrupadas:** `recordActions([ActionGroup::make([EditAction::make(), DeleteAction::make()])])`. Un único menú por fila, nunca botones sueltos.
- **Dinero con `MoneyInput`:** copia `assets/MoneyInput.php` a `app/Filament/Forms/Components/` y úsalo para cualquier importe, prima o precio. Es un `TextInput` de texto con formato español (`1.234,50`): lo formatea al cargar y al salir del campo (`live(onBlur: true)`), convierte a decimal antes de validar y de guardar, valida con la regla `numeric` y lleva teclado decimal, sufijo € y texto a la derecha. Un punto decimal tecleado se lee como millares; el reformateo al salir del campo lo deja a la vista. No lo "mejores" con máscaras salvo que lo pidan. En tablas, `TextColumn::make('amount')->money('EUR')`.
- **Fecha no futura:** `DatePicker::make('issued_on')->required()->maxDate(fn () => today())`. Usa la closure, no `today()` directo, para que se evalúe en cada petición y no al arrancar.
- **Orden por defecto explícito** en listados con fecha: `->defaultSort('issued_on', 'desc')`.

## Panel y entrada

- Sin dashboard: comenta `Dashboard::class` en las páginas del `PanelProvider`. El usuario entra por los recursos, a la primera sección que tiene permitida.
- Cada usuario debe tener al menos una sección permitida; con la navegación vacía Filament entra en bucle de redirección en `/admin`.
- Login como único método de acceso.

## Idioma y hora

- Interfaz, etiquetas y validaciones en español; código, tablas y claves en inglés.
- Instala `laravel-lang/lang` para los textos estándar, con `locale` `es` y `timezone` `Europe/Madrid` en `config/app.php`.
