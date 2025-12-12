<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Restablecer contraseña - Mi Familia')
            ->greeting('¡Hola!')
            ->line('Recibiste este correo porque solicitaste restablecer la contraseña de tu cuenta en Mi Familia.')
            ->action('Restablecer contraseña', $url)
            ->line('Este enlace para restablecer contraseña expirará en ' . config('auth.passwords.'.config('auth.defaults.passwords').'.expire') . ' minutos.')
            ->line('Si no solicitaste restablecer tu contraseña, no es necesario realizar ninguna acción.')
            ->salutation('Saludos, Mi Familia');
    }
}
