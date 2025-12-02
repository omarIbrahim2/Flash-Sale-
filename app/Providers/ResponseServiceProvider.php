<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class ResponseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
         Response::macro('success' , function(string $message  , int $code = 200 ,  array $data = []){
               return response()->json([
                'message' => $message,
                'success' => true,
                 'data' => $data,
               ], $code);
        });


        Response::macro('error' , function(string $message  , int $code = 400){
                return response()->json([
                'message' => $message,
                'success' => false,
               ], $code);
        });


        Response::macro("validationError" , function(array $errors , string $message , int $code = 422){
               return response()->json([
                   'message'=>$message,
                   'errors' => $errors,
               ], $code);
        });
    }
}
