<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    /**
     * Umbral minimo de score para considerar valido (0.0 a 1.0)
     * 0.0 = muy probable bot, 1.0 = muy probable humano
     */
    private const MIN_SCORE = 0.5;

    /**
     * Verify reCAPTCHA v3 response token.
     *
     * @param string|null $token Token recibido del frontend
     * @param string $expectedAction Accion esperada (login, register, etc.)
     * @return bool
     */
    public static function verify(?string $token, string $expectedAction = 'submit'): bool
    {
        // Si reCAPTCHA está deshabilitado, siempre retornar true
        if (!config('mi-familia.recaptcha.enabled')) {
            return true;
        }

        // Si no hay token, fallar
        if (empty($token)) {
            Log::info('reCAPTCHA: token vacio', ['ip' => request()->ip()]);
            return false;
        }

        $secretKey = config('mi-familia.recaptcha.secret_key');

        if (empty($secretKey)) {
            Log::warning('reCAPTCHA secret key not configured');
            return false;
        }

        try {
            $response = Http::timeout(10)->asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $token,
                'remoteip' => request()->ip(),
            ]);

            $result = $response->json();

            // Verificar respuesta exitosa
            if (!isset($result['success']) || !$result['success']) {
                Log::info('reCAPTCHA verification failed', [
                    'error-codes' => $result['error-codes'] ?? [],
                    'ip' => request()->ip(),
                ]);
                return false;
            }

            // Para v3: verificar score
            $score = $result['score'] ?? 0;
            if ($score < self::MIN_SCORE) {
                Log::info('reCAPTCHA score too low', [
                    'score' => $score,
                    'min_required' => self::MIN_SCORE,
                    'ip' => request()->ip(),
                ]);
                return false;
            }

            // Verificar que la accion coincida (opcional pero recomendado)
            $action = $result['action'] ?? '';
            if (!empty($expectedAction) && $action !== $expectedAction) {
                Log::info('reCAPTCHA action mismatch', [
                    'expected' => $expectedAction,
                    'received' => $action,
                    'ip' => request()->ip(),
                ]);
                // No fallar por esto, solo logear (algunas implementaciones no envian action)
            }

            Log::debug('reCAPTCHA passed', [
                'score' => $score,
                'action' => $action,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification error: ' . $e->getMessage());
            // En caso de error de conexion, permitir el acceso para no bloquear usuarios
            return true;
        }
    }

    /**
     * Check if reCAPTCHA is enabled.
     */
    public static function isEnabled(): bool
    {
        return config('mi-familia.recaptcha.enabled', false);
    }

    /**
     * Get the site key for frontend.
     */
    public static function getSiteKey(): ?string
    {
        return config('mi-familia.recaptcha.site_key');
    }
}
