<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRestaurantSelected
{

    public function handle(Request $request, Closure $next)
    {
        // Verificar si existe y no está vacío en sesión
        $restaurantId = $request->session()->get('restaurante_id');
        if (empty($restaurantId)) {
            return redirect()
                ->route('dashboard.index')
                ->with('error', 'Debes seleccionar un restaurante primero.');
        }

        // Si existe, dejamos pasar la petición normalmente.
        return $next($request);
    }
}
