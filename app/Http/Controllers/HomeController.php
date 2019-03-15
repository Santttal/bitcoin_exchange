<?php

namespace App\Http\Controllers;

use App\Lib\BitcoinPriceResolver;
use App\Models\Offer;
use App\Models\PaymentMethod;
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
        $offers = $this->addPresentedData($bitcoinPriceResolver, $offers);

        if ($user) {
            $myOffersQuery = Offer::query();
            $myOffersQuery->where('user_id', $user->id);
            $myOffers = $myOffersQuery->get()->toArray();
            $myOffers = $this->addPresentedData($bitcoinPriceResolver, $myOffers);
        } else {
            $myOffers = [];
        }


        return view('home', ['offers' => $offers, 'myOffers' => $myOffers]);
    }

    private function addPresentedData(BitcoinPriceResolver $bitcoinPriceResolver, array $offers): array
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
