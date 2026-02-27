# Changelog

Todos los cambios notables en este proyecto seran documentados en este archivo.

El formato esta basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto se adhiere a [Semantic Versioning](https://semver.org/lang/es/).

## [2.6.3] - 2026-02-26

### Cambiado
- **Renombrado "Personas" a "Mi Familia"** en menu desktop, movil y vista index para reflejar que solo muestra personas accesibles por privacidad
- Eliminadas todas las referencias a Croacia en seeders, tests y vistas (nombres, apellidos, ciudades, placeholders) reemplazadas por equivalentes latinoamericanos

### Actualizado
- Version a 2.6.3 en `config/mi-familia.php` y `composer.json`

---

## [2.6.2] - 2026-02-25

### Agregado
- **Privacidad en cintillo de fotos**: Cada usuario solo ve fotos de personas accesibles segun niveles de privacidad (BFS, creador, community/public)
- Imagenes genericas de relleno en `public/images/banner/` cuando el usuario tiene pocas fotos accesibles
- Configuracion de umbral minimo de fotos reales (`min_real_photos`) en admin del plugin
- Cache por usuario con invalidacion por version en photo-banner
- Enlace de Chat en menu movil/responsivo

### Corregido
- Cintillo de fotos mostraba fotos de todas las personas sin respetar privacidad
- Chat no aparecia en el menu de navegacion movil
- `public_path()` no apuntaba al document root real en hostings con estructura separada
- Rango de velocidad del cintillo ampliado a 10-300s (antes max 120s)

### Cambiado
- Actualizada version a 2.6.2 en `config/mi-familia.php` y `composer.json`

---

## [2.6.1] - 2026-02-25

### Corregido
- **Fix error "Undefined array key person_id"** al editar usuarios sin persona vinculada en el panel de admin
  - `AdminController::updateUser()` fallaba cuando `person_id` no se enviaba en el formulario
  - Aplicado operador null coalesce (`?? null`) para manejar campos nullable ausentes del array validado

### Cambiado
- Actualizada version a 2.6.1 en `config/mi-familia.php` y `composer.json`

---

## [2.6.0] - 2026-02-20

### Agregado
- **Sistema de claim para usuarios nuevos**: Permite a nuevos usuarios reclamar perfiles existentes en el arbol
- Deteccion de "dummy person" (persona sin conexiones familiares) para permitir re-claim
- Metodo `canBeViewedForClaim()` con verificacion de privacidad relajada para claims
- Vista `show-limited.blade.php` para perfiles con privacidad restringida pero disponibles para claim
- Metodo `resolveClaimRecipients()` para enrutar notificaciones de claim
- Tipo de mensaje `relationship_claim` para flujo "estoy relacionado directamente"
- Columna `metadata` (JSON) en tabla messages
- FK `claiming_person_id` en messages para rastrear persona dummy en claims
- Seccion "¿Ya existes en el arbol?" en pagina de bienvenida
- Banner de orientacion en dashboard para usuarios sin conexiones familiares

### Cambiado
- Metodos `addParentRelationship`, `addChildRelationship`, `addSiblingRelationship`, `addSpouseRelationship` cambiados a `public` en PersonController

---

## [2.5.0] - 2026-02-15

### Agregado
- **Sistema de personalizacion de sitio** via `site_settings`
- Fuente personalizable (12 opciones, default: Ubuntu) via SiteSettingsService
- Carga de fuentes con variable CSS `--mf-font` en todos los layouts
- Panel admin `/admin/content` para edicion de texto/imagenes
- Panel admin `/admin/settings` para personalizacion de colores

---

## [2.1.0] - 2026-02-10

### Agregado
- **Sistema de privacidad v2.1** con traversal BFS para familia extendida
- Metodo `getAllConnectedPersonIds()` en Person.php (carga grafo completo en 2 queries)
- Linaje siempre visible: ancestros/descendientes ignoran niveles de privacidad
- Cache de datos del arbol (TTL 120s) con CacheInvalidationObserver
- Flujo "Este soy yo" para que admin/creador pueda re-vincular a otra persona

---

## [2.0.0] - 2026-02-04

### Changed
- Initial open-source release as "Mi Familia" genealogy platform
- Generalized all culture-specific fields to generic heritage fields
- Updated color scheme to neutral blue palette
- Licensed under MIT
- Supported languages: Spanish (default) and English

---

## [1.0.8] - 2026-02-03

### Corregido

#### Sistema de Privacidad
- **Corregido bug critico** donde los familiares no podian ver los perfiles de otros miembros de la familia
- Implementado metodo `canBeViewedBy()` en el modelo `Person` que maneja correctamente los 4 niveles de privacidad:
  - `private`: Solo el creador puede ver
  - `family`: Familiares directos pueden ver
  - `community`: Todos los usuarios registrados pueden ver
  - `public`: Visible para todos
- Agregado metodo `isDirectFamilyOf()` para verificar relaciones familiares directas
- Actualizado `authorizeView()` en `PersonController`, `FamilyController` y `TreeController`

#### Borrado en Cascada de Relaciones
- **Corregido bug critico** en `destroyRelationship()` donde las consultas SQL tenian agrupacion incorrecta, causando eliminacion de familias no relacionadas
- Agregada logica para preservar familias con hijos al eliminar relacion de conyuge (solo se desvincula el conyuge, no se elimina la familia)
- Corregido metodo `update()` en `FamilyController` para que no elimine accidentalmente todos los hijos al actualizar datos de la familia

#### Indicador de Ascendencia en Arbol
- **Restaurado el contorno azul** (#1057A4) para personas con ascendencia destacada en el arbol genealogico
- Agregada logica en `drawNode()` de `tree/view.blade.php` para aplicar estilo visual basado en `hasHeritage`
- Borde rojo para la persona central (raiz del arbol)

#### Identificacion de Apellidos en Admin
- **Mejorada la identificacion de apellidos** en el reporte de apellidos del panel de administracion
- Nuevo metodo `getHeritageSurnames()` que usa multiples criterios:
  - Campo `has_heritage` de la persona
  - Sufijos tipicos de apellidos
  - Variantes historicas registradas en `surname_variants`
- Agregado uso del modelo `SurnameVariant` para mejor deteccion

#### Envio de Correo de Verificacion
- **Corregido el envio automatico** del correo de verificacion al registrar nuevos usuarios
- Agregado envio explicito del correo como respaldo despues del evento `Registered`
- Agregado logging de errores de correo para facilitar diagnostico
- El registro de usuario ya no falla si hay problemas con el envio del correo

### Archivos Modificados

- `app/Models/Person.php` - Nuevos metodos de privacidad
- `app/Http/Controllers/PersonController.php` - Correcciones de autorizacion y eliminacion
- `app/Http/Controllers/FamilyController.php` - Correcciones de autorizacion y actualizacion
- `app/Http/Controllers/TreeController.php` - Correcciones de autorizacion
- `app/Http/Controllers/Admin/ReportController.php` - Mejora en identificacion de apellidos
- `app/Http/Controllers/Auth/RegisterController.php` - Correccion de envio de correo
- `resources/views/tree/view.blade.php` - Restauracion del indicador visual de ascendencia

---

## [1.0.7] - Version anterior

Version estable anterior antes de las correcciones de bugs criticos.
