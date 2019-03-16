<?php

namespace App\Http\Controllers;

use App\Lib\BitcoinPriceResolver;
use App\Models\Balance;
use App\Models\Bitcoin;
use App\Models\Offer;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Http\Request;

class TradeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @param BitcoinPriceResolver $bitcoinPriceResolver
     * @param Request $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function store(BitcoinPriceResolver $bitcoinPriceResolver, Request $request)
    {
        $offer_id = (int)$request->post('offer_id');
        $fiatAmount = round((float)$request->post('amount'), 2);

        /** @var Offer $offer */
        $offer = Offer::findOrFail($offer_id);
        /** @var User $offerUser */
        $offerUser = User::findOrFail($offer->user_id);
        $offerUserSatoshiBalance = $offerUser->getBalance(true);

        if ($offer->status !== Offer::STATUS_ENABLED || $offer->user_id === auth()->user()->id) {
            return redirect()->route('dashboard');
        }

        $oneBtcPrice = $bitcoinPriceResolver->getPrice($offer->fiat);
        $satoshiAmount = round($fiatAmount / $oneBtcPrice * Bitcoin::SATOSHI);

        if ($offerUserSatoshiBalance < $satoshiAmount) {
            $satoshiAmount = $offerUserSatoshiBalance;
            $fiatAmount = round($satoshiAmount * $oneBtcPrice / Bitcoin::SATOSHI, 2);
        }
        if ($fiatAmount > 0) {
            $trade = new Trade();
            $trade->offer_id = $offer->id;
            $trade->user_id = auth()->user()->id;
            $trade->amount_satoshi = $satoshiAmount;
            $trade->amount_fiat = $fiatAmount;
            $trade->status = Trade::STATUS_ACTIVE;
            $trade->save();
        }

        return redirect()->route('dashboard');
    }

    /**
     * Show the application dashboard.
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function update($id, Request $request)
    {
        $id = (int)$id;
        /** @var Trade $trade */
        $trade = Trade::findOrFail($id);
        if ($trade->offer->user_id !== auth()->user()->id) {
            return redirect()->route('dashboard');
        }
        switch ($action = $request->get('action')) {
            case Trade::ACTION_SELL:
                \DB::transaction(function() use ($id) {
                    /** @var Trade $trade */
                    $trade = Trade::findOrFail($id);
                    if ($trade->status == Trade::STATUS_ACTIVE && $trade->amount_satoshi <= $trade->offer->user->getBalance(true)) {
                        $sellerId = $trade->offer->user_id;
                        $buyerId = $trade->user_id;
                        $buyerBalance = new Balance([
                            'user_id' => $buyerId,
                            'amount' => $trade->amount_satoshi,
                            'type' => Balance::TYPE_TRANSACTION,
                        ]);
                        $buyerBalance->save();
                        $sellerBalance = new Balance([
                            'user_id' => $sellerId,
                            'amount' => -$trade->amount_satoshi,
                            'type' => Balance::TYPE_TRANSACTION,
                        ]);
                        $sellerBalance->save();
                        $trade->status = Trade::STATUS_CLOSED;
                        $trade->save();
                    } else {
                        // show message "not enough BTC"
                    }
                });
                break;
            case Trade::ACTION_CANCEL:
                if ($trade->status == Trade::STATUS_ACTIVE) {
                    $trade->status = Trade::STATUS_CANCELLED;
                    $trade->save();
                }

                break;
        }

        return redirect()->route('dashboard');
    }
}
