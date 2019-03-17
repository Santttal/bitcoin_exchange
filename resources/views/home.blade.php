@extends('layouts.app')
@section('content')
    @include('flash::message')
<div class="container mb-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Offers</div>
                <div class="card-body">
                    <form class="form-inline" action="{{ route('dashboard') }}">
                        <div class="input-group mb-2 mr-sm-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text">Amount</div>
                            </div>
                            <input type="text" class="form-control" name="amount" value="{{ request('amount') }}">
                        </div>
                        <div class="input-group mb-2 mr-sm-2">
                            <label class="my-1 mr-2" for="fiat_input">Currency</label>
                            <select name="fiat" class="custom-select my-1 mr-sm-2" id="fiat_input">
                                <option value="">Choose...</option>
                                @foreach($fiats as $fiat)
                                    <option {{ (request('fiat') == $fiat ? 'selected':'') }}>{{ $fiat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-group mb-2 mr-sm-2">
                            <label class="my-1 mr-2" for="payment_method_input">Payment method</label>
                            <select name="payment_method" class="custom-select my-1 mr-sm-2" id="payment_method_input">
                                <option value="">Choose...</option>
                                @foreach($payment_methods as $payment_method)
                                    <option value="{{ $payment_method->id }}" {{ (request('payment_method') == $payment_method->id  ? 'selected':'') }}>{{ $payment_method->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mb-2">Filter</button>
                    </form>
                    @if(\count($offers))
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th scope="col">Owner</th>
                            <th scope="col">Payment method</th>
                            <th scope="col">Min/max amount</th>
                            <th scope="col">price per 1 BTC</th>
                            @auth
                            <th scope="col">actions</th>
                            @endauth
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($offers as $offer)
                            <tr>
                                <td>{{ $offer['owner'] }}</td>
                                <td>{{ $offer['payment_method'] }}</td>
                                <td>{{ $offer['min_fiat'] }} - {{ $offer['max_fiat'] }} {{ $offer['fiat'] }}</td>
                                <td>{{ $offer['price'] }} {{ $offer['fiat'] }}</td>
                                @auth
                                    <td>
                                        <form method="POST" action="{{ route('trades.store') }}">
                                            @csrf
                                            <div class="form-group form-check-inline">
                                                <input type="number" class="form-control mr-2" name="amount">
                                                <input type="hidden" name="offer_id" value="{{ $offer['id'] }}">
                                                <button type="submit" class="btn btn-primary">buy</button>
                                            </div>
                                        </form>
                                    </td>
                                @endauth
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @else
                        <div>No offers found</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(\count($trades))
<div class="container mb-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Trades</div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Partner</th>
                            <th scope="col">Amount(fiat)</th>
                            <th scope="col">Amount(BTC)</th>
                            <th scope="col">Payment method</th>
                            <th scope="col">Status</th>
                            <th scope="col">Started at</th>
                            <th scope="col">actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($trades as $trade)
                            <tr>
                                <td>{{ $trade->id }}</td>
                                <td>
                                    @if($trade->user->id === auth()->user()->id)
                                        {{ $trade->offer->user->name }}
                                    @else
                                        {{ $trade->user->name }}
                                    @endif
                                </td>
                                <td>{{ $trade->amount_fiat }} {{ $trade->offer->fiat }}</td>
                                <td>{{ $trade->amount_satoshi / \App\Models\Bitcoin::SATOSHI }}</td>
                                <td>{{ $trade->offer->paymentMethod->name }}</td>
                                <td>{{ $trade->status }}</td>
                                <td>{{ $trade->created_at }}</td>
                                <td>
                                    @if($trade->offer->user->id === auth()->user()->id)
                                        <div class="form-group form-check-inline">
                                        <form method="POST" action="{{ route('trades.update', $trade->id) }}">
                                            @csrf
                                            {{ method_field('PUT') }}
                                            <div class="form-group form-check-inline">
                                                <input type="hidden" name="trade_id" value="{{ $trade->id }}">
                                                <input type="hidden" name="action" value="{{ \App\Models\Trade::ACTION_SELL }}">
                                                <button type="submit" class="btn btn-success">sell</button>
                                            </div>
                                        </form>
                                        <form method="POST" action="{{ route('trades.update', $trade->id) }}">
                                            @csrf
                                            {{ method_field('PUT') }}
                                            <div class="form-group form-check-inline">
                                                <input type="hidden" name="trade_id" value="{{ $trade->id }}">
                                                <input type="hidden" name="action" value="{{ \App\Models\Trade::ACTION_CANCEL }}">
                                                <button type="submit" class="btn btn-danger">cancel</button>
                                            </div>
                                        </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@if(\count($myOffers))
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">My offers</div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th scope="col">Payment method</th>
                                <th scope="col">Min/max amount</th>
                                <th scope="col">price per 1 BTC</th>
                                @auth
                                <th scope="col">actions</th>
                                @endauth
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($myOffers as $offer)
                                <tr>
                                    <td>{{ $offer['payment_method'] }}</td>
                                    <td>{{ $offer['min_fiat'] }} - {{ $offer['max_fiat'] }} {{ $offer['fiat'] }}</td>
                                    <td>{{ $offer['price'] }} {{ $offer['fiat'] }}</td>
                                    <td>
                                        <div class="form-group form-check-inline">
                                            <form>
                                                <button class="btn btn-primary mr-2">
                                                    <a class="text-white" href="{{ route('offers.edit', [$offer['id']]) }}">edit</a>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('offers.destroy', $offer['id']) }}">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}
                                                <button type="submit" class="btn btn-primary mr-2">delete</button>
                                            </form>
                                            <form method="POST" action="{{ route('offers.change-status') }}">
                                                {{ csrf_field() }}
                                                {{ method_field('PUT') }}
                                                <input type="hidden" name="id" value="{{ $offer['id'] }}">
                                                @if($offer['status'] === \App\Models\Offer::STATUS_ENABLED)
                                                    <button type="submit" class="btn btn-danger">disable</button>
                                                @else
                                                    <button type="submit" class="btn btn-success">enable</button>
                                                @endif

                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection
