<?php

namespace App\Models;

use App\Exceptions\HostNotSetException;
use App\Observers\GameObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'data',
        'max_players',
        'host_id'
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

    public static function firstAvailableOrCreate(array $attributes = []): Game
    {
        if (!array_key_exists('host_id', $attributes) || is_null($attributes['host_id'])) {
            throw new HostNotSetException();
        }

        $game = Game::has('users', '<', 'game.max_players')->orderBy('created_at', 'desc')->first();

        if (is_null($game)) {
            // no games found create one
            $game = Game::create($attributes);
            $game->users()->attach($attributes['host_id']);
        }

        return $game;
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

    public function setRandomHost()
    {
        $host = $this->users()->inRandomOrder()->first();
        if (null != $host) {
            $this->host()->associate($host);
            $this->save();
        }
    }
}
