<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'preson_users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'nip_sapk', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
  	 * One to Many relation
  	 *
  	 * @return Illuminate\Database\Eloquent\Relations\BelongsTo
  	 */
  	public function role()
  	{
  		return $this->belongsTo('App\Models\Role');
  	}

    public function skpd()
    {
      return $this->belongsTo('App\Models\Skpd');
    }
}
