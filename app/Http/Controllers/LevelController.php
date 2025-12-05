<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LevelController extends Controller
{
    public function index()
    {
        // Charger tous les niveaux avec leurs étapes
        $levels = Level::with('steps')->get();

        // Renvoyer vers la page Vue "Levels" avec les données
        return Inertia::render('Levels', [
            'levels' => $levels
        ]);
    }


    public function create()
{
    return Inertia::render('LevelCreate');
}

    public function store(Request $req)
    {
        Level::create($req->only('name','description'));
        return redirect()->route('levels.index')->with('success', 'Niveau ajouté');
    }

    public function update(Request $req, Level $level)
    {
        $level->update($req->only('name','description'));
        return redirect()->route('levels.index')->with('success', 'Niveau mis à jour');
    }

public function show(Level $level)
{
    return Inertia::render('Game', [
        'level' => $level->load('steps')
    ]);
}


    public function destroy(Level $level)
    {
        $level->delete();
        return redirect()->route('levels.index')->with('success', 'Niveau supprimé');
    }
}
