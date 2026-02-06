<?php

namespace Plugin\ResearchAssistant\Services\Providers;

use Illuminate\Support\Facades\Http;

class DeepseekProvider implements AIProviderInterface
{
    protected string $apiKey = '';
    protected string $baseUrl = 'https://api.deepseek.com/v1';
    protected int $tokensUsed = 0;

    public const CUSTOM_MODEL = '_custom_';

    public function __construct(?string $apiKey = null)
    {
        if ($apiKey) {
            $this->apiKey = $apiKey;
        }
    }

    public function getName(): string
    {
        return 'Deepseek';
    }

    public function getModels(): array
    {
        // Modelos actuales de Deepseek (Feb 2025)
        return [
            'deepseek-chat' => 'DeepSeek V3',
            'deepseek-reasoner' => 'DeepSeek R1 (Reasoning)',
            self::CUSTOM_MODEL => '-- ' . __('Modelo personalizado') . ' --',
        ];
    }

    public function supportsCustomModel(): bool
    {
        return true;
    }

    public function analyze(string $prompt, array $context = []): array
    {
        $model = $context['model'] ?? 'deepseek-chat';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
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
            throw new \Exception("Deepseek API error: {$error}");
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
