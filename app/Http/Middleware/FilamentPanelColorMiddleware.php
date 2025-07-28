<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Repository\FilamentColorRepository;
use Closure;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class FilamentPanelColorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $colors = FilamentColorRepository::userColor();
        FilamentColor::register($colors);

        return $next($request);
    }
}
