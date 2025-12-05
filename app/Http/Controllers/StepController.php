<?php

namespace App\Http\Controllers;

use App\Models\Step;
use App\Models\Level;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StepController extends Controller
{
    public function store(Request $req, $levelId)
    {
        Step::create([
            'level_id' => $levelId,
            'title' => $req->title,
            'constraints' => $req->constraints,
            'order' => $req->order ?? 1
        ]);

        return redirect()->route('levels.show', $levelId)
                         ->with('success', 'Étape ajoutée avec succès');
    }

    public function update(Request $req, $levelId, Step $step)
    {
        $step->update($req->only('title','constraints','order'));

        return redirect()->route('levels.show', $levelId)
                         ->with('success', 'Étape mise à jour');
    }

    

    public function destroy($levelId, Step $step)
    {
        $step->delete();

        return redirect()->route('levels.show', $levelId)
                         ->with('success', 'Étape supprimée');
    }
}
