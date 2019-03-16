<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property int offer_id
 * @property int user_id
 * @property int amount_satoshi
 * @property float amount_fiat
 * @property string status
 */
class Trade extends Model
{
    use SoftDeletes;

    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';
    const STATUS_CANCELLED = 'cancelled';

    const ACTION_SELL = 'sell';
    const ACTION_CANCEL = 'cancel';

    public function offer() {
        return $this->hasOne(Offer::class, 'id', 'offer_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
