<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;   
use Illuminate\Support\Facades\Session;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function authenticate(Request $request)
    {
       $validated = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
          // Convertir los campos al formato que acepta la API
    $dataToSend = [
        'UserName' => $validated['username'],
        'Password' => $validated['password'],
    ];
        $response = Http::post(
            config('services.identity.url').'/auth/login',
            $dataToSend
        ); // :contentReference[oaicite:2]{index=2}

        if (! $response->ok()) {
            return back()->withErrors(['auth' => 'Credenciales no válidas']);
        }

        $data   = $response->json();          // ← ajusta a tu payload real
        $token  = $data['token'];             // JWT


        // Decodificar el JWT para obtener el nombre
        $secretKey = env('JWT_SECRET'); // Asegúrate de que esta clave sea la misma que usas para firmar el JWT
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        $name = $decoded->unique_name; // Ajusta según la estructura de tu JWT
        $restaurante_id = $decoded->restauranteId; // Ajusta según la estructura de tu JWT
        $rol = $decoded->{'http://schemas.microsoft.com/ws/2008/06/identity/claims/role'} ; // Ajusta según la estructura de tu JWT
        $employee_id = $decoded->sub; // Ajusta según la estructura de tu JWT

    
        Session::put([
            'jwt'  => $token,
            'unique_name' => $name,
            'restaurante_id' => $restaurante_id,
            'role' => $rol,
            'employee_id' => $employee_id,
        ]);

        return redirect()->intended('/');
    }

    public function logout(Request $request)
    {
        // Aquí puedes realizar cualquier acción adicional al cerrar sesión, como invalidar el token en el servidor si es necesario.
        // Por ejemplo, si tienes un endpoint para invalidar el token, puedes llamarlo aquí.

        // Limpiar la sesión y redirigir a la página de inicio de sesión
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Session::flush();
        return redirect()->route('login');
    
}


 // Vista para que staff complete su perfil
    public function showCompleteForm(Request $request)
    {
        $token = $request->query('token');
        if (! $token) {
            abort(400, 'Token de invitación faltante.');
        }
        return view('auth.complete-profile', ['inviteToken' => $token]);
    }



  public function register(Request $request)
    {
        // Si viene invite_token → completado de perfil
        if ($request->has('invite_token')) {
            $payload = $request->validate([
                'invite_token'     => 'required|string',
                'username'              => 'required|string|min:3|max:30',
                'first_name'       => 'required|string',
                'last_name'        => 'required|string',
                'password'         => 'required|string|min:8|confirmed',
                'password_confirmation'  => 'required|string|min:8',
            ]);

            $dataToSend = [
                'InviteToken'      => $payload['invite_token'],
                'UserName'         => $payload['username'],       
                'FirstName'        => $payload['first_name'],
                'LastName'         => $payload['last_name'],
                'Password'         => $payload['password'],
                'PasswordConfirm'  => $payload['password_confirmation'],
            ];

            $response = Http::post(
                config('services.identity.url').'/auth/register',
                $dataToSend
            );
            // 4) Si falla, vuelve al formulario con los errores
            if (! $response->ok()) {
                $errors = $response->json('errors') 
                        ?? ['complete' => $response->body()];
                return back()
                    ->withErrors($errors)
                    ->withInput();
            }

             return view('auth.complete-profile', [
        'inviteToken' => $payload['invite_token'],
        'completed'   => true,                    // indicador de éxito
    ])->with('success', 'Tu perfil se ha completado correctamente. Ya puedes iniciar sesión.');

        } else {
            // Flujo de invitación (Owner/SuperAdmin)
            $payload = $request->validate([
                'email'             => 'required|email',
                'username'          => 'required|min:3|max:30',
                'password'          => 'required|min:8|confirmed',
                'role'              => 'required|in:Staff,Owner', 
            ]);

            // Prepara datos en el formato .NET
            $dataToSend = [
                'Email'              => $payload['email'],
                'UserName'           => $payload['username'],
                'Password'           => $payload['password'],
                'PasswordConfirm'    => $payload['password_confirmation'],
                'Role'               => $payload['role'],
            ];

            // Llamada con Bearer: tomamos el JWT del Owner de la sesión
            $jwt = Session::get('jwt');
            $response = Http::withToken($jwt)
                ->post(config('services.identity.url').'/auth/register', $dataToSend);

            if (! $response->ok()) {
                return back()
                    ->withErrors(['invite' => $response->json('errors') ?? $response->body()])
                    ->withInput();
            }

            // El API no devuelve el token aquí, solo un mensaje
            return back()->with('success',
                'Invitación enviada correctamente. El usuario recibirá un email.');
        }
    }

}