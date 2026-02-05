<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Canales de Broadcast para el Chat
|--------------------------------------------------------------------------
|
| Canal privado entre dos usuarios. Se usa min/max para que ambos
| compartan el mismo canal independientemente de quien inicia.
|
*/

Broadcast::channel('chat.{userA}.{userB}', function ($user, $userA, $userB) {
    return (int) $user->id === (int) $userA || (int) $user->id === (int) $userB;
});
