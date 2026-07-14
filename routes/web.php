<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;

use App\Http\Controllers\UserController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BedController;
use App\Http\Controllers\FlexworkerController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ExportController;

use App\Http\Controllers\Api\ApiGroupController;
use App\Http\Controllers\Api\ApiHouseController;
use App\Http\Controllers\Api\ApiRoomController;
use App\Http\Controllers\Api\ApiFlexworkerController;
use App\Http\Controllers\Api\ApiBedController;
use App\Http\Controllers\Api\ImportController;

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    //GROUPS
    Route::get('/groups', [GroupController::class, 'index'])->name('group.index');
    Route::get('/groups/create', [GroupController::class, 'create'])->name('group.create');
    Route::post('/groups/create', [GroupController::class, 'store'])->name('group.store');
    Route::get('/groups/{group}', [GroupController::class, 'show'])->name('group.show');
    Route::put('/groups/{group}', [GroupController::class, 'update'])->name('group.update');
    Route::get('/groups/{group}/delete', [GroupController::class, 'destroy'])->name('group.destroy');

    //HOUSES
    Route::get('/houses', [HouseController::class, 'index'])->name('house.index');
    Route::get('/houses/create', [HouseController::class, 'create'])->name('house.create');
    Route::post('/houses/create', [HouseController::class, 'store'])->name('house.store');
    Route::get('/houses/{house}', [HouseController::class, 'show'])->name('house.show');
    Route::put('/houses/{house}', [HouseController::class, 'update'])->name('house.update');
    Route::get('/houses/{house}/delete', [HouseController::class, 'destroy'])->name('house.destroy');

    //ROOMS
    Route::get('/rooms', [RoomController::class, 'index'])->name('room.index');
    Route::get('/rooms/create', [RoomController::class, 'create'])->name('room.create');
    Route::post('/rooms/create', [RoomController::class, 'store'])->name('room.store');
    Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('room.show');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('room.update');
    Route::get('/rooms/{room}/delete', [RoomController::class, 'destroy'])->name('room.destroy');

    //BEDS
    Route::get('/beds', [BedController::class, 'index'])->name('bed.index');
    Route::get('/beds/create', [BedController::class, 'create'])->name('bed.create');
    Route::post('/beds/create', [BedController::class, 'store'])->name('bed.store');
    Route::get('/beds/{bed}', [BedController::class, 'show'])->name('bed.show');
    Route::put('/beds/{bed}', [BedController::class, 'update'])->name('bed.update');
    Route::get('/beds/{bed}/delete', [BedController::class, 'destroy'])->name('bed.destroy');

    //FLEXWORKERS
    Route::get('/flexworkers', [FlexworkerController::class, 'index'])->name('flexworker.index');
    Route::get('/flexworkers/create', [FlexworkerController::class, 'create'])->name('flexworker.create');
    Route::post('/flexworkers/create', [FlexworkerController::class, 'store'])->name('flexworker.store');
    Route::get('/flexworkers/importcsv', [FlexworkerController::class, 'importCSV']);
    Route::get('/flexworkers/{flexworker}', [FlexworkerController::class, 'show'])->name('flexworker.show');
    Route::put('/flexworkers/{flexworker}', [FlexworkerController::class, 'update'])->name('flexworker.update');

    //BOOKINGS
    Route::get('/bookings', [BookingController::class, 'index'])->name('booking.index');
    Route::get('/bookings/conflicts', [BookingController::class, 'conflicts'])->name('booking.conflicts');
    Route::get('/bookings/weekview', [BookingController::class, 'weekview'])->name('booking.weekview');
    Route::get('/bookings/check-future-bookings', [BookingController::class, 'checkFutureBookings'])->name('bookings.checkFuture');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/bookings/create', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/bookings/{booking}/vacation/create', [BookingController::class, 'createVacation'])->name('booking.vacation.create');
    Route::post('/bookings/{booking}/vacation/create', [BookingController::class, 'storeVacation'])->name('booking.vacation.store');
    Route::get('/bookings/{booking}/relocation/create', [BookingController::class, 'createRelocation'])->name('booking.relocation.create');
    Route::post('/bookings/{booking}/relocation/create', [BookingController::class, 'storeRelocation'])->name('booking.relocation.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('booking.show');
    Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('booking.update');    
    Route::get('/bookings/{booking}/delete', [BookingController::class, 'destroy'])->name('booking.destroy');

    //USERS
    Route::get('/users', [UserController::class, 'index'])->name('user.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('user.create');
    Route::post('/users', [UserController::class, 'store'])->name('user.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('user.show');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('user.update');
    Route::get('/users/{user}/delete', [UserController::class, 'destroy'])->name('user.destroy');

    //EXPORTS
    Route::get('/exports', [ExportController::class, 'index'])->name('export.index');
    Route::get('/exports/{export}/download', [ExportController::class, 'download'])->name('export.download');

    //GENERAL
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');

    //API
    Route::get('/import/snelstart', [ImportController::class, 'importSnelstart'])->name('api.import.snelstart');

    //API
    Route::get('/api/groups', [ApiGroupController::class, 'index'])->name('api.group.index');
    Route::get('/api/houses', [ApiHouseController::class, 'index'])->name('api.house.index');
    Route::get('/api/rooms', [ApiRoomController::class, 'index'])->name('api.room.index');
    Route::get('/api/flexworkers', [ApiFlexworkerController::class, 'index'])->name('api.flexworker.index');
    Route::get('/api/beds', [ApiBedController::class, 'index'])->name('api.bed.index');
    Route::get('/api/beds/{bed}/unavailable-dates', [ApiBedController::class, 'getUnavailableDates'])->name('api.bed.unavailable-dates');
    Route::get('/api/beds/available', [ApiBedController::class, 'getAvailableBeds'])->name('api.bed.available');
});

require __DIR__.'/auth.php';
