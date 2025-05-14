<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $jwt           = Session::get('jwt');
        $restaurantes  = collect(Session::get('restaurantes'));
        $restauranteId = $request->query('restauranteId', Session::get('restaurante_id'));

        // 1) Leer filtros, con default: día + Confirmada
        $filterType = $request->query('filterType', 'day');      // day | week | month
        $estado     = $request->query('estado', 'Confirmada');   // '', Pendiente, Confirmada, Cancelada
        $baseDate   = $request->query('baseDate', now()->toDateString());
        $baseMonth  = $request->query('baseMonth', now()->format('Y-m'));

        // 2) Calcular rango según filterType
        if ($filterType === 'month') {
            $desde = Carbon::parse("{$baseMonth}-01")->startOfMonth();
            $hasta = Carbon::parse("{$baseMonth}-01")->endOfMonth();
        } else {
            $f0 = Carbon::parse($baseDate);
            if ($filterType === 'week') {
                $desde = $f0->startOfWeek();
                $hasta = $f0->endOfWeek();
            } else { // day
                $desde = $f0->startOfDay();
                $hasta = $f0->endOfDay();
            }
        }

        $labelDesde = $desde->format('d/m/Y');
        $labelHasta = $hasta->format('d/m/Y');

        $baseUrl = rtrim(config('services.identity.url'), '/');

        // 3) TOTAL HOY — siempre de 00:00 a 23:59, filtrado por estado
        $hoyDesde = Carbon::today()->startOfDay()->toDateTimeString();
        $hoyHasta = Carbon::today()->endOfDay()->toDateTimeString();

        $respHoy = Http::withToken($jwt)
            ->get("$baseUrl/reservas/total", [
                'restauranteId' => $restauranteId,
                'fechaDesde'    => $hoyDesde,
                'fechaHasta'    => $hoyHasta,
                'estado'        => $estado,
            ]);

        $totalHoy = $respHoy->ok() ? $respHoy->json('total') : 0;

        // 4) TOTAL RANGO seleccionado (hora completa)
        $respRango = Http::withToken($jwt)
            ->get("$baseUrl/reservas/total", [
                'restauranteId' => $restauranteId,
                'fechaDesde'    => $desde->startOfDay()->toDateTimeString(),
                'fechaHasta'    => $hasta->endOfDay()->toDateTimeString(),
                'estado'        => $estado,
            ]);

        $totalRango = $respRango->ok() ? $respRango->json('total') : 0;

        // 5) SERIES: horarias si es “day”, diarias en otro caso
        $labels = $datos = [];
        $hourly = collect();

        if ($filterType === 'day') {
            // Endpoint horario
            $respHourly = Http::withToken($jwt)
                ->get("$baseUrl/reservas/series/hourly", [
                    'restauranteId' => $restauranteId,
                    'fecha'         => $baseDate,
                    'estado'        => $estado,
                ]);

            if ($respHourly->ok()) {
                $hourly = collect($respHourly->json()); // [{ hour, total }, …]
            }
        } else {
            // Endpoint diario
            $respSeries = Http::withToken($jwt)
                ->get("$baseUrl/reservas/series", [
                    'restauranteId' => $restauranteId,
                    'fechaDesde'    => $desde->startOfDay()->toDateTimeString(),
                    'fechaHasta'    => $hasta->endOfDay()->toDateTimeString(),
                    'estado'        => $estado,
                ]);

            if ($respSeries->ok()) {
                $series = collect($respSeries->json());
                $labels = $series->pluck('fecha')->map(fn($f) => Carbon::parse($f)->format('d/m'))->toArray();
                $datos  = $series->pluck('total')->toArray();
            }
        }

        // 6) CALENDARIO MES: si corresponde, preparamos datos
        $blankCells   = 0;
        $calendarDays = [];
        $maxCount     = 1;

        if ($filterType === 'month') {
            // 6.1) Lapso de mes
            $period = CarbonPeriod::create($desde, $hasta);

            // 6.2) Celdas en blanco antes del día 1 (Lun=1…Dom=7)
            $blankCells = $desde->isoWeekday() - 1;

            // 6.3) Lookup de totales por fecha (labels/datos)
            $lookup = collect($labels)
                ->mapWithKeys(fn($f,$i)=>[
                    Carbon::createFromFormat('d/m', $f)->format('Y-m-d')
                    => $datos[$i] ?? 0
                ]);

            // 6.4) Construir array de días con total
            foreach ($period as $day) {
                $iso = $day->format('Y-m-d');
                $calendarDays[] = [
                    'day'   => $day->format('d'),
                    'total' => $lookup->get($iso, 0),
                ];
            }

            // 6.5) Máximo para normalizar opacidad
            $maxCount = max(1, collect($calendarDays)->pluck('total')->max());
        }

        // 7) Renderizar vista
        return view('dashboard.index', compact(
            'restaurantes',
            'restauranteId',
            'filterType',
            'estado',
            'baseDate',
            'baseMonth',
            'labelDesde',
            'labelHasta',
            'totalHoy',
            'totalRango',
            'labels',
            'datos',
            'hourly',
            'blankCells',
            'calendarDays',
            'maxCount'
        ));
    }
}
