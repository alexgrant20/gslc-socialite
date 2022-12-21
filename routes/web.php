<?php

use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('discord', function () {
    return Socialite::driver('discord')->redirect();
})->name('discord-auth');

Route::get('callback', function () {
    try {
        $user = Socialite::driver('discord')->stateless()->user();
    } catch (Exception $e) {
        return to_route('login');
    }

    $userInDB = User::where('email', $user->email)->first();

    if (!empty($userInDB)) {
        Auth::login($userInDB);
    } else {
        $user = User::create([
            'name' => $user->name,
            'email' => $user->email,
            'password' => Hash::make('password'),
        ]);

        Auth::login($user);
    }

    return to_route('dashboard');
});

require __DIR__ . '/auth.php';