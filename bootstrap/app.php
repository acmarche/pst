<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
        $email = config('MAIL_IT_ADDRESS', null);
        if (! $email) {
            return;
        }
        $exceptions->report(function (Throwable $e) use ($email): void {
            if (app()->environment('production')) {
                $url = request()->fullUrl();
                $method = request()->method();
                $ip = request()->ip();
                $userAgent = request()->userAgent();
                $user = auth()->user();
                $file = $e->getFile();
                $line = $e->getLine();
                $class = get_class($e);
                try {
                    Mail::raw(
                        "Exception: {$e->getMessage()}\n\n".
                        "class: {$class}\n\n".
                        "file: {$file}\n\n".
                        "line: {$line}\n\n".
                        "URL: {$method} {$url}\n".
                        "IP: {$ip}\n".
                        "User Agent: {$userAgent}\n".
                        'User: '.($user ? $user->email : 'Guest')."\n\n".
                        "Stack Trace:\n{$e->getTraceAsString()}",
                        function ($message) use ($e, $email) {
                            $message->to($email)
                                ->subject('Pst [500 Error] '.class_basename($e).': '.Str::limit($e->getMessage(), 50));
                        }
                    );
                } catch (Throwable $th) {
                    Log::error('Failed to send exception email', [
                        'error' => $th->getMessage(),
                        'original_exception' => $th->getMessage(),
                    ]);
                }
            }
        });
        $exceptions->report(function (QueryException $e) use ($email): void {
            $rawSql = $e->getRawSql();
            $user = auth()->user();

            try {
                Mail::raw(
                    "Exception: {$e->getMessage()}\n\n".
                    'sql: '.$rawSql."\n\n".
                    'connection:'.$e->getConnectionName()."\n\n".
                    'code: '.$e->getCode()."\n\n".
                    'User: '.($user ? $user->email : 'Guest')."\n\n".
                    "Stack Trace:\n{$e->getTraceAsString()}",
                    function ($message) use ($e, $email) {
                        $message->to($email)
                            ->subject('Pst [Sql Error] '.class_basename($e).': '.Str::limit($e->getMessage(), 50));
                    }
                );
            } catch (Throwable $th) {
                Log::error('Failed to send exception email', [
                    'error' => $th->getMessage(),
                    'original_exception' => $th->getMessage(),
                ]);
            }
        });
    })->create();
