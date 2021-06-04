<?php

namespace App\Laravue\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Errors
 *
 * @property string $error_name
 * @property string $error_status
 * @property string $name
 * @property string $ip
 * @property string $port
 * @property string $endpoint
 * @property string $server_type
 * @property string $request_type
 * @property string $success
 * @property integer $error_count
 * 
 *
 * @method static Errors create(array $errors)
 * @package App
 */
class Errors extends Model
{
   
    protected $fillable = ['error_name','error_status','name','ip','port','endpoint','server_type','request_type','success','error_count'];
    protected $table = 'error_logs';
   
}
