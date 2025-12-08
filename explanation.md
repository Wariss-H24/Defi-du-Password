# Explication du projet "Defi-du-Password"

Ce document explique l'architecture et le fonctionnement du jeu de mots de passe dans ce dépôt Laravel + Inertia + Vue3.

## Vue d'ensemble
- Stack : Laravel (backend) + Inertia + Vue 3 (frontend) + Vite.
- But : un jeu composé de niveaux (`Level`) et d'étapes (`Step`). Chaque étape a une contrainte (règle) que l'utilisateur doit satisfaire en entrant un mot de passe.
- Les règles sont vérifiées côté serveur (méthode `checkConstraints`), les tentatives sont enregistrées dans `password_attempts`.

## Fichiers clés
- `routes/web.php`
  - Définit les routes du jeu :
    - `GET /play/{level}` → `GameController@play`
    - `POST /play/{level}/step/{step}` → `GameController@validateStep`

- `app/Http/Controllers/GameController.php`
  - `play(Level $level)` :
    - Charge le `Level` avec ses `steps` et ajoute pour chaque étape la propriété `validate_url` (URL de la requête POST pour valider l'étape).
    - Génère `nextLevelUrl` (URL du niveau suivant) et renvoie la page Inertia `Game` avec ces props.
  - `validateStep(Request $req, Level $level, Step $step)` :
    - Lit `$input = $req->password` et `$constraints = $step->constraints`.
    - Appelle `checkConstraints($input, $constraints)` pour vérifier si l'entrée satisfait la règle.
    - Enregistre la tentative dans la table `password_attempts` via le modèle `PasswordAttempt`.
    - Si la requête est AJAX/XHR, renvoie JSON `{ success: true }` (200) ou `{ error: '...'} ` (422) pour faciliter la gestion côté client.
    - Si non-AJAX, utilise le fallback `back()->with('flash', ...)`.
    - Un `Log::info()` a été ajouté pour déboguer les entrées reçues (utile pour reproduire et corriger les problèmes d'encodage/spaces).
  - `checkConstraints($input, $constraints)` :
    - Contient un gros `switch` avec toutes les règles (niveaux 1 à 12). Chaque case applique une expression régulière / conditions PHP pour valider l'entrée.
    - Si une nouvelle règle est ajoutée dans le seeder, il faut ajouter le cas correspondant ici.

- `app/Models/Level.php` et `app/Models/Step.php`
  - `Level` a une relation `hasMany('steps')`.
  - `Step` contient au moins : `level_id`, `title`, `constraints`, `order`, `password` (optionnel), etc.

- `app/Models/PasswordAttempt.php`
  - Modèle créé pour stocker les tentatives : `user_id`, `level_id`, `step_id`, `password_hash`, `success`, timestamps.

- Migration :
  - `database/migrations/*_create_password_attempts_table.php` : crée la table `password_attempts`.

- `resources/js/pages/Game.vue`
  - Reçoit les props Inertia : `level` (avec `steps`) et `nextLevelUrl`.
  - Gère l'affichage de l'étape courante et une barre de progression.
  - Lors de la validation :
    - Envoie une requête POST sur `currentStep.validate_url` (fourni par le contrôleur) via `router.post(...)` d'Inertia.
    - Le traitement de la réponse (onSuccess/onError) lit les props renvoyées (ou la réponse JSON) et met à jour l'interface : passe à l'étape suivante, affiche un message d'erreur, ou redirige vers `props.nextLevelUrl` si le niveau est terminé.
  - Remarques : le code a été ajusté pour ne plus appeler `router.route(...)` (fonction Ziggy) — maintenant le backend fournit les URLs directement.

- `database/seeders/DatabaseSeeder.php`
  - Contient un tableau `constraints` (12 niveaux × 8 étapes) et crée automatiquement les `Level` et `Step`.
  - Crée aussi un utilisateur de test (`test@example.com`).

## Flux d'une validation (schéma simplifié)
1. L'utilisateur saisit une valeur dans `Game.vue` et clique "Valider".
2. Le frontend envoie POST vers `currentStep.validate_url` (ex: `/play/1/step/1`) avec `{ password: '...' }`.
3. `GameController::validateStep` vérifie la valeur avec `checkConstraints`.
4. La tentative est enregistrée dans `password_attempts`.
5. Si OK, la réponse renvoie un succès ; sinon une erreur. Le frontend met à jour l'UI et passe à l'étape suivante en cas de succès.

## Points de vigilance / erreurs fréquentes et résolutions
- `ViteManifestNotFoundException` → lancer `npm install` puis `npm run build` pour générer `public/build/manifest.json`.
- `TypeError: route is not a function` côté JS → provient d'un appel à `router.route(...)` sans Ziggy; la solution utilisée ici est de faire générer l'URL côté backend (`$step->validate_url`) ou intégrer Ziggy correctement.
- `Class "App\Models\PasswordAttempt" not found` → créer le modèle `PasswordAttempt` et la migration, puis exécuter `php artisan migrate`.
- Doublons pendant le seeder → utiliser `firstOrCreate` / `updateOrCreate` au lieu de `create`, ou exécuter `php artisan migrate:fresh --seed` en développement (perd les données).
- Pour debugger côté serveur : consulter `storage/logs/laravel.log` (les `Log::info()` ajoutés apparaîtront ici).

## Améliorations possibles
- Persistance de la progression par utilisateur (`user_game_progress`) pour reprendre le niveau en cas de reconnexion.
- Messages d'erreur plus précis côté frontend (indiquer exactement pourquoi la règle a échoué).
- Réimplémenter Ziggy si tu veux utiliser `route('name')` côté JS.
- Rendre certaines règles configurables (stocker un type de règle et des paramètres au lieu d'un long switch).

---

Si tu veux une copie imprimable plus détaillée d'une classe particulière (ex: expliquer uniquement `checkConstraints`), dis‑moi laquelle et je la génère.
