<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RestaurantController extends Controller
{
    public function showAssignStaffForm($restaurantId)
    {
        return view('restaurants.assign-staff', [
            'restaurantId' => $restaurantId
        ]);
    }
    public function assignStaff(Request $request, $restaurantId)
{
    $data = $request->validate([
        'Email' => 'required|email',
    ]);

    $payload = [
        'Email' => $data['Email']
    ];

    $response = Http::withToken(session('jwt'))
        ->post(config('services.identity.url') . "/restaurantes/{$restaurantId}/staff", $payload);

    if ($response->failed()) {
        return back()->withErrors(['error' => 'No se pudo asignar el staff al restaurante.']);
    }

    return back()->with('success', 'Usuario asignado exitosamente.');
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
}