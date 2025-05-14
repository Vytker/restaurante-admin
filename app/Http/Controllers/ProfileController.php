<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ProfileController extends Controller
{
    /* ========== 1. Mostrar perfil ========== */
    public function edit()
    {
        $jwt = Session::get('jwt');

        $resp = Http::withToken($jwt)
            ->get(config('services.identity.url') . '/auth/me');

        if (! $resp->ok()) {
            return back()->withErrors(['auth' => 'No se pudo cargar el perfil']);
        }

        $profile = $resp->json();               // { id, email, firstName, … }
        return view('profile.edit', compact('profile'));
    }

    /* ========== 2. Actualizar perfil ========== */
    public function update(Request $req)
    {
        $data = $req->validate([
            'firstName' => 'nullable|string|max:50',
            'lastName'  => 'nullable|string|max:50',
            'telefono'  => 'nullable|string|max:20',
        ]);

        $resp = Http::withToken(Session::get('jwt'))
            ->put(config('services.identity.url') . '/auth/me', $data);

        return $resp->successful()
            ? back()->with('success', 'Perfil actualizado')
            : back()->withErrors(['auth' => 'No se pudo actualizar'])->withInput();
    }

    /* ========== 3. Formulario cambio contraseña ========== */
    public function editPassword()
    {
        return view('profile.password');
    }

    /* ========== 4. Cambiar contraseña ========== */
    public function updatePassword(Request $req)
    {
        $req->validate([
            'passwordActual'         => 'required|string',
            'passwordNueva'          => 'required|string|min:6|same:passwordNuevaConfirm',
            'passwordNuevaConfirm'   => 'required|string|min:6',
        ]);

        $payload = [
            'passwordActual'       => $req->passwordActual,
            'passwordNueva'        => $req->passwordNueva,
            'passwordNuevaConfirm' => $req->passwordNuevaConfirm,
        ];

        $resp = Http::withToken(Session::get('jwt'))
            ->put(config('services.identity.url') . '/auth/change-password', $payload);

        return $resp->successful()
            ? back()->with('success', 'Contraseña cambiada')
            : back()->withErrors(['auth' => 'Contraseña actual incorrecta']);
    }
}
