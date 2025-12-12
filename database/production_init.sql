-- ============================================================================
-- Mi Familia - Inicialización para Producción
-- ============================================================================
-- Ejecutar DESPUÉS de importar schema.sql
-- Copiar TODO el contenido y pegarlo en phpMyAdmin > SQL > Ejecutar
-- ============================================================================

-- Paso 1: Crear usuario SIN persona asociada
INSERT INTO users (id, email, password, person_id, is_admin, email_verified_at, first_login_completed, language, privacy_level, created_at, updated_at)
VALUES (1, 'admin@mi-familia.com', '$2y$12$CLw76SW08NNQ17WyF.tKrOYI8wlkAuqqkvok2mQorYWQrKg/KEauK', NULL, 1, NOW(), 1, 'es', 'direct_family', NOW(), NOW());

-- Paso 2: Crear persona con created_by apuntando al usuario
INSERT INTO persons (id, first_name, patronymic, gender, is_living, privacy_level, consent_status, created_by, user_id, created_at, updated_at)
VALUES (1, 'Administrador', 'Sistema', 'U', 1, 'private', 'not_required', 1, 1, NOW(), NOW());

-- Paso 3: Actualizar usuario para vincular la persona
UPDATE users SET person_id = 1 WHERE id = 1;

-- ============================================================================
-- Usuario administrador creado:
--   Email: admin@mi-familia.com
--   Password: MiFamiliaAdmin2025!
--
-- CAMBIAR LA CONTRASEÑA INMEDIATAMENTE DESPUÉS DEL PRIMER LOGIN
-- ============================================================================
