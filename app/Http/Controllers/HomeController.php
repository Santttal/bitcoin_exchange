<?php

namespace App\Http\Controllers;

use App\Lib\BitcoinPriceResolver;
use App\Models\Offer;
use App\Models\PaymentMethod;
use App\Models\Trade;
use App\Models\User;

class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @param BitcoinPriceResolver $bitcoinPriceResolver
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(BitcoinPriceResolver $bitcoinPriceResolver)
    {
        $offersQuery = Offer::query();
        if ($user = auth()->user()) {
            $offersQuery->where('user_id', '<>', $user->id);
        }
        $offersQuery->where('status', Offer::STATUS_ENABLED);
        $offers = $offersQuery->get()->toArray();
        $offers = $this->addOfferData($bitcoinPriceResolver, $offers);

        if ($user) {
            $trades = Trade::query()
                ->where('status', Trade::STATUS_ACTIVE)
                ->whereIn('offer_id', array_column($offers, 'id'), 'OR')
                ->with(['offer', 'user', 'offer.paymentMethod', 'offer.user'])
                ->get()
            ;

            $myOffersQuery = Offer::query();
            $myOffersQuery->where('user_id', $user->id);
            $myOffers = $myOffersQuery->get()->toArray();
            $myOffers = $this->addOfferData($bitcoinPriceResolver, $myOffers);
        } else {
            $myOffers = [];
            $trades = [];
        }

        return view('home', ['offers' => $offers, 'trades' => $trades, 'myOffers' => $myOffers]);
    }

    private function addOfferData(BitcoinPriceResolver $bitcoinPriceResolver, array $offers): array
    {
        foreach ($offers as &$offer) {
            $offer['owner'] = User::findOrFail($offer['user_id'])->name;
            $offer['payment_method'] = PaymentMethod::findOrFail($offer['payment_method_id'])->name;
            $offer['price'] = round($bitcoinPriceResolver->getPrice($offer['fiat']) * (1 + $offer['margin'] / 100), 2);
        }
        unset($offer);

        return $offers;
    }
}
