<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Raksts extends Model
{
    protected $table      = 'raksts';
    protected $primaryKey = 'id_raksts';

    protected $fillable = [
        'id_lietotajs',
        'virsraksts',
        'teksts',
        'kategorija',
        'rating_score',
    ];

    public $timestamps = false;
 
    protected $dates = ['datums'];
    const CREATED_AT = 'datums';

    public function lietotajs()
    {
        return $this->belongsTo(Lietotajs::class, 'id_lietotajs', 'id_lietotajs');
    }

    public function komentari()
    {
        return $this->hasMany(Komentars::class, 'id_raksts', 'id_raksts');
    }

    public function balsojumi()
    {
        return $this->hasMany(BalsojumsRaksts::class, 'id_raksts', 'id_raksts');
    }
}