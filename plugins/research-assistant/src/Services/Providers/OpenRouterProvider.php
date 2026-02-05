<?php

namespace Plugin\ResearchAssistant\Services\Providers;

use Illuminate\Support\Facades\Http;

class OpenRouterProvider implements AIProviderInterface
{
    protected string $apiKey = '';
    protected string $baseUrl = 'https://openrouter.ai/api/v1';
    protected int $tokensUsed = 0;

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
        return [
            'anthropic/claude-3.5-sonnet' => 'Claude 3.5 Sonnet',
            'anthropic/claude-3-haiku' => 'Claude 3 Haiku',
            'openai/gpt-4-turbo' => 'GPT-4 Turbo',
            'openai/gpt-3.5-turbo' => 'GPT-3.5 Turbo',
            'google/gemini-pro' => 'Gemini Pro',
            'meta-llama/llama-3-70b-instruct' => 'Llama 3 70B',
            'mistralai/mixtral-8x7b-instruct' => 'Mixtral 8x7B',
        ];
    }

    public function analyze(string $prompt, array $context = []): array
    {
        $model = $context['model'] ?? 'anthropic/claude-3.5-sonnet';

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
