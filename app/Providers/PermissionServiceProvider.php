<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate; // Importez la façade Gate
use App\Models\User; // Importez le modèle User pour les types de données

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // DÉFINITION DU GATE "isAdmin" pour vérifier le rôle
        Gate::define('isAdmin', function (User $user) {
            // Puisque nous avons décidé que tous les nouveaux utilisateurs sont 'joueur',
            // seul un utilisateur modifié manuellement pourra devenir 'admin'.
            return $user->role === 'admin';
        });
    }
}