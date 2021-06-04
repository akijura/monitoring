<?php

namespace App\Laravue\Models;


use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

/**
 * Class Sms
 *
 * @property string $name
 * @property string $description
 * @property string $permission
 *
 * @method static Sms create(array $sms)
 * @package App
 */
class Sms extends Model
{
    use Notifiable, HasRoles, HasApiTokens;
   
    protected $fillable = ['phone_number','user_id','server_id'];
}
