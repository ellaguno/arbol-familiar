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
            // Welcome page (ES)
            // =============================================
            ['group' => 'welcome', 'key' => 'login_position', 'value' => 'left', 'type' => 'text', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'hero_show', 'value' => '1', 'type' => 'boolean', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'hero_image', 'value' => 'images/hero-beach.jpg', 'type' => 'image', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'logo_image', 'value' => 'images/logo.png', 'type' => 'image', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'hero_title', 'value' => '¡Conecta con tu familia!', 'type' => 'text', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'hero_subtitle', 'value' => 'Construye tu árbol genealógico y descubre los momentos más importantes de tu historia.', 'type' => 'text', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'description_1', 'value' => 'es un espacio creado para reunir a las familias y sus descendientes. Nuestra intención es preservar la memoria de nuestras familias y fortalecer los lazos con nuestra comunidad y nuestros parientes en todas partes del mundo.', 'type' => 'textarea', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'description_2', 'value' => 'En este sitio podrás registrar tu historia, invitar a tus familiares, encontrar parientes y descubrir relaciones que no conocías y así formar parte de un legado vivo que une pasado, presente y futuro.', 'type' => 'textarea', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'login_title', 'value' => '¡Hola! Inicia tu sesión', 'type' => 'text', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'register_cta', 'value' => '¡Da click aquí y únete!', 'type' => 'text', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'register_question', 'value' => '¿Todavía no estás registrado?', 'type' => 'text', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'register_tagline', 'value' => 'Disfruta tu origen y vive la historia. Compártelo con los miembros de tu familia.', 'type' => 'textarea', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'show_description', 'value' => '1', 'type' => 'boolean', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'show_register', 'value' => '1', 'type' => 'boolean', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'show_features', 'value' => '1', 'type' => 'boolean', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'show_free_section', 'value' => '1', 'type' => 'boolean', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'feature_images_shape', 'value' => 'round', 'type' => 'text', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'feature_1_title', 'value' => '¡Solo necesitas empezar!', 'type' => 'text', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'feature_1_text', 'value' => 'Es muy sencillo, ingresa primero tus datos y después podrás añadir a tus padres, abuelos, hermanos, hijos y demás familiares. Una vez agregados podrás invitarlos a participar en tu árbol y compartir información, imágenes y documentos de su historia.', 'type' => 'textarea', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'feature_1_image', 'value' => 'images/feature-start.jpg', 'type' => 'image', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'feature_2_title', 'value' => '¿Tienes un árbol en otro sitio?', 'type' => 'text', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'feature_2_text', 'value' => '¡Tráelo para acá! Esta plataforma trabaja con datos de clasificación Gedcom, el estándar compartido de las principales bases de datos genealógicas, así que si tienes registros en otras plataformas, puedes importar su información fácilmente.', 'type' => 'textarea', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'feature_2_image', 'value' => 'images/feature-import.jpg', 'type' => 'image', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'feature_3_title', 'value' => 'Tú eliges con quien compartir.', 'type' => 'text', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'feature_3_text', 'value' => 'Tu información es tuya y no saldrá de este sitio. Podrás elegir compartirlo con tu familia y tu comunidad. Solo podrán consultarla quienes tú autorices.', 'type' => 'textarea', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'feature_3_image', 'value' => 'images/feature-privacy.jpg', 'type' => 'image', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'free_title', 'value' => 'es de uso libre.', 'type' => 'text', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'free_text_1', 'value' => 'es gratuito para todos los usuarios y sus familiares.', 'type' => 'textarea', 'language' => 'es'],
            ['group' => 'welcome', 'key' => 'free_text_2', 'value' => 'Registra tu historia familiar, conecta con tus parientes y preserva la memoria de tu familia para las generaciones futuras.', 'type' => 'textarea', 'language' => 'es'],

            // =============================================
            // Welcome page (EN)
            // =============================================
            ['group' => 'welcome', 'key' => 'hero_title', 'value' => 'Connect with your family!', 'type' => 'text', 'language' => 'en'],
            ['group' => 'welcome', 'key' => 'hero_subtitle', 'value' => 'Build your family tree and discover the most important moments of your history.', 'type' => 'text', 'language' => 'en'],
            ['group' => 'welcome', 'key' => 'description_1', 'value' => 'is a space created to bring families and their descendants together. Our intention is to preserve our families\' memory and strengthen the bonds with our community and relatives all over the world.', 'type' => 'textarea', 'language' => 'en'],
            ['group' => 'welcome', 'key' => 'description_2', 'value' => 'On this site you can record your history, invite your relatives, find family members and discover connections you didn\'t know existed, becoming part of a living legacy that unites past, present and future.', 'type' => 'textarea', 'language' => 'en'],
            ['group' => 'welcome', 'key' => 'login_title', 'value' => 'Hello! Sign in', 'type' => 'text', 'language' => 'en'],
            ['group' => 'welcome', 'key' => 'register_cta', 'value' => 'Click here and join!', 'type' => 'text', 'language' => 'en'],
            ['group' => 'welcome', 'key' => 'register_question', 'value' => 'Not registered yet?', 'type' => 'text', 'language' => 'en'],
            ['group' => 'welcome', 'key' => 'register_tagline', 'value' => 'Enjoy your roots and live the story. Share it with your family members.', 'type' => 'textarea', 'language' => 'en'],
            ['group' => 'welcome', 'key' => 'feature_1_title', 'value' => 'You just need to start!', 'type' => 'text', 'language' => 'en'],
            ['group' => 'welcome', 'key' => 'feature_1_text', 'value' => 'It\'s very simple, first enter your information and then you can add your parents, grandparents, siblings, children and other relatives. Once added, you can invite them to participate in your tree and share information, images and documents about their history.', 'type' => 'textarea', 'language' => 'en'],
            ['group' => 'welcome', 'key' => 'feature_2_title', 'value' => 'Have a tree somewhere else?', 'type' => 'text', 'language' => 'en'],
            ['group' => 'welcome', 'key' => 'feature_2_text', 'value' => 'Bring it here! This platform works with GEDCOM data, the standard shared by major genealogy databases, so if you have records on other platforms, you can easily import your information.', 'type' => 'textarea', 'language' => 'en'],
            ['group' => 'welcome', 'key' => 'feature_3_title', 'value' => 'You choose who to share with.', 'type' => 'text', 'language' => 'en'],
            ['group' => 'welcome', 'key' => 'feature_3_text', 'value' => 'Your information is yours and won\'t leave this site. You can choose to share it with your family and community. Only those you authorize will be able to see it.', 'type' => 'textarea', 'language' => 'en'],
            ['group' => 'welcome', 'key' => 'free_title', 'value' => 'is free to use.', 'type' => 'text', 'language' => 'en'],
            ['group' => 'welcome', 'key' => 'free_text_1', 'value' => 'is free for all users and their family members.', 'type' => 'textarea', 'language' => 'en'],
            ['group' => 'welcome', 'key' => 'free_text_2', 'value' => 'Record your family history, connect with your relatives and preserve your family\'s memory for future generations.', 'type' => 'textarea', 'language' => 'en'],

            // =============================================
            // Welcome first-time page (ES)
            // =============================================
            ['group' => 'welcome_first', 'key' => 'greeting_text', 'value' => 'Estas a un paso de comenzar a construir tu arbol genealogico y conectar con tu historia familiar.', 'type' => 'textarea', 'language' => 'es'],
            ['group' => 'welcome_first', 'key' => 'description_1', 'value' => 'Nos alegra que formes parte de esta comunidad dedicada a entender nuestras raices y fortalecer los lazos familiares.', 'type' => 'textarea', 'language' => 'es'],
            ['group' => 'welcome_first', 'key' => 'description_2', 'value' => 'la informacion que adjuntes a tu arbol es privada y solo sera compartida con quien tu elijas hacerlo. Por ello, la informacion que ingreses sobre parientes vivos requerira introducir un correo electronico de la persona agregada. El o ella recibira un correo automatico solicitando su autorizacion para compartir sus datos y una invitacion para participar en el sitio.', 'type' => 'textarea', 'language' => 'es'],
            ['group' => 'welcome_first', 'key' => 'description_3', 'value' => 'Si ya es un miembro recibira un mensaje en su seccion de mensajes invitandole a unirse a tu arbol. Asi de sencillo!', 'type' => 'textarea', 'language' => 'es'],
            ['group' => 'welcome_first', 'key' => 'thanks_text', 'value' => 'Gracias por ser parte de este proyecto tan especial!', 'type' => 'text', 'language' => 'es'],
            ['group' => 'welcome_first', 'key' => 'main_image', 'value' => 'images/familia_fondo.jpg', 'type' => 'image', 'language' => 'es'],
            ['group' => 'welcome_first', 'key' => 'circle_image', 'value' => 'images/familia_moderna.jpg', 'type' => 'image', 'language' => 'es'],
            ['group' => 'welcome_first', 'key' => 'banner_text', 'value' => 'Mi Familia es una plataforma que busca ayudarnos a entender nuestras raices y fortalecer los lazos familiares y comunitarios.', 'type' => 'textarea', 'language' => 'es'],
            ['group' => 'welcome_first', 'key' => 'instructions_title', 'value' => 'Para iniciar, alimenta tu perfil con más información, esta servirá como punto de partida. A partir de ahí podrás agregar más familiares desde tu árbol.', 'type' => 'textarea', 'language' => 'es'],
            ['group' => 'welcome_first', 'key' => 'instructions_1', 'value' => 'En el árbol verás un recuadro con tus datos. Da click sobre él y se desplegará una sección en el lado derecho, desde ahí podrás acceder a tu perfil o agregar familiares inmediatos: padres, cónyuge, hijos y hermanos.', 'type' => 'textarea', 'language' => 'es'],
            ['group' => 'welcome_first', 'key' => 'instructions_2', 'value' => 'Una vez agregado un nuevo familiar, podrás seleccionarlo y la sección lateral mostrará sus datos. Dando click sobre el botón Centrar en el árbol podrás agregar familiares directos a su posición: padres, cónyuge, hijos y hermanos, expandiendo de esta forma tu árbol. Para regresar a ti, solo debes hacer click en tu posición.', 'type' => 'textarea', 'language' => 'es'],
            ['group' => 'welcome_first', 'key' => 'instructions_3', 'value' => 'Podrás editar más información de cada usuario y sus relaciones desde su perfil individual. También podrás buscar más familiares en la opción Búsqueda desde el menú superior, o en la sección Agregar relación desde cada perfil.', 'type' => 'textarea', 'language' => 'es'],
            ['group' => 'welcome_first', 'key' => 'cta_text', 'value' => 'Continua a editar tu perfil', 'type' => 'text', 'language' => 'es'],
            ['group' => 'welcome_first', 'key' => 'decorative_image', 'value' => 'images/circulos2.jpg', 'type' => 'image', 'language' => 'es'],

            // =============================================
            // Welcome first-time page (EN)
            // =============================================
            ['group' => 'welcome_first', 'key' => 'greeting_text', 'value' => 'You are one step away from building your family tree and connecting with your family history.', 'type' => 'textarea', 'language' => 'en'],
            ['group' => 'welcome_first', 'key' => 'description_1', 'value' => 'We are glad you are part of this community dedicated to understanding our roots and strengthening family bonds.', 'type' => 'textarea', 'language' => 'en'],
            ['group' => 'welcome_first', 'key' => 'description_2', 'value' => 'the information you attach to your tree is private and will only be shared with whom you choose. Therefore, information you enter about living relatives will require their email address. They will receive an automatic email requesting authorization to share their data and an invitation to participate on the site.', 'type' => 'textarea', 'language' => 'en'],
            ['group' => 'welcome_first', 'key' => 'description_3', 'value' => 'If they are already a member, they will receive a message in their messages section inviting them to join your tree. It\'s that simple!', 'type' => 'textarea', 'language' => 'en'],
            ['group' => 'welcome_first', 'key' => 'thanks_text', 'value' => 'Thank you for being part of this very special project!', 'type' => 'text', 'language' => 'en'],
            ['group' => 'welcome_first', 'key' => 'banner_text', 'value' => 'Mi Familia is a platform that seeks to help us understand our roots and strengthen family and community bonds.', 'type' => 'textarea', 'language' => 'en'],
            ['group' => 'welcome_first', 'key' => 'instructions_title', 'value' => 'To get started, fill out your profile with more information — this will serve as your starting point. From there you can add more relatives from your tree.', 'type' => 'textarea', 'language' => 'en'],
            ['group' => 'welcome_first', 'key' => 'instructions_1', 'value' => 'In the tree you will see a box with your data. Click on it and a section will appear on the right side, from there you can access your profile or add immediate relatives: parents, spouse, children and siblings.', 'type' => 'textarea', 'language' => 'en'],
            ['group' => 'welcome_first', 'key' => 'instructions_2', 'value' => 'Once a new relative is added, you can select them and the side section will show their data. By clicking the Center on tree button you can add direct relatives to their position: parents, spouse, children and siblings, thus expanding your tree. To return to yourself, just click on your position.', 'type' => 'textarea', 'language' => 'en'],
            ['group' => 'welcome_first', 'key' => 'instructions_3', 'value' => 'You can edit more information about each user and their relationships from their individual profile. You can also search for more relatives in the Search option from the top menu, or in the Add relationship section from each profile.', 'type' => 'textarea', 'language' => 'en'],
            ['group' => 'welcome_first', 'key' => 'cta_text', 'value' => 'Continue to edit your profile', 'type' => 'text', 'language' => 'en'],

            // =============================================
            // Login page (ES)
            // =============================================
            ['group' => 'login', 'key' => 'title', 'value' => 'Iniciar Sesion', 'type' => 'text', 'language' => 'es'],
            ['group' => 'login', 'key' => 'subtitle', 'value' => 'Accede a tu arbol genealogico', 'type' => 'text', 'language' => 'es'],
            ['group' => 'login', 'key' => 'register_text', 'value' => 'No tienes cuenta?', 'type' => 'text', 'language' => 'es'],
            ['group' => 'login', 'key' => 'register_button', 'value' => 'Registrate gratis', 'type' => 'text', 'language' => 'es'],

            // =============================================
            // Login page (EN)
            // =============================================
            ['group' => 'login', 'key' => 'title', 'value' => 'Sign In', 'type' => 'text', 'language' => 'en'],
            ['group' => 'login', 'key' => 'subtitle', 'value' => 'Access your family tree', 'type' => 'text', 'language' => 'en'],
            ['group' => 'login', 'key' => 'register_text', 'value' => 'Don\'t have an account?', 'type' => 'text', 'language' => 'en'],
            ['group' => 'login', 'key' => 'register_button', 'value' => 'Register for free', 'type' => 'text', 'language' => 'en'],

            // =============================================
            // Mail (ES)
            // =============================================
            ['group' => 'mail', 'key' => 'logo_image', 'value' => 'images/logo.png', 'type' => 'image', 'language' => 'es'],
            ['group' => 'mail', 'key' => 'footer_text', 'value' => 'Mi Familia - Tu arbol genealogico', 'type' => 'text', 'language' => 'es'],

            // =============================================
            // Mail (EN)
            // =============================================
            ['group' => 'mail', 'key' => 'footer_text', 'value' => 'Mi Familia - Your family tree', 'type' => 'text', 'language' => 'en'],

            // =============================================
            // Colors (shared, ES only)
            // =============================================
            ['group' => 'colors', 'key' => 'font', 'value' => 'Ubuntu', 'type' => 'text', 'language' => 'es'],
            ['group' => 'colors', 'key' => 'primary', 'value' => '#3b82f6', 'type' => 'color', 'language' => 'es'],
            ['group' => 'colors', 'key' => 'secondary', 'value' => '#2563eb', 'type' => 'color', 'language' => 'es'],
            ['group' => 'colors', 'key' => 'accent', 'value' => '#f59e0b', 'type' => 'color', 'language' => 'es'],
            ['group' => 'colors', 'key' => 'light', 'value' => '#dbeafe', 'type' => 'color', 'language' => 'es'],
            ['group' => 'colors', 'key' => 'dark', 'value' => '#1d4ed8', 'type' => 'color', 'language' => 'es'],
            ['group' => 'colors', 'key' => 'theme_mode', 'value' => 'dark', 'type' => 'text', 'language' => 'es'],
            ['group' => 'colors', 'key' => 'bg_color', 'value' => '', 'type' => 'text', 'language' => 'es'],
            ['group' => 'colors', 'key' => 'bg_image', 'value' => '', 'type' => 'text', 'language' => 'es'],

            // =============================================
            // Footer (ES)
            // =============================================
            ['group' => 'footer', 'key' => 'footer_col_1', 'value' => '<img src="/images/logo.png" alt="Mi Familia" class="h-20 object-contain">', 'type' => 'html', 'language' => 'es'],
            ['group' => 'footer', 'key' => 'footer_col_2', 'value' => '<a href="/help" class="block text-gray-600 hover:text-[#3b82f6]">¿Cómo funciona Mi Familia?</a>
<a href="/ancestors-info" class="block text-gray-600 hover:text-[#3b82f6]">Donde encontrar más información de mis antepasados</a>
<a href="/privacy" class="block text-gray-600 hover:text-[#3b82f6]">Privacidad</a>
<a href="/terms" class="block text-gray-600 hover:text-[#3b82f6]">Términos y condiciones</a>', 'type' => 'html', 'language' => 'es'],
            ['group' => 'footer', 'key' => 'footer_col_3', 'value' => '', 'type' => 'html', 'language' => 'es'],

            // =============================================
            // Footer (EN)
            // =============================================
            ['group' => 'footer', 'key' => 'footer_col_1', 'value' => '<img src="/images/logo.png" alt="Mi Familia" class="h-20 object-contain">', 'type' => 'html', 'language' => 'en'],
            ['group' => 'footer', 'key' => 'footer_col_2', 'value' => '<a href="/help" class="block text-gray-600 hover:text-[#3b82f6]">How does Mi Familia work?</a>
<a href="/ancestors-info" class="block text-gray-600 hover:text-[#3b82f6]">Where to find more information about my ancestors</a>
<a href="/privacy" class="block text-gray-600 hover:text-[#3b82f6]">Privacy</a>
<a href="/terms" class="block text-gray-600 hover:text-[#3b82f6]">Terms and conditions</a>', 'type' => 'html', 'language' => 'en'],
            ['group' => 'footer', 'key' => 'footer_col_3', 'value' => '', 'type' => 'html', 'language' => 'en'],

            // =============================================
            // Navigation (shared, ES only)
            // =============================================
            ['group' => 'navigation', 'key' => 'show_research', 'value' => '0', 'type' => 'text', 'language' => 'es'],
            ['group' => 'navigation', 'key' => 'show_help', 'value' => '0', 'type' => 'text', 'language' => 'es'],

            // =============================================
            // Heritage (shared, ES only)
            // =============================================
            ['group' => 'heritage', 'key' => 'heritage_enabled', 'value' => '0', 'type' => 'text', 'language' => 'es'],
            ['group' => 'heritage', 'key' => 'heritage_label', 'value' => 'Herencia cultural', 'type' => 'text', 'language' => 'es'],
            ['group' => 'heritage', 'key' => 'heritage_regions', 'value' => json_encode(config('mi-familia.heritage_regions', ['region_1' => 'Region 1', 'region_2' => 'Region 2', 'region_3' => 'Region 3', 'region_4' => 'Region 4', 'other' => 'Otra region', 'unknown' => 'Desconocida'])), 'type' => 'json', 'language' => 'es'],
            ['group' => 'heritage', 'key' => 'heritage_decades', 'value' => json_encode(config('mi-familia.migration_decades', ['1850-1860' => '1850 - 1860', '1860-1870' => '1860 - 1870', '1870-1880' => '1870 - 1880', '1880-1890' => '1880 - 1890', '1890-1900' => '1890 - 1900', '1900-1910' => '1900 - 1910', '1910-1920' => '1910 - 1920', '1920-1930' => '1920 - 1930', '1930-1940' => '1930 - 1940', '1940-1950' => '1940 - 1950', '1950-1960' => '1950 - 1960', '1960-1970' => '1960 - 1970', '1970-1980' => '1970 - 1980', '1980-1990' => '1980 - 1990', '1990-2000' => '1990 - 2000', '2000-2010' => '2000 - 2010', '2010-2020' => '2010 - 2020', '2020-present' => '2020 - Presente'])), 'type' => 'json', 'language' => 'es'],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key'], 'language' => $setting['language']],
                ['value' => $setting['value'], 'type' => $setting['type']]
            );
        }
    }
}
