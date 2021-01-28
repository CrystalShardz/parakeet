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
        $rules = [
            'game' => 'sometimes|nullable|exists:games,id'
        ];

        $game = Game::find($this->get('game')) ?? null;

        if(!is_null($game)) {
            $rules['seat'] = 'sometimes|nullable|min:1|max:' . $game->max_players;
        }

        return $rules;
    }
}
