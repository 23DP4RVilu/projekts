<?php
 
namespace App\Http\Controllers;
 
use App\Models\Raksts;
use App\Models\Log;
use Illuminate\Http\Request;
 
class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Raksts::with('lietotajs')
            ->withCount('komentari');
 
        if ($request->has('kategorija') && $request->kategorija !== 'Visi') {
            $query->where('kategorija', $request->kategorija);
        }
 
        if ($request->has('sort')) {
            if ($request->sort === 'top') {
                $query->orderBy('rating_score', 'desc');
            } elseif ($request->sort === 'new') {
                $query->orderBy('datums', 'desc');
            }
        } else {
            $query->orderBy('datums', 'desc');
        }
 
        $posts = $query->paginate(20);
 
        if ($request->user('sanctum')) {
            $userId = $request->user('sanctum')->id_lietotajs;
            $posts->getCollection()->transform(function ($post) use ($userId) {
                $vote = \App\Models\BalsojumsRaksts::where('id_lietotajs', $userId)
                    ->where('id_raksts', $post->id_raksts)
                    ->first();
                $post->user_vote = $vote ? $vote->tips : 0;
                return $post;
            });
        }
 
        return response()->json($posts);
    }
 
    public function show(Request $request, $id)
    {
        $post = Raksts::with('lietotajs')->withCount('komentari')->findOrFail($id);
 
        if ($request->user('sanctum')) {
            $userId = $request->user('sanctum')->id_lietotajs;
            $vote = \App\Models\BalsojumsRaksts::where('id_lietotajs', $userId)
                ->where('id_raksts', $post->id_raksts)
                ->first();
            $post->user_vote = $vote ? $vote->tips : 0;
        }
 
        return response()->json($post);
    }
 
    public function store(Request $request)
    {
        $request->validate([
            'virsraksts' => 'required|string|max:300',
            'teksts'     => 'required|string',
            'kategorija' => 'required|in:Dziesma,Albums,Recenzija,Zinas',
        ]);
 
        $post = Raksts::create([
            'id_lietotajs' => $request->user()->id_lietotajs,
            'virsraksts'   => $request->virsraksts,
            'teksts'       => $request->teksts,
            'kategorija'   => $request->kategorija,
        ]);
 
        Log::create([
            'id_lietotajs' => $request->user()->id_lietotajs,
            'darbiba'      => 'Izveidots raksts: ' . $post->virsraksts,
            'tabula'       => 'raksts',
        ]);
 
        return response()->json($post->load('lietotajs'), 201);
    }
 
    public function update(Request $request, $id)
    {
        $post = Raksts::findOrFail($id);
 
        if ($post->id_lietotajs !== $request->user()->id_lietotajs && $request->user()->loma !== 'Admin') {
            return response()->json(['message' => 'Nav atļauts.'], 403);
        }
 
        $request->validate([
            'virsraksts' => 'sometimes|string|max:300',
            'teksts'     => 'sometimes|string',
            'kategorija' => 'sometimes|in:Dziesma,Albums,Recenzija,Zinas',
        ]);
 
        $post->update($request->only(['virsraksts', 'teksts', 'kategorija']));
 
        Log::create([
            'id_lietotajs' => $request->user()->id_lietotajs,
            'darbiba'      => 'Rediģēts raksts ID: ' . $id,
            'tabula'       => 'raksts',
        ]);
 
        return response()->json($post->load('lietotajs'));
    }
 
    public function destroy(Request $request, $id)
    {
        $post = Raksts::findOrFail($id);
 
        if ($post->id_lietotajs !== $request->user()->id_lietotajs && $request->user()->loma !== 'Admin') {
            return response()->json(['message' => 'Nav atļauts.'], 403);
        }
 
        $post->delete();
 
        Log::create([
            'id_lietotajs' => $request->user()->id_lietotajs,
            'darbiba'      => 'Dzēsts raksts ID: ' . $id,
            'tabula'       => 'raksts',
        ]);
 
        return response()->json(['message' => 'Raksts dzēsts.']);
    }
}
 