<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Handle HTTP exceptions (403, 404, etc.) with toast redirects
        $this->renderable(function (HttpException $e, Request $request) {
            // Only handle web requests, not API
            if ($request->expectsJson()) {
                return null;
            }

            $statusCode = $e->getStatusCode();
            $message = $e->getMessage();

            // Handle specific status codes
            switch ($statusCode) {
                case 403:
                    $message = $message ?: __('No tienes permiso para realizar esta acción.');
                    return redirect()->back()
                        ->with('error', $message)
                        ->withInput();

                case 404:
                    $message = $message ?: __('El recurso solicitado no fue encontrado.');
                    return redirect()->back()
                        ->with('error', $message);

                case 500:
                    $message = __('Ocurrió un error interno. Por favor intenta de nuevo.');
                    return redirect()->back()
                        ->with('error', $message);

                default:
                    // Let other exceptions be handled normally
                    return null;
            }
        });
    }
}
