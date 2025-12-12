# Guia de Despliegue - Mi Familia v2.0.0

## Requisitos del Servidor
- PHP 8.1 o superior (probado con 8.3)
- MySQL 5.7+ o MariaDB 10.3+
- Extensiones PHP: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, PDO_MySQL, Tokenizer, XML, GD

## Archivos de Despliegue

| Archivo | Tamano | Destino |
|---------|--------|---------|
| `mi-familia-app.zip` | ~7 MB | `../mi-familia/` (fuera del directorio publico) |
| `mi-familia-public.zip` | ~4 MB | Directorio publico del sitio (ej: `public_html/`) |

---

## Pasos de Instalacion desde Cero

### 1. Crear Base de Datos
1. Ir a **MySQL Databases** en cPanel (o tu panel de control)
2. Crear base de datos: `tu_usuario_mi_familia`
3. Crear usuario: `tu_usuario_mifamilia`
4. Asignar **TODOS los privilegios** al usuario sobre la BD

### 2. Subir y Extraer ZIP Publico

1. Subir `mi-familia-public.zip` al directorio publico del sitio (ej: `public_html/` o `tudominio.com/`)
2. Extraer el contenido
3. **Renombrar archivos de produccion:**
   - `public/index.production.php` -> `index.php` (mover a raiz)
   - `public/.htaccess.production` -> `.htaccess` (mover a raiz)
4. Mover contenido de `public/build/` y `public/images/` a la raiz
5. Eliminar carpeta `public/` vacia

**Estructura resultante:**
```
public_html/ (o tu directorio publico)
├── index.php
├── .htaccess
├── favicon.ico
├── robots.txt
├── build/
│   ├── manifest.json
│   └── assets/
│       ├── app-*.css
│       └── app-*.js
└── images/
    └── (imagenes del sitio)
```

### 3. Subir y Extraer ZIP de Aplicacion

1. Subir `mi-familia-app.zip` a `../mi-familia/` (un nivel arriba del directorio publico)
2. Extraer el contenido
3. **Renombrar:** `.env.production` -> `.env`

**Estructura resultante:**
```
mi-familia/
├── app/
├── bootstrap/
│   └── cache/
├── config/
├── database/
│   ├── schema.sql
│   └── seed_data.sql
├── lang/
├── public/
│   └── build/          <- IMPORTANTE! Ver paso 4
├── resources/
├── routes/
├── storage/
├── vendor/
└── .env
```

### 4. Copiar build/ a mi-familia/public/ (CRITICO!)

**Laravel lee el manifest.json desde `mi-familia/public/build/`, no desde el directorio publico.**

Copiar el contenido de `build/` del directorio publico a `mi-familia/public/build/`:
- `manifest.json`
- `assets/` (con los archivos .css y .js)

**Estructura final de mi-familia/public/build/:**
```
mi-familia/public/build/
├── manifest.json
└── assets/
    ├── app-*.css
    └── app-*.js
```

### 5. Configurar .env

Editar `mi-familia/.env` con tus datos:

```env
APP_NAME="Mi Familia"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:TU_APP_KEY_AQUI
APP_URL=https://tudominio.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=tu_usuario_mi_familia
DB_USERNAME=tu_usuario_mifamilia
DB_PASSWORD=tu_password_real

MAIL_MAILER=smtp
MAIL_HOST=mail.tudominio.com
MAIL_PORT=465
MAIL_USERNAME=no-reply@tudominio.com
MAIL_PASSWORD=password_correo_real
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="no-reply@tudominio.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 6. Generar APP_KEY

Ejecuta localmente:
```bash
php artisan key:generate --show
```
Copia el resultado (ej: `base64:aiLXB75O...`) y pegalo en el `.env` del servidor.

### 7. Configurar Permisos

| Archivo/Carpeta | Permisos |
|-----------------|----------|
| `.htaccess` | 644 |
| `mi-familia/storage/` | 755 (recursivo) |
| `mi-familia/bootstrap/cache/` | 755 |

### 8. Crear Directorios Faltantes

Crear manualmente si no existen:
- `mi-familia/storage/framework/sessions/`
- `mi-familia/storage/framework/views/`
- `mi-familia/storage/framework/cache/`

**Alternativa**: Crear archivo `fix.php` en el directorio publico:

```php
<?php
$base = __DIR__ . '/../mi-familia';

$dirs = [
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/framework/cache',
];

echo "<pre>";
foreach ($dirs as $dir) {
    $path = "$base/$dir";
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        echo "Creado: $dir\n";
    }
}
echo "\nListo! Elimina este archivo.";
echo "</pre>";
```

Visitar `https://tudominio.com/fix.php` y luego **eliminar el archivo**.

### 9. Crear Symlink de Storage

Crear archivo `create-storage-link.php` en el directorio publico:

```php
<?php
$target = __DIR__ . '/../mi-familia/storage/app/public';
$link = __DIR__ . '/storage';

if (file_exists($link)) {
    echo "El symlink ya existe";
} else {
    if (symlink($target, $link)) {
        echo "Symlink creado exitosamente!";
    } else {
        echo "Error al crear symlink";
    }
}
// ELIMINAR ESTE ARCHIVO DESPUES DE EJECUTAR
```

Visitar `https://tudominio.com/create-storage-link.php` y luego **eliminar el archivo**.

### 10. Importar Base de Datos

**En phpMyAdmin:**
1. Seleccionar tu base de datos
2. Ir a pestana **Importar**
3. Subir `database/schema.sql` (estructura de tablas)
4. Subir `database/seed_data.sql` (usuario admin inicial)

**IMPORTANTE:** El `schema.sql` solo crea tablas vacias. Debes importar `seed_data.sql` para tener el usuario administrador.

### 11. Usuario Administrador

El archivo `seed_data.sql` crea el usuario administrador:

| Campo | Valor |
|-------|-------|
| Email | `admin@mi-familia.app` |
| Password | `MiFamilia2026!` |

**Cambiar la contrasena inmediatamente despues del primer login.**

---

## Resumen de Pasos

1. Subir `mi-familia-public.zip` al directorio publico y extraer
2. Subir `mi-familia-app.zip` a `../mi-familia/` y extraer
3. Renombrar archivos de produccion (.env, index.php, .htaccess)
4. **Copiar `build/` a `mi-familia/public/build/`**
5. Configurar `.env` con datos de BD y correo
6. Configurar permisos (storage 755, .htaccess 644)
7. Crear directorios faltantes en storage/framework/
8. Crear symlink de storage (ejecutar create-storage-link.php)
9. Importar `schema.sql` y `seed_data.sql` en phpMyAdmin
10. Eliminar scripts temporales (fix.php, create-storage-link.php)

---

## Verificacion Post-Instalacion

- [ ] Abrir https://tudominio.com - debe cargar la pagina de inicio
- [ ] Verificar que los estilos cargan (CSS/JS)
- [ ] Probar login con usuario admin (`admin@mi-familia.app` / `MiFamilia2026!`)
- [ ] **Cambiar contrasena del admin**
- [ ] Probar registro de nuevo usuario
- [ ] Verificar que las imagenes cargan
- [ ] Probar cambio de idioma (?lang=en, ?lang=es)
- [ ] Verificar panel de administracion (/admin)

---

## Solucion de Problemas

### Error 403 Forbidden
- Verificar permisos de `.htaccess` (debe ser 644, NO 600)

### Error 500
- Verificar que existen `storage/framework/sessions/`, `views/`, `cache/`
- Verificar permisos de storage/ y bootstrap/cache/ (755)
- Revisar logs en `mi-familia/storage/logs/laravel.log`

### CSS/JS no cargan (ViteManifestNotFoundException)
- Verificar que existe `mi-familia/public/build/manifest.json`
- El manifest debe apuntar al CSS correcto (verificar nombre del archivo)

### Error de base de datos
- Verificar credenciales en .env
- Verificar que las tablas fueron importadas

### Pagina en blanco
- Verificar que APP_KEY este configurada en .env
- Habilitar APP_DEBUG=true temporalmente para ver el error

### Imagenes no cargan
- Verificar que el symlink de storage existe
- Ejecutar `create-storage-link.php`

---

## Contacto Tecnico
- Desarrollador: Eduardo Llaguno Velasco
- GitHub: [https://github.com/ellaguno/arbol-familiar](https://github.com/ellaguno/arbol-familiar)
