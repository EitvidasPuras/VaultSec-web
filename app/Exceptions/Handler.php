<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param \Throwable $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json(['error' => $request->method() . " method is not supported for this route"], 405);
        }

        if ($exception instanceof ModelNotFoundException) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()->json(['error' => 'Page not found'], 404);
        }

        if ($exception instanceof ThrottleRequestsException) {
            return response()->json(['error' => 'Too many attempts. Try again later'], 409);
        }

        return parent::render($request, $exception);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json(['error' => $exception->getMessage()], 401);
//        return $request->expectsJson()
//            ? response()->json(['error' => $exception->getMessage()], 401)
//            : response()->json(['error' => $exception->getMessage()], 401);
//            : redirect()->guest($exception->redirectTo() ?? route('login'));
//        return parent::unauthenticated($request, $exception);
    }
}
