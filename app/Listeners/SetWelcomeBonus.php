<?php

namespace App\Listeners;

use App\Models\Balance;
use App\Models\Bitcoin;
use Illuminate\Auth\Events\Registered;

class SetWelcomeBonus
{
    const AMOUNT = 5;

    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Registered  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        $balance = new Balance();
        $balance->user_id = $event->user->id;
        $balance->amount = self::AMOUNT * Bitcoin::SATOSHI;
        $balance->type = Balance::TYPE_BONUS;

        $balance->save();
    }
}
