<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class SlotController extends Controller
{
    protected string $baseUrl;
    protected string $jwt;
    protected \Illuminate\Http\Client\PendingRequest $apiClient;

    public function __construct()
    {
        // Asegúrate de que la URL base incluya el endpoint de slots, por ejemplo: HORARIOS_API_URL/slots
        $this->baseUrl = config('services.horarios.url') . '/slots';
        $jwt     = Session::get('jwt');
        $restaurantId  = Session::get('restaurante_id');

         $this->apiClient = Http::withToken($jwt)
            ->withHeaders([
                'X-Restaurante-Id' => $restaurantId,
            ]);
    }

    // Listado de slots
    public function index(Request $request)
    {
        $response = $this->apiClient
            ->get("{$this->baseUrl}");

        if (!$response->ok()) {
            return back()->withErrors(['error' => 'No se pudieron obtener los slots.']);
        }

        $slots = $response->json();
        return view('slots.index', compact('slots'));
    }

    // Mostrar formulario para crear un slot
    public function create()
    {
        return view('slots.form', ['slot' => null]);
    }

    // Guardar nuevo slot
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'start' => 'required|date_format:H:i',
            'end'   => 'required|date_format:H:i|after:start',
        ]);

         $response = $this->apiClient
            ->post("{$this->baseUrl}", $data);

        if (!$response->successful()) {
            return back()->withErrors(['error' => 'No se pudo crear el slot.'])->withInput();
        }

        return redirect()->route('slots.index')
                         ->with('success', 'Slot creado correctamente.');
    }

    // Mostrar formulario para editar un slot
    public function edit($id)
    {
        $response = $this->apiClient
            ->get("{$this->baseUrl}/{$id}");

        if (!$response->ok()) {
            return back()->withErrors(['error' => 'Slot no encontrado.']);
        }

        $slot = $response->json();
        return view('slots.form', compact('slot'));
    }

    // Actualizar un slot
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'start' => 'required|date_format:H:i',
            'end'   => 'required|date_format:H:i|after:start',
        ]);

        $response = $this->apiClient
            ->put("{$this->baseUrl}/{$id}", $data);

        if (!$response->successful()) {
            return back()->withErrors(['error' => 'No se pudo actualizar el slot.'])->withInput();
        }

        return redirect()->route('slots.index')
                         ->with('success', 'Slot actualizado correctamente.');
    }

    // Eliminar un slot
    public function destroy($id)
    {
        $response = $this->apiClient
            ->delete("{$this->baseUrl}/{$id}");


        if (!$response->successful()) {
            return back()->withErrors(['error' => 'No se pudo eliminar el slot.']);
        }

        return redirect()->route('slots.index')
                         ->with('success', 'Slot eliminado correctamente.');
    }
}