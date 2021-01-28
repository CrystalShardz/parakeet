<?php

namespace App\Http\Requests;

use App\Models\Game;
use Illuminate\Foundation\Http\FormRequest;

class JoinGameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $game = Game::findOrFail($this->get('game'));
        return [
            'game' => 'required|exists:games,id',
            'seat' => 'sometimes|numeric|min:1|max:' . $game->max_players
        ];
    }
}
