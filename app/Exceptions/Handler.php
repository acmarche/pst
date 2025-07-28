<?php

namespace App\Exceptions;

use App;
use App\Mail\ExceptionMail;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Mail;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            $this->sendErrorMail($e);
        });
    }

    protected function sendErrorMail(Throwable $exception): void
    {
        if (App::isLocal()) {
            return;
        }

        $email = config('MAIL_IT_ADDRESS', null);

        if ($email) {
            $body = "An error occurred: \n" . $exception->getMessage() . "\n" . $exception->getTraceAsString();
            try {
                Mail::to($email)->send(new ExceptionMail($body));
            } catch (\Throwable $th) {
                //
            }
        }
    }
}
