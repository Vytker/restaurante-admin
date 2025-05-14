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
        Session::put([
            'jwt'  => $token,
            'unique_name' => $name,
            'restaurante_id' => $restaurante_id,
            'role' => $rol,
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
public function register(Request $request)
{
    // 1) Validación Laravel (antes de llamar al API)
    $validator = Validator::make($request->all(), [
        'username' => 'required|min:3|max:30',
        'email'    => 'required|email',
        'password' => 'required|min:6|confirmed',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // 2) Llamada al API Identity (.NET)
    $response = Http::post(
        config('services.identity.url').'/register',
        $request->only('username', 'email', 'password')
    );

    if (! $response->created()) {      // 201 esperado
        // Convierte errores del API a mensaje UX; ajusta según tu payload
        $msg = $response->json('message') ?? 'Registro no disponible';
        return back()->withErrors(['auth' => $msg])->withInput();
    }

    // 3) Auto-login: tu API suele devolver también el JWT
    $data  = $response->json();
    $token = $data['token'];
    $name  = $data['user']['name'];

    $request->session()->put(['jwt' => $token, 'name' => $name]);

    return redirect()->route('/')->with('success', 'Registro exitoso. Bienvenido, '.$name);
}

}