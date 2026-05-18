<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'logs';
    protected $primaryKey = 'id_log';
    public $timestamps = false;
 
    protected $fillable = ['id_lietotajs', 'darbiba', 'tabula'];
}
 