<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Step;
use App\Models\PasswordAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class GameController extends Controller
{
    public function play(Level $level)
    {
        // Charger les étapes et ajouter une URL de validation pour chaque étape
        $level->load('steps');

        $level->steps->transform(function ($step) use ($level) {
            $step->validate_url = route('game.validate', ['level' => $level->id, 'step' => $step->id]);
            return $step;
        });

        // URL pour jouer le niveau suivant (simple incrément d'ID)
        $nextLevelUrl = route('game.play', ['level' => $level->id + 1]);

        return Inertia::render('Game', [
            'level' => $level,
            'nextLevelUrl' => $nextLevelUrl,
        ]);
    }

    public function validateStep(Request $req, Level $level, Step $step)
    {
        $input = $req->password;
        $constraints = $step->constraints;

        // Log incoming attempt for debugging
        Log::info('Game validate attempt', [
            'user_id' => auth()->id(),
            'level_id' => $level->id,
            'step_id' => $step->id,
            'constraints' => $constraints,
            'raw_input' => $req->getContent(),
            'password_field' => $input,
        ]);

        $valid = $this->checkConstraints($input, $constraints);

        // Stocker tentative
        PasswordAttempt::create([
            'user_id' => auth()->id(),
            'level_id' => $level->id,
            'step_id' => $step->id,
            'password_hash' => bcrypt($input),
            'success' => $valid
        ]);

        // If this is an AJAX / XHR request, return JSON so the frontend can handle it directly
        if ($req->ajax() || $req->wantsJson() || $req->header('X-Requested-With') === 'XMLHttpRequest') {
            if ($valid) {
                return response()->json(['success' => true], 200);
            }

            return response()->json(['error' => 'Mot de passe incorrect'], 422);
        }

        // Fallback for non-AJAX requests (normal form submit)
        if ($valid) {
            return back()->with('flash', ['success' => true]);
        } else {
            return back()->with('flash', ['error' => 'Mot de passe incorrect']);
        }
    }

private function checkConstraints($input, $constraints)
{
    switch ($constraints) {
        // 🔹 Niveau 1
        case "Entrer une lettre minuscule":
            return preg_match('/[a-z]/', $input);

        case "Entrer une lettre majuscule":
            return preg_match('/[A-Z]/', $input);

        case "Entrer un chiffre":
            return preg_match('/[0-9]/', $input);

        case "Entrer deux chiffres":
            return preg_match_all('/[0-9]/', $input) >= 2;

        case "Entrer une lettre + un chiffre":
            return preg_match('/[A-Za-z]/', $input) && preg_match('/[0-9]/', $input);

        case "Entrer deux lettres + un chiffre":
            return preg_match_all('/[A-Za-z]/', $input) >= 2 && preg_match('/[0-9]/', $input);

        case "Entrer un chiffre + un caractère spécial":
            return preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Entrer deux lettres + un caractère spécial":
            return preg_match_all('/[A-Za-z]/', $input) >= 2 && preg_match('/[^A-Za-z0-9]/', $input);

        case "Entrer trois lettres":
            return preg_match_all('/[A-Za-z]/', $input) >= 3;

        case "Entrer trois chiffres":
            return preg_match_all('/[0-9]/', $input) >= 3;

        // 🔹 Niveau 2
        case "Entrer une lettre majuscule + une minuscule":
            return preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input);

        case "Entrer une lettre + deux chiffres":
            return preg_match('/[A-Za-z]/', $input) && preg_match_all('/[0-9]/', $input) >= 2;

        case "Entrer trois chiffres + une lettre":
            return preg_match_all('/[0-9]/', $input) >= 3 && preg_match('/[A-Za-z]/', $input);

        case "Entrer une majuscule + un chiffre + un spécial":
            return preg_match('/[A-Z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Entrer quatre lettres (maj/min)":
            return preg_match_all('/[A-Za-z]/', $input) >= 4;

        case "Entrer deux lettres + deux chiffres":
            return preg_match_all('/[A-Za-z]/', $input) >= 2 && preg_match_all('/[0-9]/', $input) >= 2;

        // 🔹 Niveau 3
        case "Mot de 4 lettres minuscules":
            return preg_match('/^[a-z]{4}$/', $input);

        case "Mot de 4 lettres majuscules":
            return preg_match('/^[A-Z]{4}$/', $input);

        case "Mot de 4 chiffres":
            return preg_match('/^[0-9]{4}$/', $input);

        case "Une lettre + un chiffre + un spécial":
            return preg_match('/[A-Za-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Deux majuscules + deux chiffres":
            return preg_match_all('/[A-Z]/', $input) >= 2 && preg_match_all('/[0-9]/', $input) >= 2;

        case "Trois lettres + un spécial":
            return preg_match_all('/[A-Za-z]/', $input) >= 3 && preg_match('/[^A-Za-z0-9]/', $input);

        case "Mot de 5 caractères (lettres/chiffres)":
            return preg_match('/^[A-Za-z0-9]{5}$/', $input);

        case "Mot de 5 caractères (lettres/chiffres/spéciaux)":
            return strlen($input) === 5 &&
                   preg_match('/[A-Za-z]/', $input) &&
                   preg_match('/[0-9]/', $input) &&
                   preg_match('/[^A-Za-z0-9]/', $input);

        // 🔹 Niveau 4
        case "6 lettres uniquement":
            return preg_match('/^[A-Za-z]{6}$/', $input);

        case "6 chiffres uniquement":
            return preg_match('/^[0-9]{6}$/', $input);

        case "6 lettres + chiffres":
            return strlen($input) === 6 &&
                   preg_match('/[A-Za-z]/', $input) &&
                   preg_match('/[0-9]/', $input);

        case "6 lettres + spéciaux":
            return strlen($input) === 6 &&
                   preg_match('/[A-Za-z]/', $input) &&
                   preg_match('/[^A-Za-z0-9]/', $input);

        case "6 lettres + chiffres + spéciaux":
            return strlen($input) === 6 &&
                   preg_match('/[A-Za-z]/', $input) &&
                   preg_match('/[0-9]/', $input) &&
                   preg_match('/[^A-Za-z0-9]/', $input);

        case "Au moins 1 majuscule + 1 chiffre":
            return preg_match('/[A-Z]/', $input) && preg_match('/[0-9]/', $input);

        case "Au moins 1 minuscule + 1 spécial":
            return preg_match('/[a-z]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Au moins 1 majuscule + 1 minuscule + 1 chiffre":
            return preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input);

        // 🔹 Niveau 5
        case "Mot de 7 caractères (lettres/chiffres)":
            return preg_match('/^[A-Za-z0-9]{7}$/', $input);

        case "Mot de 7 caractères (lettres/chiffres/spéciaux)":
            return strlen($input) === 7 &&
                   preg_match('/[A-Za-z]/', $input) &&
                   preg_match('/[0-9]/', $input) &&
                   preg_match('/[^A-Za-z0-9]/', $input);

        case "Au moins 2 majuscules + 2 chiffres":
            return preg_match_all('/[A-Z]/', $input) >= 2 && preg_match_all('/[0-9]/', $input) >= 2;

        case "Au moins 2 minuscules + 2 spéciaux":
            return preg_match_all('/[a-z]/', $input) >= 2 && preg_match_all('/[^A-Za-z0-9]/', $input) >= 2;

        case "Mot de 8 caractères (mix complet)":
            return strlen($input) === 8 &&
                   preg_match('/[A-Z]/', $input) &&
                   preg_match('/[a-z]/', $input) &&
                   preg_match('/[0-9]/', $input) &&
                   preg_match('/[^A-Za-z0-9]/', $input);

        case "Au moins 1 majuscule + 1 minuscule + 1 chiffre + 1 spécial":
            return preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Mot de 8 caractères avec 3 types différents":
            return strlen($input) === 8 &&
                   ((preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input)) ||
                    (preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[^A-Za-z0-9]/', $input)) ||
                    (preg_match('/[A-Z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input)) ||
                    (preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input)));

        case "Mot de 8 caractères avec 4 types différents":
            return strlen($input) === 8 &&
                   preg_match('/[A-Z]/', $input) &&
                   preg_match('/[a-z]/', $input) &&
                   preg_match('/[0-9]/', $input) &&
                   preg_match('/[^A-Za-z0-9]/', $input);

        // 🔹 Niveau 6 (9 caractères)
        case "Mot de 9 caractères avec maj/min/chiffres/spéciaux":
            return strlen($input) === 9 && preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Au moins 2 majuscules + 2 chiffres + 1 spécial":
            return preg_match_all('/[A-Z]/', $input) >= 2 && preg_match_all('/[0-9]/', $input) >= 2 && preg_match('/[^A-Za-z0-9]/', $input) >= 1;

        case "Mot de 9 caractères avec 3 types différents":
            if (strlen($input) !== 9) return false;
            $types = 0;
            $types += preg_match('/[A-Z]/', $input) ? 1 : 0;
            $types += preg_match('/[a-z]/', $input) ? 1 : 0;
            $types += preg_match('/[0-9]/', $input) ? 1 : 0;
            $types += preg_match('/[^A-Za-z0-9]/', $input) ? 1 : 0;
            return $types >= 3;

        case "Mot de 9 caractères avec 4 types différents":
            return strlen($input) === 9 && preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Mot de 9 caractères avec séquence de chiffres":
            return strlen($input) === 9 && preg_match('/[0-9]{3,}/', $input);

        case "Mot de 9 caractères avec séquence de lettres":
            return strlen($input) === 9 && preg_match('/[A-Za-z]{3,}/', $input);

        case "Mot de 9 caractères avec séquence de spéciaux":
            return strlen($input) === 9 && preg_match('/[^A-Za-z0-9]{2,}/', $input);

        case "Mot de 9 caractères mix complet":
            return strlen($input) === 9 && preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        // 🔹 Niveau 7 (10 caractères)
        case "Mot de 10 caractères avec maj/min/chiffres/spéciaux":
            return strlen($input) === 10 && preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Au moins 3 majuscules + 3 chiffres":
            return preg_match_all('/[A-Z]/', $input) >= 3 && preg_match_all('/[0-9]/', $input) >= 3;

        case "Mot de 10 caractères avec 2 spéciaux":
            return strlen($input) === 10 && preg_match_all('/[^A-Za-z0-9]/', $input) >= 2;

        case "Mot de 10 caractères avec séquence imposée":
            // Require at least one sequence of 3 digits or 3 letters
            if (strlen($input) !== 10) return false;
            if (preg_match('/[0-9]{3,}/', $input)) return true;
            if (preg_match('/[A-Za-z]{3,}/', $input)) return true;
            return false;

        case "Mot de 10 caractères avec mix complet":
            return strlen($input) === 10 && preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Mot de 10 caractères avec ordre imposé":
            // letters then digits then specials (in that order)
            return strlen($input) === 10 && preg_match('/^[A-Za-z]+[0-9]+[^A-Za-z0-9]+$/', $input);

        case "Mot de 10 caractères avec 4 types différents":
            return strlen($input) === 10 && preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Mot de 10 caractères avec contraintes avancées":
            // at least 2 upper, 2 lower, 2 digits, 1 special
            return strlen($input) === 10 && preg_match_all('/[A-Z]/', $input) >= 2 && preg_match_all('/[a-z]/', $input) >= 2 && preg_match_all('/[0-9]/', $input) >= 2 && preg_match_all('/[^A-Za-z0-9]/', $input) >= 1;

        // 🔹 Niveau 8 (11 caractères)
        case "Mot de 11 caractères avec maj/min/chiffres/spéciaux":
            return strlen($input) === 11 && preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Au moins 3 majuscules + 3 chiffres + 2 spéciaux":
            return preg_match_all('/[A-Z]/', $input) >= 3 && preg_match_all('/[0-9]/', $input) >= 3 && preg_match_all('/[^A-Za-z0-9]/', $input) >= 2;

        case "Mot de 11 caractères avec séquence imposée":
            return strlen($input) === 11 && (preg_match('/[0-9]{3,}/', $input) || preg_match('/[A-Za-z]{3,}/', $input));

        case "Mot de 11 caractères avec mix complet":
            return strlen($input) === 11 && preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Mot de 11 caractères avec ordre imposé":
            return strlen($input) === 11 && preg_match('/^[A-Za-z]+[0-9]+[^A-Za-z0-9]+$/', $input);

        case "Mot de 11 caractères avec 4 types différents":
            return strlen($input) === 11 && preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Mot de 11 caractères avec contraintes avancées":
            return strlen($input) === 11 && preg_match_all('/[A-Z]/', $input) >= 2 && preg_match_all('/[a-z]/', $input) >= 2 && preg_match_all('/[0-9]/', $input) >= 2 && preg_match_all('/[^A-Za-z0-9]/', $input) >= 1;

        case "Mot de 11 caractères avec séquence de lettres/chiffres":
            return strlen($input) === 11 && (preg_match('/[A-Za-z]{3,}/', $input) || preg_match('/[0-9]{3,}/', $input));

        // 🔹 Niveau 9 (12 caractères)
        case "Mot de 12 caractères avec maj/min/chiffres/spéciaux":
            return strlen($input) === 12 && preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Au moins 4 majuscules + 4 chiffres":
            return preg_match_all('/[A-Z]/', $input) >= 4 && preg_match_all('/[0-9]/', $input) >= 4;

        case "Mot de 12 caractères avec 3 spéciaux":
            return strlen($input) === 12 && preg_match_all('/[^A-Za-z0-9]/', $input) >= 3;

        case "Mot de 12 caractères avec séquence imposée":
            return strlen($input) === 12 && (preg_match('/[0-9]{4,}/', $input) || preg_match('/[A-Za-z]{4,}/', $input));

        case "Mot de 12 caractères avec mix complet":
            return strlen($input) === 12 && preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Mot de 12 caractères avec ordre imposé":
            return strlen($input) === 12 && preg_match('/^[A-Za-z]+[0-9]+[^A-Za-z0-9]+$/', $input);

        case "Mot de 12 caractères avec contraintes avancées":
            return strlen($input) === 12 && preg_match_all('/[A-Z]/', $input) >= 2 && preg_match_all('/[a-z]/', $input) >= 2 && preg_match_all('/[0-9]/', $input) >= 2 && preg_match_all('/[^A-Za-z0-9]/', $input) >= 2;

        case "Mot de 12 caractères avec séquence complexe":
            return strlen($input) === 12 && (preg_match('/[A-Za-z]{4,}/', $input) || preg_match('/[0-9]{4,}/', $input));

        // 🔹 Niveau 10 (13 caractères)
        case "Mot de 13 caractères avec maj/min/chiffres/spéciaux":
            return strlen($input) === 13 && preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Au moins 4 majuscules + 4 chiffres + 2 spéciaux":
            return preg_match_all('/[A-Z]/', $input) >= 4 && preg_match_all('/[0-9]/', $input) >= 4 && preg_match_all('/[^A-Za-z0-9]/', $input) >= 2;

        case "Mot de 13 caractères avec séquence imposée":
            return strlen($input) === 13 && (preg_match('/[0-9]{4,}/', $input) || preg_match('/[A-Za-z]{4,}/', $input));

        case "Mot de 13 caractères avec mix complet":
            return strlen($input) === 13 && preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Mot de 13 caractères avec ordre imposé":
            return strlen($input) === 13 && preg_match('/^[A-Za-z]+[0-9]+[^A-Za-z0-9]+$/', $input);

        case "Mot de 13 caractères avec 4 types différents":
            return strlen($input) === 13 && preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Mot de 13 caractères avec contraintes avancées":
            return strlen($input) === 13 && preg_match_all('/[A-Z]/', $input) >= 2 && preg_match_all('/[a-z]/', $input) >= 2 && preg_match_all('/[0-9]/', $input) >= 2 && preg_match_all('/[^A-Za-z0-9]/', $input) >= 2;

        case "Mot de 13 caractères avec séquence complexe":
            return strlen($input) === 13 && (preg_match('/[A-Za-z]{4,}/', $input) || preg_match('/[0-9]{4,}/', $input));

        // 🔹 Niveau 11 (14 caractères)
        case "Mot de 14 caractères avec maj/min/chiffres/spéciaux":
            return strlen($input) === 14 && preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Au moins 5 majuscules + 5 chiffres":
            return preg_match_all('/[A-Z]/', $input) >= 5 && preg_match_all('/[0-9]/', $input) >= 5;

        case "Mot de 14 caractères avec 3 spéciaux":
            return strlen($input) === 14 && preg_match_all('/[^A-Za-z0-9]/', $input) >= 3;

        case "Mot de 14 caractères avec séquence imposée":
            return strlen($input) === 14 && (preg_match('/[0-9]{4,}/', $input) || preg_match('/[A-Za-z]{4,}/', $input));

        case "Mot de 14 caractères avec mix complet":
            return strlen($input) === 14 && preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Mot de 14 caractères avec ordre imposé":
            return strlen($input) === 14 && preg_match('/^[A-Za-z]+[0-9]+[^A-Za-z0-9]+$/', $input);

        case "Mot de 14 caractères avec contraintes avancées":
            return strlen($input) === 14 && preg_match_all('/[A-Z]/', $input) >= 2 && preg_match_all('/[a-z]/', $input) >= 2 && preg_match_all('/[0-9]/', $input) >= 2 && preg_match_all('/[^A-Za-z0-9]/', $input) >= 2;

        case "Mot de 14 caractères avec séquence complexe":
            return strlen($input) === 14 && (preg_match('/[A-Za-z]{4,}/', $input) || preg_match('/[0-9]{4,}/', $input));

        // 🔹 Niveau 12 (15 caractères)
        case "Mot de 15 caractères final avec maj/min/chiffres/spéciaux":
            return strlen($input) === 15 && preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Au moins 5 majuscules + 5 chiffres + 3 spéciaux":
            return preg_match_all('/[A-Z]/', $input) >= 5 && preg_match_all('/[0-9]/', $input) >= 5 && preg_match_all('/[^A-Za-z0-9]/', $input) >= 3;

        case "Mot de 15 caractères avec séquence imposée":
            return strlen($input) === 15 && (preg_match('/[0-9]{4,}/', $input) || preg_match('/[A-Za-z]{4,}/', $input));

        case "Mot de 15 caractères avec mix complet":
            return strlen($input) === 15 && preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Mot de 15 caractères avec ordre imposé":
            return strlen($input) === 15 && preg_match('/^[A-Za-z]+[0-9]+[^A-Za-z0-9]+$/', $input);

        case "Mot de 15 caractères avec 4 types différents":
            return strlen($input) === 15 && preg_match('/[A-Z]/', $input) && preg_match('/[a-z]/', $input) && preg_match('/[0-9]/', $input) && preg_match('/[^A-Za-z0-9]/', $input);

        case "Mot de 15 caractères avec contraintes avancées":
            return strlen($input) === 15 && preg_match_all('/[A-Z]/', $input) >= 2 && preg_match_all('/[a-z]/', $input) >= 2 && preg_match_all('/[0-9]/', $input) >= 2 && preg_match_all('/[^A-Za-z0-9]/', $input) >= 3;

        case "Mot de 15 caractères ultime":
            // Ultime = très strict : min 3 upper, 3 lower, 3 digits, 2 specials
            return strlen($input) === 15 && preg_match_all('/[A-Z]/', $input) >= 3 && preg_match_all('/[a-z]/', $input) >= 3 && preg_match_all('/[0-9]/', $input) >= 3 && preg_match_all('/[^A-Za-z0-9]/', $input) >= 2;

            default:
                return false;
        }
    }
}
