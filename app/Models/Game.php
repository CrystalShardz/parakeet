<?php

namespace App\Models;

use App\Observers\GameObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'data',
        'max_players'
    ];
    protected $casts = [
        'data' => 'array'
    ];

    public $keyType = 'string';
    public $incrementing = false;

    public static function boot()
    {
        parent::boot();
        parent::observe(GameObserver::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->using(GameUser::class);
    }

    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    /**
     * Checks if the game is full
     *
     * @return boolean
     */
    public function isFull(): bool
    {
        return $this->users->count() >= $this->max_players;
    }
}
