<?php

namespace App\Laravue\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Servers
 *
 * @property string $name
 * @property string $type
 * @property string $ip
 * @property string $port
 * @property string $login
 * @property string $password
 * @property string $server_type
 * @property string $endpoint
 * @property string $status
 * @property string $statusText
 * @property string $request_type
 * 
 *
 * @method static Servers create(array $servers)
 * @package App
 */
class Servers extends Model
{
   
    protected $fillable = ['name','type','ip','port','login','password','server_type','endpoint','status','statusText','request_type'];
   
}
