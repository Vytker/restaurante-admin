<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class GestionHorariosController extends Controller
{
    protected string $baseUrl;
    protected string $jwt;
    protected \Illuminate\Http\Client\PendingRequest $apiClient;

    public function __construct()
    {
        $this->baseUrl = config('services.horarios.url');
        $this->jwt     = Session::get('jwt');

          // Creamos un cliente HTTP preconfigurado
        $restaurantId = Session::get('restaurante_id');
        $this->apiClient = Http::withToken($this->jwt)
            ->withHeaders([
                'X-Restaurante-Id' => $restaurantId,
            ]);
    }

        public function index(Request $request)
    {
        $day = $request->query('day', today()->toDateString());

        // 1) Traer slots (plantillas)
        $slotsResp = $this->apiClient->get("{$this->baseUrl}/slots");

        // 2) Traer assignments para $day
         $asgsResp = $this->apiClient->get("{$this->baseUrl}/assignments", ['date' => $day]);
  // 3) staff
    $restaurantId = Session::get('restaurante_id');
    $staffResp = Http::withToken($this->jwt)
        ->get(config('services.identity.url') . "/restaurantes/{$restaurantId}/staff/list");
     

      if (! $slotsResp->ok() || ! $asgsResp->ok() || ! $staffResp->ok()) {
        return back()->withErrors('Error cargando datos.');
    }

        $slots       = $slotsResp->json();
        $assignments = $asgsResp->json();
        $empleados   = $staffResp->json();  // aquí tu array de StaffDto
        
        return view('gestion_horarios.index', compact('day','slots','assignments', 'empleados'));
    }

    public function create()
{
    // 1) Sacamos el restaurant_id de sesión
    $restaurantId = Session::get('restaurante_id');
    $jwt          = $this->jwt;

    // 2) Llamamos al endpoint de staff
    $staffResp = Http::withToken($jwt)
        ->get(config('services.identity.url') . "/restaurantes/{$restaurantId}/staff/list");

    if (! $staffResp->ok()) {
        return back()->withErrors(['error' => 'No se pudo listar el staff.']);
    }

    $empleados = $staffResp->json();

    // 3) Pasamos `empleados` a la vista junto a `horario` (nulo para create)
    return view('gestion_horarios.form', [
        'horario'   => null,
        'empleados' => $empleados,
    ]);
}

    public function store(Request $request)
    {
        $data = $request->validate([
            'horaInicio' => 'required|date_format:H:i',
            'horaFin'    => 'required|date_format:H:i|after:horaInicio',
            'capacidad'  => 'required|integer|min:1',
        ]);

        $resp = $this->apiClient
            ->post("{$this->baseUrl}/assignments", $data);

        if (! $resp->successful()) {
            return back()->withErrors(['error' => 'No se pudo crear el horario.'])->withInput();
        }

        return redirect()->route('horarios.list')
                         ->with('success', 'Horario creado correctamente.');
    }

   public function edit(string $id)
{
    // 1) Traemos el slot/horario existente
    $resp = Http::withToken($this->jwt)
        ->get("{$this->baseUrl}/{$id}");

    if (! $resp->ok()) {
        return back()->withErrors(['error' => 'Horario no encontrado.']);
    }
    $horario = $resp->json();

    // 2) Traemos de nuevo el staff
    $restaurantId = Session::get('restaurante_id');
    $staffResp = Http::withToken($this->jwt)
        ->get(config('services.identity.url') . "/restaurantes/{$restaurantId}/staff/list");

    if (! $staffResp->ok()) {
        return back()->withErrors(['error' => 'No se pudo listar el staff.']);
    }
    $empleados = $staffResp->json();

    // 3) Pasamos `horario` y `empleados` a la vista
    return view('gestion_horarios.form', compact('horario','empleados'));
}

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'horaInicio' => 'required|date_format:H:i',
            'horaFin'    => 'required|date_format:H:i|after:horaInicio',
            'capacidad'  => 'required|integer|min:1',
        ]);

        $resp = Http::withToken($this->jwt)
            ->put("{$this->baseUrl}/{$id}", $data);

        if (! $resp->successful()) {
            return back()->withErrors(['error' => 'No se pudo actualizar el horario.'])->withInput();
        }

        return redirect()->route('horarios.list')
                         ->with('success', 'Horario actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $resp = Http::withToken($this->jwt)
            ->delete("{$this->baseUrl}/{$id}");

        if (! $resp->successful()) {
            return back()->withErrors(['error' => 'No se pudo eliminar el horario.']);
        }

        return redirect()->route('horarios.list')
                         ->with('success', 'Horario eliminado correctamente.');
    }

    // Mostrar el formulario de slot
public function showCreateSlotForm()
{
    return view('gestion_horarios.form');
}

public function createSlot(Request $request)
{
    $data = $request->validate([
        'name'  => 'required|string|max:100',
        'start' => 'required|date_format:H:i',
        'end'   => 'required|date_format:H:i|after:start',
    ]);

    $resp = Http::withToken($this->jwt)
        ->post("{$this->baseUrl}/slots", $data);

    if (! $resp->successful()) {
        return back()->withErrors(['error' => 'No se pudo crear el turno.'])->withInput();
    }

    return redirect()->route('horarios.list')
                     ->with('success', 'Turno creado correctamente.');
}

public function assignments(Request $request)
    {
        $day = $request->query('day', today()->toDateString());

        // Llamada al microservicio de Assignments
        $resp = $this->apiClient
        ->get("{$this->baseUrl}/assignments", ['date' => $day]);

        if (! $resp->ok()) {
            return response()->json([
                'error' => 'No se pudieron cargar las asignaciones'
            ], 500);
        }

        // Devolvemos directamente el JSON que viene del servicio
        return response()->json($resp->json());
    }
}
