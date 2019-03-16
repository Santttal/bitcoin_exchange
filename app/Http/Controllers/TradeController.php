<?php

namespace App\Http\Controllers;

use App\Http\Requests\OfferFormRequest;
use App\Lib\BitcoinPriceResolver;
use App\Lib\Fiat;
use App\Models\Bitcoin;
use App\Models\Offer;
use App\Models\PaymentMethod;
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
        $fiatAmount = (float)$request->post('amount');

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
     * @param OfferFormRequest $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function update($id, OfferFormRequest $request)
    {
        $id = (int)$id;
        $offer = Offer::findOrFail($id);
        if ($offer->user_id !== auth()->user()->id) {
            return redirect()->route('dashboard');
        }
        $offer->margin = $request->post('margin');
        $offer->fiat = $request->post('fiat');
        $offer->payment_method_id = $request->post('payment_method');
        $offer->min_fiat = $request->post('min_fiat');
        $offer->max_fiat = $request->post('max_fiat');

        $offer->save();

        return redirect()->route('dashboard');
    }
}
