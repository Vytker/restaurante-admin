<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RestaurantController;
use App\Http\Middleware\RemoteAuth;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GestionHorariosController;
use App\Http\Controllers\SlotController;
use App\Http\Controllers\StaffController;


Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

Route::view('/login', 'auth.login')->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/complete-profile', [AuthController::class, 'showCompleteForm']);
Route::post('/complete-profile', [AuthController::class, 'register']);

Route::middleware(RemoteAuth::class)->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');



Route::get('/staff/invite', [AuthController::class, 'showInviteForm'])
     ->middleware('auth', 'role:Owner,SuperAdmin');
// Procesar invitación
Route::post('/staff/invite', [AuthController::class, 'register'])
     ->middleware('auth', 'role:Owner,SuperAdmin');

Route::get('/admin/restaurants', [AdminController::class, 'restaurants'])->name('admin.restaurants');

    Route::get('/admin/restaurants/create', function () {
        return view('admin.create_restaurant');
    })->name('admin.restaurants.create');

    Route::post('/admin/restaurants/create', [AdminController::class, 'store'])->name('admin.restaurants.store');

    Route::post('/admin/select-restaurant', [AdminController::class, 'selectRestaurant'])
    ->name('admin.selectRestaurant');

    Route::post('/admin/deselect-restaurant', [AdminController::class, 'deselectRestaurant'])
        ->name('admin.deselectRestaurant');

});

Route::middleware(RemoteAuth::class)->group(function () {
    // Mostrar el formulario de asignación de staff

    Route::get('/restaurants/{restaurantId}/staff', [RestaurantController::class, 'showAssignStaffForm'])
         ->name('restaurants.staff.form')
         ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);

    // Procesar la petición de asignación
    Route::post('/restaurants/{restaurantId}/staff', [RestaurantController::class, 'assignStaff'])
         ->name('restaurants.staff.assign')
         ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
    // Listar el staff asignado
     Route::get('/restaurants/{restaurantId}/staff/list', [RestaurantController::class, 'listStaff'])
     ->name('restaurants.staff.list')
     ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     
     Route::delete('/restaurants/{restaurantId}/staff/{staffId}', [RestaurantController::class, 'destroyStaff'])
    ->name('restaurants.staff.destroy')
    ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
    
});

Route::middleware(RemoteAuth::class)->group(function () {
    
    Route::get('/reservas', [\App\Http\Controllers\ReservationController::class, 'listReservations'])
         ->name('reservations.list')
         ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
          // Formulario para crear reserva
    Route::get('/reservas/create', [\App\Http\Controllers\ReservationController::class, 'createReservation'])
    ->name('reservations.create')
    ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     

// Crear reserva(POST)
Route::post('/reservas', [\App\Http\Controllers\ReservationController::class, 'storeReservation'])
    ->name('reservations.store')
    ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     

// Actualizar estado de una reserva (PUT)
Route::put('/reservas/{id}/estado', [\App\Http\Controllers\ReservationController::class, 'updateStatus'])
    ->name('reservations.updateStatus')
    ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     
    
    Route::get('/reservas/filter', [\App\Http\Controllers\ReservationController::class, 'filter'])
    ->name('reservations.filter')
    ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     

Route::get ('/turnos',               [ReservationController::class, 'listTurnos'])->name('turnos.list')->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     
Route::get ('/turnos/crear',         [ReservationController::class, 'editTurno'])->name('turnos.create')->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     
Route::get ('/turnos/{id}/editar',   [ReservationController::class, 'editTurno'])->name('turnos.edit')->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     
Route::post('/turnos',               [ReservationController::class, 'saveTurno'])->name('turnos.store')->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     
Route::put ('/turnos/{id}',          [ReservationController::class, 'saveTurno'])->name('turnos.update')->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     
Route::delete('/turnos/{id}',        [ReservationController::class, 'deleteTurno'])->name('turnos.destroy')->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     


        /* PERFIL ------------------------------------------------------------- */
    Route::get ('/perfil',  [ProfileController::class, 'edit'  ])->name('profile.edit');
    Route::put ('/perfil',  [ProfileController::class, 'update'])->name('profile.update');

    /* CONTRASEÑA --------------------------------------------------------- */
    Route::get ('/password', [ProfileController::class, 'editPassword' ])->name('password.edit');
    Route::put ('/password', [ProfileController::class, 'updatePassword'])->name('password.update');


Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');


 // Listado
    Route::get('/gestion-horarios', [GestionHorariosController::class, 'index'])
         ->name('horarios.list')
         ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     

    // Formulario alta
    Route::get('/gestion-horarios/crear', [GestionHorariosController::class, 'create'])
         ->name('horarios.create')
         ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     

    // Procesar alta
    Route::post('/gestion-horarios', [GestionHorariosController::class, 'store'])
         ->name('horarios.store')->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     

    // Formulario edición
    Route::get('/gestion-horarios/{id}/editar', [GestionHorariosController::class, 'edit'])
         ->name('horarios.edit')
         ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     

    // Procesar edición
    Route::put('/gestion-horarios/{id}', [GestionHorariosController::class, 'update'])
         ->name('horarios.update')->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     

    // Borrar
    Route::delete('/gestion-horarios/{id}', [GestionHorariosController::class, 'destroy'])
         ->name('horarios.destroy')
         ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     

         Route::post('gestion-horarios/assignments', [GestionHorariosController::class, 'CreateAssignment'])
  ->name('horarios.assign')->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     

Route::get('gestion-horarios/assignments', [GestionHorariosController::class, 'assignments'])
     ->name('horarios.assignments')
     ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     

  // Mostrar formulario de creación de slot
Route::get('gestion-horarios/nuevo-turno', [GestionHorariosController::class, 'showCreateSlotForm'])
     ->name('horarios.createSlotForm')
     ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     


// Endpoint que recibe el POST y lo envía a la API
Route::post('gestion-horarios/slots', [GestionHorariosController::class, 'createSlot'])
     ->name('horarios.createSlot')
     ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     
});

Route::middleware(RemoteAuth::class)->group(function () {
    // Listado de slots
    Route::get('/slots', [SlotController::class, 'index'])->name('slots.index')
    ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     

    // Formulario para crear un nuevo slot
    Route::get('/slots/create', [SlotController::class, 'create'])->name('slots.create')
    ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     
    Route::post('/slots', [SlotController::class, 'store'])->name('slots.store')
    ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     

    // Formulario para editar un slot (GET) y actualizar (PUT)
    Route::get('/slots/{id}/edit', [SlotController::class, 'edit'])->name('slots.edit')
    ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     
    Route::put('/slots/{id}', [SlotController::class, 'update'])->name('slots.update')
    ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     

    // Eliminar un slot
    Route::delete('/slots/{id}', [SlotController::class, 'destroy'])->name('slots.destroy')
    ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     

     // Calendario staff calendar

});

Route::middleware([RemoteAuth::class])
    ->prefix('staff')
    ->group(function(){
        Route::get('/calendar', [StaffController::class, 'calendar'])
             ->name('staff.calendar')
             ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     
        Route::get('/my-shifts', [StaffController::class, 'myShifts'])
             ->name('staff.myShifts')
             ->middleware(\App\Http\Middleware\CheckRestaurantSelected::class);
     
    });