<?php

namespace Plugin\ResearchAssistant\Services\Providers;

use Illuminate\Support\Facades\Http;

class AnthropicProvider implements AIProviderInterface
{
    protected string $apiKey = '';
    protected string $baseUrl = 'https://api.anthropic.com/v1';
    protected int $tokensUsed = 0;

    public function __construct(?string $apiKey = null)
    {
        if ($apiKey) {
            $this->apiKey = $apiKey;
        }
    }

    public function getName(): string
    {
        return 'Anthropic';
    }

    public function getModels(): array
    {
        return [
            'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet',
            'claude-3-opus-20240229' => 'Claude 3 Opus',
            'claude-3-haiku-20240307' => 'Claude 3 Haiku',
        ];
    }

    public function analyze(string $prompt, array $context = []): array
    {
        $model = $context['model'] ?? 'claude-3-5-sonnet-20241022';

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'Content-Type' => 'application/json',
        ])->timeout(120)->post($this->baseUrl . '/messages', [
            'model' => $model,
            'max_tokens' => $context['max_tokens'] ?? 2000,
            'system' => $this->getSystemPrompt(),
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new \Exception("Anthropic API error: {$error}");
        }

        $data = $response->json();

        // Anthropic uses input_tokens + output_tokens
        $this->tokensUsed = ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0);

        // Anthropic returns content as an array of blocks
        $content = '';
        foreach ($data['content'] ?? [] as $block) {
            if ($block['type'] === 'text') {
                $content .= $block['text'];
            }
        }

        return [
            'content' => $content,
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
