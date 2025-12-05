<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{
    /**
     * Rediriger l'utilisateur vers la page d'authentification Google.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Gérer le rappel (callback) de Google après l'authentification.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                // L'utilisateur existe déjà, on le connecte
                Auth::login($user);
            } else {
                // Nouvel utilisateur, on le crée et on le connecte
                // $isFirstUser = User::count() === 0;
                // $role = $isFirstUser ? 'admin' : 'joueur';
                $role = 'joueur'; // Rôle par défaut pour les nouveaux utilisateurs
                $newUser = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => \Hash::make('your_random_password'), // Un mot de passe arbitraire si la colonne est non-nullable
                    'role' => $role,
                ]);

                // TODO: AJOUTER LA LOGIQUE DE RÔLE ICI (Affecter le rôle 'Joueur' par défaut)

                Auth::login($newUser);
            }

            return redirect()->route('dashboard'); // Redirigez vers la page de jeu/dashboard
        } catch (\Exception $e) {
            // Gérer les erreurs (ex: connexion interrompue, etc.)
            return redirect('/login')->withErrors(['error' => 'Erreur de connexion Google.']);
        }
    }
}