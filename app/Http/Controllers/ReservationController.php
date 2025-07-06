<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class ReservationController extends Controller
{

public function listReservations(Request $request)
{
    $restaurantId = Session::get('restaurante_id');
    $jwt          = Session::get('jwt');

    // 1) Leer página y tamaño desde la query
    $page     = max(1, (int) $request->input('page', 1));
    $pageSize = max(1, (int) $request->input('pageSize', 10));
    $skip     = ($page - 1) * $pageSize;

    // 2) Construir la URL OData con $top y $skip
    $query = http_build_query([
      'restauranteId' => $restaurantId,
      '$top'          => $pageSize,
      '$skip'         => $skip,
      // …añade aquí filtros adicionales si los tienes…
    ]);
    $url = config('services.identity.url') . "/odata/Reservas?{$query}";

    // 3) Petición y parseo
    $resp = Http::withToken($jwt)->get($url);
    if (! $resp->ok()) {
        return back()->withErrors(['error'=>'No se pudieron obtener las reservas']);
    }
    $data     = $resp->json();
    $reservas = $data['value'] ?? $data;  // si viene wrapper OData, si no igual es array

    // 4) Indicar a la vista la página actual y el tamaño
    return view('reservations.list', [
        'reservas' => $reservas,
        'page'     => $page,
        'pageSize' => $pageSize,
    ]);
}
            // Mostrar el formulario de creación de reserva
    // Al pasar la fecha (opcional vía query string) se consultan los turnos disponibles
    public function createReservation(Request $request)
    {
        $fecha = $request->query('fecha') ?? date('Y-m-d');  // Obtiene la fecha actual si no se envía una, es decir hoy
        // Se asume que en la sesión se guardó 'restaurante_id' y 'jwt'
        $restaurantId = Session::get('restaurante_id');

        // Consulta a la API de slots (endpoint: GET /api/reservas/slots?restauranteId=...&fecha=...)
        $response = Http::withToken(Session::get('jwt'))
            ->get(config('services.identity.url') . "/reservas/slots", [
                'restauranteId' => $restaurantId,
                'fecha' => $fecha,

            ]);
        $slots = [];
        if ($response->ok()) {
            $slots = $response->json();
        }
        
        return view('reservations.create', compact('slots', 'fecha'));
    }
    
        // Procesar el envío para crear la reserva
    public function storeReservation(Request $request)
    {
        $data = $request->validate([
            'nombreCliente'      => 'required|string|max:255',
            'email'              => 'required|email',
            'fechaReserva'       => 'required|date',
            'numeroComensales'   => 'required|integer|min:1',
            'notas'              => 'nullable|string',
            'turnoId'            => 'required|string', // Asegúrate de que el tipo coincida con el de la API
        ]);
    
        $restaurantId = Session::get('restaurante_id');
    
        $response = Http::withToken(Session::get('jwt'))
            ->post(config('services.identity.url') . "/reservas?restauranteId=" . $restaurantId, $data);
    
        if (!$response->successful()) {
            return back()->withErrors(['error' => 'No se pudo crear la reserva.'])->withInput();
        }
         // Se espera que la API retorne un código de reserva en el cuerpo de respuesta
        $result = $response->json();
        return redirect()->route('reservations.list')
            ->with('success', 'Reserva creada con código: ' . ($result['code'] ?? ''));
    }

     // Método para actualizar el estado de una reserva (ya existente)
     public function updateStatus(Request $request, $id)
     {
         $data = $request->validate([
             'estado' => 'required|string',
         ]);

         $url = config('services.identity.url') . "/reservas/{$id}/{$data['estado']}";
 
         $response = Http::withToken(Session::get('jwt'))->put($url);
 
         if (!$response->successful()) {
             return back()->withErrors(['error' => 'No se pudo actualizar el estado de la reserva.']);
         }
 
         return redirect()->back()->with('success', 'Estado actualizado correctamente.');
     }
     public function filter(Request $request)
{
    // Si no se envían filtros, por defecto estado 'Pendiente'
    $estado = $request->input('estado', 'Pendiente');
    // Usamos null en lugar de cadena vacía para fecha
    $fechaDesde = $request->input('fechaDesde') ?: null;
    $fechaHasta = $request->input('fechaHasta') ?: null;
    $nombre = $request->input('nombre') ?: null;

    // Armar la condición de filtro (solo se añaden los que tengan valor)
    $filters = [];
    if ($estado) {
        $filters[] = "Estado eq '$estado'";
    }
    $today    = Carbon::today()->setTime(0,0,0)->toIso8601String();
    $tomorrow = Carbon::tomorrow()->setTime(0,0,0)->toIso8601String();
    if ($fechaDesde && $fechaDesde >= $today) {
        $filters[] = "FechaReserva ge $fechaDesde";
    } elseif ($fechaDesde) {
        $filters[] = "FechaReserva ge $today";
    }
       $today    = Carbon::today()->setTime(0,0,0)->toIso8601String();
    $tomorrow = Carbon::tomorrow()->setTime(0,0,0)->toIso8601String();
    if ($fechaHasta && $fechaHasta <= $tomorrow) {
        $filters[] = "FechaReserva le $fechaHasta";
    } elseif ($fechaHasta) {
        $filters[] = "FechaReserva le $tomorrow";
    }
    
    if ($nombre) {
        $filters[] = "contains(NombreCliente, '$nombre')";
    }

    // Si se armó algún filtro, los concatenamos con " and "
    $filterString = count($filters) > 0 ? implode(' and ', $filters) : "";

    // Parámetros para OData, utilizando la notación estándar:
    $pageSize = $request->input('pageSize', '10');
    $page = $request->input('page', '1');
    $orderBy = $request->input('orderBy', 'FechaReserva');

    $params = [];
    if ($filterString) {
        $params['$filter'] = $filterString; // ej: Estado eq 'Pendiente'
    }
    $params['$top'] = $pageSize;
    $params['page'] = $page;           // Si tu API no espera "page", puedes omitirlo.
    $params['$orderby'] = $orderBy;

    // Construir la URL usando http_build_query para codificar los parámetros correctamente
    $url = config('services.identity.url') . "/odata/Reservas?" . http_build_query($params);
    
    // Para depurar, podrías dd($url);
    // dd($url);

    $response = Http::withToken(Session::get('jwt'))->get($url);

    if($response->successful()){
        $reservas = $response->json();
        return view('reservations.list', compact('reservas'));
    } else {
        return back()->withErrors(['error' => 'Error al obtener reservas filtradas.']);
    }
}
 public function listTurnos(Request $request)
    {
        $restaurantId = Session::get('restaurante_id');

        $response = Http::withToken(Session::get('jwt'))
            ->get(config('services.identity.url') . "/turnos", [
                'restauranteId' => $restaurantId   // opcional: el API usa el claim
            ]);

        $turnos = $response->json();

        return view('turnos.list', compact('turnos'));
    }

    /**
     * GET /turnos/crear      (sin $id)
     * GET /turnos/{id}/editar (con $id)
     * Muestra el formulario para alta o edición
     */
    public function editTurno(Request $request, $id = null)
    {
        $turno = null;

        if ($id) {
            $restaurantId = Session::get('restaurante_id');

            $resp = Http::withToken(Session::get('jwt'))
                ->get(config('services.identity.url') . "/turnos/{$id}", [
                    'restauranteId' => $restaurantId   // el backend lo pide por query
                ]);

            if ($resp->ok()) {
                $turno = $resp->json();
            } else {
                return back()->withErrors(['error' => 'Turno no encontrado.']);
            }
        }

        return view('turnos.form', compact('turno'));
    }

    public function saveTurno(Request $request, $id = null)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:100',
            'horaInicio'  => 'required|date_format:H:i',
            'horaFin'     => 'required|date_format:H:i|after:horaInicio',
            'capacidad'       => 'required|integer|min:1',
        ]);

        // nota: el API infiere el restaurante desde el JWT
        $jwt = Session::get('jwt');
        $role = Session::get('role');
        $restaurantId = Session::get('restaurante_id');
        
        $baseUrl = config('services.identity.url') . "/turnos";

        if ($id) {
            // --- UPDATE ---
            $url = config('services.identity.url') . "/turnos/{$id}";
            if($role === "SuperAdmin"){
                $url .= '?'.http_build_query(['restauranteId'=>$restaurantId]);
            }
            $apiResp = Http::withToken($jwt)->put($url, $data);
            $failMsg    = 'No se pudo actualizar el turno.';
            $successMsg = 'Turno actualizado correctamente.';
        } else {
            // --- CREATE ---
            if($role === "SuperAdmin"){
                $data['restauranteId'] = $restaurantId; // el API lo espera en el body
            }
            $apiResp = Http::withToken($jwt)->post($baseUrl, $data);
            $failMsg    = 'No se pudo crear el turno.';
            $successMsg = 'Turno creado correctamente.';
        }

        if (! $apiResp->successful()) {
              // Intenta extraer un mensaje JSON { error : "..."}
            $apiMsg = $apiResp->json('error')
              ?? $apiResp->json('mensaje')
              ?? $apiResp->body();        // texto plano

            return back()->withErrors(['error' => $apiMsg])->withInput();
        }

        return redirect()->route('turnos.list')->with('success', $successMsg);
    }
    /**
     * DELETE /turnos/{id}
     */
    public function deleteTurno(Request $request, $id)
{
    // Confirmación extra del lado servidor (opcional)
    if (! $id) {
        return back()->withErrors(['error' => 'Id de turno inválido.']);
    }

    $jwt = Session::get('jwt');
    $role        = Session::get('role');
    $restaurantId= Session::get('restaurante_id');

    $baseUrl = config('services.identity.url') . '/turnos';   // /api/turnos si tu prefijo no lo trae
    $url = config('services.identity.url')."/turnos/{$id}";
 if ($role === 'SuperAdmin') {
         $url .= '?'.http_build_query(['restauranteId'=>$restaurantId]);
    }
    $response = Http::withToken($jwt)->delete($url);

    if (! $response->successful()) {
        return back()->withErrors(['error' => 'No se pudo eliminar el turno.']);
    }

    return redirect()->route('turnos.list')->with('success', 'Turno eliminado correctamente.');
}

}