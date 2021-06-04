<?php

namespace App\Laravue\Models;


use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

/**
 * Class ServerTypes
 *
 * @property string $name
 * @property string $description
 * @property string $permission
 *
 * @method static Servertypes create(array $servers)
 * @package App
 */
class ServerTypes extends Model
{
    use Notifiable, HasRoles, HasApiTokens;
   
    protected $fillable = ['name','description','permission'];
}
