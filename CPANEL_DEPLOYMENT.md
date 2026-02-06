# Deployment en cPanel - Mi Familia

## Estructura de Archivos

```
/home/usuario/
├── mi-familia/              ← Aplicación Laravel (FUERA de public_html)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── lang/
│   ├── plugins/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   └── ...
│
└── public_html/             ← Directorio público del sitio
    ├── build/
    ├── css/
    ├── images/
    ├── storage -> ../mi-familia/storage/app/public  ← Symlink
    ├── .htaccess
    ├── index.php
    └── ...
```

## Paso 1: Subir los Archivos

### 1.1 Subir mi-familia-app.zip
1. En cPanel, ve al **Administrador de Archivos**
2. Navega a `/home/tu_usuario/` (directorio home, NO public_html)
3. Sube `mi-familia-app.zip`
4. Extrae el zip (clic derecho → Extract)
5. Debería crear la carpeta `mi-familia/`

### 1.2 Subir mi-familia-public.zip
1. Navega a `/home/tu_usuario/public_html/`
2. **IMPORTANTE**: Si es un dominio adicional, navega a la carpeta del dominio
3. Sube `mi-familia-public.zip`
4. Extrae el zip
5. Los archivos deben quedar directamente en public_html (no en subcarpeta)

## Paso 2: Configurar Base de Datos

### 2.1 Crear Base de Datos en cPanel
1. Ve a **MySQL® Databases**
2. Crear nueva base de datos (ej: `usuario_mifamilia`)
3. Crear nuevo usuario (ej: `usuario_mifam`)
4. Asignar usuario a la base de datos con **TODOS los privilegios**

### 2.2 Configurar .env
1. Ve al Administrador de Archivos
2. Navega a `/home/tu_usuario/mi-familia/`
3. Edita el archivo `.env` (o renombra `.env.example` a `.env`)
4. Configura:

```env
APP_NAME="Mi Familia"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=usuario_mifamilia
DB_USERNAME=usuario_mifam
DB_PASSWORD=tu_contraseña_segura

MAIL_MAILER=smtp
MAIL_HOST=mail.tu-dominio.com
MAIL_PORT=465
MAIL_USERNAME=noreply@tu-dominio.com
MAIL_PASSWORD=contraseña_email
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@tu-dominio.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Paso 3: Crear Symlink de Storage

### Opción A: Usando el script (recomendado)
1. Visita: `https://tu-dominio.com/create-storage-link.php`
2. Debería mostrar "Storage link created successfully"
3. **Elimina el archivo** `create-storage-link.php` después de usarlo

### Opción B: Manualmente en cPanel Terminal
```bash
cd /home/tu_usuario/public_html
ln -s ../mi-familia/storage/app/public storage
```

### Opción C: Manualmente en Administrador de Archivos
1. En public_html, crea un nuevo archivo llamado `storage`
2. No funcionará directamente - usa Opción A o B

## Paso 4: Ejecutar Migraciones y Seeder

### 4.1 Generar APP_KEY
Visita: `https://tu-dominio.com/fix.php`
- Esto genera el APP_KEY y configura permisos

### 4.2 Ejecutar Migraciones
Visita: `https://tu-dominio.com/run-migration.php`
- Esto crea todas las tablas en la base de datos

### 4.3 Ejecutar Seeder (crear admin)
Visita: `https://tu-dominio.com/seed.php`
- Esto crea el usuario administrador y configuraciones

**Usuario administrador creado:**
- Email: `admin@mi-familia.org`
- Password: `MiFamilia2025!`
- **¡Cambia la contraseña inmediatamente!**

## Paso 5: Limpieza de Seguridad

**MUY IMPORTANTE**: Elimina estos archivos después del deployment:
- `public_html/fix.php`
- `public_html/run-migration.php`
- `public_html/seed.php`
- `public_html/create-storage-link.php`

## Paso 6: Configurar Permisos

En cPanel Terminal o SSH:
```bash
cd /home/tu_usuario/mi-familia

# Permisos de directorios
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Permisos de archivos
chmod 644 .env
```

## Paso 7: Verificar Instalación

1. Visita `https://tu-dominio.com`
2. Deberías ver la página de bienvenida
3. Inicia sesión con el usuario admin
4. Ve a Administración y configura el sitio

## Solución de Problemas

### Error 500
- Revisa que `.env` tenga todos los valores correctos
- Verifica permisos de `storage/` y `bootstrap/cache/`
- Revisa logs en `mi-familia/storage/logs/laravel.log`

### Página en blanco
- Habilita `APP_DEBUG=true` temporalmente en `.env`
- Revisa el log de errores de PHP en cPanel

### Imágenes no cargan
- Verifica que el symlink `storage` exista en public_html
- Debe apuntar a `../mi-familia/storage/app/public`

### Error de base de datos
- Verifica credenciales en `.env`
- Asegúrate de que el usuario tenga todos los privilegios

## Actualización del Sitio

Para futuras actualizaciones:
1. Sube los nuevos archivos (sobrescribiendo)
2. Ejecuta `run-migration.php` si hay cambios en BD
3. Limpia caché visitando `fix.php`

## Soporte

Para más información, consulta:
- MANUAL.md - Manual de usuario completo
- README.md - Información del proyecto
