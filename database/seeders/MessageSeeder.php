<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ivan = User::where('email', 'ivan.horvat@example.com')->first();
        $admin = User::where('email', 'admin@mi-familia.org')->first();

        $petar = Person::where('first_name', 'Petar')->where('patronymic', 'Horvat')->first();
        $anaBabic = Person::where('first_name', 'Ana')->where('patronymic', 'Babic')->first();

        // Mensaje de bienvenida del sistema
        Message::create([
            'sender_id' => null,
            'recipient_id' => $ivan->id,
            'type' => 'system',
            'subject' => 'Bienvenido a Mi Familia',
            'body' => 'Bienvenido a Mi Familia, la plataforma genealogica de codigo abierto. Aqui podras construir tu arbol genealogico, conectar con familiares y descubrir tus raices. Si tienes alguna pregunta, no dudes en contactarnos.',
            'action_required' => false,
            'read_at' => now()->subDays(10),
        ]);

        // Mensaje de solicitud de consentimiento
        Message::create([
            'sender_id' => null,
            'recipient_id' => $ivan->id,
            'type' => 'consent_request',
            'subject' => 'Solicitud de consentimiento pendiente',
            'body' => 'Has registrado a Ana Babic en tu arbol genealogico. Como es una persona viva mayor de edad con correo electronico registrado, se le ha enviado una solicitud de consentimiento. Tienes 7 dias para que responda antes de que su informacion se oculte automaticamente.',
            'related_person_id' => $anaBabic->id,
            'action_required' => true,
            'action_status' => 'pending',
        ]);

        // Mensaje de relacion encontrada
        Message::create([
            'sender_id' => null,
            'recipient_id' => $ivan->id,
            'type' => 'relationship_found',
            'subject' => 'Posible familiar encontrado',
            'body' => 'Hemos encontrado un usuario que podria estar relacionado contigo. Carlos Rodriguez Kovacevic tambien tiene herencia etnica de la misma region. ¿Te gustaria enviarle una solicitud de conexion?',
            'action_required' => true,
            'action_status' => 'pending',
        ]);

        // Mensaje general del admin
        Message::create([
            'sender_id' => $admin->id,
            'recipient_id' => $ivan->id,
            'type' => 'general',
            'subject' => 'Evento de la comunidad',
            'body' => 'Estimado Ivan, te invitamos al proximo evento de la comunidad que se celebrara el proximo mes. Sera una gran oportunidad para conocer a otros miembros de la comunidad.',
            'action_required' => false,
        ]);

        // Mensaje de invitacion pendiente
        Message::create([
            'sender_id' => null,
            'recipient_id' => $ivan->id,
            'type' => 'invitation',
            'subject' => 'Invitacion enviada a Petar Horvat',
            'body' => 'Se ha enviado una invitacion a petar.horvat@example.com para que se una a Mi Familia. Cuando acepte, podra ver y editar su informacion en tu arbol genealogico.',
            'related_person_id' => $petar->id,
            'action_required' => false,
        ]);

        // Mensaje para el admin
        Message::create([
            'sender_id' => null,
            'recipient_id' => $admin->id,
            'type' => 'system',
            'subject' => 'Nuevo usuario registrado',
            'body' => 'Se ha registrado un nuevo usuario en el sistema: ivan.horvat@example.com',
            'action_required' => false,
            'read_at' => now()->subDays(5),
        ]);
    }
}
