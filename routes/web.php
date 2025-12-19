<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
// routes/web.php

use App\Http\Controllers\LevelController;
use App\Http\Controllers\StepController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\GameController;
// use Illuminate\Support\Facades\Route;

// ...

// Route de redirection vers Google
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle']);

// Route de rappel (callback) de Google
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// Route de test pour la page d'arrivée
// Route::get('/dashboard', function () {
//     return view('welcome'); // Remplacer par votre vraie vue de dashboard plus tard
// })->middleware('auth')->name('dashboard'); // Protéger cette route

// ...
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');


Route::get('dashboard', function () {
    // return redirect('/dashboard');
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');




Route::get('/levels', function () {
    return Inertia::render('Levels'); // correspond à resources/js/Pages/Levels.vue
});


Route::get('/levels/create', [LevelController::class, 'create'])->name('levels.create');
Route::post('/levels', [LevelController::class, 'store'])->name('levels.store');

// CRUD complet pour les niveaux
Route::resource('levels', LevelController::class);

// CRUD imbriqué pour les étapes
Route::resource('levels.steps', StepController::class)->shallow();


// Route pour jouer un niveau
Route::get('/play/{level}', [GameController::class, 'play'])->name('game.play');
Route::post('/play/{level}/step/{step}', [GameController::class, 'validateStep'])->name('game.validate');
// Endpoint pour définir le mot de passe de l'utilisateur depuis l'interface de jeu
Route::post('/user/password/set-from-game', [GameController::class, 'setPasswordFromGame'])->middleware('auth')->name('user.password.setFromGame');
require __DIR__.'/settings.php';
