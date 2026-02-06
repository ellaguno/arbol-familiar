<?php

namespace Plugin\ResearchAssistant\Services\Providers;

use Illuminate\Support\Facades\Http;

class OpenRouterProvider implements AIProviderInterface
{
    protected string $apiKey = '';
    protected string $baseUrl = 'https://openrouter.ai/api/v1';
    protected int $tokensUsed = 0;

    // Marcador especial para modelo personalizado
    public const CUSTOM_MODEL = '_custom_';

    public function __construct(?string $apiKey = null)
    {
        if ($apiKey) {
            $this->apiKey = $apiKey;
        }
    }

    public function getName(): string
    {
        return 'OpenRouter';
    }

    public function getModels(): array
    {
        // Modelos populares de OpenRouter (actualizar periodicamente)
        // Ver: https://openrouter.ai/models?order=most-popular
        return [
            'anthropic/claude-sonnet-4' => 'Claude Sonnet 4',
            'anthropic/claude-3.5-sonnet' => 'Claude 3.5 Sonnet',
            'openai/gpt-4o' => 'GPT-4o',
            'openai/gpt-4o-mini' => 'GPT-4o Mini',
            'google/gemini-2.0-flash-001' => 'Gemini 2.0 Flash',
            'google/gemini-pro-1.5' => 'Gemini Pro 1.5',
            'deepseek/deepseek-chat' => 'DeepSeek V3',
            'deepseek/deepseek-r1' => 'DeepSeek R1',
            'meta-llama/llama-3.3-70b-instruct' => 'Llama 3.3 70B',
            'qwen/qwen-2.5-72b-instruct' => 'Qwen 2.5 72B',
            self::CUSTOM_MODEL => '-- ' . __('Modelo personalizado') . ' --',
        ];
    }

    /**
     * Check if custom model input is supported.
     */
    public function supportsCustomModel(): bool
    {
        return true;
    }

    public function analyze(string $prompt, array $context = []): array
    {
        $model = $context['model'] ?? 'anthropic/claude-sonnet-4';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'HTTP-Referer' => config('app.url'),
            'X-Title' => config('app.name'),
            'Content-Type' => 'application/json',
        ])->timeout(120)->post($this->baseUrl . '/chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $this->getSystemPrompt()],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $context['temperature'] ?? 0.7,
            'max_tokens' => $context['max_tokens'] ?? 2000,
        ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new \Exception("OpenRouter API error: {$error}");
        }

        $data = $response->json();
        $this->tokensUsed = $data['usage']['total_tokens'] ?? 0;

        return [
            'content' => $data['choices'][0]['message']['content'] ?? '',
            'tokens' => $this->tokensUsed,
            'model' => $data['model'] ?? $model,
        ];
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    public function getTokensUsed(): int
    {
        return $this->tokensUsed;
    }

    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    protected function getSystemPrompt(): string
    {
        return <<<PROMPT
Eres un experto genealogista e historiador con amplio conocimiento en investigacion familiar. Tu tarea es analizar informacion de busquedas genealogicas y proporcionar:

1. **Resumen de hallazgos**: Los datos mas relevantes encontrados, organizados de manera clara
2. **Conexiones potenciales**: Relaciones entre la informacion de diferentes fuentes
3. **Sugerencias de investigacion**: Proximos pasos especificos para continuar la investigacion
4. **Advertencias**: Informacion que podria ser incorrecta o necesita verificacion adicional

Responde siempre en espanol. Se preciso y cita las fuentes cuando sea posible. Organiza tu respuesta de manera clara y facil de leer.
PROMPT;
    }
}
