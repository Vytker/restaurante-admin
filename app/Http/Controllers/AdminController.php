<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AdminController extends Controller
{
    public function restaurants(Request $request)
    {
        // Verificar que el rol del usuario sea Superadmin.
        if (Session::get('role') !== 'SuperAdmin') {
            abort(403, 'No tiene permisos para acceder a esta zona.');
        }
        
        // Llamar al endpoint del servicio identity para obtener todos los restaurantes.
        // Se asume que dicho endpoint es /restaurantes y que requiere enviar el token JWT en la cabecera.
        $response = Http::withToken(Session::get('jwt'))
            ->get(config('services.identity.url').'/restaurantes');
        
        if (!$response->ok()) {
            return back()->withErrors(['error' => 'No se pudieron obtener los restaurantes.']);
        }
        
        $restaurants = $response->json();
        return view('admin.restaurants', compact('restaurants'));
    }

    public function store(Request $request)
    {
        // Validar los datos del restaurante y anidar los datos del owner
        $data = $request->validate([
            'Nombre' => 'required|string|max:255',
            'Slug'   => 'required|string',
            'Owner.UserName'   => 'required|string|max:255',
            'Owner.FirstName'  => 'required|string|max:255',
            'Owner.LastName'   => 'required|string|max:255',
            'Owner.Email'      => 'required|email',
            'Owner.Password'   => 'required|string|min:6|confirmed',
        ]);
        
        // Preparar el payload para el API (la estructura debe coincidir con RestauranteCreateDto):
        $payload = [
            'Nombre' => $data['Nombre'],
            'Slug'   => $data['Slug'],
            'Owner'  => [
                'UserName'  => $data['Owner']['UserName'],
                'FirstName' => $data['Owner']['FirstName'],
                'LastName'  => $data['Owner']['LastName'],
                'Email'     => $data['Owner']['Email'],
                'Password'  => $data['Owner']['Password']  // se envía la contraseña, asumiendo que el API la procesa
            ]
        ];
        
        // Llamar al endpoint del servicio Identity para crear el restaurante (y el owner asociado)
        $restaurantResponse = Http::withToken(session('jwt'))
            ->post(config('services.identity.url').'/restaurantes', $payload);
        
            if ($restaurantResponse->status() !== 201 || !isset($restaurantResponse->json()['id'])) {
                return back()->withErrors(['error' => 'No se pudo crear el restaurante y el owner.']);
            }
        
        $restaurant = $restaurantResponse->json();  // Se espera que se retorne el restaurante creado
        
        return redirect()->route('admin.restaurants')
            ->with('success', 'Restaurante y owner creados correctamente.');
    }
    public function assignStaff(Request $request, $restaurantId)
{
    // Validar datos, por ejemplo: UserId del usuario a asignar
    $data = $request->validate([
        'UserId' => 'required|uuid',
    ]);
    
    // Preparar el payload que espera el endpoint del API (por ejemplo, AddStaffDto)
    // En este caso, el dto puede incluir solo el UserId, pues el restaurantId se pasa en la URL.
    $payload = [
        'UserId' => $data['UserId']
    ];
    
    // Hacer la llamada al endpoint para asignar el usuario al restaurante
    $response = Http::withToken(session('jwt'))
        ->post(config('services.identity.url')."/restaurantes/{$restaurantId}/staff", $payload);
    
    if ($response->failed()) {
        return back()->withErrors(['error' => 'No se pudo asignar el staff al restaurante.']);
    }
    
    return back()->with('success', 'Usuario asignado exitosamente.');
}

public function selectRestaurant(Request $request)
{
    $restaurantId = $request->input('restaurant_id');
    if (!$restaurantId) {
        return back()->withErrors(['error' => 'No se recibió el ID del restaurante.']);
    }
    // Guardar el ID en la sesión para usarlo en otras vistas (reservas, staff, etc.)
    session()->put('restaurante_id', $restaurantId);

    // Redirigir a una vista de dashboard o detalles del restaurante
    return redirect()->route('admin.restaurants')->with('success', 'Restaurante seleccionado correctamente.');
}

public function deselectRestaurant(Request $request)
{
    // Eliminar el restaurante seleccionado de la sesión
    session()->forget('restaurante_id');
    
    return redirect()->route('admin.restaurants')
                     ->with('success', 'Restaurante deseleccionado correctamente.');
}
}