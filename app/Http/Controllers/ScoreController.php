<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Score;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class ScoreController extends Controller
{
    public static function store(Request $request){
        $validation = Validator::make($request->all(), [
            'user' => 'required|exists:users,id',
            'level' => 'required|integer|max:20',
            'score' => 'required|min:0',
            'max_score' => 'required|min:0',
            'type' => 'required|in:1,2,3'
        ]);

        if($validation->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'The payload is not formatted correctly',
                'errors' => $validation->errors()
            ], 201);
        }

        $data = $validation->validated();

        $existingScore = Score::where('user', $data['user'])
            ->where('level', $data['level'])
            ->first();

        if ($existingScore && $existingScore->score < $data['score']) {
            $existingScore->score = $data['score'];
            $existingScore->save();

            return response()->json([
                'status' => 200,
                'message' => 'Score updated successfully.'
            ], 200);
        } else if($existingScore && $existingScore->score >= $data['score']){

            return response()->json([
                'status' => 201,
                'message' => 'Score stays the same.'
            ], 200);

        }else{
            Score::create($data);

            return response()->json([
                'status' => 201,
                'message' => 'Score successfully saved.'
            ], 201);
        }
    }

    public static function scores($user){
        $scores = Score::where('user', $user)->get();

        $maxLevel = 1; // Start with level 1
        $processedScores = $scores->map(function ($score) use (&$maxLevel) {
            $maxScore = $score->max_score;
            $stars = floor(($score->score / $maxScore) * 5);

            if ($score->type == 3) {
                $completed = $stars >= 3;
            } else {
                $completed = $stars == 5;
            }

            if ($completed) {
                $maxLevel = $maxLevel + 1;
            }

            return [
                'score_id' => $score->id,
                'score' => $score->score,
                'max_score' => $maxScore,
                'stars' => $stars,
                'completed' => $completed,
                'level' => $score->level
            ];
        });


        if($maxLevel > 20){
            $maxLevel = 20;
        }

        return response()->json([
            'scores' => $processedScores,
            'max_level' => $maxLevel,
        ]);
    }

    public static function allScores(){
        return response()->json([
            'scores' => Score::with('user')->get()
        ]);
    }

}
