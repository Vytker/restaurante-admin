<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RestaurantController;
use App\Http\Middleware\RemoteAuth;
Route::get('/', function () {
    return view('dashboard');
});


Route::view('/login', 'auth.login')->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::view('/register', 'auth.register')->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

Route::middleware(RemoteAuth::class)->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');
    // Otras rutas protegidas...

    Route::get('/admin/restaurants', [AdminController::class, 'restaurants'])->name('admin.restaurants');

    Route::get('/admin/restaurants/create', function () {
        return view('admin.create_restaurant');
    })->name('admin.restaurants.create');

    Route::post('/admin/restaurants/create', [AdminController::class, 'store'])->name('admin.restaurants.store');

});

Route::middleware(RemoteAuth::class)->group(function () {
    // Mostrar el formulario de asignación de staff
    Route::get('/restaurants/{restaurantId}/staff', [RestaurantController::class, 'showAssignStaffForm'])
         ->name('restaurants.staff.form');

    // Procesar la petición de asignación
    Route::post('/restaurants/{restaurantId}/staff', [RestaurantController::class, 'assignStaff'])
         ->name('restaurants.staff.assign');
    // Listar el staff asignado
     Route::get('/restaurants/{restaurantId}/staff/list', [RestaurantController::class, 'listStaff'])
     ->name('restaurants.staff.list');
    
});

Route::middleware(RemoteAuth::class)->group(function () {
    
    Route::get('/reservas', [\App\Http\Controllers\ReservationController::class, 'listReservations'])
         ->name('reservations.list');
          // Formulario para crear reserva
    Route::get('/reservas/create', [\App\Http\Controllers\ReservationController::class, 'createReservation'])
    ->name('reservations.create');

// Crear reserva(POST)
Route::post('/reservas', [\App\Http\Controllers\ReservationController::class, 'storeReservation'])
    ->name('reservations.store');

// Actualizar estado de una reserva (PUT)
Route::put('/reservas/{id}/estado', [\App\Http\Controllers\ReservationController::class, 'updateStatus'])
    ->name('reservations.updateStatus');
    
    Route::get('/reservas/filter', [\App\Http\Controllers\ReservationController::class, 'filter'])
    ->name('reservations.filter');
});