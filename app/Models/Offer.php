<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property int user_id
 * @property float margin
 * @property string fiat
 * @property int payment_method_id
 * @property string status
 * @property float min_fiat
 * @property float max_fiat
 */
class Offer extends Model
{
    use SoftDeletes;

    const STATUS_ENABLED = 'enabled';
    const STATUS_DISABLED = 'disabled';

    public function paymentMethod()
    {
        return $this->hasOne(PaymentMethod::class, 'id', 'payment_method_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
