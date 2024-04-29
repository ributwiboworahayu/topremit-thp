<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e): Response|JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        if ($e instanceof HttpExceptionInterface) {
            return match ($e->getStatusCode()) {
                400 => response()->json([
                    'status' => "error",
                    'message' => 'Bad Request'
                ], 400),
                401 => response()->json([
                    'status' => "error",
                    'message' => 'Unauthorized'
                ], 401),
                404 => response()->json([
                    'status' => "error",
                    'message' => 'Not Found'
                ], 404),
                405 => response()->json([
                    'status' => "error",
                    'message' => 'Method Not Allowed'
                ], 405),
                429 => response()->json([
                    'status' => "error",
                    'message' => 'Too Many Requests'
                ], 429),
                503 => response()->json([
                    'status' => "error",
                    'message' => 'Under Maintenance'
                ], 503),
                default => parent::render($request, $e),
            };
        }

        if (!$request->has('grant_type')) {

            return match (config('app.debug')) {
                true => response()->json([
                    'status' => "error",
                    'message' => [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'message' => $e->getMessage()
                    ]
                ], 500),
                default => response()->json([
                    'status' => "error",
                    'message' => 'Internal Server Error'
                ], 500),
            };
        }

        return parent::render($request, $e);
    }
}
