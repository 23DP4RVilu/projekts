<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
 
class BalsojumsRaksts extends Model
{
    protected $table      = 'balsojums_raksts';
    protected $primaryKey = null;
    public    $incrementing = false;
    public    $timestamps   = false;
 
    protected $fillable = ['id_lietotajs', 'id_raksts', 'tips'];
}
 