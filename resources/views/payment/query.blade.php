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
        <form id="query-form" action="{{ route('payment.query') }}" method="POST">
            {{ csrf_field() }}
            <div class="form-group row">
                <div class="col-sm-6">
                    <label for="customer_name">Customer Name</label>
                    {{ Form::text('customer_name', null, ['class' => 'form-control']) }}
                </div>
                <div class="col-sm-6">
                    <label for="transaction_id">Transaction ID</label>
                    {{ Form::text('transaction_id', null, ['class' => 'form-control']) }}
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
            <br /><br />

        <table id="query-table" class="table" style="display: none;">
            <tr>
                <th>Name</th>
                <td rel="customer_name"></td>
            </tr>
            <tr>
                <th>Phone</th>
                <td rel="customer_phone"></td>
            </tr>
            <tr>
                <th>Curreny</th>
                <td rel="currency"></td>
            </tr>
            <tr>
                <th>Price</th>
                <td rel="amount"></td>
            </tr>
        </table>
    </div>
@endsection