<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Step extends Model
{
    /** @use HasFactory<\Database\Factories\StepFactory> */
    use HasFactory;

    
    protected $fillable = ['level_id','title','constraints','order'];
    protected $casts = ['constraints' => 'array'];
    public function level() {
        return $this->belongsTo(Level::class);
    
}
}
