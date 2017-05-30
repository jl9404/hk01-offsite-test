@extends('layouts.master')

@section('content')

    <div class="container">
        @if (count($errors) > 0)
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    <form id="payment-form" action="{{ route('payment.store') }}" method="POST">
        {{ csrf_field() }}
        <div class="form-group row">
            <div class="col-sm-6">
                <label for="customer_name">Customer Name</label>
                {{ Form::text('customer_name', null, ['class' => 'form-control']) }}
            </div>
            <div class="col-sm-6">
                <label for="customer_phone">Customer Phone</label>
                {{ Form::text('customer_phone', null, ['class' => 'form-control']) }}
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-12">
                <label for="price">Price</label>
                <div class="input-group">
                    <div class="input-group-addon">
                        {{ Form::select('currency', array_combine($currency = ['HKD', 'USD', 'AUD', 'EUR', 'JPY', 'CNY'], $currency), null) }}
                    </div>
                    {{ Form::number('amount', null, ['class' => 'form-control', 'placeholder' => '10.00',  'step' => '0.01', 'min' => '0.01']) }}
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-12">
                <label for="ccname">Credit Card Holder Name</label>
                {{ Form::text('ccname', null, ['class' => 'form-control']) }}
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-12">
                <label for="ccnumber">Credit Card Number</label>
                {{ Form::text('ccnumber', null, ['class' => 'form-control']) }}
            </div>
        </div>
        <div class="form-group row">
            <div class="col-sm-3">
                <label for="ccmonth">Exp. Month</label>
                {{ Form::selectMonth('ccmonth', null,  ['class' => 'form-control'], '%m') }}
            </div>
            <div class="col-sm-3">
                <label for="ccyear">Exp. Month</label>
                {{ Form::select('ccyear', array_combine($year = range(date('Y'), date('Y') + 10), $year), null, ['class' => 'form-control']) }}
            </div>
            <div class="col-sm-6">
                <label for="cvv">CVV</label>
                <input type="text" class="form-control" id="cvv" name="cvv" />
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
    </div>


@endsection