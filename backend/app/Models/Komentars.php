<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
 
class Komentars extends Model
{
    protected $table      = 'komentars';
    protected $primaryKey = 'id_komentars';
 
    protected $fillable = [
        'id_raksts',
        'id_lietotajs',
        'parent_id',
        'teksts',
        'rating_score',
    ];
 
    public $timestamps = false;
 
    protected $dates = ['datums'];
 
    public function lietotajs()
    {
        return $this->belongsTo(Lietotajs::class, 'id_lietotajs', 'id_lietotajs');
    }
 
    public function raksts()
    {
        return $this->belongsTo(Raksts::class, 'id_raksts', 'id_raksts');
    }
 
    public function replies()
    {
        return $this->hasMany(Komentars::class, 'parent_id', 'id_komentars');
    }
 
    public function parent()
    {
        return $this->belongsTo(Komentars::class, 'parent_id', 'id_komentars');
    }
 
    public function balsojumi()
    {
        return $this->hasMany(BalsojumsKomentars::class, 'id_komentars', 'id_komentars');
    }
}
 