<?php

namespace Plugin\PresenceCommunication;

use App\Plugins\PluginServiceProvider;
use Illuminate\Routing\Router;
use Plugin\PresenceCommunication\Middleware\TrackPresence;

class PresenceCommunicationPlugin extends PluginServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        // Registrar middleware de presencia en el grupo web
        if ($this->app->bound('router')) {
            /** @var Router $router */
            $router = $this->app->make('router');
            $router->pushMiddlewareToGroup('web', TrackPresence::class);
        }

        // Cargar canales de broadcast
        $channelsFile = $this->pluginPath . '/routes/channels.php';
        if (file_exists($channelsFile)) {
            require $channelsFile;
        }
    }

    public function hooks(): array
    {
        return [
            'dashboard.widgets' => 'presence-communication::hooks.dashboard-widget',
            'header.menu.items' => 'presence-communication::hooks.menu-indicator',
        ];
    }
}
