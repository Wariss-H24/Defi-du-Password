<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    /** @use HasFactory<\Database\Factories\GameFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'current_level_id',
        'current_step_id',
        'status',
        'progress',
    ];

     // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currentLevel()
    {
        return $this->belongsTo(Level::class, 'current_level_id');
    }

    public function currentStep()
    {
        return $this->belongsTo(Step::class, 'current_step_id');
    }
}
