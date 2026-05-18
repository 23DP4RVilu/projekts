<?php
 
namespace App\Http\Controllers;
 
use App\Models\Lietotajs;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
 
class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'lietotajvards' => 'required|string|max:50|unique:lietotajs,lietotajvards',
            'e_pasts'       => 'required|email|max:100|unique:lietotajs,e_pasts',
            'parole'        => 'required|string|min:6',
        ]);
 
        $user = Lietotajs::create([
            'lietotajvards' => $request->lietotajvards,
            'e_pasts'       => $request->e_pasts,
            'parole'        => Hash::make($request->parole),
        ]);
 
        Log::create([
            'id_lietotajs' => $user->id_lietotajs,
            'darbiba'      => 'Reģistrācija',
            'tabula'       => 'lietotajs',
        ]);
 
        $token = $user->createToken('auth_token')->plainTextToken;
 
        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }
 
    public function login(Request $request)
    {
        $request->validate([
            'lietotajvards' => 'required|string',
            'parole'        => 'required|string',
        ]);
 
        $user = Lietotajs::where('lietotajvards', $request->lietotajvards)->first();
 
        if (!$user || !Hash::check($request->parole, $user->parole)) {
            throw ValidationException::withMessages([
                'lietotajvards' => ['Nepareizs lietotājvārds vai parole.'],
            ]);
        }
 
        Log::create([
            'id_lietotajs' => $user->id_lietotajs,
            'darbiba'      => 'Pieslēgšanās',
            'tabula'       => 'lietotajs',
        ]);
 
        $token = $user->createToken('auth_token')->plainTextToken;
 
        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }
 
    public function logout(Request $request)
    {
        Log::create([
            'id_lietotajs' => $request->user()->id_lietotajs,
            'darbiba'      => 'Atslēgšanās',
            'tabula'       => 'lietotajs',
        ]);
 
        $request->user()->currentAccessToken()->delete();
 
        return response()->json(['message' => 'Veiksmīgi atslēgts.']);
    }
 
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}