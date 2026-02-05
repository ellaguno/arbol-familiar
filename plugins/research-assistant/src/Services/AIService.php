<?php

namespace Plugin\ResearchAssistant\Services;

use Plugin\ResearchAssistant\Services\Providers\AIProviderInterface;
use Plugin\ResearchAssistant\Services\Providers\AnthropicProvider;
use Plugin\ResearchAssistant\Services\Providers\DeepseekProvider;
use Plugin\ResearchAssistant\Services\Providers\OpenAIProvider;
use Plugin\ResearchAssistant\Services\Providers\OpenRouterProvider;

class AIService
{
    protected array $providers = [];
    protected array $settings = [];

    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
        $this->initializeProviders();
    }

    /**
     * Initialize all AI providers.
     */
    protected function initializeProviders(): void
    {
        $this->providers = [
            'openrouter' => new OpenRouterProvider($this->getDecryptedKey('openrouter_api_key')),
            'deepseek' => new DeepseekProvider($this->getDecryptedKey('deepseek_api_key')),
            'openai' => new OpenAIProvider($this->getDecryptedKey('openai_api_key')),
            'anthropic' => new AnthropicProvider($this->getDecryptedKey('anthropic_api_key')),
        ];
    }

    /**
     * Get decrypted API key from settings.
     */
    protected function getDecryptedKey(string $key): ?string
    {
        $value = $this->settings[$key] ?? null;

        if (empty($value)) {
            return null;
        }

        try {
            return decrypt($value);
        } catch (\Exception $e) {
            // If decryption fails, assume it's plain text (for backwards compatibility)
            return $value;
        }
    }

    /**
     * Get a specific provider by name.
     */
    public function getProvider(string $name): ?AIProviderInterface
    {
        return $this->providers[$name] ?? null;
    }

    /**
     * Get all available providers with their configuration status.
     */
    public function getAvailableProviders(): array
    {
        $available = [];

        foreach ($this->providers as $key => $provider) {
            $available[$key] = [
                'name' => $provider->getName(),
                'configured' => $provider->isConfigured(),
                'models' => $provider->getModels(),
            ];
        }

        return $available;
    }

    /**
     * Get configured providers only.
     */
    public function getConfiguredProviders(): array
    {
        return array_filter($this->providers, fn($p) => $p->isConfigured());
    }

    /**
     * Analyze search results using AI.
     */
    public function analyze(
        string $query,
        array $personContext,
        array $searchResults,
        string $providerName,
        string $model
    ): array {
        $provider = $this->getProvider($providerName);

        if (!$provider) {
            throw new \Exception(__('Proveedor de IA no encontrado: :provider', ['provider' => $providerName]));
        }

        if (!$provider->isConfigured()) {
            throw new \Exception(__('El proveedor :provider no esta configurado', ['provider' => $provider->getName()]));
        }

        $prompt = $this->buildAnalysisPrompt($query, $personContext, $searchResults);

        $result = $provider->analyze($prompt, ['model' => $model]);

        // Parse suggestions from the response
        $suggestions = $this->extractSuggestions($result['content']);

        return [
            'analysis' => $result['content'],
            'suggestions' => $suggestions,
            'tokens' => $result['tokens'],
            'model' => $result['model'],
        ];
    }

    /**
     * Build the analysis prompt.
     */
    protected function buildAnalysisPrompt(string $query, array $personContext, array $searchResults): string
    {
        $prompt = "## Consulta de investigacion\n{$query}\n\n";

        if (!empty($personContext)) {
            $prompt .= "## Contexto de la persona\n";

            if (!empty($personContext['name'])) {
                $prompt .= "- **Nombre completo**: {$personContext['name']}\n";
            }
            if (!empty($personContext['birth_date'])) {
                $prompt .= "- **Fecha de nacimiento**: {$personContext['birth_date']}\n";
            }
            if (!empty($personContext['birth_place'])) {
                $prompt .= "- **Lugar de nacimiento**: {$personContext['birth_place']}\n";
            }
            if (!empty($personContext['death_date'])) {
                $prompt .= "- **Fecha de defuncion**: {$personContext['death_date']}\n";
            }
            if (!empty($personContext['death_place'])) {
                $prompt .= "- **Lugar de defuncion**: {$personContext['death_place']}\n";
            }
            if (!empty($personContext['father'])) {
                $prompt .= "- **Padre**: {$personContext['father']}\n";
            }
            if (!empty($personContext['mother'])) {
                $prompt .= "- **Madre**: {$personContext['mother']}\n";
            }
            if (!empty($personContext['spouses']) && is_array($personContext['spouses'])) {
                $prompt .= "- **Conyuges**: " . implode(', ', $personContext['spouses']) . "\n";
            }
            if (!empty($personContext['occupation'])) {
                $prompt .= "- **Ocupacion**: {$personContext['occupation']}\n";
            }
            if (!empty($personContext['notes'])) {
                $prompt .= "- **Notas**: {$personContext['notes']}\n";
            }

            $prompt .= "\n";
        }

        $prompt .= "## Resultados de busqueda\n\n";

        foreach ($searchResults as $source => $results) {
            $prompt .= "### {$source}\n";

            if (empty($results)) {
                $prompt .= "No se encontraron resultados.\n\n";
                continue;
            }

            foreach ($results as $result) {
                $title = $result['title'] ?? 'Sin titulo';
                $type = $result['type'] ?? 'unknown';

                if ($type === 'search_url') {
                    $prompt .= "- **{$title}**: URL de busqueda disponible\n";
                } elseif ($type === 'article' || $type === 'person_search') {
                    $snippet = $result['snippet'] ?? '';
                    $prompt .= "- **{$title}**: {$snippet}\n";
                } elseif ($type === 'error') {
                    $prompt .= "- Error: {$result['snippet']}\n";
                }
            }

            $prompt .= "\n";
        }

        $prompt .= "---\n\n";
        $prompt .= "Por favor, analiza la informacion anterior y proporciona:\n";
        $prompt .= "1. Un resumen de los hallazgos mas relevantes\n";
        $prompt .= "2. Posibles conexiones entre los datos\n";
        $prompt .= "3. Sugerencias especificas para continuar la investigacion (lista con viñetas)\n";
        $prompt .= "4. Advertencias sobre datos que necesitan verificacion\n";

        return $prompt;
    }

    /**
     * Extract suggestions from the AI response.
     */
    protected function extractSuggestions(string $content): array
    {
        $suggestions = [];

        // Look for bullet points or numbered lists in suggestions section
        if (preg_match('/(?:sugerencias|proximos pasos|recomendaciones)[:\s]*\n((?:[-*\d.]+\s+.+\n?)+)/i', $content, $matches)) {
            $lines = explode("\n", $matches[1]);
            foreach ($lines as $line) {
                $line = trim($line);
                $line = preg_replace('/^[-*\d.]+\s*/', '', $line);
                if (!empty($line)) {
                    $suggestions[] = $line;
                }
            }
        }

        // If no suggestions found, try to extract any bullet points
        if (empty($suggestions)) {
            if (preg_match_all('/^[-*]\s+(.+)$/m', $content, $matches)) {
                $suggestions = array_slice($matches[1], 0, 5);
            }
        }

        return $suggestions;
    }

    /**
     * Update settings.
     */
    public function updateSettings(array $settings): void
    {
        $this->settings = array_merge($this->settings, $settings);
        $this->initializeProviders();
    }
}
