<?php

namespace App\Http\Controllers;

use App\Lib\BitcoinPriceResolver;
use App\Models\Fiat;
use App\Models\Offer;
use App\Models\PaymentMethod;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @param BitcoinPriceResolver $bitcoinPriceResolver
     * @param Request $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(BitcoinPriceResolver $bitcoinPriceResolver, Request $request)
    {
        $offersQuery = $this->queryFromRequest($request);
        if ($user = auth()->user()) {
            $offersQuery->where('user_id', '<>', $user->id);
        }
        $offersQuery->where('status', Offer::STATUS_ENABLED);
        $offers = $offersQuery->get()->toArray();
        $offers = $this->addOfferData($bitcoinPriceResolver, $offers);

        if ($user) {
            $myOffersQuery = Offer::query();
            $myOffersQuery->where('user_id', $user->id);
            $myOffers = $myOffersQuery->get()->toArray();
            $myOffers = $this->addOfferData($bitcoinPriceResolver, $myOffers);

            $tradeQuery = Trade::query()->where('status', Trade::STATUS_ACTIVE);
            $tradeQuery->with(['offer', 'user', 'offer.paymentMethod', 'offer.user']);
            if (\count($myOffers)) {
                $tradeQuery->whereRaw('(user_id = ? OR offer_id IN (?))', [$user->id, implode(',', array_column($myOffers, 'id'))]);
            } else {
                $tradeQuery->where('user_id', $user->id);
            }

            $trades = $tradeQuery->get();

        } else {
            $myOffers = [];
            $trades = [];
        }

        return view(
            'home',
            [
                'offers' => $offers,
                'trades' => $trades,
                'myOffers' => $myOffers,
                'fiats' => Fiat::availableCurrencies(),
                'payment_methods' => PaymentMethod::all(['id', 'name']),
            ]
        );
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

    private function queryFromRequest(Request $request)
    {
        $query = Offer::query();
        $fiats = Fiat::availableCurrencies();
        $paymentMethods = PaymentMethod::all('id')->pluck('id')->toArray();
        if ($amount = (float)$request->get('amount')) {
            $query->where('min_fiat', '<=', $amount);
            $query->where('max_fiat', '>=', $amount);
        }

        if (\in_array($request->get('fiat'), $fiats)) {
            $query->where('fiat', $request->get('fiat'));
        }

        if (\in_array($request->get('payment_method'), $paymentMethods)) {
            $query->where('payment_method_id', $request->get('payment_method'));
        }

        return $query;
    }
}
