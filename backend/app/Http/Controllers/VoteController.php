<?php
 
namespace App\Http\Controllers;
 
use App\Models\BalsojumsRaksts;
use App\Models\BalsojumsKomentars;
use App\Models\Raksts;
use App\Models\Komentars;
use App\Models\Log;
use Illuminate\Http\Request;
 
class VoteController extends Controller
{
    public function votePost(Request $request, $id)
    {
        $request->validate(['tips' => 'required|in:-1,1']);
 
        $post = Raksts::findOrFail($id);
        $userId = $request->user()->id_lietotajs;
        $tips = (int) $request->tips;
 
        $existing = BalsojumsRaksts::where('id_lietotajs', $userId)
            ->where('id_raksts', $id)
            ->first();
 
        if ($existing) {
            if ($existing->tips === $tips) {
                $existing->delete();
                $post->decrement('rating_score', $tips);
                $newScore = $post->fresh()->rating_score;
                return response()->json(['rating_score' => $newScore, 'user_vote' => 0]);
            } else {
                $existing->update(['tips' => $tips]);
                $post->increment('rating_score', $tips * 2);
            }
        } else {
            BalsojumsRaksts::create([
                'id_lietotajs' => $userId,
                'id_raksts'    => $id,
                'tips'         => $tips,
            ]);
            $post->increment('rating_score', $tips);
        }
 
        Log::create([
            'id_lietotajs' => $userId,
            'darbiba'      => 'Balsoja par rakstu ID: ' . $id . ' tips: ' . $tips,
            'tabula'       => 'balsojums_raksts',
        ]);
 
        $newScore = $post->fresh()->rating_score;
        return response()->json(['rating_score' => $newScore, 'user_vote' => $tips]);
    }
 
    public function voteComment(Request $request, $id)
    {
        $request->validate(['tips' => 'required|in:-1,1']);
 
        $comment = Komentars::findOrFail($id);
        $userId = $request->user()->id_lietotajs;
        $tips = (int) $request->tips;
 
        $existing = BalsojumsKomentars::where('id_lietotajs', $userId)
            ->where('id_komentars', $id)
            ->first();
 
        if ($existing) {
            if ($existing->tips === $tips) {
                $existing->delete();
                $comment->decrement('rating_score', $tips);
                $newScore = $comment->fresh()->rating_score;
                return response()->json(['rating_score' => $newScore, 'user_vote' => 0]);
            } else {
                $existing->update(['tips' => $tips]);
                $comment->increment('rating_score', $tips * 2);
            }
        } else {
            BalsojumsKomentars::create([
                'id_lietotajs' => $userId,
                'id_komentars' => $id,
                'tips'         => $tips,
            ]);
            $comment->increment('rating_score', $tips);
        }
 
        Log::create([
            'id_lietotajs' => $userId,
            'darbiba'      => 'Balsoja par komentāru ID: ' . $id . ' tips: ' . $tips,
            'tabula'       => 'balsojums_komentars',
        ]);
 
        $newScore = $comment->fresh()->rating_score;
        return response()->json(['rating_score' => $newScore, 'user_vote' => $tips]);
    }
}