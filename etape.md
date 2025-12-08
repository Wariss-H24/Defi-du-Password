# Étapes pour tester / modifier le jeu (quick-start)

Ce fichier décrit pas à pas ce que faire pour lancer, tester et modifier le jeu.

## 1) Préparer l'environnement (première installation)
1. Installer les dépendances PHP (si nécessaire) :
   - `composer install`
2. Installer Node / npm si ce n'est pas déjà fait.
3. Installer les dépendances frontend :
```powershell
npm install
```

## 2) Générer les assets Vite (production / dev)
- Pour un build production (génère `public/build/manifest.json`) :
```powershell
npm run build
```
- Pour le développement (hot reload) :
```powershell
npm run dev
```

## 3) Préparer la base de données
- Configurer `.env` (connexion MySQL / SQLite selon ton choix).
- Pour une installation propre (développement) :
```powershell
php artisan migrate:fresh --seed
```
  - Ceci recrée les tables et exécute le seeder qui crée les niveaux et étapes.
- Si tu veux juste lancer les migrations nouvelles :
```powershell
php artisan migrate
php artisan db:seed
```

## 4) Lancer le serveur Laravel
```powershell
php artisan serve
```
- L'application sera disponible par défaut sur `http://localhost:8000`.

## 5) Tester le jeu
- Ouvrir dans le navigateur : `http://localhost:8000/play/1` pour commencer au niveau 1.
- Saisir la valeur demandée dans le champ et cliquer sur `Valider`.
- Vérifier :
  - Si la requête POST retourne `200` (JSON `{ success: true }`), l'étape suivante doit s'afficher.
  - Si la requête retourne `422` ou un JSON `{ error: '...' }`, le message d'erreur s'affiche et tu peux réessayer.

## 6) Débogage rapide
- Si la page renvoie une erreur liée à Vite (`manifest.json`) : exécute `npm run build`.
- Si la console du navigateur montre `route is not a function` : reconstruis les assets (`npm run build`) ou assure-toi que le backend fournit bien `validate_url` (contrôleur).
- Pour voir les logs Laravel :
```powershell
Get-Content .\storage\logs\laravel.log -Tail 200
# ou pour suivre en direct
Get-Content .\storage\logs\laravel.log -Wait
```
- Nettoyage caches :
```powershell
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
```

## 7) Modifier / ajouter des niveaux et étapes
- Option A (manuel via code) :
  - Modifier `database/seeders/DatabaseSeeder.php` : ajouter/éditer le tableau `constraints`.
  - Puis exécuter :
```powershell
php artisan migrate:fresh --seed
```
  - Avantage : méthode rapide en développement.

- Option B (via UI existante) :
  - Utiliser les routes et contrôleurs `LevelController` et `StepController` (CRUD présent dans le projet). Créer un niveau/étape depuis l'interface.

## 8) Rendre le seeder idempotent
- Pour éviter erreurs `Duplicate entry` : remplacer `create(...)` par `firstOrCreate([...], [...])` dans le seeder.

Exemple :
```php
Level::firstOrCreate([
    'name' => 'Niveau 1',
], ['description' => '...']);
```

## 9) Ajouter un mot de passe chiffré pour une étape
- Si tu veux stocker un mot de passe réversible (pour l'afficher ou le vérifier côté serveur) : utiliser `encrypt()` et `decrypt()` de Laravel.
- Exemple dans migration `steps` : ajouter une colonne `password_encrypted` (nullable, `text`). Lors de la création d'une étape :
```php
$step->password_encrypted = encrypt('leMotDePasse');
$step->save();
```
- Pour comparer côté serveur : `decrypt($step->password_encrypted)` et comparer.
- Si tu préfères ne pas stocker le mot en clair : hash avec `bcrypt()` et vérifier avec `Hash::check()`.

## 10) Commandes utiles
- Lancer tests (s'il y en a) :
```powershell
php artisan test
```
- Rebuild frontend :
```powershell
npm run build
```
- Serveur Laravel :
```powershell
php artisan serve
```

## 11) Conseils pour les prochaines modifications
- Toujours lancer `npm run build` après modifications de `resources/js` si tu n'utilises pas `npm run dev`.
- Lorsque tu ajoute une nouvelle contrainte dans le seeder (titre d'étape), ajoute le case correspondant dans `checkConstraints`.
- Lors d'un bug côté JS lié aux routes, vérifie si Ziggy est installé ou si le backend fournit directement les URLs.

---

Si tu veux, je peux ajouter un script `README.md` plus résumé ou intégrer ces fichiers dans la doc du repo. Dis‑moi si tu veux des versions en anglais ou plus détaillées par fichier (ex: `GameController.md`).