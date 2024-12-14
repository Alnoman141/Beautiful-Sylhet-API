<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/fa_v1.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
        //TODO @fa 2024-03-28 authentication exception
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status_code' => 401,
                    'error' => 'Unauthorized',
                    'message' => 'Please Login'
                ], 401);
            }
            abort(401, 'Please Login');
        });
        //TODO @fa 2024-03-28 model not found exception
        $exceptions->render(function (ModelNotFoundException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status_code' => 403,
                    'error' => 'ModelNotFoundException',
                    'message' => 'Model Data Not Found'
                ], 403);
            }
            abort(403, 'Data Not Found !');
        });
        //TODO @fa 2024-03-28 relation not found exception
        $exceptions->render(function (RelationNotFoundException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status_code' => 403,
                    'error' => 'RelationNotFoundException',
                    'message' => 'Relation Not Found'
                ], 403);
            }
            abort(403, $exception->getMessage());
        });
        //TODO @fa 2024-03-28 method not allowed exception
        $exceptions->render(function (MethodNotAllowedHttpException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status_code' => 403,
                    'error' => 'MethodNotAllowedHttpException',
                    'message' => 'Method Not Allowed'
                ], 403);
            }
            abort(403, 'Method Not Allowed !');
        });
        //TODO @fa 2024-03-28 route not found exception
        $exceptions->render(function (RouteNotFoundException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status_code' => 403,
                    'error' => 'RouteNotFoundException',
                    'message' => 'Route Not Found'
                ], 403);
            }
            abort(403, 'Route Not Found !');
        });
        //TODO @fa 2024-03-28 not found exception
        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status_code' => 403,
                    'error' => 'NotFoundHttpException',
                    'message' => 'Not Found'
                ], 403);
            }
            abort(403, 'Not Found !');
        });
        //TODO @fa 2024-03-28 query exception
        $exceptions->render(function (QueryException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status_code' => 403,
                    'error' => 'QueryException',
                    'message' => 'Query Exception'
                ], 403);
            }
            abort(403, $exception->getMessage());
        });
        //TODO @fa 2024-03-28 BadMethodCallException
        $exceptions->render(function (BadMethodCallException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status_code' => 403,
                    'error' => 'BadMethodCallException',
                    'message' => 'Bad Method Call'
                ], 403);
            }
            abort(403, 'Bad Method Call !');
        });
        //TODO @fa 2024-03-28 HttpException
        $exceptions->render(function (HttpException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status_code' => 403,
                    'error' => 'HttpException',
                    'message' => 'Http Exception'
                ], 403);
            }
            abort(403, 'Http Exception !');
        });
        //TODO @fa 2024-03-28 invalid argument exception
        $exceptions->render(function (InvalidArgumentException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status_code' => 403,
                    'error' => 'InvalidArgumentException',
                    'message' => 'Invalid Argument'
                ], 403);
            }
            abort(403, 'Invalid Argument !');
        });
        //TODO @fa 2024-03-28 fatal error exception
        $exceptions->render(function (FatalError $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status_code' => 500,
                    'error' => 'FatalErrorException',
                    'message' => 'Fatal Error'
                ], 403);
            }
            abort(403, 'Fatal Error !');
        });
        $exceptions->render(function (BindingResolutionException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status_code' => 500,
                    'error' => 'BindingResolutionException',
                    'message' => 'Binding Resolution Error'
                ], 403);
            }
            abort(403, 'Binding Resolution Error !');
        });
    })->create();
