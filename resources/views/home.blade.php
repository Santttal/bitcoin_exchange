@extends('layouts.app')



@section('content')
@if(\count($offers))
    <div class="container mb-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">Offers</div>
                    <div class="card-body">
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
                                    <td>
                                        <form method="POST">
                                            <div class="form-group form-check-inline">
                                                <input type="number" class="form-control mr-2" name="amount">
                                                <button type="submit" class="btn btn-primary">buy</button>
                                            </div>
                                        </form>

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
<div class="container mb-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Trades</div>
                <div class="card-body">
                </div>
            </div>
        </div>
    </div>
</div>
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
