<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
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
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getBalance($satoshi = false)
    {
        $balance = (int)\DB::table('balances')
            ->select(\DB::raw('IFNULL(sum(amount),0) as sum'))
            ->where('user_id', $this->id)
            ->pluck('sum')
            ->first();

        if (!$satoshi) {
            return $balance / Bitcoin::SATOSHI;
        } else {
            return $balance;
        }
    }
}
