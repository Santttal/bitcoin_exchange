@extends('layouts.app')

@push('js')
<script>
    var app = new window.Vue({
        el: '#app',
        data: {
            fiat: '{{ $offer->fiat }}',
            btc_prices: {!! json_encode($btcPrices) !!},
            payment_method: {{ $offer->payment_method_id }},
            margin: {{ $offer->margin }}
        },
        methods: {
            caclulate_offer: function () {
                if (!this.btc_prices[this.fiat]) {
                    return 'Fill data to calculate offer';
                }
                var price = this.btc_prices[this.fiat];
                var margin = parseFloat(this.margin);
                if (!isNaN(margin)) {
                    price = price + price * margin / 100;
                }

                return Math.round(price * 100) / 100 + ' ' + this.fiat;
            }
        }
    });
</script>

@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">New offer</div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('update-offer', $offer->id) }}">
                        @csrf
                        {{ method_field('PUT') }}
                        <div class="form-group">
                            <label for="fiat">Fiat currency</label>
                            <select class="form-control" name="fiat" id="fiat" v-model="fiat">
                                <option value="">(select value)</option>
                                @foreach ($fiats as $fiat)
                                    <option {{ ($offer->fiat == $fiat ? 'selected':'') }}>{{ $fiat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="payment_method">Payment method</label>
                            <select class="form-control" name="payment_method" id="payment_method" v-model="payment_method">
                                <option value="">(select value)</option>
                                @foreach ($payments as $payment)
                                    <option value="{{ $payment->id }}" {{ $offer->payment_method_id == $payment->id ? 'selected':'' }}>{{ $payment->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="margin">Margin (%)</label>
                            <input type="number" class="form-control" name="margin" id="margin" value="{{ $offer->margin }}" v-model="margin">
                        </div>
                        <div class="form-group">
                            <label for="margin">Min fiat</label>
                            <input type="number" class="form-control" name="min_fiat" id="min_fiat" value="{{ $offer->min_fiat }}">
                        </div>
                        <div class="form-group">
                            <label for="margin">Max fiat</label>
                            <input type="number" class="form-control" name="max_fiat" id="max_fiat" value="{{ $offer->max_fiat }}">
                        </div>
                        <div class="form-group">
                            <div>
                                Final offer price: @{{ caclulate_offer() }}
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
