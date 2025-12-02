<?php

use App\Exceptions\HoldExpiredException;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use App\Exceptions\InvalidQuantityException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
              
        $exceptions->render(function(Throwable $th , Request $request){
                     
               if($th  instanceof InvalidQuantityException){
                   return response()->error($th->getMessage());
               }

               if($th instanceof HoldExpiredException){
                   return response()->error($th->getMessage());
               }

               if($th instanceof NotFoundHttpException){
                   return response()->error('not found' , 404);
               }
        });
         
    })->create();
