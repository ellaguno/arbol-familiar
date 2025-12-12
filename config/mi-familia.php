<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Informacion de la Aplicacion
    |--------------------------------------------------------------------------
    */

    'version' => '2.0.0',

    'developer' => [
        'name' => 'Eduardo Llaguno Velasco',
        'email' => 'eduardo@llaguno.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuracion de Subida de Archivos
    |--------------------------------------------------------------------------
    */

    'upload' => [
        'max_files' => env('UPLOAD_MAX_FILES', 10),
        'max_size' => env('UPLOAD_MAX_SIZE', 2048), // KB
        'allowed_images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'allowed_documents' => ['pdf', 'doc', 'docx'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuracion de Invitaciones
    |--------------------------------------------------------------------------
    */

    'invitation_expiry_days' => env('INVITATION_EXPIRY_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Configuracion de Consentimiento
    |--------------------------------------------------------------------------
    */

    'consent_wait_days' => env('CONSENT_WAIT_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Regiones de Herencia
    |--------------------------------------------------------------------------
    */

    'heritage_regions' => [
        'region_1' => 'Region 1',
        'region_2' => 'Region 2',
        'region_3' => 'Region 3',
        'region_4' => 'Region 4',
        'other' => 'Otra region',
        'unknown' => 'Desconocida',
    ],

    /*
    |--------------------------------------------------------------------------
    | Estado Civil
    |--------------------------------------------------------------------------
    */

    'marital_statuses' => [
        'single' => 'Soltero/a',
        'married' => 'Casado/a',
        'common_law' => 'Union libre',
        'divorced' => 'Divorciado/a',
        'widowed' => 'Viudo/a',
    ],

    /*
    |--------------------------------------------------------------------------
    | Decadas de Migracion
    |--------------------------------------------------------------------------
    */

    'migration_decades' => [
        '1850-1860' => '1850 - 1860',
        '1860-1870' => '1860 - 1870',
        '1870-1880' => '1870 - 1880',
        '1880-1890' => '1880 - 1890',
        '1890-1900' => '1890 - 1900',
        '1900-1910' => '1900 - 1910',
        '1910-1920' => '1910 - 1920',
        '1920-1930' => '1920 - 1930',
        '1930-1940' => '1930 - 1940',
        '1940-1950' => '1940 - 1950',
        '1950-1960' => '1950 - 1960',
        '1960-1970' => '1960 - 1970',
        '1970-1980' => '1970 - 1980',
        '1980-1990' => '1980 - 1990',
        '1990-2000' => '1990 - 2000',
        '2000-2010' => '2000 - 2010',
        '2010-2020' => '2010 - 2020',
        '2020-present' => '2020 - Presente',
    ],

    /*
    |--------------------------------------------------------------------------
    | Grados de Parentesco
    |--------------------------------------------------------------------------
    */

    'relationship_degrees' => [
        'self' => 'Yo mismo',
        'father' => 'Padre',
        'mother' => 'Madre',
        'grandfather_paternal' => 'Abuelo paterno',
        'grandmother_paternal' => 'Abuela paterna',
        'grandfather_maternal' => 'Abuelo materno',
        'grandmother_maternal' => 'Abuela materna',
        'great_grandfather_pp' => 'Bisabuelo PP (padre de abuelo paterno)',
        'great_grandmother_pp' => 'Bisabuela PP (madre de abuelo paterno)',
        'great_grandfather_pm' => 'Bisabuelo PM (padre de abuela paterna)',
        'great_grandmother_pm' => 'Bisabuela PM (madre de abuela paterna)',
        'great_grandfather_mp' => 'Bisabuelo MP (padre de abuelo materno)',
        'great_grandmother_mp' => 'Bisabuela MP (madre de abuelo materno)',
        'great_grandfather_mm' => 'Bisabuelo MM (padre de abuela materna)',
        'great_grandmother_mm' => 'Bisabuela MM (madre de abuela materna)',
        'great_great_grandparent' => 'Tatarabuelo(a)',
        'great_great_great_grandparent' => 'Trastatarabuelo(a)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Relaciones Familiares (para usuarios con herencia cultural)
    |--------------------------------------------------------------------------
    */

    'family_relationships' => [
        'spouse' => 'Conyuge',
        'father_in_law' => 'Suegro',
        'mother_in_law' => 'Suegra',
        'brother_in_law' => 'Cunado',
        'sister_in_law' => 'Cunada',
        'son_in_law' => 'Yerno',
        'daughter_in_law' => 'Nuera',
        'stepfather' => 'Padrastro',
        'stepmother' => 'Madrastra',
        'stepson' => 'Hijastro',
        'stepdaughter' => 'Hijastra',
        'other' => 'Otro familiar',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuracion de Seguridad
    |--------------------------------------------------------------------------
    */

    'security' => [
        'max_login_attempts' => 5,
        'lockout_minutes' => 15,
        'password_min_length' => 8,
        'require_password_uppercase' => true,
        'require_password_lowercase' => true,
        'require_password_number' => true,
        'require_password_symbol' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA
    |--------------------------------------------------------------------------
    */

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
        'enabled' => env('RECAPTCHA_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Idiomas Soportados
    |--------------------------------------------------------------------------
    */

    'languages' => [
        'es' => 'Español',
        'en' => 'English',
    ],

];
