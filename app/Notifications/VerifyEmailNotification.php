<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends VerifyEmail
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $code = $notifiable->confirmation_code;

        $message = (new MailMessage)
            ->subject('Verifica tu correo electrónico - Mi Familia')
            ->greeting('¡Hola!')
            ->line('Gracias por registrarte en Mi Familia. Por favor, haz clic en el botón de abajo para verificar tu dirección de correo electrónico.')
            ->action('Verificar correo electrónico', $verificationUrl);

        // Mostrar el codigo de 6 digitos si existe
        if ($code) {
            $message->line('También puedes verificar tu cuenta ingresando el siguiente código de 6 dígitos:')
                    ->line('**' . $code . '**');
        }

        $message->line('Este enlace de verificación expirará en ' . Config::get('auth.verification.expire', 60) . ' minutos.')
                ->line('Si no creaste una cuenta, no es necesario realizar ninguna acción.')
                ->salutation('Saludos, Mi Familia');

        return $message;
    }
}
