<?php
 
namespace App\Http\Controllers;
 
use App\Models\Komentars;
use App\Models\Log;
use Illuminate\Http\Request;
 
class CommentController extends Controller
{
    public function index(Request $request, $postId)
    {
        $comments = Komentars::with('lietotajs')
            ->where('id_raksts', $postId)
            ->orderBy('datums', 'asc')
            ->get();
 
        if ($request->user('sanctum')) {
            $userId = $request->user('sanctum')->id_lietotajs;
            $comments->transform(function ($comment) use ($userId) {
                $vote = \App\Models\BalsojumsKomentars::where('id_lietotajs', $userId)
                    ->where('id_komentars', $comment->id_komentars)
                    ->first();
                $comment->user_vote = $vote ? $vote->tips : 0;
                return $comment;
            });
        }
 
        $tree = $this->buildTree($comments->toArray());
 
        return response()->json($tree);
    }
 
    private function buildTree(array $comments, $parentId = null)
    {
        $branch = [];
        foreach ($comments as $comment) {
            if ($comment['parent_id'] == $parentId) {
                $children = $this->buildTree($comments, $comment['id_komentars']);
                if ($children) {
                    $comment['replies'] = $children;
                } else {
                    $comment['replies'] = [];
                }
                $branch[] = $comment;
            }
        }
        return $branch;
    }
 
    public function store(Request $request, $postId)
    {
        $request->validate([
            'teksts'    => 'required|string',
            'parent_id' => 'nullable|exists:komentars,id_komentars',
        ]);
 
        $comment = Komentars::create([
            'id_raksts'    => $postId,
            'id_lietotajs' => $request->user()->id_lietotajs,
            'parent_id'    => $request->parent_id,
            'teksts'       => $request->teksts,
        ]);
 
        Log::create([
            'id_lietotajs' => $request->user()->id_lietotajs,
            'darbiba'      => 'Pievienots komentārs rakstam ID: ' . $postId,
            'tabula'       => 'komentars',
        ]);
 
        return response()->json($comment->load('lietotajs'), 201);
    }
 
    public function update(Request $request, $id)
    {
        $comment = Komentars::findOrFail($id);
 
        if ($comment->id_lietotajs !== $request->user()->id_lietotajs && $request->user()->loma !== 'Admin') {
            return response()->json(['message' => 'Nav atļauts.'], 403);
        }
 
        $request->validate(['teksts' => 'required|string']);
        $comment->update(['teksts' => $request->teksts]);
 
        Log::create([
            'id_lietotajs' => $request->user()->id_lietotajs,
            'darbiba'      => 'Rediģēts komentārs ID: ' . $id,
            'tabula'       => 'komentars',
        ]);
 
        return response()->json($comment->load('lietotajs'));
    }
 
    public function destroy(Request $request, $id)
    {
        $comment = Komentars::findOrFail($id);
 
        if ($comment->id_lietotajs !== $request->user()->id_lietotajs && $request->user()->loma !== 'Admin') {
            return response()->json(['message' => 'Nav atļauts.'], 403);
        }
 
        $comment->delete();
 
        Log::create([
            'id_lietotajs' => $request->user()->id_lietotajs,
            'darbiba'      => 'Dzēsts komentārs ID: ' . $id,
            'tabula'       => 'komentars',
        ]);
 
        return response()->json(['message' => 'Komentārs dzēsts.']);
    }
}