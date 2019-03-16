<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property int user_id
 * @property int amount
 * @property string type
 */
class Balance extends Model
{
    use SoftDeletes;

    const TYPE_BONUS = 'bonus';
    const TYPE_TRANSACTION = 'transaction';

    protected $fillable = [
        'user_id', 'amount', 'type',
    ];
}
