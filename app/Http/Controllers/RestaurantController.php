<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class RestaurantController extends Controller
{
    public function showAssignStaffForm($restaurantId)
    {
        return view('restaurants.assign-staff', [
            'restaurantId' => $restaurantId
        ]);
    }
    protected function makeTempPassword(int $length = 10): string
{
    // Define cada conjunto de caracteres
    $upper   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lower   = 'abcdefghijklmnopqrstuvwxyz';
    $digits  = '0123456789';
    $symbols = '!@#$%^&*()-_=+[]{}<>?';

    // Asegura al menos uno de cada uno
    $pw = [];
    $pw[] = $upper[random_int(0, strlen($upper) - 1)];
    $pw[] = $lower[random_int(0, strlen($lower) - 1)];
    $pw[] = $digits[random_int(0, strlen($digits) - 1)];
    $pw[] = $symbols[random_int(0, strlen($symbols) - 1)];

    // Rellena el resto al azar con la unión de todos
    $all = $upper . $lower . $digits . $symbols;
    for ($i = 4; $i < $length; $i++) {
        $pw[] = $all[random_int(0, strlen($all) - 1)];
    }

    // Mezcla el array y lo convierte en string
    shuffle($pw);
    return implode('', $pw);
}
   // 2) Procesar el POST
     public function assignStaff(Request $request, $restaurantId)
    {
        // Validamos solo el email (puedes extenderlo si quieres pedir username o password)
        $data = $request->validate([
            'email' => 'required|email',
        ]);

           $restId = session('restaurante_id');
    if (! $restId) {
        return back()->withErrors([
            'error' => 'No hay restaurante seleccionado. Por favor, selecciona uno antes.'
        ]);
    }

        $tempPwd = $this->makeTempPassword(12); // Generamos una contraseña temporal
        // Preparamos exactamente el mismo payload que funciona en Postman:
        $payload = [
            'email'          => $data['email'],        // coincide con Postman
            'userName'       => $data['email'],        // o cualquier otro username que quieras
            'password'       => $tempPwd,  // contraseña temporal o fija
            'passwordConfirm'=> $tempPwd,
            'role'           => 'Staff',
            'RestaurantId'    => $restId,
        ];

        // La URL base debe apuntar a tu API .NET en localhost:8000
        $apiUrl = config('services.identity.url') . '/auth/register';

        // Llamada con Bearer tal cual en Postman
        $response = Http::withToken(Session::get('jwt'))
                        ->post($apiUrl, $payload);

        if ($response->failed()) {
            $err = $response->json('errors')  
                 ?? $response->body();
            return back()
                ->withErrors(['error' => $err])
                ->withInput();
        }

        return back()
            ->with('success',
                   'Invitación enviada. El staff recibirá un email para completar su perfil.');
    }
public function listStaff($restaurantId)
{
    $response = Http::withToken(session('jwt'))
        ->get(config('services.identity.url') . "/restaurantes/{$restaurantId}/staff/list");

    if (!$response->ok()) {
        return back()->withErrors(['error' => 'No se pudo listar el staff.']);
    }
    
    $staff = $response->json(); // Se espera un array de StaffDto
    
    return view('restaurants.list-staff', compact('staff', 'restaurantId'));
}

public function destroyStaff($restaurantId, $staffId)
{
    $response = Http::withToken(session('jwt'))
        ->delete(config('services.identity.url') . "/restaurantes/{$restaurantId}/staff/{$staffId}");

    if (!$response->ok()) {
        return back()->withErrors(['error' => 'No se pudo eliminar el staff.']);
    }

    return back()->with('success', 'Staff eliminado correctamente.');
}
}