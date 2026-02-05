<?php

namespace App\Plugins;

class HookManager
{
    /**
     * Hooks registrados.
     * @var array<string, array<array{content: mixed, plugin: string, priority: int}>>
     */
    protected array $hooks = [];

    /**
     * Registrar contenido para un punto de hook.
     */
    public function register(string $hookName, mixed $content, string $pluginSlug, int $priority = 10): void
    {
        $this->hooks[$hookName][] = [
            'content' => $content,
            'plugin' => $pluginSlug,
            'priority' => $priority,
        ];
    }

    /**
     * Renderizar todo el contenido registrado para un hook.
     */
    public function render(string $hookName, array $data = []): string
    {
        if (empty($this->hooks[$hookName])) {
            return '';
        }

        $items = $this->hooks[$hookName];
        usort($items, fn($a, $b) => $a['priority'] <=> $b['priority']);

        $output = '';
        foreach ($items as $item) {
            $content = $item['content'];

            if (is_string($content) && str_contains($content, '::')) {
                // Referencia a vista Blade: 'plugin-slug::vista'
                $output .= view($content, $data)->render();
            } elseif (is_callable($content)) {
                $output .= call_user_func($content, $data);
            } elseif (is_string($content)) {
                $output .= $content;
            }
        }

        return $output;
    }

    /**
     * Verificar si hay contenido registrado para un hook.
     */
    public function has(string $hookName): bool
    {
        return !empty($this->hooks[$hookName]);
    }

    /**
     * Obtener todos los hooks registrados.
     */
    public function all(): array
    {
        return $this->hooks;
    }
}
