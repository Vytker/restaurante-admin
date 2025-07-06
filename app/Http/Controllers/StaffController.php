<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class StaffController extends Controller
{
    public function calendar()
    {
        // 1) Traer lista de turnos (slots)
        $slots = Http::withToken(session('jwt'))
            ->get(config('services.horarios.url').'/slots')
            ->json() ?? [];

        // 2) Mapeamos a lo mínimo que necesitamos: { id, name }
        $slotsMap = array_map(function($s){
            return [
                'id'   => $s['id'] ?? null,
                'name' => $s['name'] ?? 'Turno',
                'startTime' => $s['startTime'] ?? ($s['horaInicio'] ?? ($s['inicio'] ?? null)),
                'endTime'   => $s['endTime']   ?? ($s['horaFin']   ?? ($s['fin']   ?? null)),
            ];
        }, $slots);

        // 2) Traer listado de empleados asignados al restaurante
        $staff = Http::withToken(session('jwt'))
            ->get(config('services.identity.url')
                 ."/restaurantes/".session('restaurante_id')."/staff/list")
            ->json() ?? [];
       
        // 3) Mapeamos a lo mínimo que necesitamos: { id, fullName }
        $employees = array_map(function($u){
            return [
                'id'       => $u['id'] ?? null,
                'fullName' => $u['fullName'] ?? $u['userName'] ?? 'Empleado',
            ];
        }, $staff);

        return view('staff.calendar', compact('slots','employees'));
    }

     public function myShifts(Request $request)
    {
        // 1) Leer el mes del filtro, formato "YYYY-MM". Si no viene, usamos el mes actual.
        $month = $request->get('month', now()->format('Y-m'));

        // 2) Crear un Carbon a partir de "YYYY-MM"
        try {
            $dt = Carbon::createFromFormat('Y-m', $month);
        } catch (\Exception $e) {
            // mes inválido → usar hoy
            $dt = now();
        }

        // 3) Calcular inicio y fin de mes
        $start = $dt->copy()->startOfMonth()->toDateString();   // e.g. "2025-05-01"
        $end   = $dt->copy()->endOfMonth()  ->toDateString();   // e.g. "2025-05-31"

        // 4) Llamamos a assignments/range con esos filtros
        $assignments = Http::withToken(session('jwt'))
            ->get(
                config('services.horarios.url') . '/assignments/range',
                [ 'start' => $start, 'end' => $end ]
            )
            ->json() 
            ?? [];

        // 5) Para mapear slotName necesitaremos los slots:
        $slotsById = collect(
            Http::withToken(session('jwt'))
                ->get(config('services.horarios.url').'/slots')
                ->json() 
                ?? []
        )->keyBy('id');

        // 6) Tu propio employee_id
        $me = session('employee_id');

        // 7) Filtrar solo lo mío y adjuntar slotName
        $shifts = collect($assignments)
            ->filter(fn($a) => $a['empleadoId'] === $me)
            ->map(function($a) use($slotsById) {
                return [
                    'fechaHoraInicio' => $a['fechaHoraInicio'],
                    'fechaHoraFin'    => $a['fechaHoraFin'],
                    'slotName'        => $slotsById->get($a['slotId'])['name'] ?? '–',
                ];
            })
            ->values()
            ->all();

        // 8) Pasar la variable 'month' a la vista para que el campo <input type="month"> siga marcado
        return view('staff.my-shifts', [
            'shifts' => $shifts,
            'month'  => $month,
        ]);
    }
}
