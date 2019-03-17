<?php

namespace App\Http\Controllers;

use App\Http\Requests\OfferFormRequest;
use App\Lib\BitcoinPriceResolver;
use App\Lib\Fiat;
use App\Models\Offer;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class OfferController extends Controller
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
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function create(BitcoinPriceResolver $bitcoinPriceResolver)
    {
        $prices = [];
        foreach (Fiat::availableCurrencies() as $currency) {
            $prices[$currency] = $bitcoinPriceResolver->getPrice($currency);
        }

        return view(
            'create_offer',
            [
                'fiats' => Fiat::availableCurrencies(),
                'payments' => PaymentMethod::all(['id', 'name']),
                'btcPrices' => $prices,
            ]
        );
    }

    /**
     * Show the application dashboard.
     *
     * @param OfferFormRequest $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function store(OfferFormRequest $request)
    {
        $offer = new Offer();
        $offer->user_id = auth()->user()->id;
        $offer->margin = $request->post('margin');
        $offer->fiat = $request->post('fiat');
        $offer->payment_method_id = $request->post('payment_method');
        $offer->status = Offer::STATUS_ENABLED;
        $offer->min_fiat = $request->post('min_fiat');
        $offer->max_fiat = $request->post('max_fiat');

        $offer->save();
        flash('Offer created')->success();

        return redirect()->route('dashboard');
    }

    public function destroy($id)
    {
        /** @var Offer $offer */
        $offer = Offer::findOrFail((int)$id);
        if ($offer->user_id === auth()->user()->id) {
            $offer->delete();
            flash('Offer removed')->success();
        }

        return redirect()->route('dashboard');
    }

    public function edit($id, BitcoinPriceResolver $bitcoinPriceResolver)
    {
        $id = (int)$id;
        /** @var Offer $offer */
        $offer = Offer::findOrFail($id);
        if ($offer->user_id !== auth()->user()->id) {
            return redirect()->route('dashboard');
        }

        $prices = [];
        foreach (Fiat::availableCurrencies() as $currency) {
            $prices[$currency] = $bitcoinPriceResolver->getPrice($currency);
        }

        return view(
            'edit_offer',
            [
                'fiats' => Fiat::availableCurrencies(),
                'payments' => PaymentMethod::all(['id', 'name']),
                'btcPrices' => $prices,
                'offer' => $offer,
            ]
        );
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
        flash('Offer updated')->success();

        return redirect()->route('dashboard');
    }

    public function changeStatus(Request $request)
    {
        /** @var Offer $offer */
        $offer = Offer::findOrFail((int)$request->get('id'));

        if ($offer->user_id === auth()->user()->id) {
            if ($offer->status === Offer::STATUS_ENABLED) {
                $offer->status = Offer::STATUS_DISABLED;
                flash('Offer disabled')->success();
            } else {
                $offer->status = Offer::STATUS_ENABLED;
                flash('Offer enabled')->success();
            }
        }

        $offer->save();

        return redirect()->route('dashboard');
    }
}
