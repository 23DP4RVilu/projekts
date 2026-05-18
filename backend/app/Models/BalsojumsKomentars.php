<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
 
class BalsojumsKomentars extends Model
{
    protected $table      = 'balsojums_komentars';
    protected $primaryKey = null;
    public    $incrementing = false;
    public    $timestamps   = false;
 
    protected $fillable = ['id_lietotajs', 'id_komentars', 'tips'];
}