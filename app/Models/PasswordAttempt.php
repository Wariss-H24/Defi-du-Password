<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'level_id',
        'step_id',
        'password_hash',
        'success',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function step()
    {
        return $this->belongsTo(Step::class);
    }
}
