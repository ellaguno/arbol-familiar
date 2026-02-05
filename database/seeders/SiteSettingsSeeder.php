<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // =============================================
            // Welcome page
            // =============================================
            ['group' => 'welcome', 'key' => 'hero_image', 'value' => 'images/hero-beach.jpg', 'type' => 'image'],
            ['group' => 'welcome', 'key' => 'logo_image', 'value' => 'images/logo.png', 'type' => 'image'],
            ['group' => 'welcome', 'key' => 'hero_title', 'value' => '¡Conecta con tu familia!', 'type' => 'text'],
            ['group' => 'welcome', 'key' => 'hero_subtitle', 'value' => 'Construye tu árbol genealógico y descubre los momentos más importantes de tu historia.', 'type' => 'text'],
            ['group' => 'welcome', 'key' => 'description_1', 'value' => 'es un espacio creado para reunir a las familias y sus descendientes. Nuestra intención es preservar la memoria de nuestras familias y fortalecer los lazos con nuestra comunidad y nuestros parientes en todas partes del mundo.', 'type' => 'textarea'],
            ['group' => 'welcome', 'key' => 'description_2', 'value' => 'En este sitio podrás registrar tu historia, invitar a tus familiares, encontrar parientes y descubrir relaciones que no conocías y así formar parte de un legado vivo que une pasado, presente y futuro.', 'type' => 'textarea'],
            ['group' => 'welcome', 'key' => 'login_title', 'value' => '¡Hola! Inicia tu sesión', 'type' => 'text'],
            ['group' => 'welcome', 'key' => 'register_cta', 'value' => '¡Da click aquí y únete!', 'type' => 'text'],
            ['group' => 'welcome', 'key' => 'register_question', 'value' => '¿Todavía no estás registrado?', 'type' => 'text'],
            ['group' => 'welcome', 'key' => 'register_tagline', 'value' => 'Disfruta tu origen y vive la historia. Compártelo con los miembros de tu familia.', 'type' => 'textarea'],
            ['group' => 'welcome', 'key' => 'feature_images_shape', 'value' => 'round', 'type' => 'text'],
            ['group' => 'welcome', 'key' => 'feature_1_title', 'value' => '¡Solo necesitas empezar!', 'type' => 'text'],
            ['group' => 'welcome', 'key' => 'feature_1_text', 'value' => 'Es muy sencillo, ingresa primero tus datos y después podrás añadir a tus padres, abuelos, hermanos, hijos y demás familiares. Una vez agregados podrás invitarlos a participar en tu árbol y compartir información, imágenes y documentos de su historia.', 'type' => 'textarea'],
            ['group' => 'welcome', 'key' => 'feature_1_image', 'value' => 'images/feature-start.jpg', 'type' => 'image'],
            ['group' => 'welcome', 'key' => 'feature_2_title', 'value' => '¿Tienes un árbol en otro sitio?', 'type' => 'text'],
            ['group' => 'welcome', 'key' => 'feature_2_text', 'value' => '¡Tráelo para acá! Esta plataforma trabaja con datos de clasificación Gedcom, el estándar compartido de las principales bases de datos genealógicas, así que si tienes registros en otras plataformas, puedes importar su información fácilmente.', 'type' => 'textarea'],
            ['group' => 'welcome', 'key' => 'feature_2_image', 'value' => 'images/feature-import.jpg', 'type' => 'image'],
            ['group' => 'welcome', 'key' => 'feature_3_title', 'value' => 'Tú eliges con quien compartir.', 'type' => 'text'],
            ['group' => 'welcome', 'key' => 'feature_3_text', 'value' => 'Tu información es tuya y no saldrá de este sitio. Podrás elegir compartirlo con tu familia y tu comunidad. Solo podrán consultarla quienes tú autorices.', 'type' => 'textarea'],
            ['group' => 'welcome', 'key' => 'feature_3_image', 'value' => 'images/feature-privacy.jpg', 'type' => 'image'],
            ['group' => 'welcome', 'key' => 'free_title', 'value' => 'es de uso libre.', 'type' => 'text'],
            ['group' => 'welcome', 'key' => 'free_text_1', 'value' => 'es gratuito para todos los usuarios y sus familiares.', 'type' => 'textarea'],
            ['group' => 'welcome', 'key' => 'free_text_2', 'value' => 'Registra tu historia familiar, conecta con tus parientes y preserva la memoria de tu familia para las generaciones futuras.', 'type' => 'textarea'],

            // =============================================
            // Welcome first-time page
            // =============================================
            ['group' => 'welcome_first', 'key' => 'greeting_text', 'value' => 'Estas a un paso de comenzar a construir tu arbol genealogico y conectar con tu historia familiar.', 'type' => 'textarea'],
            ['group' => 'welcome_first', 'key' => 'description_1', 'value' => 'Nos alegra que formes parte de esta comunidad dedicada a entender nuestras raices y fortalecer los lazos familiares.', 'type' => 'textarea'],
            ['group' => 'welcome_first', 'key' => 'description_2', 'value' => 'la informacion que adjuntes a tu arbol es privada y solo sera compartida con quien tu elijas hacerlo. Por ello, la informacion que ingreses sobre parientes vivos requerira introducir un correo electronico de la persona agregada. El o ella recibira un correo automatico solicitando su autorizacion para compartir sus datos y una invitacion para participar en el sitio.', 'type' => 'textarea'],
            ['group' => 'welcome_first', 'key' => 'description_3', 'value' => 'Si ya es un miembro recibira un mensaje en su seccion de mensajes invitandole a unirse a tu arbol. Asi de sencillo!', 'type' => 'textarea'],
            ['group' => 'welcome_first', 'key' => 'thanks_text', 'value' => 'Gracias por ser parte de este proyecto tan especial!', 'type' => 'text'],
            ['group' => 'welcome_first', 'key' => 'main_image', 'value' => 'images/familia_fondo.jpg', 'type' => 'image'],
            ['group' => 'welcome_first', 'key' => 'circle_image', 'value' => 'images/familia_moderna.jpg', 'type' => 'image'],
            ['group' => 'welcome_first', 'key' => 'banner_text', 'value' => 'Mi Familia es una plataforma que busca ayudarnos a entender nuestras raices y fortalecer los lazos familiares y comunitarios.', 'type' => 'textarea'],
            ['group' => 'welcome_first', 'key' => 'instructions_title', 'value' => 'Para iniciar, alimenta tu perfil con más información, esta servirá como punto de partida. A partir de ahí podrás agregar más familiares desde tu árbol.', 'type' => 'textarea'],
            ['group' => 'welcome_first', 'key' => 'instructions_1', 'value' => 'En el árbol verás un recuadro con tus datos. Da click sobre él y se desplegará una sección en el lado derecho, desde ahí podrás acceder a tu perfil o agregar familiares inmediatos: padres, cónyuge, hijos y hermanos.', 'type' => 'textarea'],
            ['group' => 'welcome_first', 'key' => 'instructions_2', 'value' => 'Una vez agregado un nuevo familiar, podrás seleccionarlo y la sección lateral mostrará sus datos. Dando click sobre el botón Centrar en el árbol podrás agregar familiares directos a su posición: padres, cónyuge, hijos y hermanos, expandiendo de esta forma tu árbol. Para regresar a ti, solo debes hacer click en tu posición.', 'type' => 'textarea'],
            ['group' => 'welcome_first', 'key' => 'instructions_3', 'value' => 'Podrás editar más información de cada usuario y sus relaciones desde su perfil individual. También podrás buscar más familiares en la opción Búsqueda desde el menú superior, o en la sección Agregar relación desde cada perfil.', 'type' => 'textarea'],
            ['group' => 'welcome_first', 'key' => 'cta_text', 'value' => 'Continua a editar tu perfil', 'type' => 'text'],
            ['group' => 'welcome_first', 'key' => 'decorative_image', 'value' => 'images/circulos2.jpg', 'type' => 'image'],

            // =============================================
            // Login page
            // =============================================
            ['group' => 'login', 'key' => 'title', 'value' => 'Iniciar Sesion', 'type' => 'text'],
            ['group' => 'login', 'key' => 'subtitle', 'value' => 'Accede a tu arbol genealogico', 'type' => 'text'],
            ['group' => 'login', 'key' => 'register_text', 'value' => 'No tienes cuenta?', 'type' => 'text'],
            ['group' => 'login', 'key' => 'register_button', 'value' => 'Registrate gratis', 'type' => 'text'],

            // =============================================
            // Mail
            // =============================================
            ['group' => 'mail', 'key' => 'logo_image', 'value' => 'images/logo.png', 'type' => 'image'],
            ['group' => 'mail', 'key' => 'footer_text', 'value' => 'Mi Familia - Tu arbol genealogico', 'type' => 'text'],

            // =============================================
            // Colors
            // =============================================
            ['group' => 'colors', 'key' => 'font', 'value' => 'Ubuntu', 'type' => 'text'],
            ['group' => 'colors', 'key' => 'primary', 'value' => '#3b82f6', 'type' => 'color'],
            ['group' => 'colors', 'key' => 'secondary', 'value' => '#2563eb', 'type' => 'color'],
            ['group' => 'colors', 'key' => 'accent', 'value' => '#f59e0b', 'type' => 'color'],
            ['group' => 'colors', 'key' => 'light', 'value' => '#dbeafe', 'type' => 'color'],
            ['group' => 'colors', 'key' => 'dark', 'value' => '#1d4ed8', 'type' => 'color'],

            // =============================================
            // Footer
            // =============================================
            ['group' => 'footer', 'key' => 'footer_col_1', 'value' => '<img src="/images/logo.png" alt="Mi Familia" class="h-20 object-contain">', 'type' => 'html'],
            ['group' => 'footer', 'key' => 'footer_col_2', 'value' => '<a href="/help" class="block text-gray-600 hover:text-[#3b82f6]">¿Cómo funciona Mi Familia?</a>
<a href="/ancestors-info" class="block text-gray-600 hover:text-[#3b82f6]">Donde encontrar más información de mis antepasados</a>
<a href="/privacy" class="block text-gray-600 hover:text-[#3b82f6]">Privacidad</a>
<a href="/terms" class="block text-gray-600 hover:text-[#3b82f6]">Términos y condiciones</a>', 'type' => 'html'],
            ['group' => 'footer', 'key' => 'footer_col_3', 'value' => '', 'type' => 'html'],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key']],
                ['value' => $setting['value'], 'type' => $setting['type']]
            );
        }
    }
}
