<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Level;
use App\Models\Step;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Définition des contraintes progressives
        $constraints = [
            // Niveau 1 : très simple
            [
                "Entrer une lettre minuscule",
                "Entrer une lettre majuscule",
                "Entrer un chiffre",
                "Entrer deux chiffres",
                "Entrer une lettre + un chiffre",
                "Entrer deux lettres + un chiffre",
                "Entrer trois lettres",
                "Entrer trois chiffres",
            ],
            // Niveau 2 : combinaisons simples
            [
                "Entrer une lettre majuscule + une minuscule",
                "Entrer une lettre + deux chiffres",
                "Entrer un chiffre + un caractère spécial",
                "Entrer deux lettres + un caractère spécial",
                "Entrer trois chiffres + une lettre",
                "Entrer une majuscule + un chiffre + un spécial",
                "Entrer quatre lettres (maj/min)",
                "Entrer deux lettres + deux chiffres",
            ],
            // Niveau 3 : contraintes plus précises
            [
                "Mot de 4 lettres minuscules",
                "Mot de 4 lettres majuscules",
                "Mot de 4 chiffres",
                "Une lettre + un chiffre + un spécial",
                "Deux majuscules + deux chiffres",
                "Trois lettres + un spécial",
                "Mot de 5 caractères (lettres/chiffres)",
                "Mot de 5 caractères (lettres/chiffres/spéciaux)",
            ],
            // Niveau 4 : mini mots de passe
            [
                "6 lettres uniquement",
                "6 chiffres uniquement",
                "6 lettres + chiffres",
                "6 lettres + spéciaux",
                "6 lettres + chiffres + spéciaux",
                "Au moins 1 majuscule + 1 chiffre",
                "Au moins 1 minuscule + 1 spécial",
                "Au moins 1 majuscule + 1 minuscule + 1 chiffre",
            ],
            // Niveau 5 : complexité croissante
            [
                "Mot de 7 caractères (lettres/chiffres)",
                "Mot de 7 caractères (lettres/chiffres/spéciaux)",
                "Au moins 2 majuscules + 2 chiffres",
                "Au moins 2 minuscules + 2 spéciaux",
                "Mot de 8 caractères (mix complet)",
                "Au moins 1 majuscule + 1 minuscule + 1 chiffre + 1 spécial",
                "Mot de 8 caractères avec 3 types différents",
                "Mot de 8 caractères avec 4 types différents",
            ],
            // Niveau 6 à 12 : progression finale
            [
                "Mot de 9 caractères avec maj/min/chiffres/spéciaux",
                "Au moins 2 majuscules + 2 chiffres + 1 spécial",
                "Mot de 9 caractères avec 3 types différents",
                "Mot de 9 caractères avec 4 types différents",
                "Mot de 9 caractères avec séquence de chiffres",
                "Mot de 9 caractères avec séquence de lettres",
                "Mot de 9 caractères avec séquence de spéciaux",
                "Mot de 9 caractères mix complet",
            ],
            [
                "Mot de 10 caractères avec maj/min/chiffres/spéciaux",
                "Au moins 3 majuscules + 3 chiffres",
                "Mot de 10 caractères avec 2 spéciaux",
                "Mot de 10 caractères avec séquence imposée",
                "Mot de 10 caractères avec mix complet",
                "Mot de 10 caractères avec ordre imposé",
                "Mot de 10 caractères avec 4 types différents",
                "Mot de 10 caractères avec contraintes avancées",
            ],
            [
                "Mot de 11 caractères avec maj/min/chiffres/spéciaux",
                "Au moins 3 majuscules + 3 chiffres + 2 spéciaux",
                "Mot de 11 caractères avec séquence imposée",
                "Mot de 11 caractères avec mix complet",
                "Mot de 11 caractères avec ordre imposé",
                "Mot de 11 caractères avec 4 types différents",
                "Mot de 11 caractères avec contraintes avancées",
                "Mot de 11 caractères avec séquence de lettres/chiffres",
            ],
            [
                "Mot de 12 caractères avec maj/min/chiffres/spéciaux",
                "Au moins 4 majuscules + 4 chiffres",
                "Mot de 12 caractères avec 3 spéciaux",
                "Mot de 12 caractères avec séquence imposée",
                "Mot de 12 caractères avec mix complet",
                "Mot de 12 caractères avec ordre imposé",
                "Mot de 12 caractères avec contraintes avancées",
                "Mot de 12 caractères avec séquence complexe",
            ],
            [
                "Mot de 13 caractères avec maj/min/chiffres/spéciaux",
                "Au moins 4 majuscules + 4 chiffres + 2 spéciaux",
                "Mot de 13 caractères avec séquence imposée",
                "Mot de 13 caractères avec mix complet",
                "Mot de 13 caractères avec ordre imposé",
                "Mot de 13 caractères avec 4 types différents",
                "Mot de 13 caractères avec contraintes avancées",
                "Mot de 13 caractères avec séquence complexe",
            ],
            [
                "Mot de 14 caractères avec maj/min/chiffres/spéciaux",
                "Au moins 5 majuscules + 5 chiffres",
                "Mot de 14 caractères avec 3 spéciaux",
                "Mot de 14 caractères avec séquence imposée",
                "Mot de 14 caractères avec mix complet",
                "Mot de 14 caractères avec ordre imposé",
                "Mot de 14 caractères avec contraintes avancées",
                "Mot de 14 caractères avec séquence complexe",
            ],
            [
                "Mot de 15 caractères final avec maj/min/chiffres/spéciaux",
                "Au moins 5 majuscules + 5 chiffres + 3 spéciaux",
                "Mot de 15 caractères avec séquence imposée",
                "Mot de 15 caractères avec mix complet",
                "Mot de 15 caractères avec ordre imposé",
                "Mot de 15 caractères avec 4 types différents",
                "Mot de 15 caractères avec contraintes avancées",
                "Mot de 15 caractères ultime",
            ],
        ];

        // Création des niveaux et étapes
        foreach ($constraints as $levelIndex => $steps) {
            $level = Level::create([
                'name' => 'Niveau ' . ($levelIndex + 1),
                'description' => 'Progression du jeu - Niveau ' . ($levelIndex + 1),
            ]);

            foreach ($steps as $order => $title) {
                Step::create([
                    'level_id' => $level->id,
                    'title' => $title,
                    'constraints' => $title, // tu peux mettre un code interne si besoin
                    'order' => $order + 1,
                ]);
            }
        }

        // Création d'un utilisateur de test
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
