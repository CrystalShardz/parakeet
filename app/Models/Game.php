<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'data'
    ];
    protected $casts = [
        'data' => 'array'
    ];

    public $keyType = 'string';
    public $incrementing = false;

    public static function boot()
    {
        parent::boot();
        parent::creating(function (Game $game) {
            $game->id = Str::uuid()->toString();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
