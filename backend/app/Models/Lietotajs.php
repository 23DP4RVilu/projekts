<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
 
class Lietotajs extends Authenticatable
{
    use HasApiTokens;
 
    protected $table      = 'lietotajs';
    protected $primaryKey = 'id_lietotajs';
    public $timestamps    = false;
 
    protected $fillable = [
        'lietotajvards',
        'e_pasts',
        'parole',
        'loma',
    ];
 
    protected $hidden = ['parole'];

    public function getAuthPassword()
    {
        return $this->parole;
    }
 
    public function raksti()
    {
        return $this->hasMany(Raksts::class, 'id_lietotajs', 'id_lietotajs');
    }
 
    public function komentari()
    {
        return $this->hasMany(Komentars::class, 'id_lietotajs', 'id_lietotajs');
    }
}
 