# Changelog

Todos los cambios notables en este proyecto seran documentados en este archivo.

El formato esta basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto se adhiere a [Semantic Versioning](https://semver.org/lang/es/).

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
